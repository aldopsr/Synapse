// lib/widgets/synapse_fab.dart
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/note_service.dart';
import '../utils/constants.dart';
import '../utils/chat_notifier.dart';
import 'package:flutter_markdown/flutter_markdown.dart';

// ── Controller global ─────────────────────────────────────────
class SynapseFabController {
  static final ValueNotifier<bool> visible = ValueNotifier(true);
  static bool isGuest = true;
  // Dipanggil setiap kali ada pesan baru dari FAB chatbot
  // ChatbotScreen listen ke notifier ini untuk reload history
  // chatUpdated dipindah ke ChatNotifier di utils/chat_notifier.dart
}

// ── SynapseFab ────────────────────────────────────────────────
class SynapseFab extends StatefulWidget {
  const SynapseFab({super.key});
  @override
  State<SynapseFab> createState() => _SynapseFabState();
}

class _SynapseFabState extends State<SynapseFab> {
  static const Color _primary = Color(0xFF2A9D8F);
  double _posX = 0, _posY = 0;
  bool _init = false;
  final GlobalKey _fabKey = GlobalKey();

  void _initPos(Size size) {
    if (_init) return;
    _posX = size.width - 68;
    _posY = size.height * 0.45;
    _init = true;
  }

  void _onTap() {
    HapticFeedback.lightImpact();
    if (SynapseFabController.isGuest) {
      showDialog(
        context: context,
        builder: (_) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: const Row(children: [
            Icon(Icons.lock_rounded, color: Color(0xFF2A9D8F)),
            SizedBox(width: 8),
            Text('Login dulu yuk!',
                style: TextStyle(fontWeight: FontWeight.bold)),
          ]),
          content: const Text(
              'Fitur ini hanya untuk pengguna terdaftar. '
              'Daftar gratis untuk akses kuis, AI tutor, dan catatan!'),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Nanti', style: TextStyle(color: Colors.grey)),
            ),
          ],
        ),
      );
      return;
    }

    double screenX = _posX;
    double screenY = _posY;
    try {
      final box = _fabKey.currentContext?.findRenderObject() as RenderBox?;
      if (box != null) {
        final pos = box.localToGlobal(Offset.zero);
        screenX = pos.dx;
        screenY = pos.dy;
      }
    } catch (_) {}

    showDialog(
      context: context,
      barrierColor: Colors.black.withOpacity(0.15),
      builder: (_) => _MenuDialog(
        anchorX: screenX,
        anchorY: screenY,
        onNotes: () {
          Navigator.pop(context);
          showDialog(
            context: context,
            barrierColor: Colors.black.withOpacity(0.2),
            builder: (_) => const _StickyNoteDialog(),
          );
        },
        onChatbot: () {
          Navigator.pop(context);
          showDialog(
            context: context,
            barrierColor: Colors.black.withOpacity(0.2),
            builder: (_) => const _ChatbotDialog(),
          );
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;
    _initPos(size);

    return ValueListenableBuilder<bool>(
      valueListenable: SynapseFabController.visible,
      builder: (_, visible, __) {
        if (!visible) return const SizedBox.shrink();
        return Positioned(
          left: _posX,
          top: _posY,
          child: GestureDetector(
            onPanUpdate: (d) => setState(() {
              _posX = (_posX + d.delta.dx).clamp(4, size.width - 60);
              _posY = (_posY + d.delta.dy).clamp(80, size.height - 220);
            }),
            onPanEnd: (_) => setState(() {
              _posX = _posX < size.width / 2 ? 10 : size.width - 62;
            }),
            onTap: _onTap,
            child: Material(
              color: Colors.transparent,
              child: Container(
                key: _fabKey,
                width: 52, height: 52,
                decoration: BoxDecoration(
                  color: _primary,
                  shape: BoxShape.circle,
                  boxShadow: [BoxShadow(
                    color: _primary.withOpacity(0.4),
                    blurRadius: 16,
                    offset: const Offset(0, 4),
                  )],
                ),
                child: Center(
                  child: Image.asset(
                    'assets/images/logo_synapse.png',
                    width: 26, height: 26,
                    errorBuilder: (_, __, ___) => const Icon(
                        Icons.auto_awesome_rounded, size: 22),
                  ),
                ),
              ),
            ),
          ),
        );
      },
    );
  }
}

// ── Menu popup ────────────────────────────────────────────────
class _MenuDialog extends StatelessWidget {
  final double anchorX, anchorY;
  final VoidCallback onNotes, onChatbot;
  const _MenuDialog({
    required this.anchorX, required this.anchorY,
    required this.onNotes, required this.onChatbot,
  });

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;
    const w = 172.0;
    double px = (anchorX - w / 2 + 26).clamp(8, size.width - w - 8);
    double py = (anchorY - 118).clamp(8, size.height - 130);

    return GestureDetector(
      onTap: () => Navigator.pop(context),
      behavior: HitTestBehavior.opaque,
      child: Stack(children: [
        Positioned(
          left: px, top: py,
          child: Material(
            color: Colors.transparent,
            child: Container(
              width: w,
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                boxShadow: [BoxShadow(
                  color: Colors.black.withOpacity(0.13),
                  blurRadius: 20, offset: const Offset(0, 6),
                )],
              ),
              child: Column(mainAxisSize: MainAxisSize.min, children: [
                _MenuItem(icon: Icons.sticky_note_2_rounded,
                    label: 'Catatan', color: const Color(0xFFFF9800),
                    onTap: onNotes, isFirst: true),
                const Divider(height: 1, indent: 14, endIndent: 14),
                _MenuItem(icon: Icons.smart_toy_rounded,
                    label: 'Chat', color: const Color(0xFF2A9D8F),
                    onTap: onChatbot, isFirst: false),
              ]),
            ),
          ),
        ),
      ]),
    );
  }
}

class _MenuItem extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback onTap;
  final bool isFirst;
  const _MenuItem({required this.icon, required this.label,
      required this.color, required this.onTap, required this.isFirst});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.vertical(
        top: isFirst ? const Radius.circular(16) : Radius.zero,
        bottom: isFirst ? Radius.zero : const Radius.circular(16),
      ),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
        child: Row(children: [
          Container(
            width: 34, height: 34,
            decoration: BoxDecoration(
              color: color.withOpacity(0.12),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: color, size: 18),
          ),
          const SizedBox(width: 10),
          Text(label, style: const TextStyle(
              fontSize: 14, fontWeight: FontWeight.w600,
              color: Color(0xFF1A1A2E))),
        ]),
      ),
    );
  }
}

// ── Sticky Note Dialog ────────────────────────────────────────
class _StickyNoteDialog extends StatefulWidget {
  const _StickyNoteDialog();
  @override
  State<_StickyNoteDialog> createState() => _StickyNoteDialogState();
}

class _StickyNoteDialogState extends State<_StickyNoteDialog> {
  static const Color _primary = Color(0xFF2A9D8F);

  static const List<Color> _noteColors = [
    Colors.white,
    Color(0xFFFFF9C4),
    Color(0xFFFFCCBC),
    Color(0xFFB2EBF2),
    Color(0xFFC8E6C9),
    Color(0xFFE1BEE7),
    Color(0xFFFFE0B2),
    Color(0xFFF8BBD0),
  ];

  final NoteService _svc   = NoteService();
  final TextEditingController _ctrl = TextEditingController();
  final ScrollController _scroll   = ScrollController();

  List<NoteModel> _notes = [];
  bool _loading = true, _saving = false;
  String? _editingId;
  int _selectedColor = 0;
  int _tab = 0;

  @override
  void initState() { super.initState(); _load(); }

  Future<void> _load() async {
    final notes = await _svc.getNotes();
    if (mounted) setState(() { _notes = notes; _loading = false; });
  }

  Future<void> _save() async {
    final text = _ctrl.text.trim();
    if (text.isEmpty) return;
    setState(() => _saving = true);
    if (_editingId != null) {
      await _svc.updateNote(_editingId!, text, colorIndex: _selectedColor);
      _editingId = null;
    } else {
      await _svc.createNote(text, colorIndex: _selectedColor);
    }
    _ctrl.clear();
    _selectedColor = 0;
    await _load();
    setState(() { _saving = false; _tab = 0; });
  }

  @override
  void dispose() { _ctrl.dispose(); _scroll.dispose(); super.dispose(); }

  String _fmt(DateTime dt) {
    final diff = DateTime.now().difference(dt);
    if (diff.inMinutes < 1) return 'Baru saja';
    if (diff.inHours < 1)   return '${diff.inMinutes}m lalu';
    if (diff.inDays < 1)    return '${diff.inHours}j lalu';
    return '${dt.day}/${dt.month}';
  }

  Color _getNoteColor(NoteModel note) {
    final idx = note.colorIndex;
    if (idx < 0 || idx >= _noteColors.length) return Colors.white;
    return _noteColors[idx];
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      backgroundColor: Colors.transparent,
      insetPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 80),
      child: Container(
        width: double.infinity,
        constraints: const BoxConstraints(maxHeight: 460),
        decoration: BoxDecoration(
          color: const Color(0xFFE8F8F6),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: const Color(0xFFA7E8E1), width: 1.5),
          boxShadow: [BoxShadow(
            color: _primary.withOpacity(0.15), blurRadius: 24,
            offset: const Offset(0, 8),
          )],
        ),
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 14, 12, 0),
            child: Row(children: [
              const Icon(Icons.sticky_note_2_rounded, color: _primary, size: 18),
              const SizedBox(width: 8),
              const Text('Catatan', style: TextStyle(
                  fontSize: 15, fontWeight: FontWeight.bold,
                  color: Color(0xFF1A1A2E))),
              const Spacer(),
              _TabChip(label: '📋', active: _tab == 0,
                  onTap: () => setState(() => _tab = 0)),
              const SizedBox(width: 4),
              _TabChip(label: '✏️', active: _tab == 1,
                  onTap: () => setState(() {
                    _tab = 1; _editingId = null;
                    _ctrl.clear(); _selectedColor = 0;
                  })),
              const SizedBox(width: 6),
              GestureDetector(
                onTap: () => Navigator.pop(context),
                child: Container(
                  width: 30, height: 30,
                  decoration: BoxDecoration(
                    color: Colors.black.withOpacity(0.07),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(Icons.close_rounded,
                      size: 16, color: Colors.black54),
                ),
              ),
            ]),
          ),
          const SizedBox(height: 10),
          Flexible(child: _tab == 0 ? _buildList() : _buildWrite()),
          const SizedBox(height: 12),
        ]),
      ),
    );
  }

  Widget _buildList() {
    if (_loading) return const Center(child: Padding(
      padding: EdgeInsets.all(24),
      child: CircularProgressIndicator(color: _primary, strokeWidth: 2)));
    if (_notes.isEmpty) return Padding(
      padding: const EdgeInsets.all(24),
      child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
        Icon(Icons.note_add_outlined, size: 40, color: Colors.grey[400]),
        const SizedBox(height: 8),
        Text('Belum ada catatan.\nKetuk ✏️ untuk mulai!',
            textAlign: TextAlign.center,
            style: TextStyle(color: Colors.grey[500], fontSize: 13)),
      ]),
    );
    return ListView.builder(
      controller: _scroll,
      padding: const EdgeInsets.fromLTRB(12, 0, 12, 4),
      shrinkWrap: true,
      itemCount: _notes.length,
      itemBuilder: (_, i) {
        final note = _notes[i];
        final bg = _getNoteColor(note);
        return Container(
          margin: const EdgeInsets.only(bottom: 8),
          padding: const EdgeInsets.fromLTRB(12, 10, 8, 10),
          decoration: BoxDecoration(
            color: bg,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: Colors.black.withOpacity(0.08)),
          ),
          child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Expanded(child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(note.content, maxLines: 3,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontSize: 13,
                        color: Color(0xFF1A1A2E), height: 1.5)),
                const SizedBox(height: 4),
                Text(_fmt(note.updatedAt),
                    style: TextStyle(fontSize: 11, color: Colors.grey[500])),
              ],
            )),
            Column(children: [
              GestureDetector(
                onTap: () => setState(() {
                  _editingId = note.id;
                  _ctrl.text = note.content;
                  _selectedColor = note.colorIndex;
                  _tab = 1;
                }),
                child: Icon(Icons.edit_outlined,
                    size: 16, color: Colors.black.withOpacity(0.3)),
              ),
              const SizedBox(height: 10),
              GestureDetector(
                onTap: () async { await _svc.deleteNote(note.id); _load(); },
                child: Icon(Icons.delete_outline_rounded,
                    size: 16, color: Colors.black.withOpacity(0.3)),
              ),
            ]),
          ]),
        );
      },
    );
  }

  Widget _buildWrite() {
    final bgColor = _noteColors[_selectedColor];
    return Padding(
      padding: const EdgeInsets.fromLTRB(12, 0, 12, 0),
      child: Column(mainAxisSize: MainAxisSize.min, children: [
        // Text area dengan warna yang dipilih
        AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          constraints: const BoxConstraints(maxHeight: 120),
          decoration: BoxDecoration(
            color: bgColor,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: _primary.withOpacity(0.3)),
          ),
          child: TextField(
            controller: _ctrl,
            maxLines: null, autofocus: true,
            decoration: const InputDecoration(
              hintText: 'Tulis catatanmu...',
              hintStyle: TextStyle(color: Colors.grey, fontSize: 13),
              border: InputBorder.none,
              contentPadding: EdgeInsets.all(12),
            ),
            style: const TextStyle(fontSize: 13.5,
                color: Color(0xFF1A1A2E), height: 1.5),
          ),
        ),
        const SizedBox(height: 8),
        // Color picker row
        Row(children: [
          ...List.generate(_noteColors.length, (i) {
            final isSelected = _selectedColor == i;
            return GestureDetector(
              onTap: () => setState(() => _selectedColor = i),
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 150),
                margin: const EdgeInsets.only(right: 6),
                width: isSelected ? 26 : 22,
                height: isSelected ? 26 : 22,
                decoration: BoxDecoration(
                  color: _noteColors[i],
                  shape: BoxShape.circle,
                  border: Border.all(
                    color: isSelected ? _primary : Colors.black.withOpacity(0.15),
                    width: isSelected ? 2 : 1,
                  ),
                ),
                child: isSelected
                    ? const Icon(Icons.check_rounded, size: 12, color: Color(0xFF2A9D8F))
                    : null,
              ),
            );
          }),
        ]),
        const SizedBox(height: 10),
        Row(children: [
          if (_editingId != null) ...[
            Expanded(child: OutlinedButton(
              onPressed: () => setState(() {
                _editingId = null; _ctrl.clear();
                _selectedColor = 0; _tab = 0;
              }),
              style: OutlinedButton.styleFrom(
                side: BorderSide(color: Colors.grey[300]!),
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10)),
                padding: const EdgeInsets.symmetric(vertical: 10),
              ),
              child: const Text('Batal'),
            )),
            const SizedBox(width: 8),
          ],
          Expanded(flex: 2, child: ElevatedButton(
            onPressed: _saving ? null : _save,
            style: ElevatedButton.styleFrom(
              backgroundColor: _primary, foregroundColor: Colors.white,
              elevation: 0,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10)),
              padding: const EdgeInsets.symmetric(vertical: 10),
            ),
            child: _saving
                ? const SizedBox(width: 16, height: 16,
                    child: CircularProgressIndicator(
                        color: Colors.white, strokeWidth: 2))
                : Text(_editingId != null ? 'Update' : 'Simpan',
                    style: const TextStyle(
                        fontSize: 13, fontWeight: FontWeight.bold)),
          )),
        ]),
      ]),
    );
  }
}

// ── Chatbot Dialog ────────────────────────────────────────────
class _ChatbotDialog extends StatefulWidget {
  const _ChatbotDialog();
  @override
  State<_ChatbotDialog> createState() => _ChatbotDialogState();
}

class _ChatbotDialogState extends State<_ChatbotDialog> {
  static const Color _primary    = Color(0xFF26A69A);
  static const Color _bgColor    = Color(0xFFF5F7FA);
  static const Color _bubbleUser = Color(0xFF26A69A);

  // Key sama dengan ChatbotScreen agar history tersinkron
  static const String _historyKey = 'chat_history';

  final TextEditingController _ctrl = TextEditingController();
  final ScrollController _scroll   = ScrollController();
  final List<Map<String, dynamic>> _msgs = [];
  bool _loading = false;

  String get _baseUrl => AppConstants.baseUrl;

  @override
  void initState() {
    super.initState();
    _loadHistory();
  }

  Future<void> _loadHistory() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final raw = prefs.getString(_historyKey);
      if (raw != null && mounted) {
        final List list = jsonDecode(raw);
        setState(() {
          _msgs.addAll(list.map((e) => Map<String, dynamic>.from(e)).toList());
        });
        _scrollToBottom();
      }
    } catch (_) {}
  }

  Future<void> _saveHistory() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_historyKey, jsonEncode(_msgs));
    } catch (_) {}
  }

  Future<String?> _getToken() async {
    final p = await SharedPreferences.getInstance();
    return p.getString('token');
  }

  Future<void> _send() async {
    final text = _ctrl.text.trim();
    if (text.isEmpty || _loading) return;

    setState(() {
      _msgs.add({
        'text': text,
        'isUser': true,
        'timestamp': DateTime.now().toIso8601String(),
      });
      _loading = true;
    });
    _ctrl.clear();
    _saveHistory();
    _scrollToBottom();

    try {
      final token = await _getToken();

      // Kirim max 10 pesan terakhir sebagai context (tanpa pesan user saat ini)
      const int maxHistory = 10;
      final prevMsgs = _msgs.sublist(0, _msgs.length - 1);
      final historySlice = prevMsgs.length > maxHistory
          ? prevMsgs.sublist(prevMsgs.length - maxHistory)
          : prevMsgs;
      final history = historySlice
          .map((m) => {'role': (m['isUser'] as bool) ? 'user' : 'model', 'text': m['text'] as String})
          .toList();

      final res = await http.post(
        Uri.parse('$_baseUrl/chat'),
        headers: {
          'Authorization': 'Bearer ${token ?? ''}',
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({'message': text, 'history': history}),
      );
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        setState(() => _msgs.add({
          'text': data['reply'] ?? 'Maaf, tidak ada respons.',
          'isUser': false,
          'timestamp': DateTime.now().toIso8601String(),
        }));
      } else {
        setState(() => _msgs.add({
          'text': 'Gagal menghubungi AI. Coba lagi.',
          'isUser': false,
          'timestamp': DateTime.now().toIso8601String(),
        }));
      }
    } catch (_) {
      setState(() => _msgs.add({
        'text': 'Koneksi bermasalah.',
        'isUser': false,
        'timestamp': DateTime.now().toIso8601String(),
      }));
    } finally {
      setState(() => _loading = false);
      _saveHistory();
      // Notify ChatbotScreen untuk reload
      ChatNotifier.notify();
      _scrollToBottom();
    }
  }

  void _scrollToBottom() {
    Future.delayed(const Duration(milliseconds: 100), () {
      if (_scroll.hasClients) {
        _scroll.animateTo(_scroll.position.maxScrollExtent,
            duration: const Duration(milliseconds: 300),
            curve: Curves.easeOut);
      }
    });
  }

  @override
  void dispose() { _ctrl.dispose(); _scroll.dispose(); super.dispose(); }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      backgroundColor: Colors.transparent,
      insetPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 80),
      child: Container(
        width: double.infinity,
        constraints: const BoxConstraints(maxHeight: 440),
        decoration: BoxDecoration(
          color: _bgColor,
          borderRadius: BorderRadius.circular(20),
          boxShadow: [BoxShadow(
            color: _primary.withOpacity(0.15),
            blurRadius: 20, offset: const Offset(0, 6),
          )],
        ),
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          // Header
          Container(
            padding: const EdgeInsets.fromLTRB(16, 14, 12, 14),
            decoration: const BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
              border: Border(bottom: BorderSide(color: Color(0xFFE5E7EB))),
            ),
            child: Row(children: [
              Container(
                width: 32, height: 32,
                decoration: BoxDecoration(
                  color: _primary.withOpacity(0.15),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.smart_toy_rounded,
                    color: _primary, size: 18),
              ),
              const SizedBox(width: 10),
              const Column(crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                Text('SYNAPSE AI', style: TextStyle(
                    color: Color(0xFF1A1A2E), fontSize: 13,
                    fontWeight: FontWeight.bold)),
                Text('Asisten belajarmu', style: TextStyle(
                    color: Color(0xFF94A3B8), fontSize: 10)),
              ]),
              const Spacer(),
              if (_msgs.isNotEmpty)
                GestureDetector(
                  onTap: () async {
                    setState(() => _msgs.clear());
                    final p = await SharedPreferences.getInstance();
                    await p.remove(_historyKey);
                  },
                  child: Container(
                    padding: const EdgeInsets.all(6),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFEE2E2),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Icon(Icons.delete_sweep_rounded,
                        color: Colors.redAccent, size: 16),
                  ),
                ),
              const SizedBox(width: 6),
              GestureDetector(
                onTap: () => Navigator.pop(context),
                child: Container(
                  width: 30, height: 30,
                  decoration: const BoxDecoration(
                    color: Color(0xFFF1F5F9),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(Icons.close_rounded,
                      size: 16, color: Color(0xFF64748B)),
                ),
              ),
            ]),
          ),

          // Messages
          Flexible(
            child: _msgs.isEmpty
                ? Center(child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.smart_toy_rounded,
                          color: _primary.withOpacity(0.5), size: 36),
                      const SizedBox(height: 8),
                      const Text('Tanya apa saja seputar materi!',
                          style: TextStyle(
                              color: Color(0xFF94A3B8), fontSize: 12)),
                    ],
                  ))
                : ListView.builder(
                    controller: _scroll,
                    padding: const EdgeInsets.fromLTRB(12, 10, 12, 8),
                    itemCount: _msgs.length + (_loading ? 1 : 0),
                    itemBuilder: (_, i) {
                      if (i == _msgs.length) {
                        return Padding(
                          padding: const EdgeInsets.only(bottom: 8),
                          child: Row(children: [
                            Container(
                              width: 24, height: 24,
                              decoration: BoxDecoration(
                                  color: _primary.withOpacity(0.15),
                                  shape: BoxShape.circle),
                              child: const Icon(Icons.smart_toy_rounded,
                                  color: _primary, size: 13),
                            ),
                            const SizedBox(width: 6),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 12, vertical: 8),
                              decoration: BoxDecoration(
                                  color: Colors.white,
                                  border: Border.all(
                                      color: const Color(0xFFE5E7EB)),
                                  borderRadius: BorderRadius.circular(12)),
                              child: const Text('Sedang mengetik...',
                                  style: TextStyle(
                                      color: Color(0xFF94A3B8),
                                      fontSize: 12,
                                      fontStyle: FontStyle.italic)),
                            ),
                          ]),
                        );
                      }
                      final msg = _msgs[i];
                      final isUser = msg['isUser'] as bool;
                      return Padding(
                        padding: const EdgeInsets.only(bottom: 8),
                        child: Row(
                          mainAxisAlignment: isUser
                              ? MainAxisAlignment.end
                              : MainAxisAlignment.start,
                          crossAxisAlignment: CrossAxisAlignment.end,
                          children: [
                            if (!isUser) ...[
                              Container(
                                width: 24, height: 24,
                                decoration: BoxDecoration(
                                    color: _primary.withOpacity(0.15),
                                    shape: BoxShape.circle),
                                child: const Icon(Icons.smart_toy_rounded,
                                    color: _primary, size: 13),
                              ),
                              const SizedBox(width: 6),
                            ],
                            Flexible(
                              child: Container(
                                padding: const EdgeInsets.symmetric(
                                    horizontal: 12, vertical: 8),
                                decoration: BoxDecoration(
                                  color: isUser ? _bubbleUser : Colors.white,
                                  border: isUser
                                      ? null
                                      : Border.all(
                                          color: const Color(0xFFE5E7EB)),
                                  borderRadius: BorderRadius.only(
                                    topLeft: const Radius.circular(14),
                                    topRight: const Radius.circular(14),
                                    bottomLeft:
                                        Radius.circular(isUser ? 14 : 4),
                                    bottomRight:
                                        Radius.circular(isUser ? 4 : 14),
                                  ),
                                ),
                                child: isUser
                                    ? Text(msg['text'] as String,
                                        style: const TextStyle(
                                            color: Colors.white,
                                            fontSize: 13, height: 1.4))
                                    : MarkdownBody(
                                        data: msg['text'] as String,
                                        styleSheet: MarkdownStyleSheet(
                                          p: const TextStyle(
                                              color: Color(0xFF1A1A2E),
                                              fontSize: 13, height: 1.4),
                                          strong: const TextStyle(
                                              color: Color(0xFF1A1A2E),
                                              fontWeight: FontWeight.bold,
                                              fontSize: 13),
                                          code: TextStyle(
                                              backgroundColor: Colors.grey[100],
                                              color: Colors.redAccent,
                                              fontFamily: 'monospace'),
                                        ),
                                      ),
                              ),
                            ),
                            if (isUser) const SizedBox(width: 6),
                          ],
                        ),
                      );
                    },
                  ),
          ),

          // Input — teal seperti chatbot screen
          Container(
            padding: const EdgeInsets.fromLTRB(12, 10, 12, 14),
            decoration: const BoxDecoration(
              color: Color(0xFF26A69A),
              borderRadius: BorderRadius.vertical(bottom: Radius.circular(20)),
            ),
            child: Row(children: [
              Expanded(
                child: Container(
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.25),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: TextField(
                    controller: _ctrl,
                    style: const TextStyle(color: Colors.white, fontSize: 13),
                    cursorColor: Colors.white,
                    maxLines: 2, minLines: 1,
                    decoration: InputDecoration(
                      hintText: 'Tanya sesuatu...',
                      hintStyle: TextStyle(
                          color: Colors.white.withOpacity(0.7), fontSize: 13),
                      border: InputBorder.none,
                      enabledBorder: InputBorder.none,
                      focusedBorder: InputBorder.none,
                      contentPadding: const EdgeInsets.symmetric(
                          horizontal: 14, vertical: 8),
                    ),
                    onSubmitted: (_) => _send(),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              GestureDetector(
                onTap: _loading ? null : _send,
                child: Container(
                  width: 38, height: 38,
                  decoration: const BoxDecoration(
                    color: Colors.white,
                    shape: BoxShape.circle,
                  ),
                  child: _loading
                      ? const Center(
                          child: SizedBox(
                              width: 14, height: 14,
                              child: CircularProgressIndicator(
                                  color: Color(0xFF26A69A), strokeWidth: 2)))
                      : const Icon(Icons.send_rounded,
                          color: Color(0xFF26A69A), size: 17),
                ),
              ),
            ]),
          ),
        ]),
      ),
    );
  }
}

// ── Tab chip ──────────────────────────────────────────────────
class _TabChip extends StatelessWidget {
  final String label;
  final bool active;
  final VoidCallback onTap;
  const _TabChip(
      {required this.label, required this.active, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 5),
        decoration: BoxDecoration(
          color: active
              ? const Color(0xFF2A9D8F).withOpacity(0.15)
              : Colors.transparent,
          borderRadius: BorderRadius.circular(8),
          border: Border.all(
              color: active
                  ? const Color(0xFF2A9D8F)
                  : Colors.transparent),
        ),
        child: Text(label, style: const TextStyle(fontSize: 14)),
      ),
    );
  }
}