import 'package:flutter/material.dart';
import 'package:flutter_markdown/flutter_markdown.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/constants.dart';
import '../widgets/synapse_fab.dart';
import '../utils/chat_notifier.dart';

class ChatMessage {
  final String text;
  final bool isUser;
  final DateTime timestamp;

  ChatMessage({
    required this.text,
    required this.isUser,
    DateTime? timestamp,
  }) : timestamp = timestamp ?? DateTime.now();

  Map<String, dynamic> toJson() => {
    'text': text,
    'isUser': isUser,
    'timestamp': timestamp.toIso8601String(),
  };

  factory ChatMessage.fromJson(Map<String, dynamic> json) => ChatMessage(
    text: json['text'] ?? '',
    isUser: json['isUser'] ?? false,
    timestamp: DateTime.tryParse(json['timestamp'] ?? '') ?? DateTime.now(),
  );
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

  final String baseUrl = AppConstants.baseUrl;

  bool _isPublic  = false;
  int? _remaining;
  int  _limit     = 10;

  static const String _historyKey = 'chat_history';

  final List<String> _promptStarters = [
    "Apa itu Bahasa Pemrograman R?",
    "Gimana cara knit PDF di RStudio?",
    "Berikan contoh koding Regresi Linear",
    "Cara import dataset CSV ke R",
    "Jelaskan apa itu Tidyverse"
  ];

  @override
  void initState() {
    super.initState();
    _loadHistory();
    _checkRoleAndQuota();
    // Reload history saat FAB chatbot kirim pesan baru
    ChatNotifier.chatUpdated.addListener(_onFabChatUpdated);
  }

  @override
  void dispose() {
    ChatNotifier.chatUpdated.removeListener(_onFabChatUpdated);
    _controller.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  Widget _buildTypingBubble() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 0, 16, 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.end,
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 34,
            height: 34,
            decoration: BoxDecoration(
              color: Colors.grey.shade200,
              shape: BoxShape.circle,
            ),
            child: const Icon(
              Icons.smart_toy_rounded,
              color: Colors.grey,
              size: 18,
            ),
          ),
          const SizedBox(width: 8),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            decoration: BoxDecoration(
              color: Colors.white,
              border: Border.all(color: Colors.grey.shade200),
              borderRadius: const BorderRadius.only(
                topLeft: Radius.circular(16),
                topRight: Radius.circular(16),
                bottomRight: Radius.circular(16),
                bottomLeft: Radius.circular(4),
              ),
            ),
            child: Text(
              'Sedang mengetik...',
              style: TextStyle(
                color: Colors.grey.shade500,
                fontSize: 14,
                fontStyle: FontStyle.italic,
              ),
            ),
          ),
        ],
      ),
    );
  }

  void _onFabChatUpdated() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final raw = prefs.getString(_historyKey);
      if (raw == null || raw.isEmpty) return;
      final List<dynamic> decoded = jsonDecode(raw);
      if (mounted) {
        setState(() {
          _messages.clear();
          _messages.addAll(
            decoded.map((e) => ChatMessage.fromJson(e as Map<String, dynamic>)),
          );
        });
        _scrollToBottom();
      }
    } catch (_) {}
  }

  Future<void> _loadHistory() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final String? raw = prefs.getString(_historyKey);
      if (raw == null || raw.isEmpty) return;
      final List<dynamic> decoded = jsonDecode(raw);
      if (mounted) {
        setState(() {
          _messages.addAll(
            decoded.map((e) => ChatMessage.fromJson(e as Map<String, dynamic>)),
          );
        });
        WidgetsBinding.instance.addPostFrameCallback((_) => _scrollToBottom());
      }
    } catch (_) {}
  }

  Future<void> _saveHistory() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final encoded = jsonEncode(_messages.map((m) => m.toJson()).toList());
      await prefs.setString(_historyKey, encoded);
    } catch (_) {}
  }

  Future<void> _checkRoleAndQuota() async {
    final prefs   = await SharedPreferences.getInstance();
    final userStr = prefs.getString('user');
    if (userStr == null) return;

    try {
      final user = jsonDecode(userStr);
      if ((user['role'] ?? '') != 'public') return;

      setState(() => _isPublic = true);

      final token = prefs.getString('token');
      if (token == null) return;

      final res = await http.get(
        Uri.parse('$baseUrl/chat/quota'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );

      if (res.statusCode == 200 && mounted) {
        final data = jsonDecode(res.body);
        setState(() {
          _remaining = data['remaining'];
          _limit     = data['limit'] ?? 5;
        });
      }
    } catch (_) {}
  }

  Future<String?> _getToken() async {
    SharedPreferences prefs = await SharedPreferences.getInstance();
    return prefs.getString('token');
  }

  void _sendPrompt(String text) {
    _controller.text = text;
    _sendMessage();
    _isLoading = true;
  }

  void _sendMessage() async {
    if (_controller.text.trim().isEmpty) return;

    if (_isPublic && _remaining != null && _remaining! <= 0) {
      _showQuotaDialog();
      return;
    }

    String userText = _controller.text;

    setState(() {
      _messages.add(ChatMessage(text: userText, isUser: true));
      _isLoading = true;
    });

    _scrollToBottom();
    _controller.clear();

    try {
      final token = await _getToken();
      if (token == null) {
        setState(() {
          _messages.add(ChatMessage(
            text: 'Sesi kamu telah berakhir. Silakan login ulang ya! 🔑',
            isUser: false,
          ));
        });
        return;
      }

      // Kirim max 10 pesan terakhir sebagai context (tanpa pesan user saat ini)
      const int maxHistory = 10;
      final prevMessages = _messages.sublist(0, _messages.length - 1);
      final historySlice = prevMessages.length > maxHistory
          ? prevMessages.sublist(prevMessages.length - maxHistory)
          : prevMessages;
      final history = historySlice
          .map((m) => {'role': m.isUser ? 'user' : 'model', 'text': m.text})
          .toList();

      final response = await http.post(
        Uri.parse('$baseUrl/chat'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({'message': userText, 'history': history}),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        setState(() {
          _messages.add(ChatMessage(text: data['reply'], isUser: false));
          if (data['remaining'] != null) {
            _remaining = data['remaining'];
          }
          if (data['limit'] != null) {
            _limit = data['limit'];
          }
        });
        _scrollToBottom();
      } else if (response.statusCode == 429) {
        setState(() {
          _remaining = 0;
          _messages.add(ChatMessage(
            text: 'Kuota chat harianmu sudah habis ($_limit pesan/hari). Reset esok hari ya! 😊',
            isUser: false,
          ));
        });
        _scrollToBottom();
      } else {
        setState(() {
          _messages.add(ChatMessage(
            text: response.statusCode == 503
                ? 'SYNAPSE sedang istirahat sebentar. Coba lagi dalam beberapa detik ya! 🔄'
                : 'Waduh, ada gangguan koneksi. Coba lagi ya! 😊',
            isUser: false,
          ));
        });
      }
    } catch (e) {
      setState(() {
        _messages.add(
            ChatMessage(text: "Koneksi ke server terputus: $e", isUser: false));
      });
    } finally {
      setState(() => _isLoading = false);
      _saveHistory();
    }
  }

  void _showQuotaDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(children: [
          Icon(Icons.hourglass_empty_rounded, color: Colors.orange),
          SizedBox(width: 8),
          Text('Kuota Habis'),
        ]),
        content: Text(
          'Kamu sudah menggunakan $_limit chat hari ini. Kuota akan reset esok hari.',
        ),
        actions: [
          ElevatedButton(
            onPressed: () => Navigator.pop(context),
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF26A69A),
              foregroundColor: Colors.white,
            ),
            child: const Text('Oke'),
          ),
        ],
      ),
    );
  }

  void _clearChat() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(children: [
          Icon(Icons.warning_amber_rounded, color: Colors.red),
          SizedBox(width: 8),
          Text('Format Memori?'),
        ]),
        content: const Text(
            'Apakah Kapten yakin ingin menghapus seluruh log percakapan dengan SYNAPSE?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Batal', style: TextStyle(color: Colors.grey)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
                backgroundColor: Colors.red, foregroundColor: Colors.white),
            onPressed: () async {
              final prefs = await SharedPreferences.getInstance();
              await prefs.remove(_historyKey);
              setState(() => _messages.clear());
              if (mounted) Navigator.pop(context);
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
    return SafeArea(
      child: SingleChildScrollView(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 40.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text("Selamat Datang",
                  style: TextStyle(
                      fontSize: 32, fontWeight: FontWeight.bold,
                      color: Color(0xFF26A69A))),
              const SizedBox(height: 8),
              Text("SYNAPSE siap membantu kamu belajar hari ini.",
                  style: TextStyle(fontSize: 16, color: Colors.blueGrey[400])),
              if (_isPublic && _remaining != null) ...[
                const SizedBox(height: 14),
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.symmetric(
                      horizontal: 14, vertical: 10),
                  decoration: BoxDecoration(
                    color: _remaining! <= 1 ? Colors.red[50] : Colors.orange[50],
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                      color: _remaining! <= 1
                          ? Colors.red.shade200
                          : Colors.orange.shade200,
                    ),
                  ),
                  child: Row(children: [
                    Icon(
                      _remaining! <= 0
                          ? Icons.block_rounded
                          : Icons.timer_outlined,
                      size: 16,
                      color: _remaining! <= 1
                          ? Colors.redAccent
                          : Colors.orange[700],
                    ),
                    const SizedBox(width: 8),
                    Text(
                      _remaining! <= 0
                          ? 'Kuota chat hari ini habis. Reset esok hari.'
                          : 'Sisa kuota: $_remaining/$_limit pesan hari ini',
                      style: TextStyle(
                        fontSize: 12, fontWeight: FontWeight.w500,
                        color: _remaining! <= 1
                            ? Colors.redAccent
                            : Colors.orange[800],
                      ),
                    ),
                  ]),
                ),
              ],
              const SizedBox(height: 40),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text("Rekomendasi Pertanyaan",
                      style: TextStyle(fontSize: 18,
                          fontWeight: FontWeight.w600,
                          color: Colors.blueGrey[700])),
                  Icon(Icons.arrow_forward_rounded,
                      color: Colors.blueGrey[400]),
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
                          boxShadow: [BoxShadow(
                            color: Colors.black.withOpacity(0.04),
                            blurRadius: 10, offset: const Offset(0, 4),
                          )],
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Icon(Icons.lightbulb_outline_rounded,
                                color: Colors.amber[600], size: 24),
                            const Spacer(),
                            Text(_promptStarters[index],
                                style: TextStyle(
                                    color: Colors.blueGrey[800],
                                    fontWeight: FontWeight.w500,
                                    fontSize: 14, height: 1.3),
                                maxLines: 3,
                                overflow: TextOverflow.ellipsis),
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
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      resizeToAvoidBottomInset: true,
      backgroundColor: Colors.grey[50],
      appBar: _messages.isEmpty
          ? null
          : AppBar(
              title: const Row(children: [
                Icon(Icons.memory_rounded, color: Color(0xFF26A69A)),
                SizedBox(width: 10),
                Text('Chat SYNAPSE',
                    style: TextStyle(
                        fontWeight: FontWeight.bold, fontSize: 20)),
              ]),
              backgroundColor: Colors.white,
              foregroundColor: Colors.blueGrey[900],
              elevation: 0,
              actions: [
                IconButton(
                  icon: const Icon(Icons.delete_sweep_rounded,
                      color: Colors.redAccent),
                  tooltip: 'Bersihkan Log',
                  onPressed: _clearChat,
                ),
              ],
            ),
      body: Column(children: [
        if (_isPublic && _remaining != null && _messages.isNotEmpty)
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
            color: _remaining! <= 1 ? Colors.red[50] : Colors.orange[50],
            child: Text(
              _remaining! <= 0
                  ? '⛔ Kuota habis — reset esok hari'
                  : '⏱ Sisa kuota: $_remaining/$_limit pesan hari ini',
              style: TextStyle(
                fontSize: 11, fontWeight: FontWeight.w500,
                color: _remaining! <= 1
                    ? Colors.redAccent
                    : Colors.orange[800],
              ),
            ),
          ),

        Expanded(
          child: _messages.isEmpty
              ? _buildEmptyState()
              : ListView.builder(
                  controller: _scrollController,
                  padding: const EdgeInsets.all(16),
                  itemCount: _messages.length + (_isLoading ? 1 : 0),
                  itemBuilder: (context, index) {
                    if (index == _messages.length) {
                      return _buildTypingBubble();
                    }
                    final msg = _messages[index];
                    return Align(
                      alignment: msg.isUser
                          ? Alignment.centerRight
                          : Alignment.centerLeft,
                      child: Container(
                        margin: const EdgeInsets.only(bottom: 12),
                        padding: const EdgeInsets.symmetric(
                            horizontal: 16, vertical: 12),
                        constraints: BoxConstraints(
                          maxWidth: MediaQuery.of(context).size.width * 0.8,
                        ),
                        decoration: BoxDecoration(
                          color: msg.isUser
                              ? const Color(0xFF26A69A)
                              : Colors.white,
                          border: msg.isUser
                              ? null
                              : Border.all(color: Colors.grey.shade300),
                          boxShadow: [
                            if (!msg.isUser)
                              BoxShadow(
                                color: Colors.black.withOpacity(0.03),
                                blurRadius: 5,
                                offset: const Offset(0, 2),
                              )
                          ],
                          borderRadius: BorderRadius.only(
                            topLeft: const Radius.circular(16),
                            topRight: const Radius.circular(16),
                            bottomLeft:
                                Radius.circular(msg.isUser ? 16 : 0),
                            bottomRight:
                                Radius.circular(msg.isUser ? 0 : 16),
                          ),
                        ),
                        child: MarkdownBody(
                          data: msg.text,
                          styleSheet: MarkdownStyleSheet(
                            p: TextStyle(
                                color: msg.isUser
                                    ? Colors.white
                                    : Colors.blueGrey[800],
                                fontSize: 15, height: 1.4),
                            strong: TextStyle(
                                color: msg.isUser
                                    ? Colors.white
                                    : Colors.black,
                                fontWeight: FontWeight.bold),
                            code: TextStyle(
                              backgroundColor: msg.isUser
                                  ? Colors.teal[700]
                                  : Colors.grey[200],
                              color: msg.isUser
                                  ? Colors.white
                                  : Colors.redAccent,
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

        Container(
          decoration: const BoxDecoration(
            color: Color(0xFF26A69A),
            borderRadius: BorderRadius.only(
              topLeft: Radius.circular(30),
              topRight: Radius.circular(30),
            ),
          ),
          padding: EdgeInsets.fromLTRB(
              20, 20, 20, MediaQuery.of(context).padding.bottom + 90),
          child: Row(children: [
            Expanded(
              child: TextField(
                controller: _controller,
                minLines: 1,
                maxLines: 4,
                enabled: !(_isPublic &&
                    _remaining != null &&
                    _remaining! <= 0),
                style: const TextStyle(color: Colors.white),
                cursorColor: Colors.white,
                keyboardType: TextInputType.multiline,
                textInputAction: TextInputAction.newline,
                decoration: InputDecoration(
                  filled: true,
                  fillColor: Colors.transparent,
                  hintText: (_isPublic &&
                          _remaining != null &&
                          _remaining! <= 0)
                      ? 'Kuota habis — reset esok hari...'
                      : 'Ketik ide anda disini...',
                  hintStyle:
                      TextStyle(color: Colors.white.withOpacity(0.7)),
                  border: InputBorder.none,
                  enabledBorder: InputBorder.none,
                  focusedBorder: InputBorder.none,
                  disabledBorder: InputBorder.none,
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
                icon: const Icon(Icons.send_rounded,
                    color: Color(0xFF26A69A), size: 22),
                onPressed: _sendMessage,
              ),
            ),
          ]),
        ),
      ]),
    );
  }
}