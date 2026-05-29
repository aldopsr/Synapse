import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/constants.dart';
import '../widgets/synapse_fab.dart';

class PracticeScreen extends StatefulWidget {
  final String materialId;
  final String materialTitle;

  const PracticeScreen({
    super.key,
    required this.materialId,
    required this.materialTitle,
  });

  @override
  State<PracticeScreen> createState() => _PracticeScreenState();
}

class _PracticeScreenState extends State<PracticeScreen> {
  List<dynamic> _questions = [];
  bool _isLoading = true;
  String _errorMessage = '';

  int _currentIndex = 0;
  int _score = 0;
  String? _selectedAnswer;

  static const Color _primary = Color(0xFF2A9D8F);
  static const Color _primaryLight = Color(0xFFE8F5F3);

  @override
  void initState() {
    super.initState();
    _fetchQuestions();
  }

  Future<void> _fetchQuestions() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('token');
      final url = '${AppConstants.baseUrl}/materials/${widget.materialId}/questions';

      final response = await http.get(
        Uri.parse(url),
        headers: {
          'Accept': 'application/json',
          if (token != null) 'Authorization': 'Bearer $token',
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        setState(() {
          _questions = data['data'] ?? [];
          _isLoading = false;
        });
      } else {
        setState(() {
          _errorMessage = 'Gagal mengambil soal. (Status: ${response.statusCode})';
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = 'Terjadi kesalahan jaringan: $e';
        _isLoading = false;
      });
    }
  }

  void _nextQuestion() {
    if (_selectedAnswer == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text('Pilih jawaban dulu ya!'),
          backgroundColor: _primary,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        ),
      );
      return;
    }

    var rawCorrectAnswer = _questions[_currentIndex]['correct_answer'];
    String safeCorrectAnswer = (rawCorrectAnswer ?? 'X').toString().trim().toUpperCase();
    String myAnswer = _selectedAnswer!.trim().toUpperCase();

    if (myAnswer == safeCorrectAnswer) {
      _score++;
    }

    if (_currentIndex == _questions.length - 1) {
      _showResultDialog();
    } else {
      setState(() {
        _currentIndex++;
        _selectedAnswer = null;
      });
    }
  }

  void _showResultDialog() {
    double finalScore = (_score / _questions.length) * 100;
    final bool lulus = finalScore >= 70;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        contentPadding: const EdgeInsets.all(28),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 72, height: 72,
              decoration: BoxDecoration(
                color: lulus ? const Color(0xFFE8F5E9) : const Color(0xFFFFF3E0),
                shape: BoxShape.circle,
              ),
              child: Icon(
                lulus ? Icons.emoji_events_rounded : Icons.replay_rounded,
                color: lulus ? const Color(0xFF2E7D32) : Colors.orange[700],
                size: 36,
              ),
            ),
            const SizedBox(height: 16),
            Text(
              lulus ? 'Latihan Selesai! 🎉' : 'Hampir Berhasil!',
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 20),
            Text(
              '${finalScore.toInt()}',
              style: TextStyle(
                fontSize: 64,
                fontWeight: FontWeight.bold,
                color: lulus ? const Color(0xFF2E7D32) : Colors.orange[700],
              ),
            ),
            Text(
              'Benar $_score dari ${_questions.length} soal',
              style: TextStyle(fontSize: 14, color: Colors.grey[600]),
            ),
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () {
                  Navigator.pop(context);
                  Navigator.pop(context);
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: _primary,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  elevation: 0,
                ),
                child: const Text('Kembali ke Materi',
                    style: TextStyle(fontWeight: FontWeight.bold)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F7FA),
      appBar: AppBar(
        title: Text(
          widget.materialTitle,
          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
          overflow: TextOverflow.ellipsis,
        ),
        backgroundColor: _primary,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      // Wrap body dengan Stack agar SynapseFab bisa di-overlay
      body: Stack(
        children: [
          _isLoading
              ? const Center(child: CircularProgressIndicator(color: _primary))
              : _errorMessage.isNotEmpty
                  ? Center(
                      child: Padding(
                        padding: const EdgeInsets.all(24),
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(Icons.error_outline_rounded,
                                size: 60, color: Colors.grey),
                            const SizedBox(height: 16),
                            Text(_errorMessage,
                                textAlign: TextAlign.center,
                                style: const TextStyle(color: Colors.grey)),
                          ],
                        ),
                      ),
                    )
                  : _questions.isEmpty
                      ? const Center(
                          child: Text('Belum ada soal untuk materi ini.',
                              style: TextStyle(color: Colors.grey)))
                      : _buildQuizArena(),
          // Assistive Touch FAB
          const SynapseFab(),
        ],
      ),
    );
  }

  Widget _buildQuizArena() {
    final currentQuestion = _questions[_currentIndex];
    final double progress = (_currentIndex + 1) / _questions.length;

    final List<Map<String, String>> options = <Map<String, String>>[
      {'key': 'A', 'text': (currentQuestion['option_a'] ?? '').toString()},
      {'key': 'B', 'text': (currentQuestion['option_b'] ?? '').toString()},
      {'key': 'C', 'text': (currentQuestion['option_c'] ?? '').toString()},
      {'key': 'D', 'text': (currentQuestion['option_d'] ?? '').toString()},
    ].where((o) => o['text']!.isNotEmpty).toList();

    return Column(
      children: [
        // Progress bar
        Container(
          color: _primary,
          padding: const EdgeInsets.fromLTRB(20, 0, 20, 20),
          child: Column(
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Soal ${_currentIndex + 1} dari ${_questions.length}',
                    style: const TextStyle(
                        color: Colors.white70,
                        fontSize: 13,
                        fontWeight: FontWeight.w500),
                  ),
                  Text(
                    '${(_currentIndex + 1)}/${_questions.length}',
                    style: const TextStyle(
                        color: Colors.white, fontWeight: FontWeight.bold),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              ClipRRect(
                borderRadius: BorderRadius.circular(10),
                child: LinearProgressIndicator(
                  value: progress,
                  backgroundColor: Colors.white.withOpacity(0.3),
                  valueColor:
                      const AlwaysStoppedAnimation<Color>(Colors.white),
                  minHeight: 6,
                ),
              ),
            ],
          ),
        ),

        Expanded(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Kartu pertanyaan
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(20),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.05),
                        blurRadius: 10,
                        offset: const Offset(0, 4),
                      )
                    ],
                  ),
                  child: Text(
                    currentQuestion['question_text'] ?? 'Soal tidak ditemukan',
                    style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                        height: 1.5),
                  ),
                ),
                const SizedBox(height: 20),

                // Pilihan jawaban
                ...options.map((option) {
                  final isSelected = _selectedAnswer == option['key'];
                  return GestureDetector(
                    onTap: () =>
                        setState(() => _selectedAnswer = option['key']),
                    child: AnimatedContainer(
                      duration: const Duration(milliseconds: 200),
                      margin: const EdgeInsets.only(bottom: 12),
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: isSelected ? _primaryLight : Colors.white,
                        border: Border.all(
                          color:
                              isSelected ? _primary : Colors.grey.shade200,
                          width: isSelected ? 2 : 1,
                        ),
                        borderRadius: BorderRadius.circular(14),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.03),
                            blurRadius: 6,
                            offset: const Offset(0, 2),
                          )
                        ],
                      ),
                      child: Row(
                        children: [
                          AnimatedContainer(
                            duration: const Duration(milliseconds: 200),
                            width: 36, height: 36,
                            decoration: BoxDecoration(
                              color: isSelected
                                  ? _primary
                                  : Colors.grey.shade100,
                              shape: BoxShape.circle,
                            ),
                            child: Center(
                              child: Text(
                                option['key']!,
                                style: TextStyle(
                                  fontWeight: FontWeight.bold,
                                  color: isSelected
                                      ? Colors.white
                                      : Colors.grey[600],
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(width: 14),
                          Expanded(
                            child: Text(
                              option['text']!,
                              style: TextStyle(
                                fontSize: 15,
                                color:
                                    isSelected ? _primary : Colors.black87,
                                fontWeight: isSelected
                                    ? FontWeight.w600
                                    : FontWeight.normal,
                              ),
                            ),
                          ),
                          if (isSelected)
                            const Icon(Icons.check_circle_rounded,
                                color: _primary, size: 20),
                        ],
                      ),
                    ),
                  );
                }),

                const SizedBox(height: 8),
              ],
            ),
          ),
        ),

        // Tombol next
        Padding(
          padding: const EdgeInsets.fromLTRB(20, 0, 20, 32),
          child: SizedBox(
            width: double.infinity,
            height: 52,
            child: ElevatedButton(
              onPressed: _nextQuestion,
              style: ElevatedButton.styleFrom(
                backgroundColor: _primary,
                foregroundColor: Colors.white,
                elevation: 0,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16)),
              ),
              child: Text(
                _currentIndex == _questions.length - 1
                    ? 'Selesai & Lihat Nilai'
                    : 'Soal Selanjutnya',
                style: const TextStyle(
                    fontSize: 16, fontWeight: FontWeight.bold),
              ),
            ),
          ),
        ),
      ],
    );
  }
}