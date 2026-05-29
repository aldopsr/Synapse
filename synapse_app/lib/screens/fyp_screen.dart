// lib/screens/fyp_screen.dart
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../services/fyp_service.dart';
import 'quiz_list_screen.dart';

class FypScreen extends StatefulWidget {
  const FypScreen({super.key});
  @override
  State<FypScreen> createState() => _FypScreenState();
}

class _FypScreenState extends State<FypScreen>
    with SingleTickerProviderStateMixin {

  static const Color _teal      = Color(0xFF2A9D8F);
  static const Color _tealLight = Color(0xFFE6F4F2);
  static const Color _ink       = Color(0xFF0F172A);
  static const Color _inkMid    = Color(0xFF475569);
  static const Color _inkLight  = Color(0xFF94A3B8);
  static const Color _surface   = Color(0xFFF8FAFC);
  static const Color _border    = Color(0xFFE2E8F0);
  static const Color _red       = Color(0xFFDC2626);
  static const Color _amber     = Color(0xFFD97706);
  static const Color _sky       = Color(0xFF0284C7);
  static const Color _slate     = Color(0xFF64748B);

  final FypService _fypService = FypService();
  AnimationController? _animCtrl;

  bool _isLoading = true;
  String? _error;
  Map<String, dynamic> _summary = {};
  List<dynamic> _items = [];

  @override
  void initState() {
    super.initState();
    _animCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 350),
    );
    _loadFyp();
  }

  @override
  void dispose() {
    _animCtrl?.dispose();
    super.dispose();
  }

  Future<void> _loadFyp() async {
    setState(() { _isLoading = true; _error = null; });
    final result = await _fypService.getFyp();
    if (!mounted) return;
    if (result == null) {
      setState(() { _isLoading = false; _error = 'Gagal memuat. Periksa koneksi.'; });
      return;
    }
    setState(() {
      _isLoading = false;
      _summary   = Map<String, dynamic>.from(result['summary'] ?? {});
      _items     = List<dynamic>.from(result['data'] ?? []);
    });
    _animCtrl?.forward(from: 0);
  }

  Color _colorOf(String? key) => switch (key) {
    'red'    => _red,
    'orange' => _amber,
    'yellow' => _amber,
    'blue'   => _sky,
    _        => _slate,
  };

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: RefreshIndicator(
        color: _teal,
        onRefresh: _loadFyp,
        child: CustomScrollView(
          physics: const BouncingScrollPhysics(),
          slivers: [
            _buildAppBar(),
            if (_isLoading)
              const SliverFillRemaining(
                child: Center(child: CircularProgressIndicator(
                    color: _teal, strokeWidth: 2)),
              )
            else if (_error != null)
              SliverFillRemaining(child: _buildError())
            else if (_items.isEmpty)
              SliverFillRemaining(child: _buildEmpty())
            else
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(0, 0, 0, 120),
                sliver: SliverList(
                  delegate: SliverChildListDelegate([
                    _buildSummaryRow(),
                    _buildDivider(),
                    ..._items.asMap().entries.map((e) {
                      final i = e.key;
                      final item = e.value as Map<String, dynamic>;
                      return AnimatedBuilder(
                        animation: _animCtrl ?? const AlwaysStoppedAnimation(1),
                        builder: (ctx, child) {
                          final anim = _animCtrl;
                          final t = anim != null
                              ? (anim.value - i * 0.08).clamp(0.0, 1.0)
                              : 1.0;
                          return Opacity(
                            opacity: t,
                            child: Transform.translate(
                              offset: Offset(0, 16 * (1 - t)),
                              child: child,
                            ),
                          );
                        },
                        child: _buildCard(item),
                      );
                    }),
                  ]),
                ),
              ),
          ],
        ),
      ),
    );
  }

  // ── App Bar ─────────────────────────────────────────────────
  Widget _buildAppBar() {
    return SliverAppBar(
      expandedHeight: 148,
      pinned: true,
      floating: false,
      backgroundColor: Colors.white,
      foregroundColor: _ink,
      elevation: 0,
      surfaceTintColor: Colors.transparent,
      bottom: PreferredSize(
        preferredSize: const Size.fromHeight(1),
        child: Container(height: 1, color: _border),
      ),
      flexibleSpace: FlexibleSpaceBar(
        expandedTitleScale: 1,
        titlePadding: EdgeInsets.zero,
        background: SafeArea(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(24, 0, 24, 20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(
                      horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: _tealLight,
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: const Text(
                    'FOR YOUR PROGRESS',
                    style: TextStyle(
                      color: _teal,
                      fontSize: 10,
                      fontWeight: FontWeight.w700,
                      letterSpacing: 1.5,
                    ),
                  ),
                ),
                const SizedBox(height: 8),
                const Text(
                  'Analisis Belajarmu',
                  style: TextStyle(
                    color: _ink,
                    fontSize: 26,
                    fontWeight: FontWeight.w800,
                    height: 1.1,
                    letterSpacing: -0.5,
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  // ── Summary Row ─────────────────────────────────────────────
  Widget _buildSummaryRow() {
    final kritis = _summary['kritis']             ?? 0;
    final perlu  = _summary['perlu_latihan']      ?? 0;
    final hampir = _summary['hampir_optimal']     ?? 0;
    final belum  = (_summary['belum_dikerjakan']  ?? 0) +
                   (_summary['belum_lengkap']     ?? 0);

    return Padding(
      padding: const EdgeInsets.fromLTRB(24, 24, 24, 20),
      child: Row(
        children: [
          _buildStat('$kritis', 'Kritis',  _red),
          _buildStatDivider(),
          _buildStat('$perlu',  'Latihan', _amber),
          _buildStatDivider(),
          _buildStat('$hampir', 'Hampir',  _teal),
          _buildStatDivider(),
          _buildStat('$belum',  'Belum',   _slate),
        ],
      ),
    );
  }

  Widget _buildStat(String count, String label, Color color) =>
      Expanded(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(count,
                style: TextStyle(
                    fontSize: 26, fontWeight: FontWeight.w800,
                    color: color, height: 1)),
            const SizedBox(height: 2),
            Text(label,
                style: const TextStyle(
                    fontSize: 11, color: _inkLight,
                    fontWeight: FontWeight.w500)),
          ],
        ),
      );

  Widget _buildStatDivider() => Container(
        width: 1, height: 30,
        margin: const EdgeInsets.symmetric(horizontal: 8),
        color: _border,
      );

  Widget _buildDivider() => Container(
        height: 1, color: _border,
        margin: const EdgeInsets.fromLTRB(24, 0, 24, 4),
      );

  // ── Card ────────────────────────────────────────────────────
  // Menggunakan Stack untuk border kiri, bukan crossAxisAlignment.stretch
  // sehingga tidak ada infinite height constraint.
  Widget _buildCard(Map<String, dynamic> item) {
    final colorKey   = item['status_color']?.toString();
    final color      = _colorOf(colorKey);
    final label      = item['status_label']?.toString() ?? '';
    final title      = item['course_title']?.toString() ?? 'Mata Kuliah';
    final insight    = item['insight']?.toString() ?? '';
    final action     = item['action_label']?.toString() ?? 'Kerjakan';
    final avgScore   = item['avg_score'];
    final passing    = item['passing_score'];
    final done       = (item['done_quizzes'] ?? 0) as int;
    final total      = (item['total_quizzes'] ?? 0) as int;

    return GestureDetector(
      onTap: () {
        HapticFeedback.lightImpact();
        Navigator.push(context,
            MaterialPageRoute(builder: (_) => const QuizListScreen()));
      },
      child: Container(
        margin: const EdgeInsets.fromLTRB(24, 12, 24, 0),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: _border),
        ),
        // Stack: konten di atas, border kiri di atas konten
        child: Stack(
          children: [
            // Konten utama
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 16, 16, 16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Baris atas: label + judul + skor
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              label.toUpperCase(),
                              style: TextStyle(
                                color: color,
                                fontSize: 10,
                                fontWeight: FontWeight.w700,
                                letterSpacing: 0.8,
                              ),
                            ),
                            const SizedBox(height: 3),
                            Text(title,
                                style: const TextStyle(
                                  fontSize: 15,
                                  fontWeight: FontWeight.w700,
                                  color: _ink,
                                  height: 1.3,
                                )),
                          ],
                        ),
                      ),
                      // Skor
                      if (avgScore != null) ...[
                        const SizedBox(width: 12),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.end,
                          children: [
                            Text('$avgScore',
                                style: TextStyle(
                                    fontSize: 22,
                                    fontWeight: FontWeight.w800,
                                    color: color,
                                    height: 1)),
                            if (passing != null)
                              Text('/ $passing KKM',
                                  style: const TextStyle(
                                      fontSize: 10, color: _inkLight)),
                          ],
                        ),
                      ],
                    ],
                  ),

                  const SizedBox(height: 10),

                  // Insight
                  Text(insight,
                      style: const TextStyle(
                          fontSize: 12.5, color: _inkMid, height: 1.55)),

                  const SizedBox(height: 14),

                  // Progress + tombol
                  Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('$done dari $total kuis',
                                style: const TextStyle(
                                    fontSize: 11, color: _inkLight)),
                            const SizedBox(height: 5),
                            ClipRRect(
                              borderRadius: BorderRadius.circular(2),
                              child: LinearProgressIndicator(
                                value: total > 0 ? done / total : 0,
                                backgroundColor: _surface,
                                valueColor:
                                    AlwaysStoppedAnimation<Color>(color),
                                minHeight: 3,
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 14),
                      GestureDetector(
                        onTap: () {
                          HapticFeedback.lightImpact();
                          Navigator.push(
                              context,
                              MaterialPageRoute(
                                  builder: (_) => const QuizListScreen()));
                        },
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 13, vertical: 7),
                          decoration: BoxDecoration(
                            color: color.withOpacity(0.08),
                            borderRadius: BorderRadius.circular(6),
                            border:
                                Border.all(color: color.withOpacity(0.25)),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Text(action,
                                  style: TextStyle(
                                      color: color,
                                      fontSize: 12,
                                      fontWeight: FontWeight.w600)),
                              const SizedBox(width: 3),
                              Icon(Icons.arrow_forward_rounded,
                                  color: color, size: 12),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),

            // Border kiri berwarna — pakai Positioned, bukan stretch
            Positioned(
              left: 0, top: 0, bottom: 0,
              child: Container(
                width: 4,
                decoration: BoxDecoration(
                  color: color,
                  borderRadius: const BorderRadius.only(
                    topLeft: Radius.circular(12),
                    bottomLeft: Radius.circular(12),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ── Empty ───────────────────────────────────────────────────
  Widget _buildEmpty() => Center(
        child: Padding(
          padding: const EdgeInsets.all(40),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                width: 60, height: 60,
                decoration: BoxDecoration(
                  color: _tealLight,
                  borderRadius: BorderRadius.circular(14),
                ),
                child: const Icon(Icons.check_rounded,
                    color: _teal, size: 30),
              ),
              const SizedBox(height: 18),
              const Text('Semua sudah dikuasai',
                  style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w700,
                      color: _ink)),
              const SizedBox(height: 6),
              const Text(
                'Tidak ada mata kuliah\nyang perlu didalami saat ini.',
                style: TextStyle(
                    fontSize: 13, color: _inkMid, height: 1.6),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
      );

  // ── Error ───────────────────────────────────────────────────
  Widget _buildError() => Center(
        child: Padding(
          padding: const EdgeInsets.all(40),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                width: 60, height: 60,
                decoration: BoxDecoration(
                  color: const Color(0xFFFEE2E2),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: const Icon(Icons.wifi_off_rounded,
                    color: _red, size: 28),
              ),
              const SizedBox(height: 18),
              Text(_error ?? 'Terjadi kesalahan',
                  style: const TextStyle(
                      fontSize: 13, color: _inkMid, height: 1.6),
                  textAlign: TextAlign.center),
              const SizedBox(height: 18),
              TextButton(
                onPressed: _loadFyp,
                style: TextButton.styleFrom(
                  foregroundColor: _teal,
                  padding: const EdgeInsets.symmetric(
                      horizontal: 20, vertical: 10),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(6),
                    side: const BorderSide(color: _teal),
                  ),
                ),
                child: const Text('Coba Lagi',
                    style: TextStyle(fontWeight: FontWeight.w600)),
              ),
            ],
          ),
        ),
      );
}