import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter_markdown/flutter_markdown.dart';

class QuizHistoryScreen extends StatefulWidget {
  const QuizHistoryScreen({super.key});

  @override
  State<QuizHistoryScreen> createState() => _QuizHistoryScreenState();
}

class _QuizHistoryScreenState extends State<QuizHistoryScreen> {
  List<dynamic> _historyList = [];
  bool _isLoading = true;

  // Sesuaikan dengan baseUrl aplikasi Kapten
  final String baseUrl = 'http://192.168.1.21:8000/api'; 

  @override
  void initState() {
    super.initState();
    _fetchHistory(); 
  }

  Future<void> _fetchHistory() async {
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
        final data = jsonDecode(response.body);
        setState(() {
          _historyList = data['data']; 
          _isLoading = false;
        });
      } else {
        setState(() => _isLoading = false);
        debugPrint("Gagal mengambil data: ${response.statusCode}");
      }
    } catch (e) {
      setState(() => _isLoading = false);
      debugPrint("Sinyal terputus: $e");
    }
  }

  // 👇 PERBAIKAN 1: Tambahkan String quizId di sini
  Future<void> _getAIAdvice(String quizId, String quizTitle, int score) async {
    // Tampilkan loading berputar
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => const Center(child: CircularProgressIndicator(color: Colors.teal)),
    );

    SharedPreferences prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');

    try {
      final response = await http.post(
        Uri.parse('$baseUrl/analyze-score'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'quiz_id': quizId, // 👈 PERBAIKAN 2: Kirim ID-nya ke Laravel
          'quiz_title': quizTitle,
          'score': score
        }),
      );

      Navigator.pop(context); // Tutup loading

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        _showAdviceDialog(quizTitle, data['advice']); 
      } else {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Gagal meminta saran AI')));
      }
    } catch (e) {
      Navigator.pop(context); // Tutup loading
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
    }
  }

  void _showAdviceDialog(String title, String adviceText) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        titlePadding: const EdgeInsets.fromLTRB(20, 20, 20, 0),
        contentPadding: const EdgeInsets.fromLTRB(20, 16, 20, 20),
        title: Column( // Menggunakan Column menggantikan Row untuk menghindari overflow
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(color: Colors.teal.withOpacity(0.1), borderRadius: BorderRadius.circular(10)),
                  child: const Icon(Icons.memory_rounded, color: Colors.teal, size: 24),
                ),
                const SizedBox(width: 12),
                const Text('Analisa SYNAPSE', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
              ],
            ),
            const SizedBox(height: 12),
            Text(
              'Modul: $title', 
              style: TextStyle(fontSize: 14, color: Colors.blueGrey[600], fontStyle: FontStyle.italic),
            ),
            const Divider(),
          ],
        ),
        content: SizedBox(
          width: double.maxFinite, // Membatasi lebar agar text bisa wrap/turun ke bawah
          child: SingleChildScrollView(
            physics: const BouncingScrollPhysics(),
            child: MarkdownBody(
              data: adviceText,
              styleSheet: MarkdownStyleSheet(
                p: TextStyle(color: Colors.blueGrey[800], fontSize: 15, height: 1.5),
                strong: const TextStyle(fontWeight: FontWeight.bold, color: Colors.black87),
                listBullet: const TextStyle(color: Colors.teal),
              ),
            ), 
          ),
        ),
        actions: [
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: () => Navigator.pop(context),
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.teal,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              child: const Text('Tutup Log', style: TextStyle(fontWeight: FontWeight.bold)),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: const Text('Log Evaluasi', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
        backgroundColor: Colors.white, 
        foregroundColor: Colors.blueGrey[900],
        elevation: 1,
        shadowColor: Colors.black12,
        centerTitle: false,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: Colors.teal)) 
          : _historyList.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.inbox_rounded, size: 70, color: Colors.grey[300]),
                      const SizedBox(height: 16),
                      Text(
                        'Log kosong.\nSelesaikan modul untuk merekam jejak.',
                        textAlign: TextAlign.center,
                        style: TextStyle(fontSize: 16, color: Colors.grey[500]),
                      ),
                    ],
                  ),
                )
              : ListView.builder(
                  padding: const EdgeInsets.all(16),
                  physics: const BouncingScrollPhysics(),
                  itemCount: _historyList.length,
                  itemBuilder: (context, index) {
                    final item = _historyList[index];
                    
                    final int score = int.tryParse(item['score'].toString()) ?? 0;
                    final Color scoreColor = score >= 80 ? Colors.green[600]! : (score >= 60 ? Colors.amber[600]! : Colors.redAccent);

                    return Card(
                      elevation: 2,
                      shadowColor: Colors.black12,
                      margin: const EdgeInsets.only(bottom: 12),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(16),
                        side: BorderSide(color: Colors.grey.shade200),
                      ),
                      child: Padding(
                        padding: const EdgeInsets.all(12.0),
                        child: Row(
                          children: [
                            // Lingkaran Skor
                            Container(
                              width: 50,
                              height: 50,
                              decoration: BoxDecoration(
                                color: scoreColor.withOpacity(0.1),
                                shape: BoxShape.circle,
                                border: Border.all(color: scoreColor.withOpacity(0.5), width: 2),
                              ),
                              child: Center(
                                child: Text(
                                  score.toString(),
                                  style: TextStyle(color: scoreColor, fontWeight: FontWeight.bold, fontSize: 18),
                                ),
                              ),
                            ),
                            const SizedBox(width: 16),
                            
                            // Info Kuis
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    item['title'] ?? 'Unidentified Module', 
                                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Colors.black87),
                                    maxLines: 2,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                  const SizedBox(height: 4),
                                  Row(
                                    children: [
                                      Icon(Icons.calendar_today_rounded, size: 12, color: Colors.grey[500]),
                                      const SizedBox(width: 4),
                                      Text(
                                        item['created_at'].toString().substring(0, 10),
                                        style: TextStyle(fontSize: 12, color: Colors.grey[500], fontFamily: 'Courier'),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                            
                            // Tombol Analisa AI
                            ElevatedButton(
                              style: ElevatedButton.styleFrom(
                                backgroundColor: Colors.teal[50],
                                foregroundColor: Colors.teal[700],
                                elevation: 0,
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                              ),
                              onPressed: () {
                                _getAIAdvice(item['quiz_id'].toString(), item['title'] ?? 'Kuis', score);
                              },
                              child: const Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(Icons.auto_awesome_rounded, size: 16),
                                  SizedBox(width: 4),
                                  Text('Analisa', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                    );
                  },
                ),
    );
  }
}