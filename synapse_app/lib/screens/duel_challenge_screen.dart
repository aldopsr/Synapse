// lib/screens/duel_challenge_screen.dart
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../services/duel_service.dart';


class DuelChallengeScreen extends StatefulWidget {
  const DuelChallengeScreen({super.key});
  @override
  State<DuelChallengeScreen> createState() => _DuelChallengeScreenState();
}

class _DuelChallengeScreenState extends State<DuelChallengeScreen> {
  static const Color _primary  = Color(0xFF2A9D8F);
  static const Color _dark     = Color(0xFF0D2B28);
  static const Color _darkMid  = Color(0xFF1A4040);
  static const Color _tealGlow = Color(0xFF3ECFBE);
  static const Color _ink      = Color(0xFF0F172A);
  static const Color _inkMid   = Color(0xFF475569);
  static const Color _inkLight = Color(0xFF94A3B8);
  static const Color _border   = Color(0xFFE2E8F0);
  static const Color _bg       = Color(0xFFF5F7FA);
  static const Color _red      = Color(0xFFEF4444);

  final DuelService _service = DuelService();
  final _idCtrl              = TextEditingController();
  final _formKey             = GlobalKey<FormState>();

  List<dynamic> _quizzes      = [];
  String?       _selectedQuizId;
  String?       _selectedQuizTitle;
  bool          _isLoadingQuiz = true;
  bool          _isSending     = false;
  bool          _searchByNim   = true;
  String        _myRole        = '';
  String        _myDuelCode    = '';

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  @override
  void dispose() {
    _idCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadData() async {
    final results = await Future.wait([
      _service.getQuizList(),
      _service.getMyDuelCode(),
    ]);
    if (!mounted) return;
    final codeData = results[1] as Map<String, dynamic>?;
    setState(() {
      _quizzes      = results[0] as List;
      _myRole       = codeData?['role']?.toString() ?? '';
      _myDuelCode   = codeData?['duel_code']?.toString() ?? '';
      if (_myRole == 'public') _searchByNim = false;
      _isLoadingQuiz = false;
    });
  }

  Future<void> _send() async {
    if (!_formKey.currentState!.validate()) return;
    if (_selectedQuizId == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Pilih quiz dulu ya!'),
          behavior: SnackBarBehavior.floating));
      return;
    }
    HapticFeedback.heavyImpact();
    setState(() => _isSending = true);
    final res = await _service.challenge(
      quizId:     _selectedQuizId!,
      identifier: _idCtrl.text.trim(),
      searchBy:   _searchByNim ? 'nim' : 'duel_code',
    );
    if (!mounted) return;
    setState(() => _isSending = false);

    if (res == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Gagal mengirim. Periksa koneksi.'),
          behavior: SnackBarBehavior.floating));
      return;
    }

    final duelData = res['data'];
    if (duelData == null) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(res['message']?.toString() ?? 'Lawan tidak ditemukan'),
          behavior: SnackBarBehavior.floating));
      return;
    }

    // Berhasil → pop kembali ke DuelScreen (auto refresh via polling)
    ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
      content: Text('⚔️ Tantangan terkirim! Menunggu lawan merespons...'),
      behavior: SnackBarBehavior.floating));
    Navigator.pop(context, true);
  }

  @override
  Widget build(BuildContext context) {
    final top = MediaQuery.of(context).padding.top;

    return Scaffold(
      body: Container(
        // Sama dengan DuelScreen — arena gelap
        decoration: const BoxDecoration(
          gradient: LinearGradient(
              colors: [_dark, _darkMid, Color(0xFF1F5C55)],
              begin: Alignment.topCenter,
              end: Alignment.bottomCenter)),
        child: Column(children: [
          // ── Header ─────────────────────────────────────
          Padding(
            padding: EdgeInsets.fromLTRB(20, top + 16, 20, 20),
            child: Row(children: [
              GestureDetector(
                onTap: () => Navigator.pop(context),
                child: Container(
                  width: 40, height: 40,
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.1),
                    shape: BoxShape.circle,
                    border: Border.all(
                        color: Colors.white.withOpacity(0.2))),
                  child: const Icon(Icons.arrow_back_ios_new_rounded,
                      color: Colors.white, size: 16))),
              const SizedBox(width: 14),
              const Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('QUIZ DUEL', style: TextStyle(
                      color: Colors.white54, fontSize: 10,
                      fontWeight: FontWeight.w900, letterSpacing: 2)),
                  Text('Tantang Lawan', style: TextStyle(
                      color: Colors.white, fontSize: 22,
                      fontWeight: FontWeight.w900,
                      letterSpacing: -0.5)),
                ],
              ),
            ]),
          ),

          // ── Body scroll ─────────────────────────────────
          Expanded(
            child: _isLoadingQuiz
                ? const Center(child: CircularProgressIndicator(
                    color: _tealGlow, strokeWidth: 2))
                : SingleChildScrollView(
                    padding: EdgeInsets.fromLTRB(
                        20, 0, 20,
                        MediaQuery.of(context).padding.bottom + 24),
                    child: Form(
                      key: _formKey,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Kode Duel saya
                          if (_myDuelCode.isNotEmpty)
                            _buildMyCodeCard(),

                          const SizedBox(height: 20),

                          // Pilih Quiz
                          _buildSectionLabel('⚔️  Pilih Quiz'),
                          const SizedBox(height: 10),
                          _buildQuizPicker(),

                          const SizedBox(height: 20),

                          // Cari Lawan
                          _buildSectionLabel('🎯  Cari Lawan'),
                          const SizedBox(height: 10),

                          // Toggle NIM / Kode Duel
                          if (_myRole != 'public')
                            _buildToggleSearch(),

                          const SizedBox(height: 12),
                          _buildInputField(),

                          const SizedBox(height: 28),

                          // Kirim
                          _buildSendButton(),
                        ],
                      ),
                    ),
                  ),
          ),
        ]),
      ),
    );
  }

  Widget _buildMyCodeCard() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.08),
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: _tealGlow.withOpacity(0.3))),
      child: Row(children: [
        Container(
          width: 44, height: 44,
          decoration: BoxDecoration(
            color: _primary.withOpacity(0.2),
            shape: BoxShape.circle,
            border: Border.all(color: _tealGlow.withOpacity(0.4))),
          child: const Icon(Icons.qr_code_rounded,
              color: _tealGlow, size: 22)),
        const SizedBox(width: 14),
        Expanded(child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Kode Duel Kamu', style: TextStyle(
                color: Colors.white54, fontSize: 11,
                fontWeight: FontWeight.w600)),
            Text(_myDuelCode, style: const TextStyle(
                color: _tealGlow, fontSize: 22,
                fontWeight: FontWeight.w900, letterSpacing: 3)),
            const Text('Bagikan ke temanmu agar bisa ditantang',
                style: TextStyle(color: Colors.white38, fontSize: 10)),
          ],
        )),
        const SizedBox(width: 10),
        GestureDetector(
          onTap: () {
            HapticFeedback.lightImpact();
            Clipboard.setData(ClipboardData(text: _myDuelCode));
            ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
              content: Text('✅ Kode disalin!'),
              behavior: SnackBarBehavior.floating,
              duration: Duration(seconds: 1)));
          },
          child: Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: _tealGlow.withOpacity(0.15),
              shape: BoxShape.circle,
              border: Border.all(color: _tealGlow.withOpacity(0.3))),
            child: const Icon(Icons.copy_rounded,
                color: _tealGlow, size: 18))),
      ]),
    );
  }

  Widget _buildSectionLabel(String text) {
    return Text(text, style: const TextStyle(
        color: Colors.white70, fontSize: 13,
        fontWeight: FontWeight.w700));
  }

  Widget _buildQuizPicker() {
    if (_quizzes.isEmpty) {
      return Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white.withOpacity(0.06),
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: Colors.white.withOpacity(0.1))),
        child: const Center(child: Text('Tidak ada quiz tersedia',
            style: TextStyle(color: Colors.white38, fontSize: 13))));
    }

    return Container(
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.06),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: Colors.white.withOpacity(0.12))),
      child: Column(
        children: _quizzes.asMap().entries.map((entry) {
          final i     = entry.key;
          final quiz  = entry.value;
          final id    = (quiz['_id'] ?? quiz['id'])?.toString() ?? '';
          final title = quiz['title']?.toString() ?? 'Quiz';
          final isSelected = _selectedQuizId == id;
          final isLast = i == _quizzes.length - 1;

          return GestureDetector(
            onTap: () {
              HapticFeedback.lightImpact();
              setState(() {
                _selectedQuizId    = id;
                _selectedQuizTitle = title;
              });
            },
            child: Container(
              padding: const EdgeInsets.symmetric(
                  horizontal: 16, vertical: 14),
              decoration: BoxDecoration(
                color: isSelected
                    ? _primary.withOpacity(0.2) : Colors.transparent,
                borderRadius: BorderRadius.vertical(
                  top: i == 0
                      ? const Radius.circular(14) : Radius.zero,
                  bottom: isLast
                      ? const Radius.circular(14) : Radius.zero)),
              child: Row(children: [
                AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  width: 22, height: 22,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: isSelected ? _primary : Colors.transparent,
                    border: Border.all(
                        color: isSelected
                            ? _tealGlow : Colors.white.withOpacity(0.2),
                        width: isSelected ? 0 : 1.5)),
                  child: isSelected
                      ? const Icon(Icons.check_rounded,
                          color: Colors.white, size: 14)
                      : null),
                const SizedBox(width: 12),
                Expanded(child: Text(title, style: TextStyle(
                    color: isSelected ? _tealGlow : Colors.white70,
                    fontSize: 14,
                    fontWeight: isSelected
                        ? FontWeight.w700 : FontWeight.w500))),
                if (!isLast && !isSelected)
                  Divider(color: Colors.white.withOpacity(0.08)),
              ]),
            ),
          );
        }).toList(),
      ),
    );
  }

  Widget _buildToggleSearch() {
    return Container(
      height: 40,
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.06),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.white.withOpacity(0.1))),
      child: Row(children: [
        Expanded(child: GestureDetector(
          onTap: () => setState(() => _searchByNim = true),
          child: AnimatedContainer(
            duration: const Duration(milliseconds: 200),
            margin: const EdgeInsets.all(3),
            decoration: BoxDecoration(
              color: _searchByNim ? _primary : Colors.transparent,
              borderRadius: BorderRadius.circular(9)),
            child: Center(child: Text('Cari via NIM',
                style: TextStyle(
                    color: _searchByNim ? Colors.white : Colors.white38,
                    fontSize: 12,
                    fontWeight: FontWeight.w700)))))),
        Expanded(child: GestureDetector(
          onTap: () => setState(() => _searchByNim = false),
          child: AnimatedContainer(
            duration: const Duration(milliseconds: 200),
            margin: const EdgeInsets.all(3),
            decoration: BoxDecoration(
              color: !_searchByNim ? _primary : Colors.transparent,
              borderRadius: BorderRadius.circular(9)),
            child: Center(child: Text('Kode Duel',
                style: TextStyle(
                    color: !_searchByNim ? Colors.white : Colors.white38,
                    fontSize: 12,
                    fontWeight: FontWeight.w700)))))),
      ]),
    );
  }

  Widget _buildInputField() {
    final isNim = _searchByNim;
    return TextFormField(
      controller: _idCtrl,
      style: const TextStyle(
          color: Colors.white, fontSize: 16,
          fontWeight: FontWeight.w600, letterSpacing: 1),
      cursorColor: _tealGlow,
      keyboardType: TextInputType.text,
      decoration: InputDecoration(
        hintText: isNim ? 'Masukkan NIM lawan...' : 'Masukkan kode duel...',
        hintStyle: const TextStyle(color: Colors.white24, fontSize: 14),
        prefixIcon: Icon(
          isNim ? Icons.badge_outlined : Icons.tag_rounded,
          color: _tealGlow.withOpacity(0.7), size: 20),
        filled: true,
        fillColor: Colors.white.withOpacity(0.06),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide(color: Colors.white.withOpacity(0.12))),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide(color: Colors.white.withOpacity(0.12))),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: _tealGlow, width: 1.5)),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: _red)),
        errorStyle: const TextStyle(color: _red),
        contentPadding: const EdgeInsets.symmetric(
            horizontal: 16, vertical: 16)),
      validator: (v) {
        if (v == null || v.trim().isEmpty) {
          return isNim
              ? 'NIM tidak boleh kosong'
              : 'Kode duel tidak boleh kosong';
        }
        return null;
      },
    );
  }

  Widget _buildSendButton() {
    return SizedBox(
      width: double.infinity,
      child: ElevatedButton(
        onPressed: _isSending ? null : _send,
        style: ElevatedButton.styleFrom(
          backgroundColor: _primary,
          foregroundColor: Colors.white,
          disabledBackgroundColor: _primary.withOpacity(0.4),
          elevation: 0,
          padding: const EdgeInsets.symmetric(vertical: 15),
          shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(16)),
          shadowColor: _tealGlow.withOpacity(0.4)),
        child: _isSending
            ? const SizedBox(width: 20, height: 20,
                child: CircularProgressIndicator(
                    color: Colors.white, strokeWidth: 2))
            : const Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.sports_martial_arts_rounded, size: 20),
                  SizedBox(width: 8),
                  Text('Kirim Tantangan!', style: TextStyle(
                      fontSize: 15, fontWeight: FontWeight.w900,
                      letterSpacing: 0.5)),
                ],
              )));
  }
}