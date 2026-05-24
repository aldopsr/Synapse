import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import 'quiz_review_screen.dart';
import '../utils/constants.dart';

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

  static const Color _primary = Color(0xFF2A9D8F);

  final String baseUrl = AppConstants.baseUrl;

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
        List<dynamic> dataList = responseData['data'] ?? responseData;

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
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: _primary))
          : _errorMessage.isNotEmpty
              ? _buildError()
              : _buildContent(),
    );
  }

  Widget _buildContent() {
    return SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // ── Header teal melengkung bawah ──────────────────
          Container(
            width: double.infinity,
            decoration: const BoxDecoration(
              color: _primary,
              borderRadius: BorderRadius.only(
                bottomLeft: Radius.circular(36),
                bottomRight: Radius.circular(36),
              ),
            ),
            child: SafeArea(
              bottom: false,
              child: Column(
                children: [
                  // AppBar manual
                  Padding(
                    padding: const EdgeInsets.fromLTRB(4, 8, 16, 0),
                    child: Row(
                      children: [
                        IconButton(
                          icon: const Icon(Icons.arrow_back, color: Colors.white),
                          onPressed: () => Navigator.pop(context),
                        ),
                        const Expanded(
                          child: Text(
                            'Statistik & Riwayat',
                            textAlign: TextAlign.center,
                            style: TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.bold,
                              fontSize: 18,
                            ),
                          ),
                        ),
                        const SizedBox(width: 48),
                      ],
                    ),
                  ),
                  const SizedBox(height: 24),

                  // Kartu statistik
                  Container(
                    margin: const EdgeInsets.fromLTRB(24, 0, 24, 36),
                    padding: const EdgeInsets.symmetric(vertical: 28, horizontal: 16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(24),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.1),
                          blurRadius: 20,
                          offset: const Offset(0, 8),
                        ),
                      ],
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceAround,
                      children: [
                        _StatItem(
                          title: 'Kuis Selesai',
                          value: '$_totalQuizzes',
                          icon: Icons.task_alt_rounded,
                        ),
                        Container(
                          width: 1, height: 60,
                          color: Colors.grey.shade200,
                        ),
                        _StatItem(
                          title: 'Rata-rata',
                          value: '$_averageScore',
                          icon: Icons.auto_graph_rounded,
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),

          // ── List riwayat ──────────────────────────────────
          Padding(
            padding: const EdgeInsets.fromLTRB(24, 28, 24, 24),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Riwayat Kuis',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF334155),
                  ),
                ),
                const SizedBox(height: 16),
                _historyKuis.isEmpty
                    ? const Center(
                        child: Padding(
                          padding: EdgeInsets.only(top: 40),
                          child: Text('Belum ada riwayat kuis.',
                              style: TextStyle(color: Colors.grey)),
                        ),
                      )
                    : ListView.builder(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        itemCount: _historyKuis.length,
                        itemBuilder: (context, index) {
                          final history = _historyKuis[index];
                          final int score = history['score'] ?? 0;
                          final String title = history['title'] ??
                              history['quiz']?['title'] ??
                              'Kuis Latihan';
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
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withOpacity(0.02),
                                  blurRadius: 8,
                                  offset: const Offset(0, 2),
                                )
                              ],
                            ),
                            child: Row(
                              children: [
                                Container(
                                  width: 50, height: 50,
                                  decoration: BoxDecoration(
                                    color: score >= 80
                                        ? const Color(0xFFE8F5E9)
                                        : const Color(0xFFFFF3E0),
                                    shape: BoxShape.circle,
                                  ),
                                  child: Center(
                                    child: Text(
                                      '$score',
                                      style: TextStyle(
                                        fontWeight: FontWeight.bold,
                                        color: score >= 80
                                            ? const Color(0xFF2E7D32)
                                            : Colors.orange[800],
                                      ),
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 16),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(title,
                                          style: const TextStyle(
                                              fontWeight: FontWeight.bold,
                                              fontSize: 15,
                                              color: Color(0xFF334155))),
                                      const SizedBox(height: 4),
                                      Text(date,
                                          style: TextStyle(
                                              fontSize: 12, color: Colors.grey[500])),
                                    ],
                                  ),
                                ),
                                TextButton(
                                  style: TextButton.styleFrom(
                                    foregroundColor: _primary,
                                    backgroundColor: _primary.withOpacity(0.1),
                                    shape: RoundedRectangleBorder(
                                        borderRadius: BorderRadius.circular(12)),
                                  ),
                                  onPressed: () {
                                    List<dynamic> questionsList =
                                        history['questions'] ?? [];
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
                                  child: const Text('Review',
                                      style: TextStyle(fontWeight: FontWeight.bold)),
                                ),
                              ],
                            ),
                          );
                        },
                      ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildError() {
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
              setState(() {
                _isLoading = true;
                _errorMessage = '';
              });
              _fetchHistoryData();
            },
            style: ElevatedButton.styleFrom(backgroundColor: _primary),
            child: const Text('Coba Lagi', style: TextStyle(color: Colors.white)),
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

  const _StatItem({
    required this.title,
    required this.value,
    required this.icon,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Icon(icon, color: const Color(0xFF2A9D8F), size: 30),
        const SizedBox(height: 10),
        Text(
          value,
          style: const TextStyle(
            fontSize: 32,
            fontWeight: FontWeight.bold,
            color: Color(0xFF2A9D8F),
          ),
        ),
        const SizedBox(height: 4),
        Text(
          title,
          style: const TextStyle(
            fontSize: 12,
            color: Color(0xFF2A9D8F),
            fontWeight: FontWeight.w500,
          ),
        ),
      ],
    );
  }
}