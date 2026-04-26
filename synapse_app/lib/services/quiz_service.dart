import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../models/quiz_model.dart';
import '../models/question_model.dart';

// 🔥 sama persis kayak AuthService
String getBaseUrl() {
  if (kIsWeb) {
    return 'http://127.0.0.1:8000/api';
  }

  if (Platform.isAndroid) {
    const bool isEmulator =
        bool.fromEnvironment('ANDROID_EMULATOR', defaultValue: false);

    return isEmulator
        ? 'http://10.0.2.2:8000/api'
        : 'http://192.168.1.12:8000/api';
  }

  return 'http://127.0.0.1:8000/api';
}

class QuizService {
  // 🔑 ambil token
  Future<String?> _getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('token');
  }

  // 1. GET QUIZ
  Future<List<QuizModel>> getQuizzes() async {
    final token = await _getToken();

    if (token == null) return [];

    try {
      final response = await http.get(
        Uri.parse('${getBaseUrl()}/quizzes'),
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

  // 3. SUBMIT QUIZ
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