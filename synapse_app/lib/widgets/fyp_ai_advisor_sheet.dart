// Paste class ini di bawah fyp_screen.dart (sebelum closing brace file)
// atau buat file terpisah lib/widgets/fyp_ai_advisor_sheet.dart

import 'package:flutter/material.dart';
import 'package:flutter_markdown/flutter_markdown.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';
import '../utils/constants.dart';
import '../utils/chat_notifier.dart';
import '../screens/material_list_screen.dart';

class FypAiAdvisorSheet extends StatefulWidget {
  final Map<String, dynamic> item;

  const FypAiAdvisorSheet({super.key, required this.item});

  @override
  State<FypAiAdvisorSheet> createState() => _FypAiAdvisorSheetState();
}

class _FypAiAdvisorSheetState extends State<FypAiAdvisorSheet> {
  static const Color _primary = Color(0xFF2A9D8F);

  String? _advice;
  bool _loading = false;
  bool _error   = false;

  // Key sama dengan ChatbotScreen agar history tersinkron
  static const String _historyKey = 'chat_history';

  Future<void> _getAdvice() async {
    setState(() { _loading = true; _error = false; });

    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('token') ?? '';

      final res = await http.post(
        Uri.parse('${AppConstants.baseUrl}/ai/fyp-advice'),
        headers: {
          'Authorization':  'Bearer $token',
          'Accept':         'application/json',
          'Content-Type':   'application/json',
        },
        body: jsonEncode({
          'course_title':  widget.item['course_title'] ?? '',
          'status_label':  widget.item['status_label'] ?? '',
          'avg_score':     widget.item['avg_score'],
          'passing_score': widget.item['passing_score'],
          'done_quizzes':  widget.item['done_quizzes'],
          'total_quizzes': widget.item['total_quizzes'],
          'insight':       widget.item['insight'] ?? '',
        }),
      );

      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        final advice = data['advice'] as String? ?? '';
        final chatMessages = data['chat_messages'] as List? ?? [];

        setState(() { _advice = advice; _loading = false; });

        // Simpan ke chat_history SharedPreferences
        // agar muncul di ChatbotScreen
        if (chatMessages.isNotEmpty) {
          await _appendToChatHistory(chatMessages);
          // Notify ChatbotScreen untuk reload history
          ChatNotifier.notify();
        }
      } else {
        setState(() { _loading = false; _error = true; });
      }
    } catch (_) {
      setState(() { _loading = false; _error = true; });
    }
  }

  Future<void> _appendToChatHistory(List newMessages) async {
    try {
      final prefs    = await SharedPreferences.getInstance();
      final existing = prefs.getString(_historyKey);
      final List history = existing != null
          ? List.from(jsonDecode(existing))
          : [];
      history.addAll(newMessages);
      await prefs.setString(_historyKey, jsonEncode(history));
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    final courseTitle = widget.item['course_title']?.toString() ?? 'Mata Kuliah';
    final courseId    = widget.item['course_id']?.toString() ?? '';
    final statusLabel = widget.item['status_label']?.toString() ?? '';
    final colorKey    = widget.item['status_color']?.toString() ?? '';

    final statusColor = _statusColor(colorKey);

    return Container(
      constraints: BoxConstraints(
        maxHeight: MediaQuery.of(context).size.height * 0.75,
      ),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Handle
          Center(child: Container(
            margin: const EdgeInsets.only(top: 12, bottom: 0),
            width: 36, height: 4,
            decoration: BoxDecoration(
              color: Colors.grey[300],
              borderRadius: BorderRadius.circular(10),
            ),
          )),

          // Header
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
            child: Row(children: [
              Container(
                width: 40, height: 40,
                decoration: BoxDecoration(
                  color: _primary.withOpacity(0.1),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.auto_awesome_rounded,
                    color: _primary, size: 20),
              ),
              const SizedBox(width: 12),
              Expanded(child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('SYNAPSE AI Advisor',
                      style: TextStyle(
                          fontSize: 15, fontWeight: FontWeight.bold,
                          color: Color(0xFF0F172A))),
                  Text(courseTitle,
                      style: TextStyle(fontSize: 12, color: Colors.grey[500]),
                      maxLines: 1, overflow: TextOverflow.ellipsis),
                ],
              )),
              // Status badge
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: statusColor.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(99),
                ),
                child: Text(statusLabel,
                    style: TextStyle(
                        fontSize: 11, fontWeight: FontWeight.w700,
                        color: statusColor)),
              ),
            ]),
          ),

          const Divider(height: 24, indent: 20, endIndent: 20),

          // Body
          Flexible(
            child: SingleChildScrollView(
              padding: const EdgeInsets.fromLTRB(20, 0, 20, 0),
              child: _buildBody(),
            ),
          ),

          // Buttons
          Padding(
            padding: EdgeInsets.fromLTRB(
                20, 12, 20,
                MediaQuery.of(context).padding.bottom + 16),
            child: Column(children: [
              if (_advice == null && !_loading)
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: _getAdvice,
                    icon: const Icon(Icons.auto_awesome_rounded, size: 18),
                    label: const Text('Tanya SYNAPSE',
                        style: TextStyle(fontWeight: FontWeight.bold)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: _primary,
                      foregroundColor: Colors.white,
                      elevation: 0,
                      padding: const EdgeInsets.symmetric(vertical: 13),
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(14)),
                    ),
                  ),
                ),
              if (_advice != null) ...[
                const SizedBox(height: 4),
                Row(children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: _getAdvice,
                      icon: const Icon(Icons.refresh_rounded, size: 16),
                      label: const Text('Minta Saran Lain'),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: _primary,
                        side: const BorderSide(color: Color(0xFF2A9D8F)),
                        padding: const EdgeInsets.symmetric(vertical: 11),
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: () {
                        Navigator.pop(context);
                        Navigator.push(context, MaterialPageRoute(
                          builder: (_) => MaterialListScreen(
                            courseId: courseId,
                            courseTitle: courseTitle,
                          ),
                        ));
                      },
                      icon: const Icon(Icons.play_circle_rounded, size: 16),
                      label: const Text('Pelajari',
                          style: TextStyle(fontWeight: FontWeight.bold)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: _primary,
                        foregroundColor: Colors.white,
                        elevation: 0,
                        padding: const EdgeInsets.symmetric(vertical: 11),
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ),
                ]),
              ],
            ]),
          ),
        ],
      ),
    );
  }

  Widget _buildBody() {
    if (_loading) {
      return Padding(
        padding: const EdgeInsets.symmetric(vertical: 32),
        child: Column(children: [
          const CircularProgressIndicator(color: _primary, strokeWidth: 2),
          const SizedBox(height: 16),
          Text('SYNAPSE sedang menganalisis performamu...',
              style: TextStyle(fontSize: 13, color: Colors.grey[500])),
        ]),
      );
    }

    if (_error) {
      return Padding(
        padding: const EdgeInsets.symmetric(vertical: 24),
        child: Column(children: [
          Icon(Icons.wifi_off_rounded, size: 40, color: Colors.grey[400]),
          const SizedBox(height: 10),
          Text('Gagal terhubung ke AI.\nCoba lagi ya!',
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 13, color: Colors.grey[500])),
          const SizedBox(height: 16),
          TextButton.icon(
            onPressed: _getAdvice,
            icon: const Icon(Icons.refresh_rounded, size: 16),
            label: const Text('Coba lagi'),
            style: TextButton.styleFrom(foregroundColor: _primary),
          ),
        ]),
      );
    }

    if (_advice == null) {
      return Padding(
        padding: const EdgeInsets.symmetric(vertical: 24),
        child: Column(children: [
          Container(
            width: 64, height: 64,
            decoration: BoxDecoration(
              color: _primary.withOpacity(0.08),
              shape: BoxShape.circle,
            ),
            child: const Icon(Icons.auto_awesome_rounded,
                color: _primary, size: 30),
          ),
          const SizedBox(height: 14),
          const Text('Mau tahu apa yang perlu diperbaiki?',
              style: TextStyle(
                  fontSize: 15, fontWeight: FontWeight.bold,
                  color: Color(0xFF0F172A))),
          const SizedBox(height: 6),
          Text('SYNAPSE AI akan analisis performamu\ndan kasih saran belajar yang spesifik.',
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 13, color: Colors.grey[500], height: 1.5)),
          const SizedBox(height: 8),
          // Info bahwa akan masuk ke chatbot
          Container(
            margin: const EdgeInsets.only(top: 8),
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            decoration: BoxDecoration(
              color: const Color(0xFFE6F4F2),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Row(children: [
              const Icon(Icons.chat_bubble_outline_rounded,
                  color: _primary, size: 16),
              const SizedBox(width: 8),
              const Expanded(
                child: Text(
                  'Hasil saran akan tersimpan di Chat SYNAPSE kamu',
                  style: TextStyle(fontSize: 12, color: _primary),
                ),
              ),
            ]),
          ),
        ]),
      );
    }

    // Tampilkan advice dengan markdown
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        // Info tersimpan di chat
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
          margin: const EdgeInsets.only(bottom: 14),
          decoration: BoxDecoration(
            color: const Color(0xFFE6F4F2),
            borderRadius: BorderRadius.circular(10),
          ),
          child: Row(children: [
            const Icon(Icons.check_circle_rounded, color: _primary, size: 15),
            const SizedBox(width: 8),
            const Text('Tersimpan di Chat SYNAPSE',
                style: TextStyle(fontSize: 12, color: _primary,
                    fontWeight: FontWeight.w600)),
          ]),
        ),
        MarkdownBody(
          data: _advice!,
          styleSheet: MarkdownStyleSheet(
            p: const TextStyle(
                fontSize: 14, color: Color(0xFF334155), height: 1.6),
            strong: const TextStyle(
                fontSize: 14, fontWeight: FontWeight.bold,
                color: Color(0xFF0F172A)),
            listBullet: const TextStyle(
                fontSize: 14, color: Color(0xFF2A9D8F)),
            h3: const TextStyle(
                fontSize: 15, fontWeight: FontWeight.bold,
                color: Color(0xFF0F172A)),
          ),
        ),
      ]),
    );
  }

  Color _statusColor(String? key) {
    switch (key) {
      case 'red':    return const Color(0xFFE75480);
      case 'orange':
      case 'yellow': return const Color(0xFFF4A62A);
      case 'blue':   return const Color(0xFF2D9CDB);
      default:       return const Color(0xFF2A9D8F);
    }
  }
}