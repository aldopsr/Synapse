import 'dart:convert';
import 'dart:io' show Platform; // 👈 Tambahkan ini untuk deteksi Platform
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter/foundation.dart'; 

class AuthService {
  // 👇 IP Cerdas pendeteksi Platform
  static String get _getBaseUrl {
    if (kIsWeb) {
      return 'http://127.0.0.1:8000/api'; // Untuk Web
    } else if (Platform.isAndroid) {
      return 'http://10.0.2.2:8000/api';  // Untuk Emulator Android
    } else {
      return 'http://127.0.0.1:8000/api'; // Untuk iOS Simulator
    }
  }

  final String baseUrl = _getBaseUrl;

  // --- 1. LOGIN ---
  Future<bool> login(String email, String password) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/auth/login'),
        headers: {'Accept': 'application/json','Content-Type': 'application/json',},
        body: jsonEncode({                    // 👈 Bungkus dengan jsonEncode
          'email': email,
          'password': password,
          }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final token = data['token']; // Sesuaikan dengan key token di Laravel Kapten

        // Simpan kunci akses (Token) ke brankas HP (SharedPreferences)
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
        Uri.parse('$baseUrl/auth/register'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'name': name,
          'email': email,
          'password': password,
          'password_confirmation': password, // Laravel biasanya butuh ini
          'role': role,
          'nim': nim ?? '',
          'kelas': kelas ?? '',
        }),
      );

      // Status 201 = Created (Berhasil Dibuat), 200 = OK
      if (response.statusCode == 201 || response.statusCode == 200) {
        return true;
      } else {
        debugPrint('Gagal Register: ${response.body}');
        return false;
      }
    } catch (e) {
      debugPrint('Error Koneksi Register: $e');
      return false;
    }
  }

  // --- 3. VERIFIKASI OTP ---
  Future<bool> verifyEmail(String email, String otp) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/auth/verify-otp'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'email': email,
          'otp': otp,
        }),
      );

      if (response.statusCode == 200) {
        return true;
      } else {
        debugPrint('Gagal Verifikasi: ${response.body}');
        return false;
      }
    } catch (e) {
      debugPrint('Error Koneksi Verifikasi: $e');
      return false;
    }
  }

  // --- 4. KIRIM ULANG OTP ---
  Future<bool> resendOTP(String email) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/auth/resend-otp'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'email': email,
        }),
      );

      return response.statusCode == 200;
    } catch (e) {
      debugPrint('Error Koneksi Resend OTP: $e');
      return false;
    }
  }

  // --- 5. MENGAMBIL DATA PROFIL ---
  // --- 5. MENGAMBIL DATA PROFIL ---
  Future<Map<String, dynamic>?> getUserProfile() async {
    SharedPreferences prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');

    if (token == null) {
      debugPrint('🚨 TOKEN KOSONG! Berarti belum login atau token gagal disimpan.');
      return null;
    }

    try {
      debugPrint('🔑 Mencoba pakai Token: $token'); // Cek apakah tokennya benar-benar ada bentuknya

      final response = await http.get(
        Uri.parse('$baseUrl/auth/me'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      if (response.statusCode == 200) {
        debugPrint('✅ SUKSES AMBIL PROFIL: ${response.body}');
        return jsonDecode(response.body);
      } else {
        // 👇 INI YANG PALING PENTING! Kita tangkap alasan penolakan Laravel
        debugPrint('❌ GAGAL AMBIL PROFIL!');
        debugPrint('Status Code: ${response.statusCode}');
        debugPrint('Alasan Laravel: ${response.body}');
        return null;
      }
    } catch (e) {
      debugPrint('🔥 Error Koneksi Get User: $e');
      return null;
    }
  }

  // --- 6. LOGOUT PENGHANCUR TOKEN ---
  Future<bool> logout() async {
    SharedPreferences prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');

    if (token != null) {
      try {
        // Pamit dulu ke Laravel
        await http.post(
          Uri.parse('$baseUrl/auth/logout'),
          headers: {
            'Accept': 'application/json',
            'Authorization': 'Bearer $token',
          },
        );
      } catch (e) {
        debugPrint('API Logout Gagal (Abaikan saja): $e');
      }
    }

    // Wajib hancurkan kunci di HP meskipun internet mati!
    await prefs.remove('token');
    return true;
  }
}