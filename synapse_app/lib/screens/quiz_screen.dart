import 'dart:async';
import 'package:flutter/material.dart';
import '../services/quiz_service.dart';
import '../models/quiz_model.dart';
import '../models/question_model.dart';
bool _isSubmitting = false;

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
  
  // 👇 PERBAIKAN 1: Map diubah menjadi String, String
  final Map<String, String> _answers = {}; 

  Timer? _timer;
  int _remainingSeconds = 0;
  int _timeTakenSeconds = 0;

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
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal memuat soal: $e')));
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

  Future<void> _submitQuiz() async {
  if (_isSubmitting) return; // cegah double klik

  setState(() => _isSubmitting = true);
  _timer?.cancel();

  showDialog(
    context: context,
    barrierDismissible: false,
    builder: (context) =>
        const Center(child: CircularProgressIndicator(color: Colors.teal)),
  );

  List<Map<String, dynamic>> formattedAnswers =
      _answers.entries.map((e) {
    return {'question_id': e.key, 'answer': e.value};
  }).toList();

  try {
    final result = await _quizService.submitQuiz(
      quizId: widget.quiz.id,
      timeTakenSeconds: _timeTakenSeconds,
      answers: formattedAnswers,
    );

    Navigator.pop(context);

    if (result != null) {
      _showResultDialog(
        result['score'] ?? 0,
        result['correct_answers'] ?? 0,
        result['total_questions'] ?? 0,
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Gagal mengirim jawaban')),
      );
    }
  } catch (e) {
    Navigator.pop(context);
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('Error: $e')),
    );
  } finally {
    setState(() => _isSubmitting = false);
  }
}

  void _showResultDialog(int score, int correct, int total) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) {
        return AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: const Column(
            children: [
              Icon(Icons.check_circle_outline_rounded, color: Colors.teal, size: 60),
              SizedBox(height: 10),
              Text('Evaluasi Selesai!', textAlign: TextAlign.center, style: TextStyle(fontWeight: FontWeight.bold)),
            ],
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text('Skor Akhir:', style: TextStyle(fontSize: 16, color: Colors.grey[600])),
              Text('$score', style: const TextStyle(fontSize: 60, fontWeight: FontWeight.bold, color: Colors.teal)),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(color: Colors.teal.withOpacity(0.1), borderRadius: BorderRadius.circular(20)),
                child: Text('Benar $correct dari $total', style: const TextStyle(color: Colors.teal, fontWeight: FontWeight.bold)),
              )
            ],
          ),
          actions: [
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () {
                  Navigator.pop(context); 
                  Navigator.pop(context); 
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.teal,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: const Text('Kembali ke Dashboard'),
              ),
            ),
          ],
        );
      },
    );
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
      return const Scaffold(body: Center(child: CircularProgressIndicator(color: Colors.teal)));
    }

    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(widget.quiz.title, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
        backgroundColor: Colors.white,
        foregroundColor: Colors.black87,
        elevation: 1,
        shadowColor: Colors.black12,
        actions: [
          Container(
            margin: const EdgeInsets.only(right: 16, top: 10, bottom: 10),
            padding: const EdgeInsets.symmetric(horizontal: 12),
            decoration: BoxDecoration(
              color: _remainingSeconds < 60 ? Colors.red.withOpacity(0.1) : Colors.teal.withOpacity(0.1),
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: _remainingSeconds < 60 ? Colors.red : Colors.teal),
            ),
            child: Row(
              children: [
                Icon(Icons.timer_rounded, size: 16, color: _remainingSeconds < 60 ? Colors.red : Colors.teal),
                const SizedBox(width: 6),
                Text(
                  _formattedTime,
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.bold,
                    color: _remainingSeconds < 60 ? Colors.red : Colors.teal,
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
            value: _answers.length / _questions.length,
            backgroundColor: Colors.grey[200],
            color: Colors.teal,
            minHeight: 4,
          ),
          Padding(
            padding: const EdgeInsets.all(16.0),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text('Tantangan Terpecahkan:', style: TextStyle(color: Colors.blueGrey[400], fontWeight: FontWeight.w600)),
                Text('${_answers.length} / ${_questions.length}', style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.teal)),
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

  Widget _buildQuestionCard(QuestionModel question, int index) {
    return SingleChildScrollView(
      padding: const EdgeInsets.symmetric(horizontal: 20.0, vertical: 10.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: Colors.grey.shade200),
              boxShadow: [BoxShadow(color: Colors.grey.withOpacity(0.05), blurRadius: 10, offset: const Offset(0, 5))],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(color: Colors.blueGrey[50], borderRadius: BorderRadius.circular(8)),
                  child: Text('Soal ${index + 1}', style: TextStyle(color: Colors.blueGrey[700], fontWeight: FontWeight.bold, fontSize: 12)),
                ),
                const SizedBox(height: 16),
                Text(
                  question.questionText, 
                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w600, height: 1.5, color: Colors.black87),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),
          
          _buildOption(question.id, 'a', question.optionA),
          _buildOption(question.id, 'b', question.optionB),
          _buildOption(question.id, 'c', question.optionC),
          _buildOption(question.id, 'd', question.optionD),
          
          const SizedBox(height: 30),
          
          Row(
            children: [
              if (index > 0)
                Expanded(
                  child: OutlinedButton(
                    onPressed: () => _pageController.previousPage(duration: const Duration(milliseconds: 300), curve: Curves.easeInOut),
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      side: BorderSide(color: Colors.teal.shade200),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                    child: const Text('Mundur', style: TextStyle(color: Colors.teal, fontWeight: FontWeight.bold)),
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
                      _pageController.nextPage(duration: const Duration(milliseconds: 300), curve: Curves.easeInOut);
                    }
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: index == _questions.length - 1 ? Colors.green[600] : Colors.teal,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    elevation: 4,
                    shadowColor: Colors.teal.withOpacity(0.4),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                  child: Text(
                    index == _questions.length - 1 ? 'KIRIM HASIL' : 'LANJUTKAN',
                    style: const TextStyle(fontWeight: FontWeight.bold, letterSpacing: 1),
                  ),
                ),
              ),
            ],
          )
        ],
      ),
    );
  }

  // 👇 PERBAIKAN 2: questionId diubah menjadi String
  Widget _buildOption(String questionId, String optionValue, String optionText) {
    bool isSelected = _answers[questionId] == optionValue;
    
    return GestureDetector(
      onTap: () {
        setState(() {
          _answers[questionId] = optionValue;
        });
      },
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        margin: const EdgeInsets.only(bottom: 12),
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: isSelected ? Colors.teal.withOpacity(0.08) : Colors.white,
          border: Border.all(
            color: isSelected ? Colors.teal : Colors.grey.shade300,
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
                color: isSelected ? Colors.teal : Colors.grey.shade100,
              ),
              child: Center(
                child: Text(
                  optionValue.toUpperCase(),
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
                optionText,
                style: TextStyle(
                  fontSize: 16,
                  color: isSelected ? Colors.teal.shade900 : Colors.black87,
                  fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _confirmSubmit() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text('Kirim Hasil Analisis?'),
        content: Text('Kamu sudah menjawab ${_answers.length} dari ${_questions.length} soal.\nSistem akan memproses skormu sekarang.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context), 
            child: const Text('Batal', style: TextStyle(color: Colors.grey))
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context); 
              _submitQuiz(); 
            },
            style: ElevatedButton.styleFrom(backgroundColor: Colors.green, foregroundColor: Colors.white),
            child: const Text('Ya, Eksekusi'),
          ),
        ],
      ),
    );
  }
}