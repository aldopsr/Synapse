import 'package:flutter/material.dart';
import 'package:flutter_markdown/flutter_markdown.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';

class ChatMessage {
  final String text;
  final bool isUser;
  ChatMessage({required this.text, required this.isUser});
}

class ChatbotScreen extends StatefulWidget {
  const ChatbotScreen({super.key});

  @override
  State<ChatbotScreen> createState() => _ChatbotScreenState();
}

class _ChatbotScreenState extends State<ChatbotScreen> {
  final TextEditingController _controller = TextEditingController();
  final List<ChatMessage> _messages = [];
  bool _isLoading = false;

  final ScrollController _scrollController = ScrollController();
  
  // URL BACKEND KAPTEN
  final String baseUrl = 'http://192.168.1.14:8000/api'; 

  // DAFTAR REKOMENDASI PERTANYAAN
  final List<String> _promptStarters = [
    "Apa itu Bahasa Pemrograman R?",
    "Gimana cara knit PDF di RStudio?",
    "Berikan contoh koding Regresi Linear",
    "Cara import dataset CSV ke R",
    "Jelaskan apa itu Tidyverse"
  ];

  Future<String?> _getToken() async {
    SharedPreferences prefs = await SharedPreferences.getInstance();
    return prefs.getString('token');
  }

  void _sendPrompt(String text) {
    _controller.text = text;
    _sendMessage();
  }

  void _sendMessage() async {
    if (_controller.text.trim().isEmpty) return;

    String userText = _controller.text;
    
    setState(() {
      _messages.add(ChatMessage(text: userText, isUser: true));
      _isLoading = true;
    });

    _scrollToBottom();
    _controller.clear();

    try {
      final token = await _getToken();
      
      final response = await http.post(
        Uri.parse('$baseUrl/chat'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'message': userText
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        setState(() {
          _messages.add(ChatMessage(text: data['reply'], isUser: false));
        });
        _scrollToBottom();
      } else {
        setState(() {
          _messages.add(ChatMessage(text: "Waduh, server menolak koneksi: ${response.statusCode}", isUser: false));
        });
      }
    } catch (e) {
      setState(() {
        _messages.add(ChatMessage(text: "Koneksi ke server terputus: $e", isUser: false));
      });
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  void _clearChat() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.warning_amber_rounded, color: Colors.red),
            SizedBox(width: 8),
            Text('Format Memori?'),
          ],
        ),
        content: const Text('Apakah Kapten yakin ingin menghapus seluruh log percakapan dengan SYNAPSE?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context), 
            child: const Text('Batal', style: TextStyle(color: Colors.grey)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red, foregroundColor: Colors.white),
            onPressed: () {
              setState(() {
                _messages.clear(); 
              });
              Navigator.pop(context); 
            },
            child: const Text('Hapus Log'),
          ),
        ],
      ),
    );
  }

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 300), 
          curve: Curves.easeOut,
        );
      }
    });
  }

  Widget _buildEmptyState() {
    return SingleChildScrollView(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 40.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              "Selamat Datang",
              style: TextStyle(
                fontSize: 32,
                fontWeight: FontWeight.bold,
                color: Color(0xFF26A69A), 
              ),
            ),
            const SizedBox(height: 8),
            Text(
              "SYNAPSE siap membantu Kapten belajar hari ini.",
              style: TextStyle(fontSize: 16, color: Colors.blueGrey[400]),
            ),
            const SizedBox(height: 40),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  "Rekomendasi Pertanyaan",
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w600,
                    color: Colors.blueGrey[700],
                  ),
                ),
                Icon(Icons.arrow_forward_rounded, color: Colors.blueGrey[400]),
              ],
            ),
            const SizedBox(height: 16),
            SizedBox(
              height: 130, 
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                physics: const BouncingScrollPhysics(),
                itemCount: _promptStarters.length,
                itemBuilder: (context, index) {
                  return GestureDetector(
                    onTap: () => _sendPrompt(_promptStarters[index]),
                    child: Container(
                      width: 140, 
                      margin: const EdgeInsets.only(right: 16, bottom: 8),
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(20),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.04),
                            blurRadius: 10,
                            offset: const Offset(0, 4),
                          )
                        ],
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Icon(Icons.lightbulb_outline_rounded, color: Colors.amber[600], size: 24),
                          const Spacer(),
                          Text(
                            _promptStarters[index],
                            style: TextStyle(
                              color: Colors.blueGrey[800],
                              fontWeight: FontWeight.w500,
                              fontSize: 14,
                              height: 1.3,
                            ),
                            maxLines: 3,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ],
                      ),
                    ),
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50], 
      appBar: AppBar(
        title: const Row(
          children: [
            Icon(Icons.memory_rounded, color: Color(0xFF26A69A)),
            SizedBox(width: 10),
            Text('Chat SYNAPSE', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 20)),
          ],
        ),
        backgroundColor: Colors.white,
        foregroundColor: Colors.blueGrey[900],
        elevation: 0, 
        actions: [
          if (_messages.isNotEmpty) 
            IconButton(
              icon: const Icon(Icons.delete_sweep_rounded, color: Colors.redAccent),
              tooltip: 'Bersihkan Log',
              onPressed: _clearChat, 
            ),
        ],
      ),
      body: Column(
        children: [
          Expanded(
            child: _messages.isEmpty 
              ? _buildEmptyState() 
              : ListView.builder(
                  controller: _scrollController,
                  padding: const EdgeInsets.all(16),
                  itemCount: _messages.length,
                  itemBuilder: (context, index) {
                    final msg = _messages[index];
                    return Align(
                      alignment: msg.isUser ? Alignment.centerRight : Alignment.centerLeft,
                      child: Container(
                        margin: const EdgeInsets.only(bottom: 12),
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                        constraints: BoxConstraints(
                          maxWidth: MediaQuery.of(context).size.width * 0.8,
                        ),
                        decoration: BoxDecoration(
                          color: msg.isUser ? const Color(0xFF26A69A) : Colors.white,
                          border: msg.isUser ? null : Border.all(color: Colors.grey.shade300),
                          boxShadow: [
                            if (!msg.isUser) BoxShadow(
                              color: Colors.black.withOpacity(0.03),
                              blurRadius: 5,
                              offset: const Offset(0, 2),
                            )
                          ],
                          borderRadius: BorderRadius.only(
                            topLeft: const Radius.circular(16),
                            topRight: const Radius.circular(16),
                            bottomLeft: Radius.circular(msg.isUser ? 16 : 0),
                            bottomRight: Radius.circular(msg.isUser ? 0 : 16),
                          ),
                        ),
                        child: MarkdownBody(
                          data: msg.text,
                          styleSheet: MarkdownStyleSheet(
                            p: TextStyle(color: msg.isUser ? Colors.white : Colors.blueGrey[800], fontSize: 15, height: 1.4),
                            strong: TextStyle(color: msg.isUser ? Colors.white : Colors.black, fontWeight: FontWeight.bold),
                            code: TextStyle(
                              backgroundColor: msg.isUser ? Colors.teal[700] : Colors.grey[200],
                              color: msg.isUser ? Colors.white : Colors.redAccent,
                              fontFamily: 'monospace',
                            ),
                            codeblockDecoration: BoxDecoration(
                              color: Colors.blueGrey[900],
                              borderRadius: BorderRadius.circular(8),
                            ),
                          ),
                        ),
                      ),
                    );
                  },
                ),
          ),
          
          if (_isLoading)
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 8.0),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFF26A69A))),
                  const SizedBox(width: 10),
                  Text("SYNAPSE memproses...", style: TextStyle(color: Colors.blueGrey[400], fontStyle: FontStyle.italic)),
                ],
              ),
            ),
            
          // AREA INPUT BAWAH (Kembali ke konsep asli Kapten)
          Container(
            decoration: const BoxDecoration(
              color: Color(0xFF26A69A), // Background Teal
              borderRadius: BorderRadius.only(
                topLeft: Radius.circular(30),
                topRight: Radius.circular(30),
              ),
            ),
            // UBAH PADDING BAWAH INI: 
            // Angka 110 adalah jarak aman agar inputan terdorong ke atas navbar. 
            // Silakan Kapten tambah/kurangi angka 110 ini jika dirasa masih kurang pas.
            padding: const EdgeInsets.fromLTRB(20, 20, 20, 110), 
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _controller,
                    minLines: 1,
                    maxLines: 4,
                    style: const TextStyle(color: Colors.white), 
                    cursorColor: Colors.white,
                    decoration: InputDecoration(
                      hintText: 'Ketik ide anda disini...',
                      hintStyle: TextStyle(color: Colors.white.withOpacity(0.7)),
                      border: InputBorder.none, // Tanpa garis tepi agar rapi
                      isDense: true,
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Container(
                  decoration: const BoxDecoration(
                    color: Colors.white, 
                    shape: BoxShape.circle,
                  ),
                  child: IconButton(
                    icon: const Icon(Icons.send_rounded, color: Color(0xFF26A69A), size: 22),
                    onPressed: _sendMessage,
                  ),
                )
              ],
            ),
          ),
        ],
      ),
    );
  }
}