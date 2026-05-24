import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/constants.dart';

String getBaseUrl() => AppConstants.baseUrl;

class MaterialService {

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

  Future<List<dynamic>> getMaterials() async {
    try {
      final courses = await getCourses();
      if (courses.isEmpty) return [];

      final results = await Future.wait(
        courses.map((c) {
          final id = (c['_id'] ?? c['id'])?.toString() ?? '';
          if (id.isEmpty) return Future.value(<dynamic>[]);
          return getMaterialsByCourse(id);
        }),
      );

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