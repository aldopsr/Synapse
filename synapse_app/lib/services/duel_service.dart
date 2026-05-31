// lib/services/duel_service.dart (versi lengkap)
import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/constants.dart';

class DuelService {
  Future<Map<String, String>> _headers() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');
    return {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  String get _base => AppConstants.baseUrl;

  // Tantang user
  Future<Map<String, dynamic>?> challenge({
    required String quizId,
    required String identifier,
    required String searchBy, // 'nim' | 'duel_code'
  }) async {
    try {
      final res = await http.post(
        Uri.parse('$_base/duels/challenge'),
        headers: await _headers(),
        body: jsonEncode({
          'quiz_id':             quizId,
          'opponent_identifier': identifier,
          'search_by':           searchBy,
        }),
      );
      return jsonDecode(res.body);
    } catch (e) {
      debugPrint('challenge error: $e');
      return null;
    }
  }

  // Respond tantangan
  Future<Map<String, dynamic>?> respond(
      String duelId, String action) async {
    try {
      final res = await http.post(
        Uri.parse('$_base/duels/$duelId/respond'),
        headers: await _headers(),
        body: jsonEncode({'action': action}),
      );
      return jsonDecode(res.body);
    } catch (e) {
      debugPrint('respond error: $e');
      return null;
    }
  }

  // Tandai siap battle
  Future<Map<String, dynamic>?> markReady(String duelId) async {
    try {
      final res = await http.post(
        Uri.parse('$_base/duels/$duelId/ready'),
        headers: await _headers(),
      );
      return jsonDecode(res.body);
    } catch (e) {
      debugPrint('markReady error: $e');
      return null;
    }
  }

  // Batalkan tantangan
  Future<bool> cancelDuel(String duelId) async {
    try {
      final res = await http.delete(
        Uri.parse('$_base/duels/$duelId/cancel'),
        headers: await _headers(),
      );
      return res.statusCode == 200;
    } catch (e) {
      debugPrint('cancelDuel error: $e');
      return false;
    }
  }

  // Ambil soal
  Future<Map<String, dynamic>?> getQuestions(String duelId) async {
    try {
      final res = await http.get(
        Uri.parse('$_base/duels/$duelId/questions'),
        headers: await _headers(),
      );
      return jsonDecode(res.body);
    } catch (e) {
      debugPrint('getQuestions error: $e');
      return null;
    }
  }

  // Submit jawaban
  Future<Map<String, dynamic>?> submit(
    String duelId,
    int timeTaken,
    List<Map<String, dynamic>> answers,
  ) async {
    try {
      final res = await http.post(
        Uri.parse('$_base/duels/$duelId/submit'),
        headers: await _headers(),
        body: jsonEncode({
          'time_taken_seconds': timeTaken,
          'answers': answers,
        }),
      );
      return jsonDecode(res.body);
    } catch (e) {
      debugPrint('submit error: $e');
      return null;
    }
  }

  // Status duel
  Future<Map<String, dynamic>?> getStatus(String duelId) async {
    try {
      final res = await http.get(
        Uri.parse('$_base/duels/$duelId/status'),
        headers: await _headers(),
      );
      return jsonDecode(res.body);
    } catch (e) {
      debugPrint('getStatus error: $e');
      return null;
    }
  }

  // List duel aktif
  Future<List<dynamic>> getMyDuels() async {
    try {
      final res = await http.get(
        Uri.parse('$_base/duels'),
        headers: await _headers(),
      ).timeout(const Duration(seconds: 8));
      return jsonDecode(res.body)['data'] ?? [];
    } catch (e) {
      debugPrint('getMyDuels error: $e');
      return [];
    }
  }

  // Histori
  Future<List<dynamic>> getHistory() async {
    try {
      final res = await http.get(
        Uri.parse('$_base/duels/history'),
        headers: await _headers(),
      ).timeout(const Duration(seconds: 8));
      return jsonDecode(res.body)['data'] ?? [];
    } catch (e) {
      debugPrint('getHistory error: $e');
      return [];
    }
  }

  // Quiz list untuk duel
  Future<List<dynamic>> getQuizList() async {
    try {
      final res = await http.get(
        Uri.parse('$_base/duel-quizzes'),
        headers: await _headers(),
      ).timeout(const Duration(seconds: 8));
      return jsonDecode(res.body)['data'] ?? [];
    } catch (e) {
      debugPrint('getQuizList error: $e');
      return [];
    }
  }

  // Kode duel sendiri
  Future<Map<String, dynamic>?> getMyDuelCode() async {
    try {
      final res = await http.get(
        Uri.parse('$_base/user/duel-code'),
        headers: await _headers(),
      ).timeout(const Duration(seconds: 8));
      return jsonDecode(res.body);
    } catch (e) {
      debugPrint('getMyDuelCode error: $e');
      return null;
    }
  }
}