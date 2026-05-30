import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';
import 'practice_screen.dart';
import '../utils/constants.dart';
import '../widgets/inline_model_viewer.dart';
import '../widgets/audio_capsule.dart';
import '../widgets/synapse_fab.dart';

class MaterialDetailScreen extends StatefulWidget {
  final Map<String, dynamic> material;

  const MaterialDetailScreen({super.key, required this.material});

  @override
  State<MaterialDetailScreen> createState() => _MaterialDetailScreenState();
}

class _MaterialDetailScreenState extends State<MaterialDetailScreen> {
  static const Color _primary = Color(0xFF2A9D8F);
  static const Color _primaryDark = Color(0xFF16877B);
  static const Color _softBg = Color(0xFFF6F7FB);
  static const Color _softTeal = Color(0xFFEAFBF5);
  static const Color _textDark = Color(0xFF1F2937);
  static const Color _textMuted = Color(0xFF94A3B8);
  static const Color _orange = Color(0xFFF4A62A);
  static const Color _purple = Color(0xFF7C3AED);
  static const Color _green = Color(0xFF10B981);

  final DraggableScrollableController _sheetCtrl =
      DraggableScrollableController();

  late bool _hasPractice;
  late int _arCount;
  late int _readingMinutes;

  @override
  void initState() {
    super.initState();

    final material = widget.material;

    final hasArNew = material['ar_assets'] != null &&
        (material['ar_assets'] as List).isNotEmpty;

    final hasArOld = material['has_ar'] == true ||
        (material['model_3d_path'] != null &&
            material['model_3d_path'].toString().isNotEmpty);

    _arCount = hasArNew ? (material['ar_assets'] as List).length : hasArOld ? 1 : 0;

    final hasPracticeNew = material['questions'] != null &&
        (material['questions'] as List).isNotEmpty;

    final hasPracticeOld = material['has_practice'] == true;

    _hasPractice = hasPracticeNew || hasPracticeOld;

    _readingMinutes = _calculateReadingTime(
      material['content'] ?? material['body'] ?? '',
    );
  }

  int _calculateReadingTime(String htmlContent) {
    final stripped = htmlContent.replaceAll(RegExp(r'<[^>]*>'), ' ');
    final words =
        stripped.split(RegExp(r'\s+')).where((s) => s.isNotEmpty).length;
    final minutes = (words / 200).ceil();
    return minutes < 1 ? 1 : minutes;
  }

  String? _resolveModelSource() {
    final m = widget.material;

    final assets = m['ar_assets'];
    if (assets is List && assets.isNotEmpty) {
      final first = assets.first;
      final url = first['model_3d_url']?.toString();
      if (url != null && url.isNotEmpty) return url;

      final path = first['model_3d_path']?.toString();
      if (path != null && path.isNotEmpty) return path;
    }

    final url = m['model_3d_url']?.toString();
    if (url != null && url.isNotEmpty) return url;

    final path = m['model_3d_path']?.toString();
    if (path != null && path.isNotEmpty) return path;

    return null;
  }

  void _openMaterialSheet() {
    _sheetCtrl.animateTo(
      0.88,
      duration: const Duration(milliseconds: 450),
      curve: Curves.easeOutCubic,
    );
  }

  @override
  Widget build(BuildContext context) {
    final title = widget.material['title']?.toString() ?? 'Detail Materi';
    final imageUrl = widget.material['image']?.toString();

    return Scaffold(
      backgroundColor: _primary,
      body: Stack(
        children: [
          _buildThumbnailCover(title, imageUrl),
          _buildMaterialSheet(),
          const SynapseFab(),
        ],
      ),
    );
  }

  Widget _buildThumbnailCover(String title, String? imageUrl) {
    final top = MediaQuery.of(context).padding.top;

    return Positioned.fill(
      child: Stack(
        fit: StackFit.expand,
        children: [
          if (imageUrl != null && imageUrl.isNotEmpty)
            Image.network(
              imageUrl,
              fit: BoxFit.cover,
              errorBuilder: (_, __, ___) => _buildTealBackground(),
            )
          else
            _buildTealBackground(),
          Container(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [
                  Colors.black.withOpacity(0.15),
                  _primary.withOpacity(0.55),
                  _primaryDark.withOpacity(0.96),
                ],
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
              ),
            ),
          ),
          Positioned(
            top: top + 12,
            left: 18,
            child: GestureDetector(
              onTap: () => Navigator.pop(context),
              child: _circleButton(Icons.arrow_back_rounded),
            ),
          ),
          Positioned(
            top: top + 12,
            right: 18,
            child: Container(
              width: 42,
              height: 42,
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: Colors.white.withOpacity(0.20),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Image.asset(
                'assets/images/logo_synapse.png',
                color: Colors.white,
                errorBuilder: (_, __, ___) => const Icon(
                  Icons.auto_awesome_rounded,
                  color: Colors.white,
                  size: 22,
                ),
              ),
            ),
          ),
          Positioned(
            left: 24,
            right: 24,
            bottom: 178,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.20),
                    borderRadius: BorderRadius.circular(99),
                  ),
                  child: const Text(
                    'MATERI BELAJAR',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 11,
                      fontWeight: FontWeight.w900,
                      letterSpacing: 1,
                    ),
                  ),
                ),
                const SizedBox(height: 14),
                Text(
                  title,
                  maxLines: 3,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 36,
                    height: 1.05,
                    fontWeight: FontWeight.w900,
                    letterSpacing: -0.8,
                  ),
                ),
                const SizedBox(height: 18),
                _buildCoverStats(),
                const SizedBox(height: 18),
                Row(
                  children: [
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: _openMaterialSheet,
                        icon: const Icon(Icons.menu_book_rounded),
                        label: const Text('Lihat Materi'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.white,
                          foregroundColor: _primary,
                          elevation: 0,
                          padding: const EdgeInsets.symmetric(vertical: 15),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(18),
                          ),
                        ),
                      ),
                    ),
                    if (_hasPractice) ...[
                      const SizedBox(width: 12),
                      Expanded(
                        child: ElevatedButton.icon(
                          onPressed: _onPracticePressed,
                          icon: const Icon(Icons.play_circle_fill_rounded),
                          label: const Text('Latihan'),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: _primary,
                            foregroundColor: Colors.white,
                            elevation: 0,
                            padding: const EdgeInsets.symmetric(vertical: 15),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(18),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTealBackground() {
    return Container(
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [
            Color(0xFF65C8D0),
            Color(0xFF2A9D8F),
            Color(0xFF16877B),
          ],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      child: const Center(
        child: Icon(
          Icons.menu_book_rounded,
          color: Colors.white24,
          size: 120,
        ),
      ),
    );
  }

  Widget _circleButton(IconData icon) {
    return Container(
      width: 42,
      height: 42,
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.20),
        borderRadius: BorderRadius.circular(14),
      ),
      child: Icon(icon, color: Colors.white, size: 24),
    );
  }

  Widget _buildCoverStats() {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.18),
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: Colors.white.withOpacity(0.22)),
      ),
      child: Row(
        children: [
          Expanded(
            child: _coverStat(
              Icons.schedule_rounded,
              '$_readingMinutes min',
              'baca',
            ),
          ),
          _coverDivider(),
          Expanded(
            child: _coverStat(
              Icons.view_in_ar_rounded,
              _arCount > 0 ? '$_arCount 3D' : '0 3D',
              'model',
            ),
          ),
          _coverDivider(),
          Expanded(
            child: _coverStat(
              Icons.psychology_alt_rounded,
              _hasPractice ? 'Ada' : 'Belum',
              'latihan',
            ),
          ),
        ],
      ),
    );
  }

  Widget _coverStat(IconData icon, String value, String label) {
    return Column(
      children: [
        Icon(icon, color: Colors.white, size: 20),
        const SizedBox(height: 5),
        Text(
          value,
          style: const TextStyle(
            color: Colors.white,
            fontSize: 12,
            fontWeight: FontWeight.w900,
          ),
        ),
        Text(
          label,
          style: TextStyle(
            color: Colors.white.withOpacity(0.72),
            fontSize: 10,
            fontWeight: FontWeight.w600,
          ),
        ),
      ],
    );
  }

  Widget _coverDivider() {
    return Container(
      width: 1,
      height: 42,
      color: Colors.white.withOpacity(0.20),
    );
  }

  Widget _buildMaterialSheet() {
    final modelSource = _resolveModelSource();
    final rawContent =
        (widget.material['content'] ?? widget.material['body'] ?? '')
            .toString();

    return DraggableScrollableSheet(
      controller: _sheetCtrl,
      initialChildSize: 0.16,
      minChildSize: 0.16,
      maxChildSize: 0.96,
      builder: (context, scrollCtrl) {
        return Container(
          decoration: const BoxDecoration(
            color: _softBg,
            borderRadius: BorderRadius.vertical(
              top: Radius.circular(34),
            ),
          ),
          child: ListView(
            controller: scrollCtrl,
            padding: const EdgeInsets.fromLTRB(20, 12, 20, 120),
            physics: const BouncingScrollPhysics(),
            children: [
              Center(
                child: Container(
                  width: 54,
                  height: 5,
                  decoration: BoxDecoration(
                    color: Colors.grey.shade300,
                    borderRadius: BorderRadius.circular(100),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              _buildSheetIntro(),
              if (rawContent.trim().isNotEmpty) ...[
                const SizedBox(height: 14),
                _buildAudioAwareCard(rawContent),
              ],
              if (modelSource != null) ...[
                const SizedBox(height: 16),
                InlineModelViewer(
                  rawModelSource: modelSource,
                  title: widget.material['title']?.toString() ?? 'Model 3D',
                ),
              ],
              const SizedBox(height: 16),
              _buildContentCard(),
            ],
          ),
        );
      },
    );
  }

  Widget _buildSheetIntro() {
    return Row(
      children: [
        Container(
          width: 46,
          height: 46,
          decoration: BoxDecoration(
            color: _softTeal,
            borderRadius: BorderRadius.circular(16),
          ),
          child: const Icon(
            Icons.menu_book_rounded,
            color: _primary,
            size: 24,
          ),
        ),
        const SizedBox(width: 12),
        const Expanded(
          child: Text(
            'Mulai Belajar',
            style: TextStyle(
              color: _textDark,
              fontSize: 21,
              fontWeight: FontWeight.w900,
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildAudioAwareCard(String rawContent) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: const Color(0xFFEAF7FF),
        borderRadius: BorderRadius.circular(24),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(
            children: [
              Icon(Icons.volume_up_rounded, color: Color(0xFF2D9CDB)),
              SizedBox(width: 8),
              Expanded(
                child: Text(
                  'Materi ini bisa kamu dengarkan',
                  style: TextStyle(
                    color: _textDark,
                    fontSize: 14,
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          AudioCapsule(
            key: ValueKey(widget.material['id'].toString()),
            rawContent: rawContent,
          ),
        ],
      ),
    );
  }

  Widget _buildContentCard() {
    return Container(
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 22),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(28),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.045),
            blurRadius: 18,
            offset: const Offset(0, 7),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Isi Materi',
            style: TextStyle(
              color: _textDark,
              fontSize: 19,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 16),
          _buildHtmlContent(),
        ],
      ),
    );
  }

  Widget _buildHtmlContent() {
    return Html(
      data: widget.material['content'] ??
          widget.material['body'] ??
          '<p>Isi materi belum tersedia.</p>',
      style: {
        "body": Style(
          fontSize: FontSize(15),
          lineHeight: LineHeight(1.75),
          color: const Color(0xFF334155),
          margin: Margins.zero,
          padding: HtmlPaddings.zero,
        ),
        "p": Style(margin: Margins.only(bottom: 14)),
        "h1": Style(
          fontSize: FontSize(22),
          fontWeight: FontWeight.w900,
          color: _primary,
          margin: Margins.symmetric(vertical: 16),
        ),
        "h2": Style(
          fontSize: FontSize(20),
          fontWeight: FontWeight.w900,
          color: _primary,
          margin: Margins.symmetric(vertical: 14),
        ),
        "h3": Style(
          fontSize: FontSize(17),
          fontWeight: FontWeight.w900,
          color: _textDark,
          margin: Margins.symmetric(vertical: 12),
        ),
        "strong": Style(fontWeight: FontWeight.w900, color: _primary),
        "blockquote": Style(
          backgroundColor: const Color(0xFFEAFBF5),
          padding: HtmlPaddings.all(14),
          margin: Margins.symmetric(vertical: 12),
          border: const Border(
            left: BorderSide(color: _primary, width: 4),
          ),
          color: _textDark,
        ),
        "code": Style(
          backgroundColor: const Color(0xFFF1F5F9),
          padding: HtmlPaddings.symmetric(horizontal: 6, vertical: 2),
          fontFamily: 'Courier',
          color: const Color(0xFFE11D48),
          fontSize: FontSize(13),
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
          color: _primary,
          textDecoration: TextDecoration.underline,
        ),
      },
      extensions: [
        TagExtension(
          tagsToExtend: {"img"},
          builder: (extensionContext) {
            String imgUrl = extensionContext.attributes['src'] ?? '';
            final serverDomain = AppConstants.baseUrl.replaceAll('/api', '');

            if (imgUrl.isNotEmpty) {
              if (imgUrl.startsWith('/storage')) {
                imgUrl = '$serverDomain$imgUrl';
              } else if (imgUrl.contains('localhost') ||
                  imgUrl.contains('127.0.0.1') ||
                  imgUrl.contains('10.0.2.2')) {
                imgUrl = imgUrl.replaceAll(
                  RegExp(
                    r'http://(localhost|127\.0\.0\.1|10\.0\.2\.2)(:\d+)?',
                  ),
                  serverDomain,
                );
              }
            }

            return Padding(
              padding: const EdgeInsets.symmetric(vertical: 14),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(18),
                child: Image.network(
                  imgUrl,
                  width: double.infinity,
                  fit: BoxFit.contain,
                  errorBuilder: (_, __, ___) => Container(
                    height: 150,
                    width: double.infinity,
                    color: Colors.grey[100],
                    child: const Center(
                      child: Icon(Icons.broken_image_rounded),
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

  void _onPracticePressed() async {
    await AudioCapsule.stopActiveAudio();

    final material = widget.material;

    if (!mounted) return;

    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => PracticeScreen(
          materialId: material['id'].toString(),
          materialTitle: material['title']?.toString() ?? 'Latihan',
        ),
      ),
    );
  }
}