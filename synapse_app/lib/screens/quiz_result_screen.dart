import 'package:flutter/material.dart';

class QuizResultScreen extends StatelessWidget {
  final String quizTitle;
  final Map<String, dynamic> resultData;

  const QuizResultScreen({
    super.key,
    required this.quizTitle,
    required this.resultData,
  });

  static const Color _primaryColor = Color(0xFF2A9D8F);

  @override
  Widget build(BuildContext context) {
    final int score = resultData['score'] ?? 0;
    final int earnedPoints = resultData['earned_points'] ?? 0;
    final int maxPoints = resultData['max_points'] ?? 0;
    final int correctAnswers = resultData['correct_answers'] ?? 0;
    final int totalQuestions = resultData['total_questions'] ?? 0;
    final bool isPassed = resultData['is_passed'] ?? false;
    final int passingScore = resultData['passing_score'] ?? 70;
    final List<dynamic> review = resultData['review'] ?? [];

    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: const Text('Hasil Kuis',
            style:
                TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF334155))),
        backgroundColor: Colors.white,
        elevation: 0,
        iconTheme: const IconThemeData(color: Color(0xFF334155)),
        leading: IconButton(
          icon: const Icon(Icons.close_rounded),
          onPressed: () {
            // Pop sampai ke quiz list (skip 2 layer: result + quiz screen)
            Navigator.of(context).popUntil((route) => route.isFirst);
            // Atau kalau mau balik 1 layer aja, ganti dengan: Navigator.pop(context);
          },
        ),
      ),
      body: SingleChildScrollView(
        physics: const BouncingScrollPhysics(),
        child: Column(
          children: [
            // ==========================================
            // 🎉 HEADER HERO — Skor Besar
            // ==========================================
            Container(
              width: double.infinity,
              padding: const EdgeInsets.fromLTRB(24, 30, 24, 50),
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: isPassed
                      ? [const Color(0xFF2A9D8F), const Color(0xFF21867A)]
                      : [Colors.orange[400]!, Colors.orange[700]!],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
              ),
              child: Column(
                children: [
                  // Icon hasil
                  Container(
                    width: 90,
                    height: 90,
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.2),
                      shape: BoxShape.circle,
                    ),
                    child: Icon(
                      isPassed
                          ? Icons.emoji_events_rounded
                          : Icons.psychology_alt_rounded,
                      size: 50,
                      color: Colors.white,
                    ),
                  ),
                  const SizedBox(height: 16),

                  Text(
                    isPassed ? '🎉 Selamat, Lulus!' : '💪 Tetap Semangat!',
                    style: const TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                        color: Colors.white),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    quizTitle,
                    style: TextStyle(
                        fontSize: 14, color: Colors.white.withOpacity(0.85)),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 24),

                  // Skor besar
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Text(
                        '$score',
                        style: const TextStyle(
                            fontSize: 80,
                            fontWeight: FontWeight.bold,
                            color: Colors.white,
                            height: 1),
                      ),
                      const Padding(
                        padding: EdgeInsets.only(bottom: 14),
                        child: Text(' / 100',
                            style: TextStyle(
                                fontSize: 24,
                                fontWeight: FontWeight.w500,
                                color: Colors.white70)),
                      ),
                    ],
                  ),
                  Text(
                    isPassed
                        ? 'Lulus dengan KKM $passingScore'
                        : 'KKM minimal $passingScore',
                    style: TextStyle(
                        fontSize: 13, color: Colors.white.withOpacity(0.85)),
                  ),
                ],
              ),
            ),

            // ==========================================
            // 📊 STATISTIK CARDS (Float di atas hero)
            // ==========================================
            Transform.translate(
              offset: const Offset(0, -28),
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                child: Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(20),
                    boxShadow: [
                      BoxShadow(
                          color: Colors.black.withOpacity(0.08),
                          blurRadius: 20,
                          offset: const Offset(0, 5))
                    ],
                  ),
                  child: Row(
                    children: [
                      Expanded(
                        child: _buildStatItem(
                          icon: Icons.task_alt_rounded,
                          label: 'Benar',
                          value: '$correctAnswers/$totalQuestions',
                          color: Colors.green[700]!,
                        ),
                      ),
                      Container(
                          width: 1, height: 40, color: Colors.grey[200]),
                      Expanded(
                        child: _buildStatItem(
                          icon: Icons.stars_rounded,
                          label: 'Poin',
                          value: '$earnedPoints/$maxPoints',
                          color: Colors.orange[700]!,
                        ),
                      ),
                      Container(
                          width: 1, height: 40, color: Colors.grey[200]),
                      Expanded(
                        child: _buildStatItem(
                          icon: Icons.percent_rounded,
                          label: 'Akurasi',
                          value: totalQuestions > 0
                              ? '${((correctAnswers / totalQuestions) * 100).round()}%'
                              : '0%',
                          color: _primaryColor,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),

            // ==========================================
            // 📋 REVIEW JAWABAN
            // ==========================================
            if (review.isNotEmpty) ...[
              Padding(
                padding: const EdgeInsets.fromLTRB(24, 16, 24, 12),
                child: Row(
                  children: [
                    Icon(Icons.fact_check_rounded,
                        color: _primaryColor, size: 22),
                    const SizedBox(width: 8),
                    const Text(
                      'Review Jawaban',
                      style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                          color: Color(0xFF334155)),
                    ),
                  ],
                ),
              ),
              ...review.asMap().entries.map((entry) {
                return _buildReviewCard(entry.key + 1, entry.value);
              }),
              const SizedBox(height: 30),
            ],
          ],
        ),
      ),
      bottomNavigationBar: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: SizedBox(
            width: double.infinity,
            height: 54,
            child: ElevatedButton.icon(
              icon: const Icon(Icons.home_rounded),
              label: const Text('Kembali ke Beranda',
                  style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      letterSpacing: 0.5)),
              style: ElevatedButton.styleFrom(
                backgroundColor: _primaryColor,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16)),
                elevation: 4,
                shadowColor: _primaryColor.withOpacity(0.4),
              ),
              onPressed: () {
                Navigator.of(context).popUntil((route) => route.isFirst);
              },
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildStatItem({
    required IconData icon,
    required String label,
    required String value,
    required Color color,
  }) {
    return Column(
      children: [
        Icon(icon, color: color, size: 24),
        const SizedBox(height: 6),
        Text(value,
            style: TextStyle(
                fontSize: 16, fontWeight: FontWeight.bold, color: color)),
        const SizedBox(height: 2),
        Text(label,
            style: TextStyle(fontSize: 11, color: Colors.grey[600])),
      ],
    );
  }

  // ==========================================
  // KARTU REVIEW PER SOAL
  // ==========================================
  Widget _buildReviewCard(int number, dynamic item) {
    final String question = item['question'] ?? '';
    final String questionType = item['question_type'] ?? 'multiple_choice';
    final String? imageUrl = item['image_url'];
    final dynamic userAnswer = item['user_answer'];
    final String? correctAnswer = item['correct_answer'];
    final List<dynamic> correctAnswers = item['correct_answers'] ?? [];
    final bool isCorrect = item['is_correct'] ?? false;
    final int earnedPoints = item['points_earned'] ?? 0;
    final int maxPoints = item['points_max'] ?? 0;
    final String? explanation = item['explanation'];

    final Color statusColor = isCorrect ? Colors.green[700]! : Colors.red[700]!;
    final Color statusBg =
        isCorrect ? Colors.green.withOpacity(0.08) : Colors.red.withOpacity(0.08);

    return Container(
      margin: const EdgeInsets.fromLTRB(20, 0, 20, 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.grey.shade200),
        boxShadow: [
          BoxShadow(
              color: Colors.black.withOpacity(0.03),
              blurRadius: 8,
              offset: const Offset(0, 2)),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header: Nomor + Status + Poin
          Row(
            children: [
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: statusBg,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      isCorrect
                          ? Icons.check_circle_rounded
                          : Icons.cancel_rounded,
                      size: 14,
                      color: statusColor,
                    ),
                    const SizedBox(width: 4),
                    Text('Soal $number',
                        style: TextStyle(
                            color: statusColor,
                            fontWeight: FontWeight.bold,
                            fontSize: 12)),
                  ],
                ),
              ),
              const Spacer(),
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: Colors.orange[50],
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  '⭐ $earnedPoints / $maxPoints pt',
                  style: TextStyle(
                      color: Colors.orange[800],
                      fontWeight: FontWeight.bold,
                      fontSize: 11),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),

          // Pertanyaan
          Text(
            question,
            style: const TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w600,
                height: 1.5,
                color: Color(0xFF334155)),
          ),

          // Gambar
          if (imageUrl != null && imageUrl.isNotEmpty) ...[
            const SizedBox(height: 12),
            ClipRRect(
              borderRadius: BorderRadius.circular(10),
              child: Image.network(
                imageUrl,
                width: double.infinity,
                fit: BoxFit.cover,
                height: 150,
                errorBuilder: (_, __, ___) => Container(
                  height: 80,
                  color: Colors.grey[200],
                  child: const Icon(Icons.broken_image, color: Colors.grey),
                ),
              ),
            ),
          ],

          const SizedBox(height: 14),

          // Box: Jawaban Kamu
          _buildAnswerRow(
            label: 'Jawabanmu',
            answer: _formatAnswer(userAnswer, questionType),
            color: isCorrect ? Colors.green[700]! : Colors.red[700]!,
            bgColor: statusBg,
            icon: isCorrect ? Icons.check_circle : Icons.close,
          ),

          // Box: Jawaban Benar (hanya jika user salah)
          if (!isCorrect) ...[
            const SizedBox(height: 8),
            _buildAnswerRow(
              label: 'Jawaban Benar',
              answer: _formatCorrectAnswer(
                  correctAnswer, correctAnswers, questionType),
              color: Colors.green[700]!,
              bgColor: Colors.green.withOpacity(0.08),
              icon: Icons.lightbulb_rounded,
            ),
          ],

          // Box: Penjelasan
          if (explanation != null && explanation.isNotEmpty) ...[
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFFFF8E1),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: Colors.amber[200]!),
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Icon(Icons.tips_and_updates_rounded,
                      color: Colors.amber[800], size: 18),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Penjelasan',
                            style: TextStyle(
                                color: Colors.amber[900],
                                fontWeight: FontWeight.bold,
                                fontSize: 12)),
                        const SizedBox(height: 4),
                        Text(
                          explanation,
                          style: TextStyle(
                              color: Colors.brown[800],
                              fontSize: 13,
                              height: 1.5),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildAnswerRow({
    required String label,
    required String answer,
    required Color color,
    required Color bgColor,
    required IconData icon,
  }) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: color.withOpacity(0.3)),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: color, size: 16),
          const SizedBox(width: 8),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(label,
                    style: TextStyle(
                        color: color,
                        fontWeight: FontWeight.bold,
                        fontSize: 11)),
                const SizedBox(height: 2),
                Text(answer,
                    style: TextStyle(
                        color: color, fontSize: 13, fontWeight: FontWeight.w600)),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // Format jawaban user untuk display
  String _formatAnswer(dynamic answer, String type) {
    if (answer == null) return 'Tidak dijawab';

    if (type == 'true_false') {
      final v = answer.toString().toUpperCase();
      if (v == 'A') return 'Benar / True';
      if (v == 'B') return 'Salah / False';
      return v;
    }

    if (answer is List) {
      if (answer.isEmpty) return 'Tidak dijawab';
      return answer.map((e) => e.toString().toUpperCase()).join(', ');
    }

    return answer.toString().toUpperCase();
  }

  String _formatCorrectAnswer(
      String? correct, List<dynamic> corrects, String type) {
    if (type == 'multiple_answer') {
      if (corrects.isEmpty) return '-';
      return corrects.map((e) => e.toString().toUpperCase()).join(', ');
    }

    if (type == 'true_false') {
      final v = (correct ?? '').toUpperCase();
      if (v == 'A') return 'Benar / True';
      if (v == 'B') return 'Salah / False';
      return v;
    }

    return (correct ?? '-').toUpperCase();
  }
}