import 'package:flutter/material.dart';
import 'quiz_review_screen.dart';

class QuizStatisticScreen extends StatefulWidget {
  const QuizStatisticScreen({super.key});

  @override
  State<QuizStatisticScreen> createState() => _QuizStatisticScreenState();
}

class _QuizStatisticScreenState extends State<QuizStatisticScreen> {
  // 👇 INI DATA DUMMY. Nanti Kapten bisa ganti dengan data hasil fetch dari API ya!
  final List<Map<String, dynamic>> _historyKuis = [
    {
      'title': 'Pengenalan Komputer',
      'score': 100,
      'date': 'Hari ini',
      // Data 'questions' ini yang akan dikirim ke QuizReviewScreen
      'questions': [
        {'question': 'Apa kepanjangan dari CPU?', 'correct_answer': 'Central Processing Unit'},
        {'question': 'Perangkat keras yang berfungsi sebagai otak komputer adalah?', 'correct_answer': 'Prosesor'},
      ]
    },
    {
      'title': 'Jaringan Dasar',
      'score': 80,
      'date': 'Kemarin',
      'questions': [
        {'question': 'Protokol yang digunakan untuk mengirim email adalah?', 'correct_answer': 'SMTP'},
      ]
    }
  ];

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
      body: SingleChildScrollView(
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
              child: const Row(
                mainAxisAlignment: MainAxisAlignment.spaceAround,
                children: [
                  _StatItem(title: 'Kuis Selesai', value: '12', icon: Icons.task_alt_rounded),
                  _StatItem(title: 'Rata-rata', value: '85', icon: Icons.auto_graph_rounded),
                ],
              ),
            ),
            
            const SizedBox(height: 32),
            const Text('Riwayat Kuis', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF334155))),
            const SizedBox(height: 16),

            // --- LIST RIWAYAT UNTUK MENUJU REVIEW SCREEN ---
            ListView.builder(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: _historyKuis.length,
              itemBuilder: (context, index) {
                final history = _historyKuis[index];
                
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
                          color: history['score'] >= 80 ? const Color(0xFFE8F5E9) : const Color(0xFFFFF3E0),
                          shape: BoxShape.circle,
                        ),
                        child: Center(
                          child: Text(
                            '${history['score']}',
                            style: TextStyle(
                              fontWeight: FontWeight.bold, 
                              color: history['score'] >= 80 ? const Color(0xFF2E7D32) : Colors.orange[800],
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(history['title'], style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Color(0xFF334155))),
                            const SizedBox(height: 4),
                            Text(history['date'], style: TextStyle(fontSize: 12, color: Colors.grey[500])),
                          ],
                        ),
                      ),
                      // 👇 TOMBOL DIRECT KE REVIEW SCREEN
                      TextButton(
                        style: TextButton.styleFrom(
                          foregroundColor: const Color(0xFF2A9D8F),
                          backgroundColor: const Color(0xFF2A9D8F).withOpacity(0.1),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        onPressed: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (context) => QuizReviewScreen(
                                quizTitle: history['title'],
                                questions: history['questions'], // Kirim data soalnya
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
      ),
    );
  }
}

// Widget kecil untuk kotak statistik di atas
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