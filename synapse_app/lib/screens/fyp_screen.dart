// lib/screens/fyp_screen.dart
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../services/fyp_service.dart';
import '../widgets/fyp_ai_advisor_sheet.dart';
import 'material_list_screen.dart';
class FypScreen extends StatefulWidget {
  const FypScreen({super.key});
  @override
  State<FypScreen> createState() => _FypScreenState();
}

class _FypScreenState extends State<FypScreen>
    with SingleTickerProviderStateMixin {
  static const Color _primary    = Color(0xFF2A9D8F);
  static const Color _softBg     = Color(0xFFF6F7FB);
  static const Color _softTeal   = Color(0xFFE7F7F5);
  static const Color _textDark   = Color(0xFF1F2937);
  static const Color _textMuted  = Color(0xFF94A3B8);
  static const Color _red        = Color(0xFFE75480);
  static const Color _amber      = Color(0xFFF4A62A);
  static const Color _sky        = Color(0xFF2D9CDB);
  static const Color _slate      = Color(0xFF64748B);

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
        vsync: this, duration: const Duration(milliseconds: 450));
    _loadFyp();
  }

  @override
  void dispose() { _animCtrl?.dispose(); super.dispose(); }

  Future<void> _loadFyp() async {
    setState(() { _isLoading = true; _error = null; });
    final result = await _fypService.getFyp();
    if (!mounted) return;
    if (result == null) {
      setState(() { _isLoading = false;
        _error = 'Gagal memuat rekomendasi. Periksa koneksi kamu ya.'; });
      return;
    }
    setState(() {
      _isLoading = false;
      _summary = Map<String, dynamic>.from(result['summary'] ?? {});
      _items   = List<dynamic>.from(result['data'] ?? []);
    });
    _animCtrl?.forward(from: 0);
  }

  Color _colorOf(String? key) {
    switch (key) {
      case 'red':    return _red;
      case 'orange':
      case 'yellow': return _amber;
      case 'blue':   return _sky;
      default:       return _primary;
    }
  }

  Color _softColorOf(String? key) {
    switch (key) {
      case 'red':    return const Color(0xFFFFEEF5);
      case 'orange':
      case 'yellow': return const Color(0xFFFFF7DF);
      case 'blue':   return const Color(0xFFEAF7FF);
      default:       return const Color(0xFFEAFBF5);
    }
  }

  IconData _iconOf(String? key) {
    switch (key) {
      case 'red':    return Icons.warning_rounded;
      case 'orange':
      case 'yellow': return Icons.local_fire_department_rounded;
      case 'blue':   return Icons.auto_graph_rounded;
      default:       return Icons.check_circle_rounded;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _primary,
      body: Column(children: [
        _buildHeader(context),
        Expanded(
          child: ClipRRect(
            borderRadius: const BorderRadius.vertical(top: Radius.circular(38)),
            child: Container(
              width: double.infinity,
              color: _softBg,
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator(
                      color: _primary, strokeWidth: 2))
                  : RefreshIndicator(
                      color: _primary,
                      onRefresh: _loadFyp,
                      child: _error != null
                          ? ListView(
                              physics: const AlwaysScrollableScrollPhysics(),
                              children: [SizedBox(
                                height: MediaQuery.of(context).size.height * 0.48,
                                child: _buildError())],
                            )
                          : _items.isEmpty
                              ? ListView(
                                  physics: const AlwaysScrollableScrollPhysics(),
                                  children: [SizedBox(
                                    height: MediaQuery.of(context).size.height * 0.48,
                                    child: _buildEmpty())],
                                )
                              : ListView(
                                  padding: const EdgeInsets.fromLTRB(20, 16, 20, 120),
                                  physics: const BouncingScrollPhysics(
                                      parent: AlwaysScrollableScrollPhysics()),
                                  children: [
                                    _buildHandle(),
                                    const SizedBox(height: 18),
                                    _buildSummaryCard(),
                                    const SizedBox(height: 22),
                                    _buildSectionTitle(),
                                    const SizedBox(height: 14),
                                    ..._items.asMap().entries.map((entry) {
                                      final i    = entry.key;
                                      final item = Map<String, dynamic>.from(
                                          entry.value as Map);
                                      return AnimatedBuilder(
                                        animation: _animCtrl ??
                                            const AlwaysStoppedAnimation(1),
                                        builder: (context, child) {
                                          final t = _animCtrl != null
                                              ? (_animCtrl!.value - i * 0.08)
                                                  .clamp(0.0, 1.0)
                                              : 1.0;
                                          return Opacity(
                                            opacity: t,
                                            child: Transform.translate(
                                              offset: Offset(0, 18 * (1 - t)),
                                              child: child));
                                        },
                                        child: _buildRecommendationCard(item, i),
                                      );
                                    }),
                                  ],
                                ),
                    ),
            ),
          ),
        ),
      ]),
    );
  }

  Widget _buildHeader(BuildContext context) {
    final top = MediaQuery.of(context).padding.top;
    return Container(
      height: 230, width: double.infinity,
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [Color(0xFF65C8D0), Color(0xFF2A9D8F), Color(0xFF16877B)],
          begin: Alignment.topLeft, end: Alignment.bottomRight,
        ),
      ),
      child: Stack(children: [
        Positioned(top: -72, left: -58,
            child: _blob(190, Colors.white.withOpacity(0.12))),
        Positioned(top: 10, right: -56,
            child: _blob(165, Colors.teal.shade900.withOpacity(0.16))),
        Positioned(bottom: 18, left: 96,
            child: _blob(105, Colors.white.withOpacity(0.12))),
        Positioned(top: top + 16, left: 20,
          child: Image.asset('assets/images/logo_synapse.png',
            width: 42, height: 42,
            errorBuilder: (_, __, ___) => const Icon(
                Icons.auto_awesome_rounded, size: 36))),
        Positioned(top: top + 18, right: 20,
          child: Container(
            width: 42, height: 42,
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.20),
              borderRadius: BorderRadius.circular(14)),
            child: const Icon(Icons.insights_rounded,
                color: Colors.white, size: 23))),
        Positioned(left: 24, right: 24, top: top + 78,
          child: const Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('For Your\nProgress',
                  style: TextStyle(color: Colors.white, fontSize: 40,
                      height: 1.05, fontWeight: FontWeight.w900,
                      letterSpacing: -1)),
              SizedBox(height: 8),
              Text('Rekomendasi belajar khusus buat kamu',
                  style: TextStyle(color: Colors.white, fontSize: 15,
                      fontWeight: FontWeight.w700)),
            ],
          )),
      ]),
    );
  }

  Widget _blob(double size, Color color) => Container(
    width: size, height: size,
    decoration: BoxDecoration(color: color,
        borderRadius: BorderRadius.circular(size)));

  Widget _buildHandle() => Center(child: Container(
    width: 54, height: 5,
    decoration: BoxDecoration(color: Colors.grey.shade300,
        borderRadius: BorderRadius.circular(100))));

  Widget _buildSummaryCard() {
    final kritis = _summary['kritis'] ?? 0;
    final perlu  = _summary['perlu_latihan'] ?? 0;
    final hampir = _summary['hampir_optimal'] ?? 0;
    final belum  = (_summary['belum_dikerjakan'] ?? 0) +
        (_summary['belum_lengkap'] ?? 0);
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(color: Colors.white,
        borderRadius: BorderRadius.circular(26),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.055),
            blurRadius: 20, offset: const Offset(0, 8))]),
      child: Row(children: [
        _buildStat('$kritis', 'Kritis', _red),   _buildStatDivider(),
        _buildStat('$perlu',  'Latihan', _amber), _buildStatDivider(),
        _buildStat('$hampir', 'Hampir', _primary),_buildStatDivider(),
        _buildStat('$belum',  'Belum', _slate),
      ]),
    );
  }

  Widget _buildStat(String value, String label, Color color) => Expanded(
    child: Column(children: [
      Container(
        width: 42, height: 42,
        decoration: BoxDecoration(color: color.withOpacity(0.12),
            shape: BoxShape.circle),
        child: Center(child: Text(value, style: TextStyle(
            color: color, fontSize: 18, fontWeight: FontWeight.w900)))),
      const SizedBox(height: 7),
      Text(label, style: const TextStyle(color: _textMuted,
          fontSize: 11, fontWeight: FontWeight.w700)),
    ]),
  );

  Widget _buildStatDivider() => Container(
      width: 1, height: 44, color: Colors.grey.withOpacity(0.15));

  Widget _buildSectionTitle() => const Row(children: [
    Expanded(child: Text('Rekomendasi Belajar', style: TextStyle(
        color: _textDark, fontSize: 21, fontWeight: FontWeight.w900))),
    Icon(Icons.auto_awesome_rounded, color: _primary, size: 20),
  ]);

  Widget _buildRecommendationCard(Map<String, dynamic> item, int index) {
    final colorKey    = item['status_color']?.toString();
    final color       = _colorOf(colorKey);
    final bgColor     = _softColorOf(colorKey);
    final icon        = _iconOf(colorKey);
    final label       = item['status_label']?.toString() ?? '';
    final title       = item['course_title']?.toString() ?? 'Mata Kuliah';
    final insight     = item['insight']?.toString() ?? '';
    final action      = item['action_label']?.toString() ?? 'Kerjakan';
    final avgScore    = item['avg_score'];
    final passing     = item['passing_score'];
    final done        = (item['done_quizzes'] ?? 0) as int;
    final total       = (item['total_quizzes'] ?? 0) as int;
    final progress    = total > 0 ? done / total : 0.0;
    final courseId    = item['course_id']?.toString() ?? '';
    final courseTitle = title;

    // Hitung gap skor vs KKM
    final double avg = avgScore != null
        ? double.tryParse(avgScore.toString()) ?? 0
        : 0;
    final double kkm = passing != null
        ? double.tryParse(passing.toString()) ?? 70
        : 70;
    final double gap = kkm - avg;
    final bool belumMulai = avgScore == null;

    return GestureDetector(
      onTap: () {
        HapticFeedback.lightImpact();
        Navigator.push(context, MaterialPageRoute(
          builder: (_) => MaterialListScreen(
              courseId: courseId, courseTitle: courseTitle)));
      },
      child: Container(
        margin: const EdgeInsets.only(bottom: 16),
        decoration: BoxDecoration(
          color: bgColor,
          borderRadius: BorderRadius.circular(24),
          boxShadow: [BoxShadow(
            color: color.withOpacity(0.12),
            blurRadius: 20, offset: const Offset(0, 6))],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // ── Header card ───────────────────────────────
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 12),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    width: 44, height: 44,
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.8),
                      borderRadius: BorderRadius.circular(14)),
                    child: Icon(icon, color: color, size: 22)),
                  const SizedBox(width: 12),
                  Expanded(child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      if (label.isNotEmpty)
                        Container(
                          margin: const EdgeInsets.only(bottom: 4),
                          padding: const EdgeInsets.symmetric(
                              horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: color.withOpacity(0.15),
                            borderRadius: BorderRadius.circular(6)),
                          child: Text(label.toUpperCase(),
                              style: TextStyle(color: color,
                                  fontSize: 9, fontWeight: FontWeight.w900,
                                  letterSpacing: 0.8))),
                      Text(title,
                          style: const TextStyle(color: _textDark,
                              fontSize: 15, fontWeight: FontWeight.w900,
                              height: 1.2),
                          maxLines: 2, overflow: TextOverflow.ellipsis),
                    ],
                  )),
                  // Action button
                  GestureDetector(
                    onTap: () {
                      HapticFeedback.lightImpact();
                      Navigator.push(context, MaterialPageRoute(
                        builder: (_) => MaterialListScreen(
                            courseId: courseId, courseTitle: courseTitle)));
                    },
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 10, vertical: 6),
                      decoration: BoxDecoration(
                        color: color,
                        borderRadius: BorderRadius.circular(10)),
                      child: Text(action,
                          style: const TextStyle(color: Colors.white,
                              fontSize: 11, fontWeight: FontWeight.w800))),
                  ),
                ],
              ),
            ),

            // ── Insight full (tidak terpotong) ────────────
            if (insight.isNotEmpty)
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 0, 16, 12),
                child: Text(insight,
                    style: TextStyle(color: _textDark.withOpacity(0.6),
                        fontSize: 12, height: 1.5,
                        fontWeight: FontWeight.w500)),
              ),

            // ── Stats row ─────────────────────────────────
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 0, 16, 12),
              child: Row(children: [
                // Skor rata-rata
                _buildStatChip(
                  label: 'Rata-rata',
                  value: belumMulai ? '-' : '${avg.toInt()}',
                  color: color,
                  icon: Icons.bar_chart_rounded,
                ),
                const SizedBox(width: 8),
                // KKM
                _buildStatChip(
                  label: 'KKM',
                  value: '${kkm.toInt()}',
                  color: _slate,
                  icon: Icons.flag_outlined,
                ),
                const SizedBox(width: 8),
                // Gap / Status
                if (!belumMulai && gap > 0)
                  _buildStatChip(
                    label: 'Kurang',
                    value: '${gap.toInt()} poin',
                    color: color,
                    icon: Icons.trending_up_rounded,
                  )
                else if (!belumMulai && gap <= 0)
                  _buildStatChip(
                    label: 'Surplus',
                    value: '+${(-gap).toInt()} poin',
                    color: _primary,
                    icon: Icons.check_circle_outline_rounded,
                  ),
              ]),
            ),

            // ── Progress kuis ─────────────────────────────
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 0, 16, 14),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(children: [
                    Text('Kuis dikerjakan',
                        style: TextStyle(color: _textDark.withOpacity(0.5),
                            fontSize: 11, fontWeight: FontWeight.w600)),
                    const Spacer(),
                    Text('$done dari $total kuis',
                        style: TextStyle(color: color,
                            fontSize: 11, fontWeight: FontWeight.w800)),
                  ]),
                  const SizedBox(height: 6),
                  ClipRRect(
                    borderRadius: BorderRadius.circular(99),
                    child: LinearProgressIndicator(
                      value: progress, minHeight: 7,
                      backgroundColor: Colors.white.withOpacity(0.6),
                      valueColor: AlwaysStoppedAnimation<Color>(color)),
                  ),
                  if (total - done > 0) ...[
                    const SizedBox(height: 4),
                    Text('${total - done} kuis belum dikerjakan',
                        style: TextStyle(color: color.withOpacity(0.7),
                            fontSize: 10, fontWeight: FontWeight.w600)),
                  ],
                ],
              ),
            ),

            // ── Tanya SYNAPSE ─────────────────────────────
            GestureDetector(
              onTap: () {
                HapticFeedback.lightImpact();
                showModalBottomSheet(
                  context: context,
                  isScrollControlled: true,
                  backgroundColor: Colors.transparent,
                  builder: (_) => FypAiAdvisorSheet(item: item),
                );
              },
              child: Container(
                width: double.infinity,
                padding: const EdgeInsets.symmetric(vertical: 11),
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.5),
                  borderRadius: const BorderRadius.vertical(
                      bottom: Radius.circular(24)),
                  border: Border(
                      top: BorderSide(color: color.withOpacity(0.15))),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.auto_awesome_rounded, color: color, size: 14),
                    const SizedBox(width: 6),
                    Text('Tanya SYNAPSE', style: TextStyle(
                        fontSize: 12, fontWeight: FontWeight.w700,
                        color: color)),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatChip({
    required String label,
    required String value,
    required Color color,
    required IconData icon,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.7),
        borderRadius: BorderRadius.circular(10)),
      child: Row(mainAxisSize: MainAxisSize.min, children: [
        Icon(icon, size: 12, color: color.withOpacity(0.7)),
        const SizedBox(width: 4),
        Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(label, style: TextStyle(fontSize: 9,
              color: _textMuted, fontWeight: FontWeight.w600)),
          Text(value, style: TextStyle(fontSize: 12,
              color: color, fontWeight: FontWeight.w800)),
        ]),
      ]),
    );
  }


  Widget _buildEmpty() => Center(child: Padding(
    padding: const EdgeInsets.all(38),
    child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
      Container(width: 88, height: 88,
        decoration: const BoxDecoration(color: _softTeal, shape: BoxShape.circle),
        child: const Icon(Icons.check_rounded, size: 42, color: _primary)),
      const SizedBox(height: 18),
      const Text('Semua sudah dikuasai', style: TextStyle(
          fontSize: 17, fontWeight: FontWeight.w900, color: _textDark)),
      const SizedBox(height: 8),
      const Text('Tidak ada mata kuliah yang perlu didalami saat ini.',
          textAlign: TextAlign.center,
          style: TextStyle(fontSize: 13, color: _textMuted,
              height: 1.45, fontWeight: FontWeight.w500)),
    ]),
  ));

  Widget _buildError() => Center(child: Padding(
    padding: const EdgeInsets.all(38),
    child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
      Container(width: 88, height: 88,
        decoration: const BoxDecoration(
            color: Color(0xFFFFEEF5), shape: BoxShape.circle),
        child: const Icon(Icons.wifi_off_rounded, size: 42, color: _red)),
      const SizedBox(height: 18),
      const Text('Oops, gagal memuat', style: TextStyle(
          fontSize: 17, fontWeight: FontWeight.w900, color: _textDark)),
      const SizedBox(height: 8),
      Text(_error ?? 'Terjadi kesalahan.',
          textAlign: TextAlign.center,
          style: const TextStyle(fontSize: 13, color: _textMuted,
              height: 1.45, fontWeight: FontWeight.w500)),
      const SizedBox(height: 18),
      ElevatedButton(
        onPressed: _loadFyp,
        style: ElevatedButton.styleFrom(
          backgroundColor: _primary, foregroundColor: Colors.white,
          elevation: 0,
          padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 12),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16))),
        child: const Text('Coba Lagi',
            style: TextStyle(fontWeight: FontWeight.w900))),
    ]),
  ));
}