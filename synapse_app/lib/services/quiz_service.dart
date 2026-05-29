import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../models/quiz_model.dart';
import '../models/question_model.dart';
import '../utils/constants.dart';

// 🔥 sama persis kayak AuthService
String getBaseUrl() => AppConstants.baseUrl;

class QuizService {
  Future<String?> _getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('token');
  }

  // 1. GET QUIZ
  Future<List<QuizModel>> getQuizzes({String? courseId}) async {
    final token = await _getToken();
    if (token == null) return [];

    try {
      final uri = courseId != null && courseId.isNotEmpty
          ? Uri.parse('${getBaseUrl()}/quizzes?course_id=$courseId')
          : Uri.parse('${getBaseUrl()}/quizzes');
      final response = await http.get(
        uri,
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        debugPrint('✅ KUIS: ${response.body}');
        final List data = jsonDecode(response.body)['data'];
        return data.map((e) => QuizModel.fromJson(e)).toList();
      } else {
        debugPrint('❌ Gagal Get Quiz: ${response.body}');
        return [];
      }
    } catch (e) {
      debugPrint('🔥 Error Get Quiz: $e');
      return [];
    }
  }

  // 2. GET QUESTIONS
  Future<List<QuestionModel>> getQuestions(String quizId) async {
    final token = await _getToken();
    if (token == null) return [];

    try {
      final response = await http.get(
        Uri.parse('${getBaseUrl()}/quizzes/$quizId/questions'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final List data = jsonDecode(response.body)['data'];
        return data.map((e) => QuestionModel.fromJson(e)).toList();
      } else {
        debugPrint('❌ Gagal Get Soal: ${response.body}');
        return [];
      }
    } catch (e) {
      debugPrint('🔥 Error Get Soal: $e');
      return [];
    }
  }

  // 3. 🌟 SUBMIT QUIZ — Sekarang return data lengkap (review per soal)
  Future<Map<String, dynamic>?> submitQuiz({
    required String quizId,
    required int timeTakenSeconds,
    required List<Map<String, dynamic>> answers,
  }) async {
    final token = await _getToken();
    if (token == null) return null;

    try {
      final response = await http.post(
        Uri.parse('${getBaseUrl()}/quizzes/$quizId/submit'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'time_taken_seconds': timeTakenSeconds,
          'answers': answers,
        }),
      );

      if (response.statusCode == 200) {
        // Return full response: score, earned_points, max_points, review[], dll
        return jsonDecode(response.body);
      } else {
        debugPrint('❌ Gagal Submit: ${response.body}');
        return null;
      }
    } catch (e) {
      debugPrint('🔥 Error Submit: $e');
      return null;
    }
  }

  // 4. LEADERBOARD
  Future<List<dynamic>> getLeaderboard(String quizId) async {
    final token = await _getToken();
    if (token == null) return [];

    try {
      final response = await http.get(
        Uri.parse('${getBaseUrl()}/quizzes/$quizId/leaderboard'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body)['data'];
      } else {
        debugPrint('❌ Gagal Leaderboard: ${response.body}');
        return [];
      }
    } catch (e) {
      debugPrint('🔥 Error Leaderboard: $e');
      return [];
    }
  }
}