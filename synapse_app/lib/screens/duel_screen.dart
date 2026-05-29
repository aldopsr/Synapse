// lib/screens/duel_screen.dart
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
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
    with SingleTickerProviderStateMixin {
  static const Color _primary  = Color(0xFF2A9D8F);
  static const Color _ink      = Color(0xFF0F172A);
  static const Color _inkMid   = Color(0xFF475569);
  static const Color _inkLight = Color(0xFF94A3B8);
  static const Color _border   = Color(0xFFE2E8F0);
  static const Color _bg       = Color(0xFFF5F7FA);
  static const Color _red      = Color(0xFFDC2626);
  static const Color _amber    = Color(0xFFD97706);
  static const Color _green    = Color(0xFF16A34A);
  static const Color _sky      = Color(0xFF0284C7);
  static const Color _slate    = Color(0xFF64748B);

  final DuelService _service = DuelService();
  late final TabController _tabCtrl;

  List<dynamic> _activeDuels  = [];
  List<dynamic> _historyDuels = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(length: 2, vsync: this);
    _load();
  }

  @override
  void dispose() { _tabCtrl.dispose(); super.dispose(); }

  Future<void> _load() async {
    setState(() => _isLoading = true);
    final results = await Future.wait([
      _service.getMyDuels(),
      _service.getHistory(),
    ]);
    if (!mounted) return;
    setState(() {
      _activeDuels  = results[0];
      _historyDuels = results[1];
      _isLoading    = false;
    });
  }

  Color _statusColor(String? s) => switch (s) {
    'pending'   => _amber,
    'active'    => _sky,
    'completed' => _green,
    'expired'   => _slate,
    'declined'  => _red,
    'cancelled' => _slate,
    _           => _slate,
  };

  String _statusLabel(String? s) => switch (s) {
    'pending'   => 'Menunggu',
    'active'    => 'Berlangsung',
    'completed' => 'Selesai',
    'expired'   => 'Kedaluwarsa',
    'declined'  => 'Ditolak',
    'cancelled' => 'Dibatalkan',
    _           => s ?? '-',
  };

  String _statusIcon(String? s) => switch (s) {
    'pending'   => '⏳',
    'active'    => '⚔️',
    'completed' => '🏆',
    'expired'   => '⏱️',
    'declined'  => '❌',
    'cancelled' => '🚫',
    _           => '•',
  };

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _bg,
      body: NestedScrollView(
        headerSliverBuilder: (ctx, _) => [_buildAppBar()],
        body: _isLoading
            ? const Center(child: CircularProgressIndicator(
                color: _primary, strokeWidth: 2))
            : TabBarView(
                controller: _tabCtrl,
                children: [_buildActiveTab(), _buildHistoryTab()],
              ),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () async {
          HapticFeedback.lightImpact();
          final result = await Navigator.push(context,
              MaterialPageRoute(builder: (_) => const DuelChallengeScreen()));
          if (result == true) _load();
        },
        backgroundColor: _primary,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.sports_martial_arts_rounded),
        label: const Text('Tantang',
            style: TextStyle(fontWeight: FontWeight.bold)),
        elevation: 2,
      ),
    );
  }

  Widget _buildAppBar() {
    return SliverAppBar(
      expandedHeight: 120,
      pinned: true,
      floating: false,
      backgroundColor: Colors.white,
      foregroundColor: _ink,
      elevation: 0,
      surfaceTintColor: Colors.transparent,
      bottom: PreferredSize(
        preferredSize: const Size.fromHeight(48),
        child: Column(children: [
          Container(height: 1, color: _border),
          TabBar(
            controller: _tabCtrl,
            labelColor: _primary,
            unselectedLabelColor: _inkLight,
            indicatorColor: _primary,
            indicatorSize: TabBarIndicatorSize.label,
            labelStyle: const TextStyle(
                fontWeight: FontWeight.w700, fontSize: 13),
            tabs: const [Tab(text: 'Aktif'), Tab(text: 'Riwayat')],
          ),
        ]),
      ),
      flexibleSpace: FlexibleSpaceBar(
        expandedTitleScale: 1,
        titlePadding: EdgeInsets.zero,
        background: SafeArea(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(24, 0, 24, 56),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(
                      horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: const Color(0xFFE6F4F2),
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: const Text('QUIZ DUEL',
                      style: TextStyle(
                        color: _primary, fontSize: 10,
                        fontWeight: FontWeight.w700, letterSpacing: 1.5,
                      )),
                ),
                const SizedBox(height: 6),
                const Text('Arena Pertarungan',
                    style: TextStyle(
                      color: _ink, fontSize: 22,
                      fontWeight: FontWeight.w800, letterSpacing: -0.5,
                    )),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildActiveTab() {
    if (_activeDuels.isEmpty) {
      return _buildEmpty('Belum ada duel aktif',
          'Tantang temanmu dengan tombol di bawah!');
    }
    return RefreshIndicator(
      color: _primary,
      onRefresh: _load,
      child: ListView.builder(
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 120),
        itemCount: _activeDuels.length,
        itemBuilder: (ctx, i) =>
            _buildDuelCard(_activeDuels[i], isHistory: false),
      ),
    );
  }

  Widget _buildHistoryTab() {
    if (_historyDuels.isEmpty) {
      return _buildEmpty('Belum ada riwayat', 'Selesaikan duel pertamamu!');
    }
    return RefreshIndicator(
      color: _primary,
      onRefresh: _load,
      child: ListView.builder(
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 120),
        itemCount: _historyDuels.length,
        itemBuilder: (ctx, i) =>
            _buildDuelCard(_historyDuels[i], isHistory: true),
      ),
    );
  }

  Widget _buildDuelCard(Map<String, dynamic> duel, {required bool isHistory}) {
    final status      = duel['status']?.toString();
    final color       = _statusColor(status);
    final label       = _statusLabel(status);
    final icon        = _statusIcon(status);
    final quizTitle   = duel['quiz_title']?.toString() ?? 'Quiz';
    final iAm         = duel['i_am']?.toString();
    final challenger  = duel['challenger'];
    final opponent    = duel['opponent'];
    final iWon        = duel['i_won'];
    final myScore     = duel['my_score'];
    final oppScore    = duel['opponent_score'];
    final isMyTurn    = duel['is_my_turn'] == true;

    final opponentData = iAm == 'challenger' ? opponent : challenger;
    final opponentName = opponentData?['name']?.toString() ?? 'Lawan';
    final opponentNim  = opponentData?['nim']?.toString() ?? '';
    final opponentCode = opponentData?['duel_code']?.toString() ?? '';

    return GestureDetector(
      onTap: () => _onCardTap(duel),
      child: Container(
        margin: const EdgeInsets.only(bottom: 10),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: _border),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.03),
              blurRadius: 8, offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Stack(children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(18, 14, 16, 14),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Status row
                Row(children: [
                  Text('$icon ',
                      style: const TextStyle(fontSize: 11)),
                  Text(label.toUpperCase(),
                      style: TextStyle(
                        color: color, fontSize: 10,
                        fontWeight: FontWeight.w700, letterSpacing: 0.8,
                      )),
                  if (isMyTurn) ...[
                    const Spacer(),
                    Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: _primary.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: const Text('Giliranmu!',
                          style: TextStyle(
                            color: _primary, fontSize: 10,
                            fontWeight: FontWeight.w700,
                          )),
                    ),
                  ],
                ]),
                const SizedBox(height: 6),

                // Quiz title
                Row(children: [
                  Expanded(
                    child: Text(quizTitle,
                        style: const TextStyle(
                          fontSize: 14, fontWeight: FontWeight.w700,
                          color: _ink,
                        )),
                  ),
                  const Icon(Icons.chevron_right_rounded,
                      size: 18, color: _inkLight),
                ]),
                const SizedBox(height: 4),

                // Opponent
                Row(children: [
                  Icon(Icons.person_outline_rounded,
                      size: 13, color: _inkLight),
                  const SizedBox(width: 4),
                  Text(opponentName,
                      style: const TextStyle(fontSize: 12, color: _inkMid)),
                  if (opponentNim.isNotEmpty) ...[
                    const SizedBox(width: 4),
                    Text('· $opponentNim',
                        style: const TextStyle(
                            fontSize: 11, color: _inkLight)),
                  ] else if (opponentCode.isNotEmpty) ...[
                    const SizedBox(width: 4),
                    Text('· $opponentCode',
                        style: const TextStyle(
                            fontSize: 11, color: _inkLight)),
                  ],
                ]),

                // Score (history only)
                if (isHistory && myScore != null) ...[
                  const SizedBox(height: 12),
                  Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 12, vertical: 8),
                    decoration: BoxDecoration(
                      color: _bg,
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: _border),
                    ),
                    child: Row(children: [
                      _buildScoreBlock(
                          'Kamu', myScore.toString(),
                          iWon == true ? _green : _red),
                      const Expanded(
                        child: Center(
                          child: Text('VS',
                              style: TextStyle(
                                fontSize: 11, fontWeight: FontWeight.w800,
                                color: _inkLight,
                              )),
                        ),
                      ),
                      _buildScoreBlock(
                          'Lawan', oppScore?.toString() ?? '-',
                          iWon == true ? _red : _green),
                      if (iWon != null) ...[
                        const SizedBox(width: 12),
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: (iWon == true ? _green : _red)
                                .withOpacity(0.1),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            iWon == true ? '🏆 Menang' : '💀 Kalah',
                            style: TextStyle(
                              fontSize: 11, fontWeight: FontWeight.w700,
                              color: iWon == true ? _green : _red,
                            ),
                          ),
                        ),
                      ],
                    ]),
                  ),
                ],
              ],
            ),
          ),

          // Status bar kiri
          Positioned(
            left: 0, top: 0, bottom: 0,
            child: Container(
              width: 4,
              decoration: BoxDecoration(
                color: color,
                borderRadius: const BorderRadius.only(
                  topLeft: Radius.circular(14),
                  bottomLeft: Radius.circular(14),
                ),
              ),
            ),
          ),
        ]),
      ),
    );
  }

  Widget _buildScoreBlock(String label, String score, Color color) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.center,
      children: [
        Text(score,
            style: TextStyle(
                fontSize: 18, fontWeight: FontWeight.w800, color: color)),
        Text(label,
            style: const TextStyle(fontSize: 10, color: _inkLight)),
      ],
    );
  }

  void _onCardTap(Map<String, dynamic> duel) {
    final status   = duel['status']?.toString();
    final duelId   = duel['id']?.toString() ?? '';
    final iAm      = duel['i_am']?.toString();
    final isMyTurn = duel['is_my_turn'] == true;

    if (status == 'pending' && iAm == 'challenger') {
      Navigator.push(context, MaterialPageRoute(
        builder: (_) => DuelWaitingScreen(duelId: duelId, role: 'challenger'),
      )).then((_) => _load());
      return;
    }
    if (status == 'pending' && iAm == 'opponent') {
      Navigator.push(context, MaterialPageRoute(
        builder: (_) => DuelWaitingScreen(duelId: duelId, role: 'opponent'),
      )).then((_) => _load());
      return;
    }
    if (status == 'active' && isMyTurn) {
      Navigator.push(context, MaterialPageRoute(
        builder: (_) => DuelBattleScreen(duelId: duelId),
      )).then((_) => _load());
      return;
    }
    if (status == 'active' && !isMyTurn) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('⚔️ Menunggu lawan menyelesaikan duelnya...'),
        behavior: SnackBarBehavior.floating,
      ));
      return;
    }

    final msg = switch (status) {
      'completed' => duel['i_won'] == true
          ? '🏆 Kamu menang duel ini!'
          : '💀 Kamu kalah duel ini.',
      'expired'   => '⏱️ Duel kedaluwarsa.',
      'declined'  => '❌ Tantangan ditolak.',
      'cancelled' => '🚫 Tantangan dibatalkan.',
      _           => 'Status: $status',
    };
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(msg), behavior: SnackBarBehavior.floating));
  }

  Widget _buildEmpty(String title, String sub) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(40),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 72, height: 72,
              decoration: BoxDecoration(
                color: const Color(0xFFE6F4F2),
                borderRadius: BorderRadius.circular(20),
              ),
              child: const Icon(Icons.sports_martial_arts_rounded,
                  color: _primary, size: 34),
            ),
            const SizedBox(height: 18),
            Text(title,
                style: const TextStyle(
                    fontSize: 16, fontWeight: FontWeight.w700, color: _ink)),
            const SizedBox(height: 6),
            Text(sub,
                style: const TextStyle(fontSize: 13, color: _inkMid),
                textAlign: TextAlign.center),
          ],
        ),
      ),
    );
  }
}