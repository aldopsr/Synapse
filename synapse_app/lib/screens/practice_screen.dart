import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/constants.dart';

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
  String? _selectedAnswer; // Menyimpan jawaban yang dipilih user (A/B/C/D)

  @override
  void initState() {
    super.initState();
    _fetchQuestions();
  }

  Future<void> _fetchQuestions() async {
  try {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');
    
    // Gunakan getBaseUrl() yang sama dengan Service agar tidak typo
    final url = '${AppConstants.baseUrl}/materials/${widget.materialId}/questions';

    final response = await http.get(
      Uri.parse(url),
      headers: {
        'Accept': 'application/json',
        if (token != null) 'Authorization': 'Bearer $token', // Fleksibel untuk Tamu
      },
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      setState(() {
        // Ambil dari 'data' karena di Controller Laravel kita bungkus
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
        const SnackBar(content: Text('Pilih jawaban dulu, Kapten! ⚓')),
      );
      return;
    }

    // 1. Ambil kunci dari DB. (Pastikan nama kolomnya 'correct_answer' atau sesuaikan database Anda)
    var rawCorrectAnswer = _questions[_currentIndex]['correct_answer'];
    
    // 2. Kita pangkas spasinya (.trim) dan jadikan huruf besar semua (.toUpperCase)
    // Jika datanya null (kosong), kita kasih nilai 'X' biar tidak error.
    String safeCorrectAnswer = (rawCorrectAnswer ?? 'X').toString().trim().toUpperCase();
    String myAnswer = _selectedAnswer!.trim().toUpperCase();

    // 3. LOGGING (Print ke terminal/Debug Console untuk melihat apa yang sebenarnya dibandingkan)
    print('--- CEK JAWABAN SOAL ${_currentIndex + 1} ---');
    print('Jawaban Saya   : [$myAnswer]');
    print('Kunci Database : [$safeCorrectAnswer]');

    // 4. Bandingkan!
    if (myAnswer == safeCorrectAnswer) {
      _score++;
      print('Status         : BENAR! ✅ Skor nambah.');
    } else {
      print('Status         : SALAH! ❌');
    }

    // Jika ini soal terakhir, tampilkan nilai
    if (_currentIndex == _questions.length - 1) {
      _showResultDialog();
    } else {
      // Lanjut ke soal berikutnya
      setState(() {
        _currentIndex++;
        _selectedAnswer = null; // Reset pilihan
      });
    }
  }

  void _showResultDialog() {
    // Hitung persentase nilai (Skor 0 - 100)
    double finalScore = (_score / _questions.length) * 100;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text('Latihan Selesai! 🎉', textAlign: TextAlign.center),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text('Skor Akhir Anda:', style: TextStyle(fontSize: 18)),
            const SizedBox(height: 10),
            Text(
              '${finalScore.toInt()}',
              style: TextStyle(
                fontSize: 60,
                fontWeight: FontWeight.bold,
                color: finalScore >= 70 ? Colors.green : Colors.red,
              ),
            ),
            const SizedBox(height: 10),
            Text(
              'Benar $_score dari ${_questions.length} Soal',
              style: const TextStyle(fontSize: 16, color: Colors.grey),
            ),
          ],
        ),
        actions: [
          Center(
            child: ElevatedButton(
              onPressed: () {
                Navigator.pop(context); // Tutup dialog
                Navigator.pop(context); // Kembali ke halaman Materi
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.blueAccent,
                foregroundColor: Colors.white,
              ),
              child: const Text('Kembali ke Materi'),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[100],
      appBar: AppBar(
        title: Text('Latihan: ${widget.materialTitle}'),
        backgroundColor: Colors.orange.shade600,
        foregroundColor: Colors.white,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _errorMessage.isNotEmpty
              ? Center(child: Text(_errorMessage, style: const TextStyle(color: Colors.red)))
              : _questions.isEmpty
                  ? const Center(child: Text('Belum ada soal untuk materi ini.'))
                  : _buildQuizArena(),
    );
  }

  Widget _buildQuizArena() {
    final currentQuestion = _questions[_currentIndex];

    // PENTING: Sesuaikan 'option_a', 'option_b' dll dengan nama kolom di database Anda!
    final List<Map<String, String>> options = [
      {'key': 'A', 'text': currentQuestion['option_a'] ?? 'Pilihan A kosong'},
      {'key': 'B', 'text': currentQuestion['option_b'] ?? 'Pilihan B kosong'},
      {'key': 'C', 'text': currentQuestion['option_c'] ?? 'Pilihan C kosong'},
      {'key': 'D', 'text': currentQuestion['option_d'] ?? 'Pilihan D kosong'},
    ];

    return Padding(
      padding: const EdgeInsets.all(20.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Indikator Soal ke berapa
          Text(
            'Soal ${_currentIndex + 1} dari ${_questions.length}',
            style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.grey),
          ),
          const SizedBox(height: 10),
          // Pertanyaan
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(15),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(15),
              boxShadow: [BoxShadow(color: Colors.grey.shade300, blurRadius: 5)],
            ),
            child: Text(
              currentQuestion['question_text'] ?? 'Soal tidak ditemukan', // Sesuaikan 'question'
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
          ),
          const SizedBox(height: 20),
          // Pilihan Ganda (A, B, C, D)
          Expanded(
            child: ListView.builder(
              itemCount: options.length,
              itemBuilder: (context, index) {
                final option = options[index];
                final isSelected = _selectedAnswer == option['key'];

                return GestureDetector(
                  onTap: () {
                    setState(() {
                      _selectedAnswer = option['key'];
                    });
                  },
                  child: Container(
                    margin: const EdgeInsets.only(bottom: 12),
                    padding: const EdgeInsets.all(15),
                    decoration: BoxDecoration(
                      color: isSelected ? Colors.orange.shade100 : Colors.white,
                      border: Border.all(
                        color: isSelected ? Colors.orange : Colors.grey.shade300,
                        width: 2,
                      ),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Row(
                      children: [
                        CircleAvatar(
                          backgroundColor: isSelected ? Colors.orange : Colors.grey.shade200,
                          foregroundColor: isSelected ? Colors.white : Colors.black,
                          child: Text(option['key']!),
                        ),
                        const SizedBox(width: 15),
                        Expanded(
                          child: Text(
                            option['text']!,
                            style: const TextStyle(fontSize: 16),
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
          ),
          // Tombol Next / Selesai
          SizedBox(
            width: double.infinity,
            height: 50,
            child: ElevatedButton(
              onPressed: _nextQuestion,
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.orange.shade600,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              child: Text(
                _currentIndex == _questions.length - 1 ? 'Selesai & Lihat Nilai' : 'Soal Selanjutnya',
                style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
            ),
          )
        ],
      ),
    );
  }
}