// lib/services/fyp_service.dart
//
// Mengambil data analisis FYP dari endpoint GET /api/fyp.
// Tidak ada logika di sini — logika ada di Laravel.
// Flutter hanya menampilkan apa yang dikembalikan backend.

import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/constants.dart';

class FypService {
  Future<String?> _getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('token');
  }

  Future<Map<String, String>> _headers() async {
    final token = await _getToken();
    return {
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  /// Ambil data FYP. Return map dengan key 'summary' dan 'data'.
  /// Return null kalau gagal (network error, dsb).
  Future<Map<String, dynamic>?> getFyp() async {
    try {
      final response = await http.get(
        Uri.parse('${AppConstants.baseUrl}/fyp'),
        headers: await _headers(),
      );
      if (response.statusCode == 200) {
        final decoded = jsonDecode(response.body);
        return {
          'summary': decoded['summary'] ?? {},
          'data': decoded['data'] ?? [],
        };
      }
      debugPrint('FYP error: ${response.statusCode} ${response.body}');
      return null;
    } catch (e) {
      debugPrint('FYP exception: $e');
      return null;
    }
  }
}