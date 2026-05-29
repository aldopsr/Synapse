// lib/screens/duel_challenge_screen.dart
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../services/duel_service.dart';
import 'duel_waiting_screen.dart';

class DuelChallengeScreen extends StatefulWidget {
  const DuelChallengeScreen({super.key});
  @override
  State<DuelChallengeScreen> createState() => _DuelChallengeScreenState();
}

class _DuelChallengeScreenState extends State<DuelChallengeScreen> {
  static const Color _primary = Color(0xFF2A9D8F);
  static const Color _ink     = Color(0xFF0F172A);
  static const Color _inkMid  = Color(0xFF475569);
  static const Color _border  = Color(0xFFE2E8F0);
  static const Color _red     = Color(0xFFDC2626);

  final DuelService _service  = DuelService();
  final _idCtrl               = TextEditingController();
  final _formKey              = GlobalKey<FormState>();

  List<dynamic> _quizzes      = [];
  String?       _selectedQuizId;
  bool          _isLoadingQuiz = true;
  bool          _isSending     = false;
  bool          _searchByNim  = true; // true=NIM, false=duel_code
  String        _myRole       = '';
  String        _myDuelCode   = '';

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
      // Public hanya bisa search by duel_code
      if (_myRole == 'public') _searchByNim = false;
      _isLoadingQuiz = false;
    });
  }

  Future<void> _send() async {
    if (!_formKey.currentState!.validate()) return;
    if (_selectedQuizId == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Pilih quiz dulu'),
          behavior: SnackBarBehavior.floating));
      return;
    }

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

    final isSuccess = res['data'] != null;
    if (!isSuccess) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(res['message']?.toString() ?? 'Gagal'),
          backgroundColor: _red,
          behavior: SnackBarBehavior.floating));
      return;
    }

    // Berhasil → langsung ke waiting room
    final duelId = res['data']['id']?.toString() ?? '';
    if (!mounted) return;
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(
        builder: (_) => DuelWaitingScreen(
          duelId: duelId,
          role: 'challenger',
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        backgroundColor: Colors.white,
        foregroundColor: _ink,
        elevation: 0,
        surfaceTintColor: Colors.transparent,
        title: const Text('Tantang Teman',
            style: TextStyle(
                fontWeight: FontWeight.w800, fontSize: 18)),
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(1),
          child: Container(height: 1, color: _border),
        ),
      ),
      body: _isLoadingQuiz
          ? const Center(
              child: CircularProgressIndicator(color: _primary))
          : Form(
              key: _formKey,
              child: ListView(
                padding: const EdgeInsets.all(24),
                children: [
                  // Kode duel sendiri
                  if (_myDuelCode.isNotEmpty) ...[
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: const Color(0xFFE6F4F2),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                            color: _primary.withOpacity(0.3)),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.qr_code_rounded,
                              color: _primary, size: 20),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Column(
                              crossAxisAlignment:
                                  CrossAxisAlignment.start,
                              children: [
                                const Text('Kode Duelmu',
                                    style: TextStyle(
                                        fontSize: 11,
                                        color: _inkMid)),
                                Text(_myDuelCode,
                                    style: const TextStyle(
                                      fontSize: 20,
                                      fontWeight: FontWeight.w800,
                                      color: _primary,
                                      letterSpacing: 3,
                                    )),
                              ],
                            ),
                          ),
                          GestureDetector(
                            onTap: () {
                              Clipboard.setData(
                                  ClipboardData(text: _myDuelCode));
                              ScaffoldMessenger.of(context)
                                  .showSnackBar(const SnackBar(
                                content:
                                    Text('Kode disalin ke clipboard'),
                                behavior: SnackBarBehavior.floating,
                              ));
                            },
                            child: Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 10, vertical: 6),
                              decoration: BoxDecoration(
                                color: _primary.withOpacity(0.15),
                                borderRadius:
                                    BorderRadius.circular(8),
                              ),
                              child: const Text('Salin',
                                  style: TextStyle(
                                    color: _primary,
                                    fontSize: 12,
                                    fontWeight: FontWeight.w600,
                                  )),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 24),
                  ],

                  // Pilih quiz
                  const Text('Pilih Quiz',
                      style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                          color: _ink)),
                  const SizedBox(height: 8),
                  Container(
                    decoration: BoxDecoration(
                      border: Border.all(color: _border),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    padding:
                        const EdgeInsets.symmetric(horizontal: 14),
                    child: DropdownButtonHideUnderline(
                      child: DropdownButton<String>(
                        value: _selectedQuizId,
                        hint: const Text('Pilih quiz untuk duel',
                            style: TextStyle(
                                color: Color(0xFF94A3B8),
                                fontSize: 14)),
                        isExpanded: true,
                        items: _quizzes.map((q) {
                          return DropdownMenuItem<String>(
                            value: q['id']?.toString() ?? '',
                            child: Text(
                                q['title']?.toString() ?? '-',
                                style: const TextStyle(
                                    fontSize: 14)),
                          );
                        }).toList(),
                        onChanged: (val) =>
                            setState(() => _selectedQuizId = val),
                      ),
                    ),
                  ),

                  const SizedBox(height: 24),

                  // Toggle NIM / Kode Duel (hanya untuk mahasiswa)
                  if (_myRole == 'mahasiswa') ...[
                    const Text('Cari Lawan',
                        style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w700,
                            color: _ink)),
                    const SizedBox(height: 8),
                    Container(
                      decoration: BoxDecoration(
                        color: const Color(0xFFF8FAFC),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: _border),
                      ),
                      child: Row(
                        children: [
                          _buildToggle('NIM', true),
                          _buildToggle('Kode Duel', false),
                        ],
                      ),
                    ),
                    const SizedBox(height: 12),
                  ],

                  // Input identifier
                  Text(
                    _searchByNim ? 'NIM Lawan' : 'Kode Duel Lawan',
                    style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w700,
                        color: _ink),
                  ),
                  const SizedBox(height: 8),
                  TextFormField(
                    controller: _idCtrl,
                    textCapitalization: _searchByNim
                        ? TextCapitalization.none
                        : TextCapitalization.characters,
                    decoration: InputDecoration(
                      hintText: _searchByNim
                          ? 'Contoh: J0409241025'
                          : 'Contoh: AB3X9K',
                      prefixIcon: Icon(
                        _searchByNim
                            ? Icons.badge_rounded
                            : Icons.key_rounded,
                        color: const Color(0xFF94A3B8),
                      ),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(10),
                        borderSide:
                            const BorderSide(color: Color(0xFFE2E8F0)),
                      ),
                      enabledBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(10),
                        borderSide:
                            const BorderSide(color: Color(0xFFE2E8F0)),
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(10),
                        borderSide: const BorderSide(
                            color: _primary, width: 2),
                      ),
                    ),
                    validator: (v) {
                      if (v == null || v.trim().isEmpty) {
                        return _searchByNim
                            ? 'NIM wajib diisi'
                            : 'Kode Duel wajib diisi';
                      }
                      return null;
                    },
                  ),

                  const SizedBox(height: 36),

                  SizedBox(
                    width: double.infinity,
                    height: 50,
                    child: ElevatedButton.icon(
                      onPressed: _isSending ? null : _send,
                      icon: _isSending
                          ? const SizedBox(
                              width: 16, height: 16,
                              child: CircularProgressIndicator(
                                  color: Colors.white,
                                  strokeWidth: 2))
                          : const Icon(
                              Icons.sports_martial_arts_rounded),
                      label: Text(
                        _isSending ? 'Mengirim...' : 'Kirim Tantangan',
                        style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 15),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: _primary,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ),
                ],
              ),
            ),
    );
  }

  Widget _buildToggle(String label, bool isNim) {
    final isSelected = _searchByNim == isNim;
    return Expanded(
      child: GestureDetector(
        onTap: () {
          setState(() {
            _searchByNim = isNim;
            _idCtrl.clear();
          });
        },
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 10),
          decoration: BoxDecoration(
            color: isSelected ? _primary : Colors.transparent,
            borderRadius: BorderRadius.circular(9),
          ),
          child: Text(label,
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w600,
                color: isSelected ? Colors.white : _inkMid,
              )),
        ),
      ),
    );
  }
}