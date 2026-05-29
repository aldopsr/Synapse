// lib/screens/duel_waiting_screen.dart
// Waiting room — challenger menunggu accept, opponent melihat tantangan masuk
// Setelah accept → countdown 3..2..1 → DuelBattleScreen
import 'dart:async';
import 'package:flutter/material.dart';
import '../services/duel_service.dart';
import 'duel_battle_screen.dart';

class DuelWaitingScreen extends StatefulWidget {
  final String duelId;
  final String role; // 'challenger' | 'opponent'

  const DuelWaitingScreen({
    super.key,
    required this.duelId,
    required this.role,
  });

  @override
  State<DuelWaitingScreen> createState() => _DuelWaitingScreenState();
}

class _DuelWaitingScreenState extends State<DuelWaitingScreen>
    with SingleTickerProviderStateMixin {
  static const Color _primary = Color(0xFF2A9D8F);
  static const Color _ink     = Color(0xFF0F172A);
  static const Color _inkMid  = Color(0xFF475569);
  static const Color _red     = Color(0xFFDC2626);

  final DuelService _service = DuelService();

  Map<String, dynamic>? _duel;
  bool  _isLoading    = true;
  bool  _isResponding = false;
  bool  _isCancelling = false;
  int   _countdown    = 0; // >0 berarti sedang countdown
  Timer? _pollTimer;
  Timer? _countdownTimer;
  late AnimationController _pulseCtrl;
  late Animation<double>   _pulseAnim;

  @override
  void initState() {
    super.initState();
    _pulseCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 900),
    )..repeat(reverse: true);
    _pulseAnim = Tween<double>(begin: 0.95, end: 1.05)
        .animate(CurvedAnimation(parent: _pulseCtrl,
            curve: Curves.easeInOut));

    _loadDuel();
    // Polling tiap 3 detik
    _pollTimer = Timer.periodic(
        const Duration(seconds: 3), (_) => _loadDuel());
  }

  @override
  void dispose() {
    _pollTimer?.cancel();
    _countdownTimer?.cancel();
    _pulseCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadDuel() async {
    final res = await _service.getStatus(widget.duelId);
    if (!mounted) return;
    final data = res?['data'] as Map<String, dynamic>?;
    if (data == null) return;

    setState(() {
      _duel      = data;
      _isLoading = false;
    });

    // Kalau status jadi active → mulai countdown
    if (data['status'] == 'active' && _countdown == 0) {
      _pollTimer?.cancel();
      _startCountdown();
    }

    // Kalau expired/declined/cancelled → tutup
    if (['expired', 'declined', 'cancelled']
        .contains(data['status'])) {
      _pollTimer?.cancel();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(_statusMessage(data['status'])),
          behavior: SnackBarBehavior.floating,
        ));
        Navigator.pop(context);
      }
    }
  }

  void _startCountdown() {
    setState(() => _countdown = 3);
    _countdownTimer = Timer.periodic(
        const Duration(seconds: 1), (timer) {
      if (!mounted) { timer.cancel(); return; }
      if (_countdown <= 1) {
        timer.cancel();
        _goToBattle();
      } else {
        setState(() => _countdown--);
      }
    });
  }

  void _goToBattle() {
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(
        builder: (_) => DuelBattleScreen(duelId: widget.duelId),
      ),
    );
  }

  Future<void> _accept() async {
    setState(() => _isResponding = true);
    final res = await _service.respond(widget.duelId, 'accept');
    if (!mounted) return;
    setState(() => _isResponding = false);
    if (res?['data'] != null) {
      _pollTimer?.cancel();
      _startCountdown();
    }
  }

  Future<void> _decline() async {
    setState(() => _isResponding = true);
    await _service.respond(widget.duelId, 'decline');
    if (!mounted) return;
    Navigator.pop(context);
  }

  Future<void> _cancel() async {
    setState(() => _isCancelling = true);
    await _service.cancelDuel(widget.duelId);
    if (!mounted) return;
    Navigator.pop(context);
  }

  String _statusMessage(String? status) => switch (status) {
    'expired'   => 'Waktu habis, tantangan kedaluwarsa.',
    'declined'  => 'Tantangan ditolak.',
    'cancelled' => 'Tantangan dibatalkan.',
    _           => 'Duel berakhir.',
  };

  @override
  Widget build(BuildContext context) {
    // Countdown screen
    if (_countdown > 0) return _buildCountdown();

    return PopScope(
      canPop: false,
      child: Scaffold(
        backgroundColor: Colors.white,
        body: _isLoading
            ? const Center(child: CircularProgressIndicator(
                color: _primary, strokeWidth: 2))
            : _buildWaiting(),
      ),
    );
  }

  Widget _buildCountdown() {
    return Scaffold(
      backgroundColor: const Color(0xFF0F172A),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Text('Bersiap!',
                style: TextStyle(
                  color: Colors.white70,
                  fontSize: 18,
                  fontWeight: FontWeight.w500,
                )),
            const SizedBox(height: 24),
            ScaleTransition(
              scale: _pulseAnim,
              child: Text(
                '$_countdown',
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 96,
                  fontWeight: FontWeight.w900,
                  height: 1,
                ),
              ),
            ),
            const SizedBox(height: 24),
            const Text('Duel dimulai!',
                style: TextStyle(
                  color: Color(0xFF2A9D8F),
                  fontSize: 16,
                  fontWeight: FontWeight.w700,
                  letterSpacing: 1,
                )),
          ],
        ),
      ),
    );
  }

  Widget _buildWaiting() {
    final quiz         = _duel?['quiz_title']?.toString() ?? 'Quiz';
    final isChallenger = widget.role == 'challenger';
    final opponentData = _duel?['opponent'] as Map<String, dynamic>?;
    final challengerData = _duel?['challenger'] as Map<String, dynamic>?;
    final otherData = isChallenger ? opponentData : challengerData;
    final otherName    = otherData?['name']?.toString() ?? 'Lawan';

    return SafeArea(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          children: [
            // Header
            Align(
              alignment: Alignment.centerLeft,
              child: isChallenger
                  ? GestureDetector(
                      onTap: _isCancelling ? null : _cancel,
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.close_rounded,
                              size: 18, color: _red),
                          const SizedBox(width: 4),
                          Text(
                            _isCancelling ? 'Membatalkan...' : 'Batalkan',
                            style: const TextStyle(
                                color: _red,
                                fontWeight: FontWeight.w600),
                          ),
                        ],
                      ),
                    )
                  : const SizedBox.shrink(),
            ),

            const Spacer(),

            // Animasi menunggu
            ScaleTransition(
              scale: _pulseAnim,
              child: Container(
                width: 96, height: 96,
                decoration: BoxDecoration(
                  color: _primary.withOpacity(0.1),
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                    Icons.sports_martial_arts_rounded,
                    color: _primary, size: 48),
              ),
            ),

            const SizedBox(height: 28),

            // Judul quiz
            Container(
              padding: const EdgeInsets.symmetric(
                  horizontal: 12, vertical: 4),
              decoration: BoxDecoration(
                color: const Color(0xFFE6F4F2),
                borderRadius: BorderRadius.circular(6),
              ),
              child: Text(quiz,
                  style: const TextStyle(
                    color: _primary, fontSize: 12,
                    fontWeight: FontWeight.w700,
                  )),
            ),
            const SizedBox(height: 16),

            Text(
              isChallenger
                  ? 'Menunggu $otherName\nmenerima tantangan...'
                  : '$otherName\nmenantangmu untuk duel!',
              style: const TextStyle(
                fontSize: 22,
                fontWeight: FontWeight.w800,
                color: _ink,
                height: 1.3,
              ),
              textAlign: TextAlign.center,
            ),

            const SizedBox(height: 12),

            Text(
              isChallenger
                  ? 'Tantangan akan kedaluwarsa dalam 5 menit'
                  : 'Terima tantangan untuk mulai bertanding',
              style: const TextStyle(
                  fontSize: 13, color: _inkMid),
              textAlign: TextAlign.center,
            ),

            const Spacer(),

            // Tombol aksi
            if (!isChallenger) ...[
              SizedBox(
                width: double.infinity,
                height: 52,
                child: ElevatedButton(
                  onPressed: _isResponding ? null : _accept,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: _primary,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14)),
                  ),
                  child: _isResponding
                      ? const SizedBox(
                          width: 20, height: 20,
                          child: CircularProgressIndicator(
                              color: Colors.white, strokeWidth: 2))
                      : const Text('Terima & Bertanding',
                          style: TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 16)),
                ),
              ),
              const SizedBox(height: 12),
              SizedBox(
                width: double.infinity,
                height: 48,
                child: OutlinedButton(
                  onPressed: _isResponding ? null : _decline,
                  style: OutlinedButton.styleFrom(
                    foregroundColor: _red,
                    side: const BorderSide(color: _red),
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14)),
                  ),
                  child: const Text('Tolak',
                      style: TextStyle(fontWeight: FontWeight.w600)),
                ),
              ),
            ] else ...[
              // Indikator loading untuk challenger
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const SizedBox(
                    width: 14, height: 14,
                    child: CircularProgressIndicator(
                        color: _primary, strokeWidth: 2),
                  ),
                  const SizedBox(width: 10),
                  Text('Menunggu respons...',
                      style: TextStyle(
                          fontSize: 13, color: Colors.grey[500])),
                ],
              ),
            ],

            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }
}