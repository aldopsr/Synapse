import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

String getBaseUrl() {
  if (kIsWeb) {
    return 'http://127.0.0.1:8000/api';
  }

  if (Platform.isAndroid) {
    const bool isEmulator =
        bool.fromEnvironment('ANDROID_EMULATOR', defaultValue: false);

    return isEmulator
        ? 'http://10.0.2.2:8000/api'
        : 'http://192.168.1.21:8000/api'; 
  }

  // iOS Simulator / Desktop
  return 'http://127.0.0.1:8000/api';
}

class AuthService {
  // --- 1. LOGIN ---
  Future<bool> login(String email, String password) async {
    try {
      final response = await http.post(
        Uri.parse('${getBaseUrl()}/auth/login'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'email': email,
          'password': password,
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final token = data['token'];

        SharedPreferences prefs = await SharedPreferences.getInstance();
        await prefs.setString('token', token);
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
        Uri.parse('${getBaseUrl()}/auth/register'),
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
        Uri.parse('${getBaseUrl()}/auth/verify-otp'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'email': email,
          'otp': otp,
        }),
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
        Uri.parse('${getBaseUrl()}/auth/resend-otp'),
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
    final token = prefs.getString('token');

    if (token == null) return null;

    try {
      final response = await http.get(
        Uri.parse('${getBaseUrl()}/auth/me'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
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
          Uri.parse('${getBaseUrl()}/auth/logout'),
          headers: {
            'Accept': 'application/json',
            'Authorization': 'Bearer $token',
          },
        );
      } catch (_) {}
    }

    await prefs.remove('token');
    return true;
  }
}