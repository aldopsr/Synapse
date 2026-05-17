import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/constants.dart';

class AuthService {

  // --- 1. LOGIN ---
  Future<bool> login(String email, String password) async {
    try {
      final response = await http.post(
        Uri.parse(AppConstants.loginUrl),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({'email': email, 'password': password}),
      );

      if (response.statusCode == 200) {
        final data     = jsonDecode(response.body);
        final token    = data['token'];
        final userData = data['user'];

        SharedPreferences prefs = await SharedPreferences.getInstance();
        await prefs.setString('token', token);

        // Simpan data user juga — biar tidak perlu hit API terus
        if (userData != null) {
          await prefs.setString('user', jsonEncode(userData));
        }

        return true;
      } else {
        debugPrint('Gagal Login: ${response.body}');
        return false;
      }
    } catch (e) {
      debugPrint('Error Koneksi Login: $e');
      return false;
    }
  }

  // --- 2. REGISTER ---
  Future<bool> register({
    required String name,
    required String email,
    required String password,
    required String role,
    String? nim,
    String? kelas,
  }) async {
    try {
      final response = await http.post(
        Uri.parse(AppConstants.registerUrl),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'name': name,
          'email': email,
          'password': password,
          'password_confirmation': password,
          'role': role,
          'nim': nim ?? '',
          'kelas': kelas ?? '',
        }),
      );

      return response.statusCode == 200 || response.statusCode == 201;
    } catch (e) {
      debugPrint('Error Koneksi Register: $e');
      return false;
    }
  }

  // --- 3. VERIFIKASI OTP ---
  Future<bool> verifyEmail(String email, String otp) async {
    try {
      final response = await http.post(
        Uri.parse(AppConstants.verifyOtpUrl),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({'email': email, 'otp': otp}),
      );

      return response.statusCode == 200;
    } catch (e) {
      debugPrint('Error Koneksi Verifikasi: $e');
      return false;
    }
  }

  // --- 4. RESEND OTP ---
  Future<bool> resendOTP(String email) async {
    try {
      final response = await http.post(
        Uri.parse(AppConstants.resendOtpUrl),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({'email': email}),
      );

      return response.statusCode == 200;
    } catch (e) {
      debugPrint('Error Koneksi Resend OTP: $e');
      return false;
    }
  }

  // --- 5. GET PROFILE ---
  Future<Map<String, dynamic>?> getUserProfile() async {
    SharedPreferences prefs = await SharedPreferences.getInstance();
    final token   = prefs.getString('token');
    final userStr = prefs.getString('user');

    if (token == null) return null;

    // Pakai data yang tersimpan dulu (lebih cepat)
    if (userStr != null) {
      try {
        return jsonDecode(userStr);
      } catch (_) {}
    }

    // Kalau tidak ada, baru ambil dari server
    try {
      final response = await http.get(
        Uri.parse(AppConstants.getMeUrl),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      if (response.statusCode == 200) {
        final userData = jsonDecode(response.body);
        await prefs.setString('user', jsonEncode(userData));
        return userData;
      }
      return null;
    } catch (e) {
      debugPrint('Error Get Profile: $e');
      return null;
    }
  }

  // --- 6. LOGOUT ---
  Future<bool> logout() async {
    SharedPreferences prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');

    if (token != null) {
      try {
        await http.post(
          Uri.parse(AppConstants.logoutUrl),
          headers: {
            'Accept': 'application/json',
            'Authorization': 'Bearer $token',
          },
        );
      } catch (_) {}
    }

    // Hapus semua data lokal
    await prefs.clear();
    return true;
  }
}