// lib/screens/quiz_list_screen.dart
// Perubahan dari versi asli:
// - Tambah parameter courseId + courseTitle (optional)
// - Kalau ada courseId, filter kuis by matkul
// - Kalau dipanggil dari navbar tanpa parameter, tampil semua kuis (perilaku lama)
import 'package:flutter/material.dart';
import '../services/quiz_service.dart';
import '../models/quiz_model.dart';
import 'quiz_screen.dart';
import 'quiz_statistic_screen.dart';
import 'home_screen.dart';

class QuizListScreen extends StatefulWidget {
  final String? courseId;
  final String? courseTitle;

  const QuizListScreen({
    super.key,
    this.courseId,
    this.courseTitle,
  });

  @override
  State<QuizListScreen> createState() => _QuizListScreenState();
}

class _QuizListScreenState extends State<QuizListScreen> {
  final QuizService _quizService = QuizService();
  late Future<List<QuizModel>> _quizzesFuture;

  @override
  void initState() {
    super.initState();
    _loadQuizzes();
  }

  void _loadQuizzes() {
    setState(() {
      _quizzesFuture = _quizService.getQuizzes(courseId: widget.courseId);
    });
  }

  void _navigateToQuiz(QuizModel quiz) async {
    await Navigator.push(
      context,
      MaterialPageRoute(builder: (context) => QuizScreen(quiz: quiz)),
    );
    _loadQuizzes();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF2A9D8F),
      body: SafeArea(
        bottom: false,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(24, 30, 24, 20),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Kalau dipanggil dari matkul, tampilkan nama matkul
                        if (widget.courseTitle != null) ...[
                          Text(
                            widget.courseTitle!,
                            style: const TextStyle(
                              fontSize: 22,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                          const SizedBox(height: 4),
                          const Text(
                            'Pilih kuis & buktikan kemampuanmu!',
                            style: TextStyle(
                                fontSize: 13, color: Colors.white70),
                          ),
                        ] else ...[
                          const Text(
                            'Siap Mengerjakan?',
                            style: TextStyle(
                              fontSize: 24,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                            ),
                          ),
                          const SizedBox(height: 4),
                          const Text(
                            'Pilih Kuis & Buktikan!',
                            style: TextStyle(
                                fontSize: 14, color: Colors.white70),
                          ),
                        ],
                      ],
                    ),
                  ),
                  const SizedBox(width: 12),
                  // Tombol statistik — hanya tampil kalau tidak di-filter by course
                  if (widget.courseId == null)
                    GestureDetector(
                      onTap: () => Navigator.push(
                        context,
                        MaterialPageRoute(
                            builder: (_) => const QuizStatisticScreen()),
                      ),
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 12, vertical: 8),
                        decoration: BoxDecoration(
                          color: Colors.white.withOpacity(0.2),
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(
                              color: Colors.white.withOpacity(0.3)),
                        ),
                        child: const Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(Icons.bar_chart_rounded,
                                color: Colors.white, size: 18),
                            SizedBox(width: 6),
                            Text(
                              'Statistik',
                              style: TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.bold,
                                fontSize: 12,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                ],
              ),
            ),
            const SizedBox(height: 10),
            Expanded(
              child: Container(
                width: double.infinity,
                decoration: const BoxDecoration(
                  color: Color(0xFFF5F6F8),
                  borderRadius: BorderRadius.only(
                    topLeft: Radius.circular(30),
                    topRight: Radius.circular(30),
                  ),
                ),
                child: ClipRRect(
                  borderRadius: const BorderRadius.only(
                    topLeft: Radius.circular(30),
                    topRight: Radius.circular(30),
                  ),
                  child: RefreshIndicator(
                    color: const Color(0xFF2A9D8F),
                    onRefresh: () async => _loadQuizzes(),
                    child: FutureBuilder<List<QuizModel>>(
                      future: _quizzesFuture,
                      builder: (context, snapshot) {
                        if (snapshot.connectionState ==
                            ConnectionState.waiting) {
                          return const Center(
                            child: CircularProgressIndicator(
                                color: Color(0xFF2A9D8F)),
                          );
                        } else if (snapshot.hasError) {
                          return _buildInfoState(
                            'Gagal memuat data.\n${snapshot.error}',
                            Icons.error_outline_rounded,
                            Colors.red,
                          );
                        } else if (!snapshot.hasData ||
                            snapshot.data!.isEmpty) {
                          return _buildInfoState(
                            'Belum ada kuis tersedia.\nTunggu misi selanjutnya! 🏆',
                            Icons.task_alt_rounded,
                            const Color(0xFF2A9D8F),
                          );
                        }

                        final quizzes = snapshot.data!;
                        return ListView.builder(
                          physics: const AlwaysScrollableScrollPhysics(),
                          padding: EdgeInsets.only(
                            top: 24,
                            left: 24,
                            right: 24,
                            bottom: HomeScreen.navBarHeight,
                          ),
                          itemCount: quizzes.length,
                          itemBuilder: (context, index) =>
                              _buildQuizCard(context, quizzes[index]),
                        );
                      },
                    ),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildQuizCard(BuildContext context, QuizModel quiz) {
    return GestureDetector(
      onTap: () => _navigateToQuiz(quiz),
      child: Container(
        margin: const EdgeInsets.only(bottom: 16),
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: const Color(0xFFE2E6E9),
          borderRadius: BorderRadius.circular(20),
        ),
        child: Row(
          children: [
            Container(
              width: 60,
              height: 60,
              decoration: BoxDecoration(
                color: const Color(0xFF2A9D8F).withOpacity(0.15),
                borderRadius: BorderRadius.circular(16),
              ),
              child: const Icon(Icons.quiz_rounded,
                  color: Color(0xFF2A9D8F), size: 30),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    quiz.title,
                    style: const TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF1A1A2E),
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 6),
                  Row(
                    children: [
                      const Icon(Icons.timer_outlined,
                          size: 14, color: Color(0xFF64748B)),
                      const SizedBox(width: 4),
                      Text(
                        '${quiz.durationMinutes} menit',
                        style: const TextStyle(
                          fontSize: 12,
                          color: Color(0xFF64748B),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const Icon(Icons.arrow_forward_ios_rounded,
                color: Color(0xFF2A9D8F), size: 16),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoState(String msg, IconData icon, Color color) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, size: 60, color: color.withOpacity(0.4)),
            const SizedBox(height: 16),
            Text(
              msg,
              textAlign: TextAlign.center,
              style: TextStyle(
                  fontSize: 15, color: color.withOpacity(0.7), height: 1.5),
            ),
          ],
        ),
      ),
    );
  }
}