// lib/screens/duel_battle_screen.dart
import 'dart:async';
import 'package:flutter/material.dart';
import '../services/duel_service.dart';
import 'duel_screen.dart';

class DuelBattleScreen extends StatefulWidget {
  final String duelId;
  const DuelBattleScreen({super.key, required this.duelId});

  @override
  State<DuelBattleScreen> createState() => _DuelBattleScreenState();
}

class _DuelBattleScreenState extends State<DuelBattleScreen> {
  static const Color _primary  = Color(0xFF2A9D8F);
  static const Color _ink      = Color(0xFF0F172A);
  static const Color _inkMid   = Color(0xFF475569);
  static const Color _inkLight = Color(0xFF94A3B8);
  static const Color _border   = Color(0xFFE2E8F0);
  static const Color _red      = Color(0xFFDC2626);
  static const Color _green    = Color(0xFF16A34A);

  final DuelService _service = DuelService();

  List<dynamic>         _questions    = [];
  final Map<String, dynamic> _answers  = {};
  int                   _currentIndex = 0;
  bool                  _isLoadingQ   = true;
  bool                  _isSubmitting = false;
  String                _quizTitle    = 'Duel';
  int                   _duration     = 15;

  late final Stopwatch _stopwatch;
  Timer?  _timer;
  int     _secondsLeft = 0;

  bool                  _submitted       = false;
  bool                  _waitingOpponent = false;
  bool                  _waitingTimedOut = false;
  Map<String, dynamic>? _result;
  Timer?                _pollingTimer;
  Timer?                _waitingTimeoutTimer;

  static const int _waitingTimeoutSec = 180; // 3 menit

  @override
  void initState() {
    super.initState();
    _stopwatch = Stopwatch()..start();
    _loadQuestions();
  }

  @override
  void dispose() {
    _timer?.cancel();
    _pollingTimer?.cancel();
    _waitingTimeoutTimer?.cancel();
    _stopwatch.stop();
    super.dispose();
  }

  Future<void> _loadQuestions() async {
    final data = await _service.getQuestions(widget.duelId);
    if (!mounted) return;

    if (data == null || data['data'] == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Gagal memuat soal duel')),
      );
      Navigator.pop(context);
      return;
    }

    setState(() {
      _questions   = List<dynamic>.from(data['data']);
      _quizTitle   = data['quiz_title']?.toString() ?? 'Duel';
      _duration    = (data['duration'] as num?)?.toInt() ?? 15;
      _secondsLeft = _duration * 60;
      _isLoadingQ  = false;
    });

    _timer = Timer.periodic(const Duration(seconds: 1), (_) {
      if (!mounted) return;
      setState(() => _secondsLeft--);
      if (_secondsLeft <= 0) _submitAnswers();
    });
  }

  Future<void> _submitAnswers() async {
    if (_isSubmitting || _submitted) return;
    _timer?.cancel();
    setState(() => _isSubmitting = true);

    final answerList = _questions.map((q) {
      final qid = q['id']?.toString() ?? q['_id']?.toString() ?? '';
      return {'question_id': qid, 'answer': _answers[qid] ?? ''};
    }).toList();

    final timeTaken = _stopwatch.elapsed.inSeconds;
    final res = await _service.submit(widget.duelId, timeTaken, answerList);
    if (!mounted) return;

    setState(() => _isSubmitting = false);

    final duelStatus = res?['duel_status']?.toString();

    if (duelStatus == 'completed') {
      // FIX: fetch result DULU, baru set _submitted = true
      // Sehingga _buildResultScreen() tidak pernah render dengan _result == null
      await _fetchResult();
      if (!mounted) return;
      setState(() => _submitted = true);
    } else {
      // Lawan belum selesai — tampilkan waiting, mulai polling
      setState(() {
        _submitted       = true;
        _waitingOpponent = true;
      });
      _startPolling();
    }
  }

  void _startPolling() {
    // Timeout: jika lawan belum selesai dalam 3 menit, hentikan polling & tampilkan pesan
    _waitingTimeoutTimer = Timer(
      const Duration(seconds: _waitingTimeoutSec),
      () {
        if (!mounted) return;
        _pollingTimer?.cancel();
        setState(() => _waitingTimedOut = true);
      },
    );

    _pollingTimer = Timer.periodic(const Duration(seconds: 5), (_) async {
      final status = await _service.getStatus(widget.duelId);
      if (!mounted) {
        _pollingTimer?.cancel();
        return;
      }

      final duelStatus = status?['data']?['status']?.toString();

      if (duelStatus == 'completed') {
        _pollingTimer?.cancel();
        await _fetchResult();
        // _fetchResult already handles setState internally — no extra setState needed
      } else if (duelStatus == 'expired') {
        _pollingTimer?.cancel();
        if (!mounted) return;
        setState(() {
          _waitingOpponent = false;
          _result = status?['data'];
          _submitted = true;
        });
      }
    });
  }

  Future<void> _fetchResult() async {
    for (int attempt = 0; attempt < 3; attempt++) {
      if (attempt > 0) {
        await Future.delayed(const Duration(seconds: 2));
        if (!mounted) return;
      }

      final status = await _service.getStatus(widget.duelId);
      if (!mounted) return;

      final resultData = status?['data'] as Map<String, dynamic>?;
      if (resultData != null) {
        setState(() {
          _result          = resultData;
          _waitingOpponent = false;
        });
        return;
      }
    }
    // All retries exhausted — polling will catch it on next tick if still active
  }

  String _formatTime(int seconds) {
    final m = (seconds ~/ 60).toString().padLeft(2, '0');
    final s = (seconds % 60).toString().padLeft(2, '0');
    return '$m:$s';
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoadingQ) {
      return const Scaffold(
        backgroundColor: Colors.white,
        body: Center(child: CircularProgressIndicator(
            color: _primary, strokeWidth: 2)),
      );
    }

    if (_submitted) return _buildResultScreen();

    return PopScope(
      canPop: false,
      child: Scaffold(
        backgroundColor: Colors.white,
        appBar: AppBar(
          backgroundColor: Colors.white,
          foregroundColor: _ink,
          elevation: 0,
          surfaceTintColor: Colors.transparent,
          automaticallyImplyLeading: false,
          title: Text(_quizTitle,
              style: const TextStyle(
                  fontWeight: FontWeight.w800, fontSize: 16)),
          actions: [
            Container(
              margin: const EdgeInsets.only(right: 16),
              padding: const EdgeInsets.symmetric(
                  horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                color: _secondsLeft < 60
                    ? _red.withOpacity(0.1)
                    : const Color(0xFFE6F4F2),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(
                _formatTime(_secondsLeft),
                style: TextStyle(
                  fontWeight: FontWeight.w800,
                  fontSize: 14,
                  color: _secondsLeft < 60 ? _red : _primary,
                ),
              ),
            ),
          ],
          bottom: PreferredSize(
            preferredSize: const Size.fromHeight(4),
            child: LinearProgressIndicator(
              value: _questions.isEmpty
                  ? 0
                  : (_currentIndex + 1) / _questions.length,
              backgroundColor: const Color(0xFFE2E8F0),
              valueColor:
                  const AlwaysStoppedAnimation<Color>(_primary),
              minHeight: 3,
            ),
          ),
        ),
        body: Column(
          children: [
            Expanded(child: _buildQuestion()),
            _buildBottomNav(),
          ],
        ),
      ),
    );
  }

  Widget _buildQuestion() {
    if (_questions.isEmpty) {
      return const Center(child: Text('Tidak ada soal'));
    }

    final q      = _questions[_currentIndex] as Map<String, dynamic>;
    final qId    = q['id']?.toString() ?? q['_id']?.toString() ?? '';
    final qText  = q['question']?.toString() ?? '';
    final imgUrl = q['image_url']?.toString();

    final options = <String, String>{
      'A': q['option_a']?.toString() ?? '',
      'B': q['option_b']?.toString() ?? '',
      'C': q['option_c']?.toString() ?? '',
      'D': q['option_d']?.toString() ?? '',
    };

    final isMultiAnswer =
        (q['question_type']?.toString() ?? 'multiple_choice') == 'multiple_answer';
    final selectedList = isMultiAnswer
        ? List<String>.from((_answers[qId] as List?)?.cast<String>() ?? [])
        : <String>[];

    return ListView(
      padding: const EdgeInsets.fromLTRB(24, 20, 24, 20),
      children: [
        Text(
          'Soal ${_currentIndex + 1} dari ${_questions.length}',
          style: const TextStyle(
              fontSize: 12,
              color: _inkLight,
              fontWeight: FontWeight.w500),
        ),
        if (isMultiAnswer) ...[
          const SizedBox(height: 4),
          const Text(
            'Pilih semua jawaban yang benar',
            style: TextStyle(fontSize: 11, color: _primary, fontWeight: FontWeight.w600),
          ),
        ],
        const SizedBox(height: 12),
        if (imgUrl != null && imgUrl.isNotEmpty) ...[
          ClipRRect(
            borderRadius: BorderRadius.circular(10),
            child: Image.network(imgUrl,
                fit: BoxFit.cover,
                errorBuilder: (_, __, ___) => const SizedBox()),
          ),
          const SizedBox(height: 16),
        ],
        Text(qText,
            style: const TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w700,
              color: _ink,
              height: 1.5,
            )),
        const SizedBox(height: 20),
        ...options.entries.where((e) => e.value.isNotEmpty).map((e) {
          final isSelected = isMultiAnswer
              ? selectedList.contains(e.key)
              : _answers[qId] == e.key;
          return GestureDetector(
            onTap: () => setState(() {
              if (isMultiAnswer) {
                final current = List<String>.from(
                    (_answers[qId] as List?)?.cast<String>() ?? []);
                if (current.contains(e.key)) {
                  current.remove(e.key);
                } else {
                  current.add(e.key);
                }
                _answers[qId] = current;
              } else {
                _answers[qId] = e.key;
              }
            }),
            child: Container(
              margin: const EdgeInsets.only(bottom: 10),
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: isSelected
                    ? _primary.withOpacity(0.08)
                    : Colors.white,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(
                  color: isSelected ? _primary : _border,
                  width: isSelected ? 2 : 1,
                ),
              ),
              child: Row(
                children: [
                  Container(
                    width: 28,
                    height: 28,
                    decoration: BoxDecoration(
                      color: isSelected ? _primary : _border,
                      borderRadius: BorderRadius.circular(
                          isMultiAnswer ? 6 : 14),
                    ),
                    child: Center(
                      child: isMultiAnswer && isSelected
                          ? const Icon(Icons.check_rounded,
                              size: 16, color: Colors.white)
                          : Text(e.key,
                              style: TextStyle(
                                fontWeight: FontWeight.w700,
                                fontSize: 12,
                                color: isSelected ? Colors.white : _inkMid,
                              )),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(e.value,
                        style: TextStyle(
                          fontSize: 14,
                          color: isSelected ? _ink : _inkMid,
                          fontWeight: isSelected
                              ? FontWeight.w600
                              : FontWeight.normal,
                        )),
                  ),
                ],
              ),
            ),
          );
        }),
      ],
    );
  }

  Widget _buildBottomNav() {
    final isLast = _currentIndex == _questions.length - 1;
    return Container(
      padding: const EdgeInsets.fromLTRB(24, 12, 24, 24),
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border(top: BorderSide(color: _border)),
      ),
      child: Row(
        children: [
          if (_currentIndex > 0)
            Expanded(
              child: OutlinedButton(
                onPressed: () => setState(() => _currentIndex--),
                style: OutlinedButton.styleFrom(
                  foregroundColor: _primary,
                  side: const BorderSide(color: _primary),
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10)),
                ),
                child: const Text('Sebelumnya'),
              ),
            ),
          if (_currentIndex > 0) const SizedBox(width: 12),
          Expanded(
            flex: 2,
            child: ElevatedButton(
              onPressed: _isSubmitting
                  ? null
                  : isLast
                      ? _submitAnswers
                      : () => setState(() => _currentIndex++),
              style: ElevatedButton.styleFrom(
                backgroundColor: isLast ? _red : _primary,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10)),
              ),
              child: _isSubmitting
                  ? const SizedBox(
                      width: 16,
                      height: 16,
                      child: CircularProgressIndicator(
                          color: Colors.white, strokeWidth: 2))
                  : Text(
                      isLast ? 'Selesai & Kirim' : 'Selanjutnya',
                      style: const TextStyle(
                          fontWeight: FontWeight.bold)),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildResultScreen() {
    // Waiting — lawan belum selesai
    if (_waitingOpponent || _result == null) {
      return PopScope(
        canPop: false,
        child: Scaffold(
          backgroundColor: Colors.white,
          body: Center(
            child: Padding(
              padding: const EdgeInsets.all(40),
              child: _waitingTimedOut
                  ? _buildTimeoutUI()
                  : _buildWaitingUI(),
            ),
          ),
        ),
      );
    }

    // Hasil tersedia — wrapped with PopScope to prevent accidental back
    final iWon       = _result!['i_won'];
    final myScore    = _result!['my_score'];
    final oppScore   = _result!['opponent_score'];
    final winnerName = _result!['winner_name']?.toString() ?? '';
    final rawOpp     = _result!['i_am'] == 'challenger'
        ? _result!['opponent']
        : _result!['challenger'];
    final opponentInfo = rawOpp as Map<String, dynamic>?;
    final opponentName = opponentInfo?['name']?.toString() ?? 'Lawan';

    return PopScope(
      canPop: false,
      child: Scaffold(
        backgroundColor: Colors.white,
        body: SafeArea(
          child: Padding(
            padding: const EdgeInsets.all(32),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Container(
                  width: 80,
                  height: 80,
                  decoration: BoxDecoration(
                    color: iWon == true
                        ? _green.withOpacity(0.1)
                        : _red.withOpacity(0.1),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(
                    iWon == true
                        ? Icons.emoji_events_rounded
                        : Icons.sports_martial_arts_rounded,
                    color: iWon == true ? _green : _red,
                    size: 40,
                  ),
                ),
                const SizedBox(height: 20),
                Text(
                  iWon == true ? '🏆 Kamu Menang!' : '💀 Kamu Kalah',
                  style: TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.w800,
                    color: iWon == true ? _green : _red,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  iWon == true
                      ? 'Luar biasa! Kamu mengalahkan $opponentName'
                      : '$winnerName bermain lebih baik kali ini',
                  style: const TextStyle(fontSize: 14, color: _inkMid),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 32),
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    border: Border.all(color: _border),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceAround,
                    children: [
                      _buildScoreCol(
                          'Kamu',
                          myScore?.toString() ?? '-',
                          iWon == true ? _green : _red),
                      Text('VS',
                          style: TextStyle(
                              fontSize: 12,
                              color: _inkLight,
                              fontWeight: FontWeight.w700)),
                      _buildScoreCol(
                          opponentName,
                          oppScore?.toString() ?? '-',
                          iWon == true ? _red : _green),
                    ],
                  ),
                ),
                const SizedBox(height: 32),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: () {
                      Navigator.pushAndRemoveUntil(
                        context,
                        MaterialPageRoute(builder: (_) => const DuelScreen()),
                        (route) => route.isFirst,
                      );
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: _primary,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    child: const Text(
                      'Kembali ke Arena',
                      style: TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 15,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildWaitingUI() {
    return Column(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        const CircularProgressIndicator(color: _primary, strokeWidth: 2),
        const SizedBox(height: 24),
        const Text(
          'Menunggu lawan selesai...',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: _ink),
        ),
        const SizedBox(height: 8),
        const Text(
          'Halaman ini otomatis update\nsaat lawan selesai.',
          style: TextStyle(fontSize: 13, color: _inkMid),
          textAlign: TextAlign.center,
        ),
        const SizedBox(height: 32),
        TextButton(
          onPressed: () {
            _pollingTimer?.cancel();
            _waitingTimeoutTimer?.cancel();
            Navigator.pop(context);
          },
          child: const Text('Kembali ke Arena', style: TextStyle(color: _primary)),
        ),
      ],
    );
  }

  Widget _buildTimeoutUI() {
    return Column(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Container(
          width: 72,
          height: 72,
          decoration: BoxDecoration(
            color: _red.withValues(alpha: 0.08),
            shape: BoxShape.circle,
          ),
          child: const Icon(Icons.hourglass_disabled_rounded, color: _red, size: 36),
        ),
        const SizedBox(height: 20),
        const Text(
          'Lawan Tidak Merespons',
          style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: _ink),
          textAlign: TextAlign.center,
        ),
        const SizedBox(height: 8),
        const Text(
          'Sudah 3 menit berlalu.\nSepertinya lawan meninggalkan pertandingan.',
          style: TextStyle(fontSize: 13, color: _inkMid),
          textAlign: TextAlign.center,
        ),
        const SizedBox(height: 32),
        SizedBox(
          width: double.infinity,
          child: ElevatedButton(
            onPressed: () => Navigator.pop(context),
            style: ElevatedButton.styleFrom(
              backgroundColor: _primary,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(vertical: 14),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            child: const Text('Kembali ke Arena',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
          ),
        ),
      ],
    );
  }

  Widget _buildScoreCol(String name, String score, Color color) =>
      Column(
        children: [
          Text(score,
              style: TextStyle(
                fontSize: 28,
                fontWeight: FontWeight.w800,
                color: color,
              )),
          const SizedBox(height: 4),
          Text(name,
              style:
                  const TextStyle(fontSize: 12, color: _inkMid),
              maxLines: 1,
              overflow: TextOverflow.ellipsis),
        ],
      );
}