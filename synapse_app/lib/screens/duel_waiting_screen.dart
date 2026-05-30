// lib/screens/duel_waiting_screen.dart
import 'dart:async';
import 'package:flutter/material.dart';
import '../services/duel_service.dart';
import 'duel_battle_screen.dart';

class DuelWaitingScreen extends StatefulWidget {
  final String duelId;
  final String role;
  const DuelWaitingScreen({
      super.key, required this.duelId, required this.role});
  @override
  State<DuelWaitingScreen> createState() => _DuelWaitingScreenState();
}

class _DuelWaitingScreenState extends State<DuelWaitingScreen>
    with SingleTickerProviderStateMixin {
  static const Color _primary = Color(0xFF2A9D8F);
  static const Color _dark    = Color(0xFF0D2B28);
  static const Color _darkMid = Color(0xFF1A4040);
  static const Color _teal    = Color(0xFF3ECFBE);
  static const Color _red     = Color(0xFFEF4444);
  static const Color _amber   = Color(0xFFF59E0B);
  static const Color _green   = Color(0xFF22C55E);
  static const Color _slate   = Color(0xFF64748B);

  final DuelService _service = DuelService();

  Map<String, dynamic>? _duel;
  bool _isLoading    = true;
  bool _isResponding = false;
  bool _isCancelling = false;
  bool _myReady      = false;
  bool _oppReady     = false;
  bool _markingReady = false;
  int  _countdown    = 0;

  // Satu-satunya timer — hanya untuk polling saat nunggu lawan ready
  Timer? _pollTimer;
  // Timer countdown
  Timer? _cdTimer;

  AnimationController? _pulseCtrl;
  Animation<double>?   _pulseAnim;

  @override
  void initState() {
    super.initState();
    _pulseCtrl = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 900))
      ..repeat(reverse: true);
    _pulseAnim = Tween<double>(begin: 0.92, end: 1.08).animate(
        CurvedAnimation(parent: _pulseCtrl!, curve: Curves.easeInOut));

    _loadOnce();
  }

  @override
  void dispose() {
    _pollTimer?.cancel();
    _cdTimer?.cancel();
    _pulseCtrl?.dispose();
    super.dispose();
  }

  // Load sekali saat init
  Future<void> _loadOnce() async {
    final res  = await _service.getStatus(widget.duelId);
    if (!mounted) return;
    final data = res?['data'] as Map<String, dynamic>?;
    if (data == null) { setState(() => _isLoading = false); return; }

    final myReady  = data['my_ready'] == true;
    final oppReady = data['opponent_ready_status'] == true;
    final status   = data['status']?.toString() ?? '';

    setState(() {
      _duel      = data;
      _isLoading = false;
      _myReady   = myReady;
      _oppReady  = oppReady;
    });

    if (['expired','declined','cancelled'].contains(status)) {
      _showStatusAndPop(status);
    }
    // Tidak auto-countdown dari load — hanya dari tombol Ready
  }

  // Poll saat sudah ready tapi nunggu lawan
  void _startPolling() {
    _pollTimer?.cancel();
    _pollTimer = Timer.periodic(const Duration(seconds: 3), (_) async {
      final res  = await _service.getStatus(widget.duelId);
      if (!mounted) { _pollTimer?.cancel(); return; }
      final data = res?['data'] as Map<String, dynamic>?;
      if (data == null) return;

      final oppReady = data['opponent_ready_status'] == true;
      final status   = data['status']?.toString() ?? '';

      setState(() => _oppReady = oppReady);

      if (['expired','declined','cancelled'].contains(status)) {
        _pollTimer?.cancel();
        _showStatusAndPop(status);
        return;
      }

      // Lawan sudah ready → stop poll, mulai countdown
      if (oppReady && _countdown == 0) {
        _pollTimer?.cancel();
        _beginCountdown();
      }
    });
  }

  void _beginCountdown() {
    if (_countdown > 0) return; // sudah jalan
    setState(() => _countdown = 3);
    _cdTimer?.cancel();
    _cdTimer = Timer.periodic(const Duration(seconds: 1), (t) {
      if (!mounted) { t.cancel(); return; }
      if (_countdown <= 1) {
        t.cancel();
        Navigator.pushReplacement(context, MaterialPageRoute(
            builder: (_) => DuelBattleScreen(duelId: widget.duelId)));
      } else {
        setState(() => _countdown--);
      }
    });
  }

  void _showStatusAndPop(String status) {
    final msg = switch (status) {
      'expired'   => 'Waktu habis.',
      'declined'  => 'Tantangan ditolak.',
      'cancelled' => 'Tantangan dibatalkan.',
      _           => 'Duel berakhir.',
    };
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(msg), behavior: SnackBarBehavior.floating));
    Navigator.pop(context);
  }

  Future<void> _markReady() async {
    if (_markingReady || _myReady) return;
    setState(() => _markingReady = true);

    final res = await _service.markReady(widget.duelId);
    print('READY RESPONSE WAITING: $res');

    if (!mounted) return;

    final data = res?['data'] as Map<String, dynamic>?;
    final oppNowReady = data?['opponent_ready_status'] == true;
    setState(() {
      _markingReady = false;
      _myReady      = true;
      _oppReady     = oppNowReady;
    });

    if (oppNowReady) {
      // Lawan sudah ready → langsung countdown
      _beginCountdown();
    } else {
      // Lawan belum → poll sampai lawan ready
      _startPolling();
    }

  }

  Future<void> _accept() async {
    setState(() => _isResponding = true);
    final res = await _service.respond(widget.duelId, 'accept');
    if (!mounted) return;
    setState(() => _isResponding = false);
    if (res?['data'] != null) {
      // Refresh status — tidak auto-countdown
      await _loadOnce();
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

  @override
  Widget build(BuildContext context) {
    // Countdown screen — terpisah dari widget tree utama
    if (_countdown > 0) return _buildCountdown();

    return PopScope(
      canPop: false,
      child: Scaffold(
        body: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
                colors: [_dark, _darkMid, Color(0xFF1F5C55)],
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter)),
          child: _isLoading
              ? const Center(child: CircularProgressIndicator(
                  color: _teal, strokeWidth: 2))
              : _buildBody()),
      ),
    );
  }

  Widget _buildCountdown() {
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
              colors: [_dark, Color(0xFF0A1F1D)],
              begin: Alignment.topCenter,
              end: Alignment.bottomCenter)),
        child: Center(child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Text('BERSIAP!', style: TextStyle(
                color: Colors.white54, fontSize: 16,
                fontWeight: FontWeight.w900, letterSpacing: 3)),
            const SizedBox(height: 20),
            AnimatedBuilder(
              animation: _pulseAnim ?? const AlwaysStoppedAnimation(1.0),
              builder: (_, __) => Transform.scale(
                scale: _pulseAnim?.value ?? 1.0,
                child: Text('$_countdown', style: const TextStyle(
                    color: Colors.white, fontSize: 100,
                    fontWeight: FontWeight.w900, height: 1)))),
            const SizedBox(height: 20),
            const Text('⚔️ Duel Dimulai!', style: TextStyle(
                color: _teal, fontSize: 18, fontWeight: FontWeight.w800)),
          ],
        )),
      ),
    );
  }

  Widget _buildBody() {
    final status       = _duel?['status']?.toString() ?? '';
    final isChallenger = widget.role == 'challenger';
    final quiz         = _duel?['quiz_title']?.toString() ?? 'Quiz';
    final isPending    = status == 'pending';
    final isActive     = status == 'active';

    final Map<String, dynamic>? otherData = isChallenger
        ? (_duel?['opponent'] as Map<String, dynamic>?)
        : (_duel?['challenger'] as Map<String, dynamic>?);
    final otherName    = otherData?['name']?.toString() ?? 'Lawan';
    final otherNim     = otherData?['nim']?.toString() ?? '';
    final otherInitial = otherName.isNotEmpty ? otherName[0].toUpperCase() : '?';
    final bottom       = MediaQuery.of(context).padding.bottom;

    return SafeArea(
      child: Padding(
        padding: EdgeInsets.fromLTRB(24, 16, 24, bottom + 16),
        child: Column(children: [
          // Header
          Row(children: [
            if (isPending && isChallenger)
              GestureDetector(
                onTap: _isCancelling ? null : _cancel,
                child: Container(
                  padding: const EdgeInsets.symmetric(
                      horizontal: 12, vertical: 7),
                  decoration: BoxDecoration(
                    color: _red.withOpacity(0.15),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: _red.withOpacity(0.3))),
                  child: Row(mainAxisSize: MainAxisSize.min, children: [
                    const Icon(Icons.close_rounded, color: _red, size: 16),
                    const SizedBox(width: 6),
                    Text(_isCancelling ? 'Membatalkan...' : 'Batalkan',
                        style: const TextStyle(color: _red,
                            fontWeight: FontWeight.w700)),
                  ]))),
            const Spacer(),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                color: Colors.white.withOpacity(0.08),
                borderRadius: BorderRadius.circular(99),
                border: Border.all(color: _teal.withOpacity(0.3))),
              child: Text(quiz, style: const TextStyle(
                  color: _teal, fontSize: 11, fontWeight: FontWeight.w700))),
          ]),

          const Spacer(),

          // Avatar lawan
          AnimatedBuilder(
            animation: _pulseAnim ?? const AlwaysStoppedAnimation(1.0),
            builder: (_, __) => Transform.scale(
              scale: isPending ? (_pulseAnim?.value ?? 1.0) : 1.0,
              child: Container(
                width: 90, height: 90,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: _teal.withOpacity(0.15),
                  border: Border.all(color: _teal.withOpacity(0.5), width: 3),
                  boxShadow: [BoxShadow(
                      color: _teal.withOpacity(0.3),
                      blurRadius: 20, spreadRadius: 2)]),
                child: Center(child: Text(otherInitial, style: const TextStyle(
                    fontSize: 38, fontWeight: FontWeight.w900,
                    color: _teal)))))),
          const SizedBox(height: 16),

          Text(otherName, style: const TextStyle(
              color: Colors.white, fontSize: 22, fontWeight: FontWeight.w900)),
          if (otherNim.isNotEmpty)
            Text(otherNim, style: TextStyle(
                color: Colors.white.withOpacity(0.4), fontSize: 13)),
          const SizedBox(height: 8),

          Text(
            isPending
              ? (isChallenger
                  ? 'Menunggu $otherName menerima...'
                  : '$otherName menantangmu!')
              : isActive
                  ? (_myReady
                      ? '✓ Kamu siap! Menunggu lawan...'
                      : 'Tekan tombol siap untuk mulai!')
                  : 'Memproses...',
            style: TextStyle(
                color: Colors.white.withOpacity(0.6),
                fontSize: 14, height: 1.4),
            textAlign: TextAlign.center),

          if (isPending)
            Padding(
              padding: const EdgeInsets.only(top: 8),
              child: Text(
                isChallenger
                    ? 'Tantangan kedaluwarsa dalam 5 menit'
                    : 'Terima tantangan untuk mulai bertanding',
                style: TextStyle(
                    color: Colors.white.withOpacity(0.35), fontSize: 12),
                textAlign: TextAlign.center)),

          const Spacer(),

          // Tombol pending opponent
          if (isPending && !isChallenger)
            Row(children: [
              Expanded(child: OutlinedButton(
                onPressed: _isResponding ? null : _decline,
                style: OutlinedButton.styleFrom(
                  foregroundColor: _red,
                  side: BorderSide(color: _red.withOpacity(0.6)),
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14))),
                child: const Text('Tolak',
                    style: TextStyle(fontWeight: FontWeight.bold)))),
              const SizedBox(width: 12),
              Expanded(child: ElevatedButton(
                onPressed: _isResponding ? null : _accept,
                style: ElevatedButton.styleFrom(
                  backgroundColor: _primary,
                  foregroundColor: Colors.white,
                  elevation: 0,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14))),
                child: _isResponding
                    ? const SizedBox(width: 18, height: 18,
                        child: CircularProgressIndicator(
                            color: Colors.white, strokeWidth: 2))
                    : const Text('Terima ⚔️',
                        style: TextStyle(fontWeight: FontWeight.bold)))),
            ]),

          // Ready system saat active
          if (isActive) ...[
            // Status lawan
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(
                  horizontal: 16, vertical: 10),
              margin: const EdgeInsets.only(bottom: 10),
              decoration: BoxDecoration(
                color: Colors.white.withOpacity(0.06),
                borderRadius: BorderRadius.circular(12)),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    _oppReady
                        ? Icons.check_circle_rounded
                        : Icons.hourglass_top_rounded,
                    color: _oppReady ? _green : _amber, size: 16),
                  const SizedBox(width: 8),
                  Text(
                    _oppReady ? 'Lawan sudah siap!' : 'Menunggu lawan siap...',
                    style: TextStyle(
                        color: _oppReady ? _green : _amber,
                        fontSize: 13, fontWeight: FontWeight.w700)),
                ],
              )),

            // Tombol Ready
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: (_myReady || _markingReady) ? null : _markReady,
                style: ElevatedButton.styleFrom(
                  backgroundColor: _myReady ? _slate : _primary,
                  foregroundColor: Colors.white,
                  elevation: _myReady ? 0 : 4,
                  shadowColor: _primary.withOpacity(0.4),
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14))),
                child: _markingReady
                    ? const SizedBox(width: 20, height: 20,
                        child: CircularProgressIndicator(
                            color: Colors.white, strokeWidth: 2))
                    : Text(
                        _myReady ? '✓ Sudah Siap!' : '⚔️  Siap Bertanding!',
                        style: const TextStyle(
                            fontSize: 16, fontWeight: FontWeight.w900)))),
          ],
        ]),
      ),
    );
  }
}