import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/constants.dart';

String getBaseUrl() => AppConstants.baseUrl;

class MaterialService {

  // ── Helper token ────────────────────────────────────────────
  Future<String?> _getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('token');
  }

  // ── Helper headers ───────────────────────────────────────────
  Future<Map<String, String>> _headers() async {
    final token = await _getToken();
    return {
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  // =====================================================================
  // 1. AMBIL SEMUA MATA KULIAH (public — tamu bisa akses)
  // =====================================================================
  Future<List<dynamic>> getCourses() async {
    try {
      final response = await http.get(
        Uri.parse('${getBaseUrl()}/public/courses'),
        headers: await _headers(),
      );

      if (response.statusCode == 200) {
        final decoded = jsonDecode(response.body);
        return (decoded['data'] ?? decoded) as List<dynamic>;
      }
      debugPrint('getCourses error: ${response.statusCode}');
      return [];
    } catch (e) {
      debugPrint('getCourses exception: $e');
      return [];
    }
  }

  // =====================================================================
  // 2. AMBIL MATERI PER MATKUL
  // =====================================================================
  Future<List<dynamic>> getMaterialsByCourse(String courseId) async {
    try {
      final response = await http.get(
        Uri.parse('${getBaseUrl()}/public/courses/$courseId/materials'),
        headers: await _headers(),
      );

      if (response.statusCode == 200) {
        final decoded = jsonDecode(response.body);
        return (decoded['data'] ?? decoded) as List<dynamic>;
      }
      debugPrint('getMaterialsByCourse error: ${response.statusCode}');
      return [];
    } catch (e) {
      debugPrint('getMaterialsByCourse exception: $e');
      return [];
    }
  }

  // =====================================================================
  // 3. AMBIL SEMUA MATERI (tab "Semua") —
  //    Fetch parallel dari semua course, gabungkan hasilnya
  // =====================================================================
  Future<List<dynamic>> getMaterials() async {
    try {
      // Ambil daftar course dulu
      final courses = await getCourses();
      if (courses.isEmpty) return [];

      // Fetch materi dari setiap course secara parallel
      final results = await Future.wait(
        courses.map((c) {
          final id = (c['_id'] ?? c['id'])?.toString() ?? '';
          if (id.isEmpty) return Future.value(<dynamic>[]);
          return getMaterialsByCourse(id);
        }),
      );

      // Gabungkan semua hasil
      final allMaterials = <dynamic>[];
      for (final list in results) {
        allMaterials.addAll(list);
      }
      return allMaterials;
    } catch (e) {
      debugPrint('getMaterials exception: $e');
      return [];
    }
  }
}