import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/constants.dart';

String getBaseUrl() => AppConstants.baseUrl;

class MaterialService {

  // =====================================================================
  // 1. FUNGSI BARU: AMBIL SEMUA MATA KULIAH (PUBLIC - Tamu bisa akses)
  // =====================================================================
  Future<List<dynamic>> getCourses() async {
    SharedPreferences prefs = await SharedPreferences.getInstance();
    String? token = prefs.getString('token');

    try {
      final response = await http.get(
        Uri.parse('${getBaseUrl()}/public/courses'),
        headers: {
          'Accept': 'application/json',
          if (token != null) 'Authorization': 'Bearer $token', 
        },
      );

      if (response.statusCode == 200) {
        final decodedData = jsonDecode(response.body);
        if (decodedData is Map && decodedData.containsKey('data')) {
          return decodedData['data'];
        }
        return decodedData as List<dynamic>;
      } else {
        debugPrint('Gagal Get Courses Status: ${response.statusCode}');
        return [];
      }
    } catch (e) {
      debugPrint('Error Get Courses: $e');
      return [];
    }
  }

  // =====================================================================
  // 2. FUNGSI BARU: AMBIL MATERI BERDASARKAN MATKUL (PUBLIC)
  // =====================================================================
  Future<List<dynamic>> getMaterialsByCourse(String courseId) async {
    SharedPreferences prefs = await SharedPreferences.getInstance();
    String? token = prefs.getString('token');

    try {
      final response = await http.get(
        Uri.parse('${getBaseUrl()}/public/courses/$courseId/materials'),
        headers: {
          'Accept': 'application/json',
          if (token != null) 'Authorization': 'Bearer $token',
        },
      );

      if (response.statusCode == 200) {
        final decodedData = jsonDecode(response.body);
        if (decodedData is Map && decodedData.containsKey('data')) {
          return decodedData['data'];
        }
        return decodedData as List<dynamic>;
      } else {
        debugPrint('Gagal Get Materials By Course Status: ${response.statusCode}');
        return [];
      }
    } catch (e) {
      debugPrint('Error Get Materials By Course: $e');
      return [];
    }
  }

  // =====================================================================
  // 3. FUNGSI LAMA: AMBIL SEMUA MATERI (Biarkan saja untuk jaga-jaga)
  // =====================================================================
  Future<List<dynamic>> getMaterials() async {
    SharedPreferences prefs = await SharedPreferences.getInstance();
    String? token = prefs.getString('token');

    try {
      final response = await http.get(
        Uri.parse('${getBaseUrl()}/materials'),
        headers: {
          'Accept': 'application/json',
          if (token != null) 'Authorization': 'Bearer $token', 
        },
      );

      if (response.statusCode == 200) {
        final decodedData = jsonDecode(response.body);
        if (decodedData is Map && decodedData.containsKey('data')) {
          return decodedData['data'];
        }
        return decodedData as List<dynamic>;
      } else {
        debugPrint('Gagal Get Materials Status: ${response.statusCode}');
        return [];
      }
    } catch (e) {
      debugPrint('Error Get Materials: $e');
      return [];
    }
  }
}