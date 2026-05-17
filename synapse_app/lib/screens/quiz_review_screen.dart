import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/constants.dart';

class QuizReviewScreen extends StatefulWidget {
  final String quizTitle;
  final List<dynamic> questions;

  const QuizReviewScreen(
      {super.key, required this.quizTitle, required this.questions});

  @override
  State<QuizReviewScreen> createState() => _QuizReviewScreenState();
}

class _QuizReviewScreenState extends State<QuizReviewScreen> {
  static const Color _primaryColor = Color(0xFF2A9D8F);

  // Fungsi untuk memanggil AI
  Future<void> _askAIToExplain(
      String questionText, String correctAnswer) async {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) =>
          const Center(child: CircularProgressIndicator(color: _primaryColor)),
    );

    try {
      SharedPreferences prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('token');

      final response = await http.post(
        Uri.parse('${AppConstants.baseUrl}/explain-question'),
        headers: {
          'Authorization': 'Bearer $token',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'question_text': questionText,
          'correct_answer': correctAnswer
        }),
      );

      Navigator.pop(context);

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        _showExplanationDialog(data['explanation']);
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Gagal meminta penjelasan AI')));
      }
    } catch (e) {
      Navigator.pop(context);
      ScaffoldMessenger.of(context)
          .showSnackBar(SnackBar(content: Text('Error: $e')));
    }
  }

  void _showExplanationDialog(String explanation) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.auto_awesome, color: _primaryColor),
            SizedBox(width: 8),
            Text('Penjelasan SYNAPSE',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
          ],
        ),
        content: SingleChildScrollView(
            child: Text(explanation,
                style:
                    const TextStyle(height: 1.5, color: Color(0xFF334155)))),
        actions: [
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: _primaryColor,
              foregroundColor: Colors.white,
              shape:
                  RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            onPressed: () => Navigator.pop(context),
            child: const Text('Paham!'),
          ),
        ],
      ),
    );
  }

  // 🌟 Helper untuk format jawaban benar (handle multi-answer & true/false)
  String _formatCorrectAnswer(dynamic q) {
    final type = q['question_type'] ?? 'multiple_choice';

    if (type == 'multiple_answer') {
      final list = q['correct_answers'] as List? ?? [];
      if (list.isEmpty) return '-';
      return 'Pilih: ${list.map((e) => e.toString().toUpperCase()).join(', ')}';
    }

    final correct = (q['correct_answer'] ?? '').toString().toUpperCase();
    if (type == 'true_false') {
      if (correct == 'A') return 'Benar / True';
      if (correct == 'B') return 'Salah / False';
    }

    return correct.isEmpty ? '-' : 'Pilihan $correct';
  }

  String _getTypeLabel(String type) {
    switch (type) {
      case 'true_false':
        return '✓✗ True/False';
      case 'multiple_answer':
        return '☑ Multi Answer';
      default:
        return '📝 Pilihan Ganda';
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F6F8),
      appBar: AppBar(
        title: Text('Review Misi: ${widget.quizTitle}',
            style: const TextStyle(
                color: Color(0xFF334155),
                fontSize: 18,
                fontWeight: FontWeight.bold)),
        backgroundColor: Colors.white,
        iconTheme: const IconThemeData(color: Color(0xFF334155)),
        elevation: 0,
        centerTitle: true,
      ),
      body: widget.questions.isEmpty
          ? Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.inbox_rounded, size: 60, color: Colors.grey[400]),
                  const SizedBox(height: 16),
                  Text('Tidak ada data soal.',
                      style: TextStyle(color: Colors.grey[600])),
                ],
              ),
            )
          : ListView.builder(
              padding: const EdgeInsets.all(20),
              itemCount: widget.questions.length,
              itemBuilder: (context, index) {
                final q = widget.questions[index];
                final questionText = q['question'] ?? 'Teks soal kosong';
                final correctAnswer = _formatCorrectAnswer(q);
                final type = q['question_type'] ?? 'multiple_choice';
                final imageUrl = q['image_url'];
                final explanation = q['explanation'];

                return Container(
                  margin: const EdgeInsets.only(bottom: 20),
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(24),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.03),
                        blurRadius: 10,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Header: Nomor & Tipe
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 12, vertical: 6),
                            decoration: BoxDecoration(
                              color: _primaryColor.withOpacity(0.1),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Text(
                              'Soal ${index + 1}',
                              style: const TextStyle(
                                  fontWeight: FontWeight.bold,
                                  color: _primaryColor),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 10, vertical: 5),
                            decoration: BoxDecoration(
                              color: Colors.grey[100],
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Text(
                              _getTypeLabel(type),
                              style: TextStyle(
                                  color: Colors.grey[700],
                                  fontSize: 11,
                                  fontWeight: FontWeight.w600),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),

                      // Teks Soal
                      Text(
                        questionText,
                        style: const TextStyle(
                            fontSize: 16,
                            color: Color(0xFF334155),
                            height: 1.5,
                            fontWeight: FontWeight.w500),
                      ),

                      // 🌟 Gambar
                      if (imageUrl != null && imageUrl.toString().isNotEmpty) ...[
                        const SizedBox(height: 14),
                        ClipRRect(
                          borderRadius: BorderRadius.circular(12),
                          child: Image.network(
                            imageUrl.toString(),
                            width: double.infinity,
                            fit: BoxFit.cover,
                            height: 160,
                            errorBuilder: (_, __, ___) => Container(
                                height: 80,
                                color: Colors.grey[200],
                                child: const Icon(Icons.broken_image,
                                    color: Colors.grey)),
                          ),
                        ),
                      ],

                      const SizedBox(height: 20),

                      // Kotak Jawaban Benar
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: const Color(0xFFE8F5E9),
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(
                              color: const Color(0xFF81C784).withOpacity(0.5)),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Row(
                              children: [
                                Icon(Icons.check_circle_rounded,
                                    color: Color(0xFF4CAF50), size: 18),
                                SizedBox(width: 8),
                                Text('Kunci Jawaban',
                                    style: TextStyle(
                                        fontWeight: FontWeight.bold,
                                        color: Color(0xFF4CAF50),
                                        fontSize: 13)),
                              ],
                            ),
                            const SizedBox(height: 8),
                            Text(correctAnswer,
                                style: const TextStyle(
                                    fontSize: 15,
                                    color: Color(0xFF2E7D32),
                                    fontWeight: FontWeight.w600)),
                          ],
                        ),
                      ),

                      // 🌟 Penjelasan dari Dosen (jika ada)
                      if (explanation != null &&
                          explanation.toString().isNotEmpty) ...[
                        const SizedBox(height: 12),
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                            color: const Color(0xFFFFF8E1),
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(color: Colors.amber[200]!),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Icon(Icons.tips_and_updates_rounded,
                                      color: Colors.amber[800], size: 18),
                                  const SizedBox(width: 8),
                                  Text('Penjelasan dari Dosen',
                                      style: TextStyle(
                                          color: Colors.amber[900],
                                          fontWeight: FontWeight.bold,
                                          fontSize: 13)),
                                ],
                              ),
                              const SizedBox(height: 6),
                              Text(explanation.toString(),
                                  style: TextStyle(
                                      color: Colors.brown[800],
                                      fontSize: 14,
                                      height: 1.5)),
                            ],
                          ),
                        ),
                      ],

                      const SizedBox(height: 20),

                      // Tombol Tanya AI
                      SizedBox(
                        width: double.infinity,
                        height: 50,
                        child: OutlinedButton.icon(
                          style: OutlinedButton.styleFrom(
                            foregroundColor: _primaryColor,
                            side: const BorderSide(
                                color: _primaryColor, width: 1.5),
                            shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(16)),
                          ),
                          icon: const Icon(Icons.auto_awesome, size: 20),
                          label: const Text(
                            'Tanya SYNAPSE',
                            style: TextStyle(
                                fontWeight: FontWeight.bold, fontSize: 15),
                          ),
                          onPressed: () => _askAIToExplain(
                              questionText, correctAnswer),
                        ),
                      ),
                    ],
                  ),
                );
              },
            ),
    );
  }
}