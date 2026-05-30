// lib/screens/duel_screen.dart
import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/duel_service.dart';
import 'duel_battle_screen.dart';
import 'duel_challenge_screen.dart';
import 'duel_waiting_screen.dart';

class DuelScreen extends StatefulWidget {
  const DuelScreen({super.key});
  @override
  State<DuelScreen> createState() => _DuelScreenState();
}

class _DuelScreenState extends State<DuelScreen>
    with TickerProviderStateMixin {
  static const Color _primary  = Color(0xFF2A9D8F);
  static const Color _dark     = Color(0xFF0D2B28);
  static const Color _darkMid  = Color(0xFF1A4040);
  static const Color _tealGlow = Color(0xFF3ECFBE);
  static const Color _ink      = Color(0xFF0F172A);
  static const Color _inkLight = Color(0xFF94A3B8);
  static const Color _red      = Color(0xFFEF4444);
  static const Color _amber    = Color(0xFFF59E0B);
  static const Color _green    = Color(0xFF22C55E);
  static const Color _slate    = Color(0xFF64748B);

  final DuelService _service = DuelService();

  Map<String, dynamic>? _myProfile;
  List<dynamic> _pendingIncoming = [];
  List<dynamic> _pendingOutgoing = [];
  List<dynamic> _activeDuels    = [];
  List<dynamic> _historyDuels   = [];
  bool _isLoading  = true;
  bool _isGuest    = false;
  bool _myReady    = false;
  bool _oppReady   = false;
  int  _cdCount    = 0;      // countdown 3-2-1
  Timer? _cdTimer;

  AnimationController? _pulseCtrl;
  Animation<double>?   _pulseAnim;
  AnimationController? _glowCtrl;
  Animation<double>?   _glowAnim;
  Timer? _pollTimer;

  @override
  void initState() {
    super.initState();
    _pulseCtrl = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 900));
    _pulseCtrl!.repeat(reverse: true);
    _pulseAnim = Tween<double>(begin: 0.92, end: 1.08).animate(
        CurvedAnimation(parent: _pulseCtrl!, curve: Curves.easeInOut));

    _glowCtrl = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 1400));
    _glowCtrl!.repeat(reverse: true);
    _glowAnim = Tween<double>(begin: 0.3, end: 0.8).animate(
        CurvedAnimation(parent: _glowCtrl!, curve: Curves.easeInOut));

    _load();
    // Auto refresh tiap 5 detik untuk update status duel
    _pollTimer = Timer.periodic(
        const Duration(seconds: 5), (_) => _load(silent: true));
  }

  @override
  void dispose() {
    _pollTimer?.cancel();
    _cdTimer?.cancel();
    _pulseCtrl?.dispose();
    _glowCtrl?.dispose();
    super.dispose();
  }

  Future<void> _load({bool silent = false}) async {
    if (!silent) setState(() => _isLoading = true);

    final prefs   = await SharedPreferences.getInstance();
    final token   = prefs.getString('token');

    // Cek guest
    if (token == null || token.isEmpty) {
      if (mounted) setState(() { _isGuest = true; _isLoading = false; });
      return;
    }

    final userStr = prefs.getString('user');
    Map<String, dynamic> localUser = {};
    if (userStr != null) {
      try {
        final decoded = jsonDecode(userStr);
        if (decoded is Map) {
          localUser = Map<String, dynamic>.from(
              decoded.containsKey('user')
                  ? decoded['user'] as Map
                  : decoded);
        }
      } catch (_) {}
    }

    final results = await Future.wait([
      _service.getMyDuels(),
      _service.getHistory(),
      _service.getMyDuelCode(),
    ]);
    if (!mounted) return;

    final all     = results[0] as List;
    final history = results[1] as List;
    final profile = results[2] as Map<String, dynamic>?;

    final name = localUser['name']?.toString().isNotEmpty == true
        ? localUser['name'].toString()
        : profile?['name']?.toString() ?? 'Player';
    final myDuelCode = profile?['duel_code']?.toString() ?? '';

    final activeDuelData = all.where((d) => d['status'] == 'active').toList();
    final myR  = activeDuelData.isNotEmpty
        ? (activeDuelData.first['my_ready'] == true) : false;
    final oppR = activeDuelData.isNotEmpty
        ? (activeDuelData.first['opponent_ready_status'] == true) : false;

    setState(() {
      _myProfile = {...?profile, 'name': name, 'duel_code': myDuelCode};
      _pendingIncoming = all.where((d) =>
          d['status'] == 'pending' && d['i_am'] == 'opponent').toList();
      _pendingOutgoing = all.where((d) =>
          d['status'] == 'pending' && d['i_am'] == 'challenger').toList();
      _activeDuels = activeDuelData;
      _historyDuels = history;
      _isLoading = false;
      _myReady  = myR;
      _oppReady = oppR;
    });

    // Kalau saya sudah ready dan lawan baru ready → countdown
    if (myR && oppR && _cdCount == 0 && activeDuelData.isNotEmpty && mounted) {
      _startCountdownInArena(activeDuelData.first['id']?.toString() ?? '');
    }
  }

  // ── Accept ────────────────────────────────────────────────
  Future<void> _accept(Map<String, dynamic> duel) async {
    HapticFeedback.heavyImpact();
    final duelId = duel['id']?.toString() ?? '';
    final res    = await _service.respond(duelId, 'accept');
    if (!mounted) return;
    // Setelah acc, refresh saja — ready system yang handle selanjutnya
    if (res != null) _load(silent: true);
  }

  Future<void> _decline(String duelId) async {
    HapticFeedback.mediumImpact();
    await _service.respond(duelId, 'decline');
    if (mounted) _load();
  }

  // ── Countdown langsung di arena ───────────────────────────
  void _startCountdownInArena(String duelId) {
    if (_cdCount > 0) return;
    setState(() => _cdCount = 3);
    _cdTimer?.cancel();
    _cdTimer = Timer.periodic(const Duration(seconds: 1), (t) {
      if (!mounted) { t.cancel(); return; }
      if (_cdCount <= 1) {
        t.cancel();
        setState(() => _cdCount = 0);
        _pollTimer?.cancel();
        Navigator.push(context, MaterialPageRoute(
            builder: (_) => DuelBattleScreen(duelId: duelId)))
            .then((_) {
          if (mounted) {
            _myReady = false;
            _oppReady = false;
            _pollTimer = Timer.periodic(
                const Duration(seconds: 5), (_) => _load(silent: true));
            _load();
          }
        });
      } else {
        setState(() => _cdCount--);
      }
    });
  }

  // ── Ready ─────────────────────────────────────────────────
  Future<void> _markReady(String duelId) async {
    HapticFeedback.heavyImpact();
    setState(() => _myReady = true);

    final res = await _service.markReady(duelId);
    print('READY RESPONSE: $res');

    if (!mounted) return;

    if (res != null) {
      final data = res['data'] as Map<String, dynamic>?;
      final oppNowReady = data?['opponent_ready_status'] == true;

      setState(() => _oppReady = oppNowReady);

      if (oppNowReady && _cdCount == 0) {
        _startCountdownInArena(duelId);
      }
    }
  }

  // ── Guest dialog ──────────────────────────────────────────
  void _showGuestDialog() {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(children: [
          Icon(Icons.lock_rounded, color: Color(0xFF2A9D8F)),
          SizedBox(width: 8),
          Text('Login Dulu!',
              style: TextStyle(fontWeight: FontWeight.bold)),
        ]),
        content: const Text(
            'Fitur duel hanya tersedia untuk pengguna yang sudah login. '
            'Daftar gratis untuk bisa tantang teman!'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Nanti',
                style: TextStyle(color: Colors.grey))),
        ],
      ),
    );
  }

  // ── History sheet ─────────────────────────────────────────
  void _showHistorySheet() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      builder: (_) => DraggableScrollableSheet(
        initialChildSize: 0.6,
        minChildSize: 0.4,
        maxChildSize: 0.92,
        builder: (_, ctrl) => Container(
          decoration: const BoxDecoration(
            color: Color(0xFFF5F7FA),
            borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
          child: Column(children: [
            Container(
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius:
                    BorderRadius.vertical(top: Radius.circular(24))),
              padding: const EdgeInsets.fromLTRB(20, 12, 20, 16),
              child: Column(children: [
                Center(child: Container(
                    width: 36, height: 4,
                    decoration: BoxDecoration(
                        color: Colors.grey[300],
                        borderRadius: BorderRadius.circular(10)))),
                const SizedBox(height: 14),
                Row(children: [
                  const Icon(Icons.history_rounded, color: _primary, size: 20),
                  const SizedBox(width: 10),
                  const Text('Riwayat Duel', style: TextStyle(
                      fontSize: 16, fontWeight: FontWeight.w800, color: _ink)),
                  const Spacer(),
                  Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: _primary.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(99)),
                    child: Text('${_historyDuels.length} duel',
                        style: const TextStyle(
                            fontSize: 11, fontWeight: FontWeight.w700,
                            color: _primary))),
                ]),
              ]),
            ),
            Expanded(
              child: _historyDuels.isEmpty
                  ? Center(child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.history_rounded,
                            size: 48, color: Colors.grey[300]),
                        const SizedBox(height: 12),
                        Text('Belum ada riwayat duel',
                            style: TextStyle(
                                color: Colors.grey[400], fontSize: 14)),
                      ]))
                  : ListView.builder(
                      controller: ctrl,
                      padding: const EdgeInsets.fromLTRB(16, 8, 16, 32),
                      itemCount: _historyDuels.length,
                      itemBuilder: (_, i) =>
                          _buildHistoryCard(_historyDuels[i])),
            ),
          ]),
        ),
      ),
    );
  }

  // ── BUILD ─────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    final top    = MediaQuery.of(context).padding.top;
    final bottom = MediaQuery.of(context).padding.bottom;

    final incomingDuel = _pendingIncoming.isNotEmpty
        ? _pendingIncoming.first : null;
    final activeDuel = _activeDuels.isNotEmpty
        ? _activeDuels.first : null;
    final outgoingDuel = _pendingOutgoing.isNotEmpty
        ? _pendingOutgoing.first : null;
    final currentDuel = incomingDuel ?? activeDuel ?? outgoingDuel;

    final myName   = _myProfile?['name']?.toString() ?? 'Player';
    final myInitial = myName.isNotEmpty ? myName[0].toUpperCase() : '?';

    final iAm   = currentDuel?['i_am']?.toString();
    final p2Raw = currentDuel != null
        ? (iAm == 'challenger'
            ? currentDuel['opponent'] : currentDuel['challenger'])
        : null;
    final p2Name = p2Raw?['name']?.toString() ?? '';
    final p2Nim  = p2Raw?['nim']?.toString() ?? '';
    final p2Init = p2Name.isNotEmpty ? p2Name[0].toUpperCase() : '?';

    final isActive   = activeDuel != null;
    final isIncoming = incomingDuel != null;
    final isOutgoing = outgoingDuel != null && !isIncoming && !isActive;
    final isMyTurn   = activeDuel?['is_my_turn'] == true;
    final quizTitle  = currentDuel?['quiz_title']?.toString() ?? '';
    final duelId     = (activeDuel ?? currentDuel)?['id']?.toString() ?? '';

    if (_isGuest) return _buildGuestView();

    // Countdown overlay
    if (_cdCount > 0) {
      return Scaffold(
        body: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
                colors: [_dark, _darkMid],
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
                  child: Text('$_cdCount', style: const TextStyle(
                      color: Colors.white, fontSize: 100,
                      fontWeight: FontWeight.w900, height: 1)))),
              const SizedBox(height: 20),
              const Text('⚔️ Duel Dimulai!', style: TextStyle(
                  color: _tealGlow, fontSize: 18,
                  fontWeight: FontWeight.w800)),
            ],
          )),
        ),
      );
    }

    return Scaffold(
      body: _isLoading
          ? Container(
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                    colors: [_dark, _darkMid, _primary],
                    begin: Alignment.topCenter,
                    end: Alignment.bottomCenter)),
              child: const Center(child: CircularProgressIndicator(
                  color: _tealGlow, strokeWidth: 2)))
          : RefreshIndicator(
              color: _primary,
              onRefresh: _load,
              child: Container(
                decoration: const BoxDecoration(
                  gradient: LinearGradient(
                      colors: [_dark, _darkMid, Color(0xFF1F5C55)],
                      begin: Alignment.topCenter,
                      end: Alignment.bottomCenter)),
                child: SafeArea(
                  bottom: false,
                  child: Padding(
                    padding: EdgeInsets.fromLTRB(24, 16, 24, bottom + 16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [

                        // ── Header ──────────────────────
                        Row(children: [
                          const Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text('QUIZ DUEL', style: TextStyle(
                                  color: Colors.white54, fontSize: 10,
                                  fontWeight: FontWeight.w900,
                                  letterSpacing: 2)),
                              SizedBox(height: 2),
                              Text('Arena', style: TextStyle(
                                  color: Colors.white, fontSize: 26,
                                  fontWeight: FontWeight.w900,
                                  letterSpacing: -0.5)),
                            ],
                          ),
                          const Spacer(),
                          GestureDetector(
                            onTap: _isGuest ? _showGuestDialog : _showHistorySheet,
                            child: Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 14, vertical: 8),
                              decoration: BoxDecoration(
                                color: Colors.white.withOpacity(0.1),
                                borderRadius: BorderRadius.circular(20),
                                border: Border.all(
                                    color: Colors.white.withOpacity(0.2))),
                              child: const Row(children: [
                                Icon(Icons.history_rounded,
                                    color: Colors.white70, size: 15),
                                SizedBox(width: 6),
                                Text('Riwayat', style: TextStyle(
                                    color: Colors.white70, fontSize: 12,
                                    fontWeight: FontWeight.w700)),
                              ]))),
                        ]),

                        // Quiz badge
                        if (quizTitle.isNotEmpty) ...[
                          const SizedBox(height: 10),
                          Center(child: Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 16, vertical: 6),
                            decoration: BoxDecoration(
                              color: Colors.white.withOpacity(0.08),
                              borderRadius: BorderRadius.circular(99),
                              border: Border.all(
                                  color: _tealGlow.withOpacity(0.3))),
                            child: Text(quizTitle, style: const TextStyle(
                                color: _tealGlow, fontSize: 12,
                                fontWeight: FontWeight.w700)))),
                        ],

                        const SizedBox(height: 16),

                        // ── P1 ──────────────────────────
                        Expanded(
                          flex: 5,
                          child: _buildPlayerCard(
                            initial: myInitial,
                            name: myName,
                            label: 'KAMU',
                            tag: 'P1',
                            color: _tealGlow,
                            duelCode: currentDuel == null
                                ? (_myProfile?['duel_code']?.toString() ?? '')
                                : null,
                          ),
                        ),

                        // ── VS ──────────────────────────
                        SizedBox(
                          height: 72,
                          child: AnimatedBuilder(
                            animation: _glowAnim ??
                                const AlwaysStoppedAnimation(0.5),
                            builder: (_, __) {
                              final gv = _glowAnim?.value ?? 0.5;
                              return Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Expanded(child: Container(
                                    height: 1.5,
                                    decoration: BoxDecoration(
                                      gradient: LinearGradient(colors: [
                                        Colors.transparent,
                                        _tealGlow.withOpacity(gv),
                                      ])))),
                                  const SizedBox(width: 12),
                                  AnimatedBuilder(
                                    animation: _pulseAnim ??
                                        const AlwaysStoppedAnimation(1.0),
                                    builder: (_, __) => Transform.scale(
                                      scale: _pulseAnim?.value ?? 1.0,
                                      child: Container(
                                        width: 56, height: 56,
                                        decoration: BoxDecoration(
                                          shape: BoxShape.circle,
                                          color: _primary,
                                          boxShadow: [BoxShadow(
                                            color: _tealGlow
                                                .withOpacity(gv * 0.7),
                                            blurRadius: 20, spreadRadius: 3)]),
                                        child: const Center(child: Text('VS',
                                            style: TextStyle(
                                                color: Colors.white,
                                                fontSize: 16,
                                                fontWeight: FontWeight.w900)))))),
                                  const SizedBox(width: 12),
                                  Expanded(child: Container(
                                    height: 1.5,
                                    decoration: BoxDecoration(
                                      gradient: LinearGradient(colors: [
                                        _red.withOpacity(gv),
                                        Colors.transparent,
                                      ])))),
                                ],
                              );
                            },
                          ),
                        ),

                        // ── P2 ──────────────────────────
                        Expanded(
                          flex: isIncoming ? 8 : isActive ? 7 : 5,
                          child: currentDuel != null
                              ? _buildP2Card(
                                  initial: p2Init,
                                  name: p2Name,
                                  nim: p2Nim,
                                  isIncoming: isIncoming,
                                  isActive: isActive,
                                  isOutgoing: isOutgoing,
                                  duel: currentDuel)
                              : _buildP2Empty(),
                        ),

                        // Action bar hanya muncul saat keduanya sudah ready
                        if (isActive && _myReady && _oppReady) ...[
                          const SizedBox(height: 14),
                          _buildActionBar(
                              isMyTurn: isMyTurn, duelId: duelId),
                        ],

                        const SizedBox(height: 8),
                      ],
                    ),
                  ),
                ),
              ),
            ),
    );
  }

  // ── Guest view ───────────────────────────────────────────
  Widget _buildGuestView() {
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
              colors: [_dark, _darkMid, Color(0xFF1F5C55)],
              begin: Alignment.topCenter,
              end: Alignment.bottomCenter)),
        child: SafeArea(
          child: Column(children: [
            // Header
            Padding(
              padding: const EdgeInsets.fromLTRB(24, 16, 24, 0),
              child: Row(children: [
                const Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('QUIZ DUEL', style: TextStyle(
                        color: Colors.white54, fontSize: 10,
                        fontWeight: FontWeight.w900, letterSpacing: 2)),
                    SizedBox(height: 2),
                    Text('Arena', style: TextStyle(
                        color: Colors.white, fontSize: 26,
                        fontWeight: FontWeight.w900, letterSpacing: -0.5)),
                  ],
                ),
              ]),
            ),
            const Spacer(),
            // Icon
            Container(
              width: 90, height: 90,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.white.withOpacity(0.08),
                border: Border.all(color: _tealGlow.withOpacity(0.3), width: 2)),
              child: const Icon(Icons.sports_martial_arts_rounded,
                  color: _tealGlow, size: 44)),
            const SizedBox(height: 24),
            const Text('Masuk untuk Bertarung!', style: TextStyle(
                color: Colors.white, fontSize: 22,
                fontWeight: FontWeight.w900)),
            const SizedBox(height: 10),
            Text('Login untuk tantang teman\ndan bersaing di arena duel',
                textAlign: TextAlign.center,
                style: TextStyle(
                    color: Colors.white.withOpacity(0.5), fontSize: 14,
                    height: 1.5)),
            const SizedBox(height: 36),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 40),
              child: ElevatedButton(
                onPressed: _showGuestDialog,
                style: ElevatedButton.styleFrom(
                  backgroundColor: _primary, foregroundColor: Colors.white,
                  elevation: 0,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  minimumSize: const Size(double.infinity, 48),
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14))),
                child: const Text('Login Sekarang',
                    style: TextStyle(
                        fontSize: 15, fontWeight: FontWeight.w800)))),
            const Spacer(),
          ]),
        ),
      ),
    );
  }

  // ── Player card ───────────────────────────────────────────
  Widget _buildPlayerCard({
    required String initial,
    required String name,
    required String label,
    required String tag,
    required Color color,
    String? nim,
    String? duelCode,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.08),
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: color.withOpacity(0.4), width: 1.5),
        boxShadow: [BoxShadow(
            color: color.withOpacity(0.2),
            blurRadius: 20, spreadRadius: 2)]),
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          // Tag row
          Row(children: [
            Container(
              padding: const EdgeInsets.symmetric(
                  horizontal: 12, vertical: 5),
              decoration: BoxDecoration(
                color: color.withOpacity(0.18),
                borderRadius: BorderRadius.circular(8)),
              child: Text(tag, style: TextStyle(
                  color: color, fontSize: 12,
                  fontWeight: FontWeight.w900, letterSpacing: 1))),
            const Spacer(),
            Text(label, style: TextStyle(
                color: color.withOpacity(0.5), fontSize: 9,
                fontWeight: FontWeight.w900, letterSpacing: 2)),
          ]),

          // Avatar
          Center(child: Container(
            width: 80, height: 80,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: color.withOpacity(0.15),
              border: Border.all(color: color.withOpacity(0.6), width: 3),
              boxShadow: [BoxShadow(
                  color: color.withOpacity(0.35),
                  blurRadius: 20, spreadRadius: 2)]),
            child: Center(child: Text(initial, style: TextStyle(
                fontSize: 34, fontWeight: FontWeight.w900, color: color))))),

          // Nama + kode duel
          Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(name, style: const TextStyle(
                color: Colors.white, fontSize: 22,
                fontWeight: FontWeight.w900, letterSpacing: -0.5)),
            if (nim != null && nim.isNotEmpty)
              Text(nim, style: TextStyle(
                  color: Colors.white.withOpacity(0.4), fontSize: 13)),
            if (duelCode != null && duelCode.isNotEmpty) ...[
              const SizedBox(height: 8),
              Row(children: [
                Container(
                  padding: const EdgeInsets.symmetric(
                      horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: color.withOpacity(0.15),
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: color.withOpacity(0.3))),
                  child: Row(mainAxisSize: MainAxisSize.min, children: [
                    Icon(Icons.tag_rounded, color: color, size: 12),
                    const SizedBox(width: 4),
                    Text(duelCode, style: TextStyle(
                        color: color, fontSize: 13,
                        fontWeight: FontWeight.w900, letterSpacing: 2)),
                  ])),
                const SizedBox(width: 8),
                GestureDetector(
                  onTap: () {
                    HapticFeedback.lightImpact();
                    // Copy ke clipboard
                    final data = duelCode;
                    // ignore: deprecated_member_use
                    Clipboard.setData(ClipboardData(text: data));
                  },
                  child: Icon(Icons.copy_rounded,
                      color: color.withOpacity(0.5), size: 16)),
              ]),
            ],
          ]),
        ],
      ),
    );
  }

  // ── P2 dengan data ────────────────────────────────────────
  Widget _buildP2Card({
    required String initial,
    required String name,
    required String nim,
    required bool isIncoming,
    required bool isActive,
    required bool isOutgoing,
    required Map<String, dynamic> duel,
  }) {
    final duelId = duel['id']?.toString() ?? '';
    final color  = isIncoming ? _amber : isActive ? _tealGlow : _slate;
    final label  = isIncoming ? 'MENANTANGMU'
        : isActive ? 'LAWAN'
        : 'MENUNGGU RESPONS';

    return Column(children: [
      Expanded(
        child: Container(
          decoration: BoxDecoration(
            color: Colors.white.withOpacity(0.08),
            borderRadius: BorderRadius.circular(24),
            border: Border.all(color: color.withOpacity(0.4), width: 1.5),
            boxShadow: [BoxShadow(
                color: color.withOpacity(0.2),
                blurRadius: 20, spreadRadius: 2)]),
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              // Avatar
              Container(
                width: 52, height: 52,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: color.withOpacity(0.15),
                  border: Border.all(color: color.withOpacity(0.6), width: 2.5),
                  boxShadow: [BoxShadow(
                      color: color.withOpacity(0.3),
                      blurRadius: 12, spreadRadius: 1)]),
                child: Center(child: Text(
                    name.isNotEmpty ? initial : '?',
                    style: TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.w900, color: color)))),
              const SizedBox(width: 14),
              // Info
              Expanded(child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(label, style: TextStyle(
                      color: color.withOpacity(0.5), fontSize: 9,
                      fontWeight: FontWeight.w900, letterSpacing: 2)),
                  Text(name.isNotEmpty ? name : '???',
                      style: const TextStyle(color: Colors.white,
                          fontSize: 17, fontWeight: FontWeight.w900),
                      maxLines: 1, overflow: TextOverflow.ellipsis),
                  if (nim.isNotEmpty)
                    Text(nim, style: TextStyle(
                        color: Colors.white.withOpacity(0.4), fontSize: 12)),
                ],
              )),
              // P2 badge
              Container(
                padding: const EdgeInsets.symmetric(
                    horizontal: 10, vertical: 5),
                decoration: BoxDecoration(
                  color: color.withOpacity(0.18),
                  borderRadius: BorderRadius.circular(8)),
                child: Text('P2', style: TextStyle(
                    color: color, fontSize: 12,
                    fontWeight: FontWeight.w900))),
            ],
          ),
        ),
      ),

      // Tombol Accept/Decline
      if (isIncoming) ...[
        const SizedBox(height: 12),
        Row(children: [
          Expanded(child: OutlinedButton(
            onPressed: () => _decline(duelId),
            style: OutlinedButton.styleFrom(
              foregroundColor: _red,
              side: BorderSide(color: _red.withOpacity(0.6)),
              padding: const EdgeInsets.symmetric(vertical: 13),
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(14))),
            child: const Text('Tolak',
                style: TextStyle(fontWeight: FontWeight.bold)))),
          const SizedBox(width: 12),
          Expanded(child: ElevatedButton(
            onPressed: () => _accept(duel),
            style: ElevatedButton.styleFrom(
              backgroundColor: _primary, foregroundColor: Colors.white,
              elevation: 0,
              padding: const EdgeInsets.symmetric(vertical: 13),
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(14))),
            child: const Text('Terima ⚔️',
                style: TextStyle(fontWeight: FontWeight.bold)))),
        ]),
      ],

      if (isOutgoing) ...[
        const SizedBox(height: 12),
        SizedBox(
          width: double.infinity,
          child: OutlinedButton(
            onPressed: () async {
              await _service.cancelDuel(duelId);
              _load();
            },
            style: OutlinedButton.styleFrom(
              foregroundColor: _red,
              side: BorderSide(color: _red.withOpacity(0.5)),
              padding: const EdgeInsets.symmetric(vertical: 12),
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(14))),
            child: const Text('Batalkan Tantangan',
                style: TextStyle(fontWeight: FontWeight.bold)))),
      ],

      // Ready button saat duel aktif
      if (isActive) ...[
        const SizedBox(height: 12),
        // Status lawan
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
          decoration: BoxDecoration(
            color: Colors.white.withOpacity(0.06),
            borderRadius: BorderRadius.circular(10)),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                _oppReady
                    ? Icons.check_circle_rounded
                    : Icons.hourglass_top_rounded,
                color: _oppReady ? _green : _amber, size: 15),
              const SizedBox(width: 6),
              Text(
                _oppReady ? 'Lawan sudah siap!' : 'Menunggu lawan siap...',
                style: TextStyle(
                    color: _oppReady ? _green : _amber,
                    fontSize: 12, fontWeight: FontWeight.w700)),
            ],
          )),
        const SizedBox(height: 8),
        SizedBox(
          width: double.infinity,
          child: ElevatedButton(
            onPressed: _myReady ? null : () => _markReady(duelId),
            style: ElevatedButton.styleFrom(
              backgroundColor: _myReady ? _slate : _primary,
              foregroundColor: Colors.white,
              elevation: 0,
              padding: const EdgeInsets.symmetric(vertical: 14),
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(14))),
            child: Text(
              _myReady ? '✓ Sudah Siap!' : '⚔️ Siap Bertanding!',
              style: const TextStyle(
                  fontSize: 15, fontWeight: FontWeight.w900)))),
      ],
    ]);
  }

  // ── P2 kosong ─────────────────────────────────────────────
  Widget _buildP2Empty() {
    return GestureDetector(
      onTap: () async {
        if (_isGuest) { _showGuestDialog(); return; }
        HapticFeedback.lightImpact();
        final result = await Navigator.push(context,
            MaterialPageRoute(
                builder: (_) => const DuelChallengeScreen()));
        if (result == true) _load();
      },
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white.withOpacity(0.04),
          borderRadius: BorderRadius.circular(24),
          border: Border.all(
              color: Colors.white.withOpacity(0.15), width: 1.5)),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 72, height: 72,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.white.withOpacity(0.07),
                border: Border.all(
                    color: Colors.white.withOpacity(0.2), width: 2)),
              child: const Icon(Icons.add_rounded,
                  color: Colors.white54, size: 34)),
            const SizedBox(height: 16),
            const Text('Tantang Lawan', style: TextStyle(
                color: Colors.white70, fontSize: 16,
                fontWeight: FontWeight.w700)),
            const SizedBox(height: 6),
            const Text('Tap untuk pilih quiz & lawan',
                style: TextStyle(color: Colors.white30, fontSize: 12)),
          ],
        ),
      ),
    );
  }

  // ── Action bar ────────────────────────────────────────────
  Widget _buildActionBar({required bool isMyTurn, required String duelId}) {
    return GestureDetector(
      onTap: isMyTurn ? () {
        HapticFeedback.heavyImpact();
        Navigator.push(context, MaterialPageRoute(
            builder: (_) => DuelBattleScreen(duelId: duelId)))
            .then((_) => _load());
      } : null,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 14),
        decoration: BoxDecoration(
          color: isMyTurn ? _primary : Colors.white.withOpacity(0.08),
          borderRadius: BorderRadius.circular(16),
          boxShadow: isMyTurn ? [BoxShadow(
              color: _primary.withOpacity(0.4),
              blurRadius: 16, offset: const Offset(0, 4))] : null),
        child: Center(child: Text(
          isMyTurn
              ? '⚔️  Giliranmu — Mulai Sekarang!'
              : '⏳  Menunggu lawan menyelesaikan...',
          style: const TextStyle(color: Colors.white,
              fontSize: 14, fontWeight: FontWeight.w800))),
      ),
    );
  }

  // ── History card ──────────────────────────────────────────
  Widget _buildHistoryCard(Map<String, dynamic> duel) {
    final status   = duel['status']?.toString();
    final iAm      = duel['i_am']?.toString();
    final opp      = iAm == 'challenger'
        ? duel['opponent'] : duel['challenger'];
    final oppName  = opp?['name']?.toString() ?? 'Lawan';
    final quiz     = duel['quiz_title']?.toString() ?? 'Quiz';
    final myScore  = duel['my_score'];
    final oppScore = duel['opponent_score'];
    final iWon     = duel['i_won'];
    final Color color = status == 'completed'
        ? (iWon == true ? _green : _red) : _slate;

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0))),
      child: Row(children: [
        Container(width: 4, height: 46,
            margin: const EdgeInsets.only(right: 14),
            decoration: BoxDecoration(color: color,
                borderRadius: BorderRadius.circular(4))),
        Expanded(child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(quiz, style: const TextStyle(
                fontSize: 13, fontWeight: FontWeight.bold, color: _ink),
                maxLines: 1, overflow: TextOverflow.ellipsis),
            const SizedBox(height: 2),
            Text('vs $oppName', style: const TextStyle(
                fontSize: 11, color: _inkLight)),
          ],
        )),
        if (myScore != null && status == 'completed')
          Column(crossAxisAlignment: CrossAxisAlignment.end, children: [
            Row(children: [
              Text('$myScore', style: TextStyle(
                  fontSize: 17, fontWeight: FontWeight.w900, color: color)),
              const Text(' : ', style: TextStyle(
                  fontSize: 14, color: _inkLight)),
              Text('${oppScore ?? '-'}', style: TextStyle(
                  fontSize: 17, fontWeight: FontWeight.w900,
                  color: iWon == true ? _red : _green)),
            ]),
            Text(iWon == true ? '🏆 Menang' : '💀 Kalah',
                style: TextStyle(fontSize: 10,
                    fontWeight: FontWeight.w700, color: color)),
          ])
        else
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
            decoration: BoxDecoration(
              color: color.withOpacity(0.1),
              borderRadius: BorderRadius.circular(8)),
            child: Text(status ?? '-', style: TextStyle(
                fontSize: 10, fontWeight: FontWeight.w700, color: color))),
      ]),
    );
  }
}