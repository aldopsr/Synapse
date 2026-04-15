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
  // Sesuaikan URL ini dengan baseUrl aplikasi Kapten
  final String baseUrl = 'http://127.0.0.1:8000/api'; 

  Future<String?> _getToken() async {
    SharedPreferences prefs = await SharedPreferences.getInstance();
    return prefs.getString('token');
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
        content: const Text('Apakah Kapten yakin ingin menghapus seluruh log percakapan dengan SYNAPSE? Data yang dihapus tidak dapat dipulihkan.'),
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50], // Background yang lebih bersih
      appBar: AppBar(
        title: const Row(
          children: [
            Icon(Icons.memory_rounded, color: Colors.teal),
            SizedBox(width: 10),
            Text('Terminal SYNAPSE', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 20)),
          ],
        ),
        backgroundColor: Colors.white,
        foregroundColor: Colors.blueGrey[900],
        elevation: 1,
        shadowColor: Colors.black12,
        actions: [
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
            child: ListView.builder(
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
                      maxWidth: MediaQuery.of(context).size.width * 0.8, // Maksimal lebar chat 80% dari layar
                    ),
                    decoration: BoxDecoration(
                      color: msg.isUser ? Colors.teal : Colors.white,
                      border: msg.isUser ? null : Border.all(color: Colors.grey.shade300),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.05),
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
                        listBullet: TextStyle(color: msg.isUser ? Colors.white : Colors.blueGrey[800]),
                        code: TextStyle(
                          backgroundColor: msg.isUser ? Colors.teal[700] : Colors.grey[200],
                          color: msg.isUser ? Colors.white : Colors.redAccent,
                          fontFamily: 'Courier',
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
                  const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.teal)),
                  const SizedBox(width: 10),
                  Text("SYNAPSE sedang memproses data...", style: TextStyle(color: Colors.blueGrey[400], fontStyle: FontStyle.italic)),
                ],
              ),
            ),
            
          // Area Kotak Input Text
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.white,
              border: Border(top: BorderSide(color: Colors.grey.shade200)),
            ),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _controller,
                    minLines: 1,
                    maxLines: 4, // Bisa membesar otomatis sampai 4 baris
                    decoration: InputDecoration(
                      hintText: 'Ketik perintah atau pertanyaan...',
                      hintStyle: TextStyle(color: Colors.grey[400]),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(24),
                        borderSide: BorderSide.none,
                      ),
                      filled: true,
                      fillColor: Colors.blueGrey[50],
                      contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Container(
                  decoration: BoxDecoration(
                    color: Colors.teal,
                    shape: BoxShape.circle,
                    boxShadow: [
                      BoxShadow(color: Colors.teal.withOpacity(0.4), blurRadius: 8, offset: const Offset(0, 3))
                    ]
                  ),
                  child: IconButton(
                    icon: const Icon(Icons.send_rounded, color: Colors.white, size: 20),
                    onPressed: _sendMessage,
                  ),
                )
              ],
            ),
          ),
         
          const SizedBox(height: 90), 
        ],
      ),
    );
  }
}