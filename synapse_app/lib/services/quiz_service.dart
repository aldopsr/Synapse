import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../models/quiz_model.dart';
import '../models/question_model.dart';

class QuizService {
  final String baseUrl = 'http://127.0.0.1:8000/api';

  // Fungsi pembantu untuk mengambil Token User
  Future<String?> _getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('token');
  }

  // 1. Ambil Daftar Kuis
  Future<List<QuizModel>> getQuizzes() async {
    final token = await _getToken();
    
    print('🔑 Token Kuis: $token'); // Pastikan token terbawa
    
    final response = await http.get(
      Uri.parse('$baseUrl/quizzes'), 
      headers: {
        'Authorization': 'Bearer $token', 
        'Accept': 'application/json'
      },
    );

    if (response.statusCode == 200) {
      print('✅ KUIS BERHASIL DIAMBIL: ${response.body}');
      final List data = jsonDecode(response.body)['data'];
      return data.map((json) => QuizModel.fromJson(json)).toList();
    } else {
      // 👇 INI CCTV KITA! 
      print('❌ GAGAL MEMUAT KUIS!');
      print('Status Code: ${response.statusCode}');
      print('Alasan Laravel: ${response.body}');
      throw Exception('Gagal memuat daftar kuis');
    }
  }

  // 2. Ambil Soal Kuis berdasarkan ID Kuis
  Future<List<QuestionModel>> getQuestions(String quizId) async {
    final token = await _getToken();
    final response = await http.get(
      Uri.parse('$baseUrl/quizzes/$quizId/questions'), // Hasilnya: /api/quizzes/ID/questions
      headers: {
        'Authorization': 'Bearer $token', 
        'Accept': 'application/json'
      },
    );

    if (response.statusCode == 200) {
      final List data = jsonDecode(response.body)['data'];
      return data.map((json) => QuestionModel.fromJson(json)).toList();
    } else {
      throw Exception(jsonDecode(response.body)['message'] ?? 'Gagal memuat soal');
    }
  }

  // 3. Kirim Jawaban Mahasiswa ke Laravel
  Future<Map<String, dynamic>> submitQuiz({
    required String quizId,
    required int timeTakenSeconds,
    required List<Map<String, dynamic>> answers,
  }) async {
    final token = await _getToken();

    print("MENGIRIM KUIS DENGAN TOKEN: $token");
    final response = await http.post(
      Uri.parse('$baseUrl/quizzes/$quizId/submit'),
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
      throw Exception(jsonDecode(response.body)['message'] ?? 'Gagal mengirim jawaban');
    }
  }

  // 4. Ambil Data Leaderboard
  Future<List<dynamic>> getLeaderboard(String quizId) async {
    final token = await _getToken();
    final response = await http.get(
      Uri.parse('$baseUrl/quizzes/$quizId/leaderboard'),
      headers: {
        'Authorization': 'Bearer $token', 
        'Accept': 'application/json'
      },
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body)['data'];
    } else {
      throw Exception('Gagal memuat leaderboard');
    }
  }
}