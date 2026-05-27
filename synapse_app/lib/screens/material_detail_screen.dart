import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';
import 'practice_screen.dart';
import 'ar_view_screen.dart';
import '../services/auth_service.dart';
import '../utils/constants.dart';
import '../widgets/inline_model_viewer.dart';
import '../widgets/audio_capsule.dart';

class MaterialDetailScreen extends StatefulWidget {
  final Map<String, dynamic> material;

  const MaterialDetailScreen({super.key, required this.material});

  @override
  State<MaterialDetailScreen> createState() => _MaterialDetailScreenState();
}

class _MaterialDetailScreenState extends State<MaterialDetailScreen>
    with SingleTickerProviderStateMixin {
  // Theme colors (vibrant educational)
  static const Color _primaryColor = Color(0xFF2A9D8F);
  static const Color _accentOrange = Color(0xFFFF9F43);
  static const Color _accentPurple = Color(0xFFA855F7);
  static const Color _accentGreen = Color(0xFF10B981);
  static const Color _accentAmber = Color(0xFFF59E0B);
  static const Color _bgSoft = Color(0xFFFFFBF5);

  final ScrollController _scrollController = ScrollController();
  late AnimationController _fabAnimationController;
  late Animation<double> _fabAnimation;

  double _scrollProgress = 0.0;
  bool _isFabExpanded = false;

  // Computed flags
  late bool _hasAr;
  late bool _hasPractice;
  late int _arCount;
  late int _readingMinutes;

  @override
  void initState() {
    super.initState();

    // Compute flags & data
    final material = widget.material;
    bool hasArNew = material['ar_assets'] != null &&
        (material['ar_assets'] as List).isNotEmpty;
    bool hasArOld = material['has_ar'] == true ||
        (material['model_3d_path'] != null &&
            material['model_3d_path'].toString().isNotEmpty);
    _hasAr = hasArNew || hasArOld;

    _arCount = hasArNew ? (material['ar_assets'] as List).length : (hasArOld ? 1 : 0);

    bool hasPracticeNew = material['questions'] != null &&
        (material['questions'] as List).isNotEmpty;
    bool hasPracticeOld = material['has_practice'] == true;
    _hasPractice = hasPracticeNew || hasPracticeOld;

    _readingMinutes = _calculateReadingTime(
        material['content'] ?? material['body'] ?? '');

    // Setup scroll listener for progress bar
    _scrollController.addListener(_onScroll);

    // Setup FAB animation
    _fabAnimationController = AnimationController(
      duration: const Duration(milliseconds: 250),
      vsync: this,
    );
    _fabAnimation = CurvedAnimation(
      parent: _fabAnimationController,
      curve: Curves.easeOutBack,
    );
  }

  @override
  void dispose() {
    _scrollController.removeListener(_onScroll);
    _scrollController.dispose();
    _fabAnimationController.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollController.position.maxScrollExtent <= 0) return;
    final progress = (_scrollController.offset /
            _scrollController.position.maxScrollExtent)
        .clamp(0.0, 1.0);
    if ((progress - _scrollProgress).abs() > 0.01) {
      setState(() {
        _scrollProgress = progress;
      });
    }
  }

  int _calculateReadingTime(String htmlContent) {
    // Strip HTML tags
    final stripped = htmlContent.replaceAll(RegExp(r'<[^>]*>'), ' ');
    final words = stripped.split(RegExp(r'\s+')).where((s) => s.isNotEmpty).length;
    final minutes = (words / 200).ceil();
    return minutes < 1 ? 1 : minutes;
  }

  void _toggleFab() {
    setState(() {
      _isFabExpanded = !_isFabExpanded;
      if (_isFabExpanded) {
        _fabAnimationController.forward();
      } else {
        _fabAnimationController.reverse();
      }
    });
  }

  String? _resolveModelSource() {
    final m = widget.material;

    // Format baru: ar_assets[].model_3d_url / model_3d_path
    final assets = m['ar_assets'];
    if (assets is List && assets.isNotEmpty) {
      final first = assets.first;
      final url = first['model_3d_url']?.toString();
      if (url != null && url.isNotEmpty) return url;
      final path = first['model_3d_path']?.toString();
      if (path != null && path.isNotEmpty) return path;
    }

    // Format lama: langsung di material
    final url = m['model_3d_url']?.toString();
    if (url != null && url.isNotEmpty) return url;
    final path = m['model_3d_path']?.toString();
    if (path != null && path.isNotEmpty) return path;

    return null;
  }

  // ============================================================
  // BUILD
  // ============================================================
  @override
  Widget build(BuildContext context) {
    final String? modelSource = _resolveModelSource();
    final String rawContent = (widget.material['content'] ??
            widget.material['body'] ??
            '')
        .toString();

    return Scaffold(
      backgroundColor: _bgSoft,
      body: Stack(
        children: [
          CustomScrollView(
            controller: _scrollController,
            physics: const BouncingScrollPhysics(),
            slivers: [
              _buildHeroAppBar(),
              SliverToBoxAdapter(child: _buildQuickStats()),

              // 🌟 BARU: 3D Model Viewer inline (pengganti AR). Tampil di atas.
              if (modelSource != null)
                SliverToBoxAdapter(
                  child: InlineModelViewer(
                    rawModelSource: modelSource,
                    title: widget.material['title']?.toString() ?? 'Model 3D',
                  ),
                ),

              // 🌟 BARU: Tombol Dengarkan Transmisi (TTS).
              if (rawContent.trim().isNotEmpty)
                SliverToBoxAdapter(
                  child: AudioCapsule(rawContent: rawContent),
                ),

              SliverToBoxAdapter(child: _buildContentCard()),

              // Galeri AR LAMA: digantikan viewer inline di atas. Dinonaktifkan
              // agar tidak ada dua tempat menampilkan model 3D.
              // if (_hasAr) SliverToBoxAdapter(child: _buildArGallerySection()),

              if (_hasPractice)
                SliverToBoxAdapter(child: _buildPracticeTeaserCard()),
              const SliverToBoxAdapter(child: SizedBox(height: 140)),
            ],
          ),
          // 🌟 Top progress bar
          _buildTopProgressBar(),
        ],
      ),
      floatingActionButton: _buildSpeedDialFab(),
      bottomNavigationBar: _buildStickyBottomBar(),
    );
  }

  // ============================================================
  // 1. HERO APP BAR (Sticky Collapsing)
  // ============================================================
  Widget _buildHeroAppBar() {
    final material = widget.material;
    final imageUrl = material['image']?.toString();
    final title = material['title']?.toString() ?? 'Detail Materi';

    return SliverAppBar(
      expandedHeight: 260.0,
      floating: false,
      pinned: true,
      stretch: true,
      backgroundColor: _primaryColor,
      foregroundColor: Colors.white,
      elevation: 0,
      flexibleSpace: FlexibleSpaceBar(
        stretchModes: const [
          StretchMode.zoomBackground,
          StretchMode.fadeTitle,
        ],
        titlePadding:
            const EdgeInsets.only(left: 56, bottom: 16, right: 56),
        title: Text(
          title,
          style: const TextStyle(
            fontWeight: FontWeight.bold,
            fontSize: 16,
            color: Colors.white,
            shadows: [
              Shadow(color: Colors.black54, blurRadius: 8, offset: Offset(0, 2))
            ],
          ),
          maxLines: 2,
          overflow: TextOverflow.ellipsis,
        ),
        background: Stack(
          fit: StackFit.expand,
          children: [
            // Layer 1: Thumbnail atau gradient
            _buildHeroBackground(imageUrl),

            // Layer 2: Dark gradient overlay (biar text readable)
            Container(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [
                    Colors.transparent,
                    Colors.black.withOpacity(0.3),
                    Colors.black.withOpacity(0.7),
                  ],
                  stops: const [0.4, 0.7, 1.0],
                ),
              ),
            ),

            // Layer 3: Decorative emoji badge (top-right)
            Positioned(
              top: 70,
              right: 20,
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 300),
                padding: const EdgeInsets.symmetric(
                    horizontal: 14, vertical: 7),
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.25),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: Colors.white.withOpacity(0.4)),
                ),
                child: const Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text('📚', style: TextStyle(fontSize: 14)),
                    SizedBox(width: 6),
                    Text(
                      'Materi Belajar',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildHeroBackground(String? imageUrl) {
    if (imageUrl != null && imageUrl.isNotEmpty) {
      return Image.network(
        imageUrl,
        fit: BoxFit.cover,
        errorBuilder: (_, __, ___) => _buildGradientBackground(),
        loadingBuilder: (context, child, loadingProgress) {
          if (loadingProgress == null) return child;
          return _buildGradientBackground();
        },
      );
    }
    return _buildGradientBackground();
  }

  Widget _buildGradientBackground() {
    return Container(
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [_primaryColor, Color(0xFF1F7A6C), Color(0xFF14534B)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      child: Stack(
        children: [
          // Decorative circles
          Positioned(
            right: -50,
            top: 60,
            child: Container(
              width: 200,
              height: 200,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.white.withOpacity(0.05),
              ),
            ),
          ),
          Positioned(
            left: -30,
            bottom: -30,
            child: Container(
              width: 120,
              height: 120,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.white.withOpacity(0.05),
              ),
            ),
          ),
          // Big icon
          const Center(
            child: Icon(Icons.menu_book_rounded,
                size: 100, color: Colors.white24),
          ),
        ],
      ),
    );
  }

  // ============================================================
  // 2. TOP PROGRESS BAR (Reading Indicator)
  // ============================================================
  Widget _buildTopProgressBar() {
    return Positioned(
      top: 0,
      left: 0,
      right: 0,
      child: SafeArea(
        child: AnimatedOpacity(
          duration: const Duration(milliseconds: 300),
          opacity: _scrollProgress > 0.05 ? 1.0 : 0.0,
          child: LinearProgressIndicator(
            value: _scrollProgress,
            backgroundColor: Colors.white.withOpacity(0.3),
            valueColor:
                const AlwaysStoppedAnimation<Color>(_accentAmber),
            minHeight: 3,
          ),
        ),
      ),
    );
  }

  // ============================================================
  // 3. QUICK STATS BAR (Reading time + AR count + Practice)
  // ============================================================
  Widget _buildQuickStats() {
    return Transform.translate(
      offset: const Offset(0, -20),
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: 20),
        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 14),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          boxShadow: [
            BoxShadow(
              color: _primaryColor.withOpacity(0.12),
              blurRadius: 20,
              offset: const Offset(0, 8),
            )
          ],
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceAround,
          children: [
            _buildStatChip(
              emoji: '📖',
              label: '$_readingMinutes min',
              subLabel: 'baca',
              color: _accentOrange,
            ),
            _buildStatDivider(),
            _buildStatChip(
              emoji: '✨',
              label: _arCount > 0 ? '$_arCount 3D' : '—',
              subLabel: _arCount > 0 ? 'tersedia' : 'tidak ada',
              color: _accentPurple,
              dimmed: _arCount == 0,
            ),
            _buildStatDivider(),
            _buildStatChip(
              emoji: _hasPractice ? '🎯' : '⏸',
              label: _hasPractice ? 'Latihan' : 'Belum',
              subLabel: _hasPractice ? 'tersedia' : 'ada soal',
              color: _accentGreen,
              dimmed: !_hasPractice,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatChip({
    required String emoji,
    required String label,
    required String subLabel,
    required Color color,
    bool dimmed = false,
  }) {
    return Expanded(
      child: Opacity(
        opacity: dimmed ? 0.5 : 1.0,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(emoji, style: const TextStyle(fontSize: 20)),
            const SizedBox(height: 4),
            Text(
              label,
              style: TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.bold,
                color: color,
              ),
            ),
            Text(
              subLabel,
              style: TextStyle(
                fontSize: 10,
                color: Colors.grey[600],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatDivider() {
    return Container(
      width: 1,
      height: 30,
      color: Colors.grey[200],
    );
  }

  // ============================================================
  // 4. CONTENT CARD (Main reading area)
  // ============================================================
  Widget _buildContentCard() {
    final material = widget.material;
    final description = material['description']?.toString();

    return Container(
      margin: const EdgeInsets.fromLTRB(20, 12, 20, 0),
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.03),
            blurRadius: 12,
            offset: const Offset(0, 4),
          )
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // 🎯 Section header dengan emoji
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(
                    horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [_accentOrange, Color(0xFFFFB269)],
                  ),
                  borderRadius: BorderRadius.circular(20),
                  boxShadow: [
                    BoxShadow(
                      color: _accentOrange.withOpacity(0.3),
                      blurRadius: 6,
                      offset: const Offset(0, 2),
                    )
                  ],
                ),
                child: const Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text('🚀', style: TextStyle(fontSize: 12)),
                    SizedBox(width: 4),
                    Text(
                      'Pendahuluan',
                      style: TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                          fontSize: 12),
                    ),
                  ],
                ),
              ),
            ],
          ),

          if (description != null && description.isNotEmpty) ...[
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: const Color(0xFFFFF8E1),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: Colors.amber[100]!),
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('💡', style: TextStyle(fontSize: 20)),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      description,
                      style: TextStyle(
                        fontSize: 14.5,
                        fontStyle: FontStyle.italic,
                        color: Colors.brown[800],
                        height: 1.6,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],

          const Padding(
            padding: EdgeInsets.symmetric(vertical: 24.0),
            child: Row(
              children: [
                Text('📝', style: TextStyle(fontSize: 18)),
                SizedBox(width: 8),
                Text(
                  'Isi Materi',
                  style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF334155)),
                ),
                SizedBox(width: 12),
                Expanded(
                  child: Divider(color: Color(0xFFE2E8F0), thickness: 1.5),
                ),
              ],
            ),
          ),

          // 🌟 HTML Content dengan typography enhanced
          _buildEnhancedHtmlContent(),

          const SizedBox(height: 20),

          // 🌟 Achievement teaser
          _buildAchievementTeaser(),
        ],
      ),
    );
  }

  Widget _buildEnhancedHtmlContent() {
    final material = widget.material;
    return Html(
      data: material['content'] ??
          material['body'] ??
          '<p>Isi materi belum tersedia.</p>',
      style: {
        "body": Style(
          fontSize: FontSize(15.5),
          lineHeight: LineHeight(1.85),
          color: const Color(0xFF334155),
          margin: Margins.zero,
          padding: HtmlPaddings.zero,
          fontFamily: 'Inter',
        ),
        "p": Style(
          margin: Margins.only(bottom: 14),
        ),
        "h1": Style(
          fontSize: FontSize(22),
          fontWeight: FontWeight.bold,
          color: _primaryColor,
          margin: Margins.symmetric(vertical: 16),
        ),
        "h2": Style(
          fontSize: FontSize(19),
          fontWeight: FontWeight.bold,
          color: _primaryColor,
          margin: Margins.symmetric(vertical: 14),
        ),
        "h3": Style(
          fontSize: FontSize(17),
          fontWeight: FontWeight.bold,
          color: const Color(0xFF334155),
          margin: Margins.symmetric(vertical: 12),
        ),
        "strong": Style(
          fontWeight: FontWeight.bold,
          color: _primaryColor,
        ),
        "em": Style(
          fontStyle: FontStyle.italic,
          color: const Color(0xFF475569),
        ),
        "blockquote": Style(
          backgroundColor: const Color(0xFFFFF8E1),
          padding: HtmlPaddings.all(14),
          margin: Margins.symmetric(vertical: 12),
          border: const Border(
            left: BorderSide(color: _accentAmber, width: 4),
          ),
          fontStyle: FontStyle.italic,
          color: Colors.brown[800],
        ),
        "code": Style(
          backgroundColor: const Color(0xFFF1F5F9),
          padding: HtmlPaddings.symmetric(horizontal: 6, vertical: 2),
          fontFamily: 'Courier',
          color: const Color(0xFFE11D48),
          fontSize: FontSize(13.5),
        ),
        "pre": Style(
          backgroundColor: const Color(0xFF1E293B),
          padding: HtmlPaddings.all(14),
          margin: Margins.symmetric(vertical: 12),
          color: const Color(0xFFE2E8F0),
          fontFamily: 'Courier',
        ),
        "ul": Style(margin: Margins.only(bottom: 12, left: 8)),
        "ol": Style(margin: Margins.only(bottom: 12, left: 8)),
        "li": Style(margin: Margins.only(bottom: 6)),
        "a": Style(
          color: _accentOrange,
          textDecoration: TextDecoration.underline,
        ),
        "table": Style(
          backgroundColor: Colors.grey.shade50,
          margin: Margins.symmetric(vertical: 12),
        ),
        "th": Style(
          padding: HtmlPaddings.all(10),
          border: Border.all(color: Colors.grey),
          backgroundColor: _primaryColor.withOpacity(0.1),
          fontWeight: FontWeight.bold,
        ),
        "td": Style(
          padding: HtmlPaddings.all(10),
          border: Border.all(color: Colors.grey),
        ),
      },
      extensions: [
        TagExtension(
          tagsToExtend: {"img"},
          builder: (extensionContext) {
            String imgUrl = extensionContext.attributes['src'] ?? '';
            String serverDomain = AppConstants.baseUrl.replaceAll('/api', '');

            if (imgUrl.isNotEmpty) {
              if (imgUrl.startsWith('/storage')) {
                imgUrl = '$serverDomain$imgUrl';
              } else if (imgUrl.contains('localhost') ||
                  imgUrl.contains('127.0.0.1') ||
                  imgUrl.contains('10.0.2.2')) {
                imgUrl = imgUrl.replaceAll(
                    RegExp(
                        r'http://(localhost|127\.0\.0\.1|10\.0\.2\.2)(:\d+)?'),
                    serverDomain);
              }
            }

            return Padding(
              padding: const EdgeInsets.symmetric(vertical: 14.0),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(16),
                child: Image.network(
                  imgUrl,
                  width: double.infinity,
                  fit: BoxFit.contain,
                  errorBuilder: (ctx, error, stackTrace) => Container(
                    height: 150,
                    width: double.infinity,
                    decoration: BoxDecoration(
                      color: Colors.grey[100],
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.broken_image_rounded,
                            color: Colors.grey, size: 40),
                        const SizedBox(height: 8),
                        Text('Gagal memuat gambar',
                            style: TextStyle(
                                color: Colors.grey[600], fontSize: 12)),
                      ],
                    ),
                  ),
                ),
              ),
            );
          },
        ),
      ],
    );
  }

  Widget _buildAchievementTeaser() {
    if (!_hasPractice && !_hasAr) return const SizedBox.shrink();

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [_accentAmber.withOpacity(0.15), _accentOrange.withOpacity(0.1)],
        ),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: _accentAmber.withOpacity(0.3)),
      ),
      child: Row(
        children: [
          Container(
            width: 50,
            height: 50,
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [_accentAmber, _accentOrange],
              ),
              borderRadius: BorderRadius.circular(14),
              boxShadow: [
                BoxShadow(
                  color: _accentAmber.withOpacity(0.4),
                  blurRadius: 8,
                  offset: const Offset(0, 4),
                )
              ],
            ),
            child: const Icon(Icons.emoji_events_rounded,
                color: Colors.white, size: 28),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  _hasPractice
                      ? 'Selesaikan latihan untuk dapat skor!'
                      : 'Eksplorasi model 3D untuk pengalaman lebih dalam!',
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF334155),
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  _hasPractice
                      ? 'Uji pemahamanmu dan catat progres belajar'
                      : 'Lihat objek 3D langsung dari materi ini',
                  style: TextStyle(
                    fontSize: 11,
                    color: Colors.brown[600],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // ============================================================
  // 6. PRACTICE TEASER CARD
  // ============================================================
  Widget _buildPracticeTeaserCard() {
    return Container(
      margin: const EdgeInsets.fromLTRB(20, 16, 20, 0),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            _accentGreen.withOpacity(0.1),
            _accentGreen.withOpacity(0.03),
          ],
        ),
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: _accentGreen.withOpacity(0.25)),
      ),
      child: Row(
        children: [
          Container(
            width: 60,
            height: 60,
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [_accentGreen, Colors.green[700]!],
              ),
              borderRadius: BorderRadius.circular(18),
              boxShadow: [
                BoxShadow(
                  color: _accentGreen.withOpacity(0.4),
                  blurRadius: 10,
                  offset: const Offset(0, 4),
                )
              ],
            ),
            child: const Icon(Icons.psychology_alt_rounded,
                color: Colors.white, size: 30),
          ),
          const SizedBox(width: 14),
          const Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Text('🎯',
                        style: TextStyle(fontSize: 14)),
                    SizedBox(width: 6),
                    Text(
                      'Latihan Tersedia',
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF334155),
                      ),
                    ),
                  ],
                ),
                SizedBox(height: 4),
                Text(
                  'Uji pemahamanmu setelah selesai membaca!',
                  style: TextStyle(fontSize: 12, color: Colors.grey),
                ),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
            decoration: BoxDecoration(
              color: _accentGreen,
              borderRadius: BorderRadius.circular(8),
            ),
            child: const Icon(Icons.arrow_forward_rounded,
                color: Colors.white, size: 18),
          ),
        ],
      ),
    );
  }

  // ============================================================
  // 7. FAB — hanya Latihan (AR sudah dihapus, 3D viewer kini inline)
  // ============================================================
  Widget? _buildSpeedDialFab() {
    if (!_hasPractice) return null;

    return FloatingActionButton.extended(
      onPressed: _onPracticePressed,
      backgroundColor: _accentGreen,
      foregroundColor: Colors.white,
      icon: const Icon(Icons.play_circle_fill_rounded),
      label: const Text('Mulai Latihan',
          style: TextStyle(fontWeight: FontWeight.bold)),
      elevation: 8,
    );
  }

  // ============================================================
  // 8. STICKY BOTTOM BAR (Hidden for now - FAB enough)
  // ============================================================
  Widget? _buildStickyBottomBar() {
    // Kalau Kapten mau pakai bottom bar penuh selain FAB, return widget di sini.
    // Untuk sekarang null biar FAB jadi primary action.
    return null;
  }

  // ============================================================
  // ACTION HANDLERS
  // ============================================================
  void _onPracticePressed() {
    final material = widget.material;
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => PracticeScreen(
          materialId: material['id'].toString(),
          materialTitle: material['title']?.toString() ?? 'Latihan',
        ),
      ),
    );
  }
}