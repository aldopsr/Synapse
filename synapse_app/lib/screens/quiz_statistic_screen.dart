// lib/screens/quiz_statistic_screen.dart
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
  static const Color _darkTeal = Color(0xFF16877B);
  static const Color _bg = Color(0xFFF6F7FB);
  static const Color _textDark = Color(0xFF1F2937);
  static const Color _textMuted = Color(0xFF94A3B8);
  static const Color _softTeal = Color(0xFFEAFBF5);
  static const Color _orange = Color(0xFFF4A62A);
  static const Color _pink = Color(0xFFE75480);

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
    } catch (_) {
      if (mounted) {
        setState(() {
          _errorMessage = 'Koneksi ke server terputus.';
          _isLoading = false;
        });
      }
    }
  }

  Color _scoreColor(int score) {
    if (score >= 80) return _primary;
    if (score >= 60) return _orange;
    return _pink;
  }

  Color _scoreBg(int score) {
    if (score >= 80) return _softTeal;
    if (score >= 60) return const Color(0xFFFFF7DF);
    return const Color(0xFFFFEEF5);
  }

  String _scoreLabel(int score) {
    if (score >= 80) return 'Mantap';
    if (score >= 60) return 'Cukup Baik';
    return 'Perlu Latihan';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _bg,
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: _primary))
          : _errorMessage.isNotEmpty
              ? _buildError()
              : _buildContent(),
    );
  }

  Widget _buildContent() {
    return RefreshIndicator(
      color: _primary,
      onRefresh: _fetchHistoryData,
      child: SingleChildScrollView(
        physics: const BouncingScrollPhysics(
          parent: AlwaysScrollableScrollPhysics(),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: double.infinity,
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  colors: [
                    Color(0xFF65C8D0),
                    Color(0xFF2A9D8F),
                    Color(0xFF16877B),
                  ],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.only(
                  bottomLeft: Radius.circular(38),
                  bottomRight: Radius.circular(38),
                ),
              ),
              child: SafeArea(
                bottom: false,
                child: Stack(
                  children: [
                    Positioned(
                      top: -70,
                      left: -55,
                      child: _blob(180, Colors.white.withOpacity(0.12)),
                    ),
                    Positioned(
                      top: 30,
                      right: -50,
                      child: _blob(150, Colors.teal.shade900.withOpacity(0.14)),
                    ),
                    Column(
                      children: [
                        Padding(
                          padding: const EdgeInsets.fromLTRB(8, 8, 18, 0),
                          child: Row(
                            children: [
                              IconButton(
                                icon: const Icon(
                                  Icons.arrow_back_rounded,
                                  color: Colors.white,
                                ),
                                onPressed: () => Navigator.pop(context),
                              ),
                              const Expanded(
                                child: Text(
                                  '',
                                  textAlign: TextAlign.center,
                                  style: TextStyle(
                                    color: Colors.white,
                                    fontWeight: FontWeight.w900,
                                    fontSize: 18,
                                  ),
                                ),
                              ),
                              Container(
                                width: 42,
                                height: 42,
                                decoration: BoxDecoration(
                                  color: Colors.white.withOpacity(0.20),
                                  borderRadius: BorderRadius.circular(14),
                                ),
                                child: const Icon(
                                  Icons.query_stats_rounded,
                                  color: Colors.white,
                                  size: 22,
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 20),
                        const Text(
                          'Quiz Performance',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 30,
                            fontWeight: FontWeight.w900,
                            letterSpacing: -0.7,
                          ),
                        ),
                        const SizedBox(height: 6),
                        Text(
                          'Pantau hasil dan perkembangan kuismu',
                          style: TextStyle(
                            color: Colors.white.withOpacity(0.82),
                            fontSize: 13,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        const SizedBox(height: 22),
                        Container(
                          margin: const EdgeInsets.fromLTRB(22, 0, 22, 32),
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(28),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withOpacity(0.10),
                                blurRadius: 22,
                                offset: const Offset(0, 10),
                              ),
                            ],
                          ),
                          child: Row(
                            children: [
                              Expanded(
                                child: _StatItem(
                                  title: 'Kuis Selesai',
                                  value: '$_totalQuizzes',
                                  icon: Icons.task_alt_rounded,
                                  color: _primary,
                                  bgColor: _softTeal,
                                ),
                              ),
                              Container(
                                width: 1,
                                height: 70,
                                color: Colors.grey.withOpacity(0.16),
                              ),
                              Expanded(
                                child: _StatItem(
                                  title: 'Rata-rata',
                                  value: '$_averageScore',
                                  icon: Icons.auto_graph_rounded,
                                  color: const Color(0xFF2D9CDB),
                                  bgColor: const Color(0xFFEAF7FF),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(22, 26, 22, 120),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Riwayat Kuis',
                    style: TextStyle(
                      fontSize: 21,
                      fontWeight: FontWeight.w900,
                      color: _textDark,
                    ),
                  ),
                  const SizedBox(height: 16),
                  _historyKuis.isEmpty
                      ? _buildEmptyHistory()
                      : ListView.builder(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          itemCount: _historyKuis.length,
                          itemBuilder: (context, index) {
                            return _buildHistoryCard(_historyKuis[index]);
                          },
                        ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildHistoryCard(dynamic history) {
    final int score = history['score'] ?? 0;
    final String title =
        history['title'] ?? history['quiz']?['title'] ?? 'Kuis Latihan';

    String date = 'Selesai';
    if (history['created_at'] != null) {
      date = history['created_at'].toString().substring(0, 10);
    }

    final color = _scoreColor(score);
    final bgColor = _scoreBg(score);

    return Container(
      margin: const EdgeInsets.only(bottom: 15),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.045),
            blurRadius: 18,
            offset: const Offset(0, 7),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(24),
          onTap: () => _openReview(history, title),
          child: Padding(
            padding: const EdgeInsets.all(15),
            child: Row(
              children: [
                Container(
                  width: 58,
                  height: 58,
                  decoration: BoxDecoration(
                    color: bgColor,
                    borderRadius: BorderRadius.circular(18),
                  ),
                  child: Center(
                    child: Text(
                      '$score',
                      style: TextStyle(
                        fontWeight: FontWeight.w900,
                        color: color,
                        fontSize: 19,
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        _scoreLabel(score).toUpperCase(),
                        style: TextStyle(
                          color: color,
                          fontSize: 10,
                          fontWeight: FontWeight.w900,
                          letterSpacing: 0.8,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        title,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          fontWeight: FontWeight.w900,
                          fontSize: 15,
                          color: _textDark,
                          height: 1.25,
                        ),
                      ),
                      const SizedBox(height: 7),
                      Row(
                        children: [
                          Icon(
                            Icons.calendar_month_rounded,
                            size: 14,
                            color: _textMuted,
                          ),
                          const SizedBox(width: 5),
                          Text(
                            date,
                            style: const TextStyle(
                              fontSize: 12,
                              color: _textMuted,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 10),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 9,
                  ),
                  decoration: BoxDecoration(
                    color: color.withOpacity(0.10),
                    borderRadius: BorderRadius.circular(15),
                  ),
                  child: Text(
                    'Review',
                    style: TextStyle(
                      color: color,
                      fontWeight: FontWeight.w900,
                      fontSize: 12,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  void _openReview(dynamic history, String title) {
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
  }

  Widget _buildEmptyHistory() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(28),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(26),
      ),
      child: const Column(
        children: [
          Icon(Icons.quiz_outlined, color: _primary, size: 54),
          SizedBox(height: 14),
          Text(
            'Belum ada riwayat kuis',
            style: TextStyle(
              color: _textDark,
              fontSize: 17,
              fontWeight: FontWeight.w900,
            ),
          ),
          SizedBox(height: 7),
          Text(
            'Kerjakan kuis pertama kamu dan hasilnya akan muncul di sini.',
            textAlign: TextAlign.center,
            style: TextStyle(
              color: _textMuted,
              fontSize: 13,
              height: 1.45,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildError() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(38),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.wifi_off_rounded, size: 60, color: Colors.grey),
            const SizedBox(height: 16),
            Text(
              _errorMessage,
              textAlign: TextAlign.center,
              style: const TextStyle(color: Colors.grey),
            ),
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: () {
                setState(() {
                  _isLoading = true;
                  _errorMessage = '';
                });
                _fetchHistoryData();
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: _primary,
                foregroundColor: Colors.white,
                elevation: 0,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(14),
                ),
              ),
              child: const Text(
                'Coba Lagi',
                style: TextStyle(fontWeight: FontWeight.w900),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _blob(double size, Color color) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(size),
      ),
    );
  }
}

class _StatItem extends StatelessWidget {
  final String title;
  final String value;
  final IconData icon;
  final Color color;
  final Color bgColor;

  const _StatItem({
    required this.title,
    required this.value,
    required this.icon,
    required this.color,
    required this.bgColor,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Container(
          width: 48,
          height: 48,
          decoration: BoxDecoration(
            color: bgColor,
            borderRadius: BorderRadius.circular(16),
          ),
          child: Icon(icon, color: color, size: 27),
        ),
        const SizedBox(height: 10),
        Text(
          value,
          style: TextStyle(
            fontSize: 31,
            fontWeight: FontWeight.w900,
            color: color,
            height: 1,
          ),
        ),
        const SizedBox(height: 5),
        Text(
          title,
          style: const TextStyle(
            fontSize: 12,
            color: Color(0xFF64748B),
            fontWeight: FontWeight.w700,
          ),
        ),
      ],
    );
  }
}