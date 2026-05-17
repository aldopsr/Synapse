import 'dart:async';
import 'package:flutter/material.dart';
import '../services/quiz_service.dart';
import '../models/quiz_model.dart';
import '../models/question_model.dart';
import 'quiz_result_screen.dart';

class QuizScreen extends StatefulWidget {
  final QuizModel quiz;

  const QuizScreen({super.key, required this.quiz});

  @override
  State<QuizScreen> createState() => _QuizScreenState();
}

class _QuizScreenState extends State<QuizScreen> {
  final QuizService _quizService = QuizService();
  final PageController _pageController = PageController();

  List<QuestionModel> _questions = [];
  bool _isLoading = true;
  bool _isSubmitting = false;

  // 🌟 _answers sekarang dynamic: bisa String (PG/TF) atau List<String> (Multi-Answer)
  final Map<String, dynamic> _answers = {};

  Timer? _timer;
  int _remainingSeconds = 0;
  int _timeTakenSeconds = 0;

  static const Color _primaryColor = Color(0xFF2A9D8F);

  @override
  void initState() {
    super.initState();
    _remainingSeconds = widget.quiz.durationMinutes * 60;
    _loadQuestions();
  }

  Future<void> _loadQuestions() async {
    try {
      final questions = await _quizService.getQuestions(widget.quiz.id);
      setState(() {
        _questions = questions;
        _isLoading = false;
      });
      _startTimer();
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal memuat soal: $e')));
      Navigator.pop(context);
    }
  }

  void _startTimer() {
    _timer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (_remainingSeconds > 0) {
        setState(() {
          _remainingSeconds--;
          _timeTakenSeconds++;
        });
      } else {
        _timer?.cancel();
        _submitQuiz();
      }
    });
  }

  String get _formattedTime {
    int minutes = _remainingSeconds ~/ 60;
    int seconds = _remainingSeconds % 60;
    return '${minutes.toString().padLeft(2, '0')}:${seconds.toString().padLeft(2, '0')}';
  }

  // 🌟 Hitung berapa soal yang sudah dijawab
  int get _answeredCount {
    return _answers.entries.where((e) {
      final v = e.value;
      if (v is String) return v.isNotEmpty;
      if (v is List) return v.isNotEmpty;
      return false;
    }).length;
  }

  Future<void> _submitQuiz() async {
    if (_isSubmitting) return;

    setState(() => _isSubmitting = true);
    _timer?.cancel();

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) =>
          const Center(child: CircularProgressIndicator(color: _primaryColor)),
    );

    // 🌟 Format jawaban untuk dikirim — handle string & list
    List<Map<String, dynamic>> formattedAnswers = _answers.entries.map((e) {
      return {
        'question_id': e.key,
        'answer': e.value, // bisa string atau List<String>
      };
    }).toList();

    try {
      final result = await _quizService.submitQuiz(
        quizId: widget.quiz.id,
        timeTakenSeconds: _timeTakenSeconds,
        answers: formattedAnswers,
      );

      if (mounted) Navigator.pop(context); // tutup loading

      if (result != null) {
        // 🌟 Navigate ke halaman hasil baru (bukan dialog)
        if (mounted) {
          Navigator.pushReplacement(
            context,
            MaterialPageRoute(
              builder: (context) => QuizResultScreen(
                quizTitle: widget.quiz.title,
                resultData: result,
              ),
            ),
          );
        }
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Gagal mengirim jawaban')),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        Navigator.pop(context);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e')),
        );
      }
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  @override
  void dispose() {
    _timer?.cancel();
    _pageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(
          body: Center(child: CircularProgressIndicator(color: _primaryColor)));
    }

    if (_questions.isEmpty) {
      return Scaffold(
        appBar: AppBar(title: Text(widget.quiz.title)),
        body: const Center(child: Text('Belum ada soal di kuis ini.')),
      );
    }

    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(widget.quiz.title,
            style:
                const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black87,
        elevation: 1,
        shadowColor: Colors.black12,
        actions: [
          Container(
            margin: const EdgeInsets.only(right: 16, top: 10, bottom: 10),
            padding: const EdgeInsets.symmetric(horizontal: 12),
            decoration: BoxDecoration(
              color: _remainingSeconds < 60
                  ? Colors.red.withOpacity(0.1)
                  : _primaryColor.withOpacity(0.1),
              borderRadius: BorderRadius.circular(20),
              border: Border.all(
                  color: _remainingSeconds < 60
                      ? Colors.red
                      : _primaryColor),
            ),
            child: Row(
              children: [
                Icon(Icons.timer_rounded,
                    size: 16,
                    color: _remainingSeconds < 60
                        ? Colors.red
                        : _primaryColor),
                const SizedBox(width: 6),
                Text(
                  _formattedTime,
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.bold,
                    color: _remainingSeconds < 60
                        ? Colors.red
                        : _primaryColor,
                    fontFamily: 'Courier',
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
      body: Column(
        children: [
          LinearProgressIndicator(
            value: _answeredCount / _questions.length,
            backgroundColor: Colors.grey[200],
            color: _primaryColor,
            minHeight: 4,
          ),
          Padding(
            padding: const EdgeInsets.all(16.0),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text('Tantangan Terpecahkan:',
                    style: TextStyle(
                        color: Colors.blueGrey[400],
                        fontWeight: FontWeight.w600)),
                Text('$_answeredCount / ${_questions.length}',
                    style: const TextStyle(
                        fontWeight: FontWeight.bold, color: _primaryColor)),
              ],
            ),
          ),
          Expanded(
            child: PageView.builder(
              controller: _pageController,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: _questions.length,
              itemBuilder: (context, index) {
                return _buildQuestionCard(_questions[index], index);
              },
            ),
          ),
        ],
      ),
    );
  }

  // ============================================================
  // 🌟 RENDER SOAL — DINAMIS PER TIPE
  // ============================================================
  Widget _buildQuestionCard(QuestionModel question, int index) {
    return SingleChildScrollView(
      padding: const EdgeInsets.symmetric(horizontal: 20.0, vertical: 10.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // ===== KARTU UTAMA =====
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: Colors.grey.shade200),
              boxShadow: [
                BoxShadow(
                    color: Colors.grey.withOpacity(0.05),
                    blurRadius: 10,
                    offset: const Offset(0, 5))
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // 🌟 Badge meta: Soal X, Tipe, Kesulitan, Poin
                Wrap(
                  spacing: 6,
                  runSpacing: 6,
                  children: [
                    _buildMetaBadge(
                        'Soal ${index + 1}', Colors.blueGrey[700]!,
                        Colors.blueGrey[50]!),
                    _buildMetaBadge(
                        question.typeLabel, _primaryColor,
                        _primaryColor.withOpacity(0.1)),
                    _buildMetaBadge(question.difficultyLabel,
                        _getDifficultyColor(question.difficulty),
                        _getDifficultyColor(question.difficulty)
                            .withOpacity(0.15)),
                    _buildMetaBadge('⭐ ${question.points} pt',
                        Colors.orange[800]!, Colors.orange[50]!),
                  ],
                ),
                const SizedBox(height: 16),

                // 🌟 Pertanyaan
                Text(
                  question.questionText,
                  style: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.w600,
                      height: 1.5,
                      color: Colors.black87),
                ),

                // 🌟 Gambar (jika ada)
                if (question.imageUrl != null &&
                    question.imageUrl!.isNotEmpty) ...[
                  const SizedBox(height: 16),
                  ClipRRect(
                    borderRadius: BorderRadius.circular(12),
                    child: Image.network(
                      question.imageUrl!,
                      width: double.infinity,
                      fit: BoxFit.contain,
                      loadingBuilder: (context, child, loadingProgress) {
                        if (loadingProgress == null) return child;
                        return Container(
                          height: 180,
                          color: Colors.grey[100],
                          child: const Center(
                            child: CircularProgressIndicator(
                                strokeWidth: 2, color: _primaryColor),
                          ),
                        );
                      },
                      errorBuilder: (context, error, stackTrace) {
                        return Container(
                          height: 100,
                          color: Colors.grey[200],
                          child: const Center(
                            child: Icon(Icons.broken_image,
                                size: 40, color: Colors.grey),
                          ),
                        );
                      },
                    ),
                  ),
                ],
              ],
            ),
          ),
          const SizedBox(height: 24),

          // 🌟 RENDER OPSI BERDASARKAN TIPE SOAL
          if (question.isTrueFalse) ...[
            _buildTrueFalseOptions(question),
          ] else if (question.isMultiAnswer) ...[
            _buildMultiAnswerOptions(question),
          ] else ...[
            _buildMultipleChoiceOptions(question),
          ],

          const SizedBox(height: 30),

          // ===== TOMBOL NAVIGASI =====
          Row(
            children: [
              if (index > 0)
                Expanded(
                  child: OutlinedButton(
                    onPressed: () => _pageController.previousPage(
                        duration: const Duration(milliseconds: 300),
                        curve: Curves.easeInOut),
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      side: BorderSide(color: _primaryColor.withOpacity(0.5)),
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(14)),
                    ),
                    child: const Text('Mundur',
                        style: TextStyle(
                            color: _primaryColor,
                            fontWeight: FontWeight.bold)),
                  ),
                ),
              if (index > 0) const SizedBox(width: 16),
              Expanded(
                flex: 2,
                child: ElevatedButton(
                  onPressed: () {
                    if (index == _questions.length - 1) {
                      _confirmSubmit();
                    } else {
                      _pageController.nextPage(
                          duration: const Duration(milliseconds: 300),
                          curve: Curves.easeInOut);
                    }
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: index == _questions.length - 1
                        ? Colors.green[600]
                        : _primaryColor,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    elevation: 4,
                    shadowColor: _primaryColor.withOpacity(0.4),
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14)),
                  ),
                  child: Text(
                    index == _questions.length - 1
                        ? 'KIRIM HASIL'
                        : 'LANJUTKAN',
                    style: const TextStyle(
                        fontWeight: FontWeight.bold, letterSpacing: 1),
                  ),
                ),
              ),
            ],
          )
        ],
      ),
    );
  }

  // ============================================================
  // OPSI: PILIHAN GANDA (radio)
  // ============================================================
  Widget _buildMultipleChoiceOptions(QuestionModel q) {
    final options = [
      {'value': 'A', 'text': q.optionA},
      {'value': 'B', 'text': q.optionB},
      {'value': 'C', 'text': q.optionC},
      {'value': 'D', 'text': q.optionD},
    ];

    return Column(
      children: options.map((opt) {
        if ((opt['text'] as String).isEmpty) return const SizedBox.shrink();
        return _buildRadioOption(q.id, opt['value']!, opt['text']!);
      }).toList(),
    );
  }

  // ============================================================
  // OPSI: TRUE/FALSE (2 tombol besar berdampingan)
  // ============================================================
  Widget _buildTrueFalseOptions(QuestionModel q) {
    final selected = _answers[q.id] as String?;
    return Row(
      children: [
        Expanded(
          child: _buildTFButton(q.id, 'A', '✓', 'BENAR', Colors.green,
              selected == 'A'),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: _buildTFButton(q.id, 'B', '✗', 'SALAH', Colors.red,
              selected == 'B'),
        ),
      ],
    );
  }

  Widget _buildTFButton(String questionId, String value, String icon,
      String label, Color color, bool isSelected) {
    return GestureDetector(
      onTap: () {
        setState(() {
          _answers[questionId] = value;
        });
      },
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(vertical: 24),
        decoration: BoxDecoration(
          color: isSelected ? color.withOpacity(0.1) : Colors.white,
          border: Border.all(
            color: isSelected ? color : Colors.grey.shade300,
            width: isSelected ? 2 : 1,
          ),
          borderRadius: BorderRadius.circular(16),
        ),
        child: Column(
          children: [
            Text(
              icon,
              style: TextStyle(
                fontSize: 36,
                fontWeight: FontWeight.bold,
                color: isSelected ? color : Colors.grey[400],
              ),
            ),
            const SizedBox(height: 8),
            Text(
              label,
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
                color: isSelected ? color : Colors.grey[600],
                letterSpacing: 1,
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ============================================================
  // OPSI: MULTI-ANSWER (checkbox)
  // ============================================================
  Widget _buildMultiAnswerOptions(QuestionModel q) {
    final options = [
      {'value': 'A', 'text': q.optionA},
      {'value': 'B', 'text': q.optionB},
      {'value': 'C', 'text': q.optionC},
      {'value': 'D', 'text': q.optionD},
    ];

    return Column(
      children: [
        // Hint info
        Container(
          margin: const EdgeInsets.only(bottom: 12),
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: Colors.amber[50],
            borderRadius: BorderRadius.circular(10),
            border: Border.all(color: Colors.amber[200]!),
          ),
          child: Row(
            children: [
              Icon(Icons.info_outline, size: 18, color: Colors.amber[800]),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  'Pilih SEMUA jawaban yang menurutmu benar',
                  style: TextStyle(
                      fontSize: 12,
                      color: Colors.amber[900],
                      fontWeight: FontWeight.w600),
                ),
              ),
            ],
          ),
        ),
        ...options.map((opt) {
          if ((opt['text'] as String).isEmpty) return const SizedBox.shrink();
          return _buildCheckboxOption(q.id, opt['value']!, opt['text']!);
        }),
      ],
    );
  }

  // ============================================================
  // OPTION WIDGETS (Radio & Checkbox)
  // ============================================================
  Widget _buildRadioOption(String questionId, String value, String text) {
    final isSelected = _answers[questionId] == value;

    return GestureDetector(
      onTap: () {
        setState(() {
          _answers[questionId] = value;
        });
      },
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        margin: const EdgeInsets.only(bottom: 12),
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: isSelected ? _primaryColor.withOpacity(0.08) : Colors.white,
          border: Border.all(
            color: isSelected ? _primaryColor : Colors.grey.shade300,
            width: isSelected ? 2 : 1,
          ),
          borderRadius: BorderRadius.circular(16),
        ),
        child: Row(
          children: [
            Container(
              width: 32,
              height: 32,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: isSelected ? _primaryColor : Colors.grey.shade100,
              ),
              child: Center(
                child: Text(
                  value,
                  style: TextStyle(
                    color: isSelected ? Colors.white : Colors.blueGrey[400],
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Text(
                text,
                style: TextStyle(
                  fontSize: 16,
                  color: isSelected ? _primaryColor : Colors.black87,
                  fontWeight:
                      isSelected ? FontWeight.w600 : FontWeight.normal,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCheckboxOption(String questionId, String value, String text) {
    final currentList = (_answers[questionId] as List<dynamic>?) ?? [];
    final isChecked = currentList.contains(value);

    return GestureDetector(
      onTap: () {
        setState(() {
          final list = List<String>.from(_answers[questionId] ?? []);
          if (isChecked) {
            list.remove(value);
          } else {
            list.add(value);
          }
          _answers[questionId] = list;
        });
      },
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        margin: const EdgeInsets.only(bottom: 12),
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: isChecked ? _primaryColor.withOpacity(0.08) : Colors.white,
          border: Border.all(
            color: isChecked ? _primaryColor : Colors.grey.shade300,
            width: isChecked ? 2 : 1,
          ),
          borderRadius: BorderRadius.circular(16),
        ),
        child: Row(
          children: [
            Container(
              width: 32,
              height: 32,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(8),
                color: isChecked ? _primaryColor : Colors.grey.shade100,
              ),
              child: Center(
                child: isChecked
                    ? const Icon(Icons.check,
                        color: Colors.white, size: 20)
                    : Text(
                        value,
                        style: TextStyle(
                            color: Colors.blueGrey[400],
                            fontWeight: FontWeight.bold),
                      ),
              ),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Text(
                text,
                style: TextStyle(
                  fontSize: 16,
                  color: isChecked ? _primaryColor : Colors.black87,
                  fontWeight:
                      isChecked ? FontWeight.w600 : FontWeight.normal,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ============================================================
  // HELPERS
  // ============================================================
  Widget _buildMetaBadge(String text, Color textColor, Color bgColor) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Text(text,
          style: TextStyle(
              color: textColor, fontWeight: FontWeight.bold, fontSize: 11)),
    );
  }

  Color _getDifficultyColor(String difficulty) {
    switch (difficulty) {
      case 'mudah':
        return Colors.green[700]!;
      case 'sulit':
        return Colors.red[700]!;
      default:
        return Colors.amber[800]!;
    }
  }

  void _confirmSubmit() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text('Kirim Hasil Analisis?'),
        content: Text(
            'Kamu sudah menjawab $_answeredCount dari ${_questions.length} soal.\nSistem akan memproses skormu sekarang.'),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Batal',
                  style: TextStyle(color: Colors.grey))),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _submitQuiz();
            },
            style: ElevatedButton.styleFrom(
                backgroundColor: Colors.green,
                foregroundColor: Colors.white),
            child: const Text('Ya, Eksekusi'),
          ),
        ],
      ),
    );
  }
}