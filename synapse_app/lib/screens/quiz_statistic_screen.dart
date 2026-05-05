import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import 'quiz_review_screen.dart';

class QuizStatisticScreen extends StatefulWidget {
  const QuizStatisticScreen({super.key});

  @override
  State<QuizStatisticScreen> createState() => _QuizStatisticScreenState();
}

class _QuizStatisticScreenState extends State<QuizStatisticScreen> {
  List<dynamic> _historyKuis = [];
  bool _isLoading = true;
  String _errorMessage = '';
  
  int _totalQuizzes = 0;
  int _averageScore = 0;

  // Pastikan URL ini sama dengan file Kapten yang lain!
  final String baseUrl = 'http://192.168.3.51:8000/api';

  @override
  void initState() {
    super.initState();
    _fetchHistoryData();
  }

  Future<void> _fetchHistoryData() async {
    SharedPreferences prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');

    try {
      final response = await http.get(
        Uri.parse('$baseUrl/quiz-history'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final responseData = jsonDecode(response.body);
        
        // Menyesuaikan jika Laravel membungkus data dalam "data" (misal: resource API)
        List<dynamic> dataList = responseData['data'] ?? responseData;

        // Hitung total dan rata-rata
        int totalKuis = dataList.length;
        double totalNilai = 0;
        
        for (var item in dataList) {
          totalNilai += (item['score'] ?? 0);
        }
        
        int rataRata = totalKuis > 0 ? (totalNilai / totalKuis).round() : 0;

        if (mounted) {
          setState(() {
            _historyKuis = dataList;
            _totalQuizzes = totalKuis;
            _averageScore = rataRata;
            _isLoading = false;
          });
        }
      } else {
        if (mounted) {
          setState(() {
            _errorMessage = 'Gagal memuat data: Error ${response.statusCode}';
            _isLoading = false;
          });
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _errorMessage = 'Koneksi ke server terputus.';
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F6F8),
      appBar: AppBar(
        title: const Text('Statistik & Riwayat', style: TextStyle(color: Color(0xFF334155), fontWeight: FontWeight.bold, fontSize: 18)),
        backgroundColor: Colors.white,
        elevation: 0,
        iconTheme: const IconThemeData(color: Color(0xFF334155)),
        centerTitle: true,
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator(color: Color(0xFF2A9D8F)));
    }

    if (_errorMessage.isNotEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.wifi_off_rounded, size: 60, color: Colors.grey),
            const SizedBox(height: 16),
            Text(_errorMessage, style: const TextStyle(color: Colors.grey)),
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: () {
                setState(() { _isLoading = true; _errorMessage = ''; });
                _fetchHistoryData();
              },
              style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF2A9D8F)),
              child: const Text('Coba Lagi', style: TextStyle(color: Colors.white)),
            )
          ],
        ),
      );
    }

    return SingleChildScrollView(
      padding: const EdgeInsets.all(24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // --- KARTU RINGKASAN STATISTIK ---
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF2A9D8F), Color(0xFF21867A)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(24),
              boxShadow: [
                BoxShadow(color: const Color(0xFF2A9D8F).withOpacity(0.3), blurRadius: 15, offset: const Offset(0, 5)),
              ],
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                _StatItem(title: 'Kuis Selesai', value: '$_totalQuizzes', icon: Icons.task_alt_rounded),
                _StatItem(title: 'Rata-rata', value: '$_averageScore', icon: Icons.auto_graph_rounded),
              ],
            ),
          ),
          
          const SizedBox(height: 32),
          const Text('Riwayat Kuis', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF334155))),
          const SizedBox(height: 16),

          // --- LIST RIWAYAT UNTUK MENUJU REVIEW SCREEN ---
          _historyKuis.isEmpty 
          ? const Center(
              child: Padding(
                padding: EdgeInsets.only(top: 40),
                child: Text('Belum ada riwayat kuis.', style: TextStyle(color: Colors.grey)),
              )
            )
          : ListView.builder(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: _historyKuis.length,
              itemBuilder: (context, index) {
                final history = _historyKuis[index];
                
                // Menangani kemungkinan nama variabel dari backend (bisa disesuaikan)
                final int score = history['score'] ?? 0;
                final String title = history['title'] ?? history['quiz']?['title'] ?? 'Kuis Latihan';
                
                // Coba ambil tanggal dari created_at, potong 10 karakter pertama (YYYY-MM-DD)
                String date = 'Selesai';
                if (history['created_at'] != null) {
                   date = history['created_at'].toString().substring(0, 10);
                }
                
                return Container(
                  margin: const EdgeInsets.only(bottom: 16),
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(20),
                    boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.02), blurRadius: 8, offset: const Offset(0, 2))],
                  ),
                  child: Row(
                    children: [
                      // Lingkaran Nilai
                      Container(
                        width: 50,
                        height: 50,
                        decoration: BoxDecoration(
                          color: score >= 80 ? const Color(0xFFE8F5E9) : const Color(0xFFFFF3E0),
                          shape: BoxShape.circle,
                        ),
                        child: Center(
                          child: Text(
                            '$score',
                            style: TextStyle(
                              fontWeight: FontWeight.bold, 
                              color: score >= 80 ? const Color(0xFF2E7D32) : Colors.orange[800],
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Color(0xFF334155))),
                            const SizedBox(height: 4),
                            Text(date, style: TextStyle(fontSize: 12, color: Colors.grey[500])),
                          ],
                        ),
                      ),
                      // TOMBOL DIRECT KE REVIEW SCREEN
                      TextButton(
                        style: TextButton.styleFrom(
                          foregroundColor: const Color(0xFF2A9D8F),
                          backgroundColor: const Color(0xFF2A9D8F).withOpacity(0.1),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        // JIKA TOMBOL INI DITEKAN:
                        onPressed: () {
                          // Pastikan Laravel mengirim array 'questions' di dalam data riwayat ini
                          // Jika tidak, Kapten harus menembak API lagi di dalam QuizReviewScreen
                          List<dynamic> questionsList = history['questions'] ?? [];

                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (context) => QuizReviewScreen(
                                quizTitle: title,
                                questions: questionsList, 
                              ),
                            ),
                          );
                        },
                        child: const Text('Review', style: TextStyle(fontWeight: FontWeight.bold)),
                      ),
                    ],
                  ),
                );
              },
            ),
        ],
      ),
    );
  }
}

class _StatItem extends StatelessWidget {
  final String title;
  final String value;
  final IconData icon;

  const _StatItem({required this.title, required this.value, required this.icon});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Icon(icon, color: Colors.white70, size: 28),
        const SizedBox(height: 8),
        Text(value, style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: Colors.white)),
        Text(title, style: const TextStyle(fontSize: 12, color: Colors.white70)),
      ],
    );
  }
}