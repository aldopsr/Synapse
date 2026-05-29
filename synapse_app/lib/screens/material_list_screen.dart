import 'package:flutter/material.dart';
import '../services/material_service.dart';
import '../services/quiz_service.dart';
import '../models/quiz_model.dart';
import 'material_detail_screen.dart';
import 'quiz_screen.dart';

class MaterialListScreen extends StatefulWidget {
  final String courseId;
  final String courseTitle;

  const MaterialListScreen({
    super.key,
    required this.courseId,
    required this.courseTitle,
  });

  @override
  State<MaterialListScreen> createState() => _MaterialListScreenState();
}

class _MaterialListScreenState extends State<MaterialListScreen>
    with SingleTickerProviderStateMixin {
  static const Color _primary = Color(0xFF2A9D8F);
  static const Color _darkTeal = Color(0xFF1F7A6E);
  static const Color _softBg = Color(0xFFF6F7FB);
  static const Color _softTeal = Color(0xFFE7F7F5);
  static const Color _textDark = Color(0xFF1F2937);
  static const Color _textMuted = Color(0xFF94A3B8);

  List<dynamic> _materials = [];
  List<QuizModel> _quizzes = [];
  bool _isLoading = true;

  final TextEditingController _searchCtrl = TextEditingController();
  String _search = '';

  late TabController _tabCtrl;

  List<dynamic> get _filteredMaterials {
    if (_search.trim().isEmpty) return _materials;

    return _materials.where((m) {
      final title = (m['title'] ?? '').toString().toLowerCase();
      final keyword = _search.trim().toLowerCase();
      return title.contains(keyword);
    }).toList();
  }

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(length: 2, vsync: this);
    _loadData();
  }

  @override
  void dispose() {
    _tabCtrl.dispose();
    _searchCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);

    final results = await Future.wait([
      MaterialService().getMaterialsByCourse(widget.courseId),
      QuizService().getQuizzes(courseId: widget.courseId),
    ]);

    if (!mounted) return;

    setState(() {
      _materials = results[0] as List;
      _quizzes = results[1] as List<QuizModel>;
      _isLoading = false;
    });
  }

  @override
Widget build(BuildContext context) {
  return Scaffold(
    backgroundColor: _primary,
    body: _isLoading
        ? const Center(
            child: CircularProgressIndicator(
              color: Colors.white,
            ),
          )
        : Column(
            children: [
              _buildHeader(context),

              Expanded(
                child: Container(
                  width: double.infinity,
                  decoration: const BoxDecoration(
                    color: _softBg,
                    borderRadius: BorderRadius.only(
                      topLeft: Radius.circular(38),
                      topRight: Radius.circular(38),
                    ),
                  ),
                  child: Column(
                    children: [
                      const SizedBox(height: 0),

                      Container(
                        width: 54,
                        height: 5,
                        decoration: BoxDecoration(
                          color: Colors.grey.shade300,
                          borderRadius: BorderRadius.circular(100),
                        ),
                      ),

                      const SizedBox(height: 16),

                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 20),
                        child: _buildSearchBar(),
                      ),

                      const SizedBox(height: 12),

                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 20),
                        child: _buildTabBar(),
                      ),

                      const SizedBox(height: 12),

                      Expanded(
                        child: TabBarView(
                          controller: _tabCtrl,
                          children: [
                            _buildMaterialTab(),
                            _buildQuizTab(),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
  );
}

  Widget _buildHeader(BuildContext context) {
    final top = MediaQuery.of(context).padding.top;

    return Container(
      height: 220,
      width: double.infinity,
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
      child: Stack(
        children: [
          Positioned(
            top: -70,
            left: -55,
            child: _blob(190, Colors.white.withOpacity(0.12)),
          ),
          Positioned(
            top: 10,
            right: -55,
            child: _blob(165, Colors.teal.shade900.withOpacity(0.16)),
          ),
          Positioned(
            bottom: 20,
            left: 90,
            child: _blob(115, Colors.white.withOpacity(0.12)),
          ),

          Positioned(
            top: top + 12,
            left: 18,
            child: GestureDetector(
              onTap: () => Navigator.pop(context),
              child: Container(
                width: 42,
                height: 42,
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.20),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: const Icon(
                  Icons.arrow_back_rounded,
                  color: Colors.white,
                  size: 24,
                ),
              ),
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
            left: 28,
            right: 28,
            top: top + 68,
            child: SizedBox(
              height: 90,
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Flexible(
                    child: Text(
                      widget.courseTitle,
                      textAlign: TextAlign.center,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 26,
                        height: 1.05,
                        fontWeight: FontWeight.w900,
                        letterSpacing: -0.6,
                      ),
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    '${_materials.length} materi • ${_quizzes.length} kuis',
                    style: TextStyle(
                      color: Colors.white.withOpacity(0.88),
                      fontSize: 13,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  

  Widget _buildTabBar() {
    return Container(
      height: 48,
      padding: const EdgeInsets.all(6),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 14,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: TabBar(
        controller: _tabCtrl,
        indicator: BoxDecoration(
          color: _primary,
          borderRadius: BorderRadius.circular(17),
        ),
        labelColor: Colors.white,
        unselectedLabelColor: _textMuted,
        dividerColor: Colors.transparent,
        indicatorSize: TabBarIndicatorSize.tab,
        labelStyle: const TextStyle(
          fontSize: 13,
          fontWeight: FontWeight.w900,
        ),
        unselectedLabelStyle: const TextStyle(
          fontSize: 13,
          fontWeight: FontWeight.w700,
        ),
        tabs: const [
          Tab(text: 'Materi'),
          Tab(text: 'Kuis'),
        ],
      ),
    );
  }

  Widget _blob(double size, Color color) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(size),
      ),
    );
  }

  Widget _buildSearchBar() {
    return Container(
      height: 48,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.055),
            blurRadius: 18,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: TextField(
        controller: _searchCtrl,
        onChanged: (v) => setState(() => _search = v),
        cursorColor: _primary,
        style: const TextStyle(
          color: _textDark,
          fontWeight: FontWeight.w600,
        ),
        decoration: InputDecoration(
          hintText: 'Cari materi...',
          hintStyle: const TextStyle(
            color: _textMuted,
            fontSize: 14,
          ),
          prefixIcon: const Icon(
            Icons.search_rounded,
            color: _primary,
          ),
          suffixIcon: _search.isNotEmpty
              ? IconButton(
                  icon: const Icon(
                    Icons.close_rounded,
                    color: _textMuted,
                  ),
                  onPressed: () {
                    _searchCtrl.clear();
                    setState(() => _search = '');
                  },
                )
              : null,
          border: InputBorder.none,
          contentPadding: const EdgeInsets.symmetric(vertical: 16),
        ),
      ),
    );
  }

  Widget _buildMaterialTab() {
  if (_materials.isEmpty) {
    return _buildEmpty(
      icon: Icons.menu_book_outlined,
      title: 'Belum ada materi',
      subtitle: 'Materi untuk mata kuliah ini belum tersedia.',
    );
  }

  if (_filteredMaterials.isEmpty) {
    return _buildEmpty(
      icon: Icons.search_off_rounded,
      title: 'Materi tidak ditemukan',
      subtitle: 'Coba cari dengan kata kunci lain.',
    );
  }

  return RefreshIndicator(
    color: _primary,
    onRefresh: _loadData,
    child: GridView.builder(
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 40),
      physics: const BouncingScrollPhysics(),
      itemCount: _filteredMaterials.length,
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        crossAxisSpacing: 14,
        mainAxisSpacing: 14,
        childAspectRatio: 0.82,
      ),
      itemBuilder: (ctx, i) => _buildMaterialCard(_filteredMaterials[i], i),
    ),
  );
}

  Widget _buildMaterialCard(dynamic item, int index) {
    final String? imageUrl = item['image'];
    final title = item['title'] ?? 'Materi';

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.055),
            blurRadius: 18,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(24),
          onTap: () => Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => MaterialDetailScreen(material: item),
            ),
          ),
          child: Padding(
            padding: const EdgeInsets.all(10),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Expanded(
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(20),
                    child: Stack(
                      children: [
                        Positioned.fill(child: _buildThumbnail(imageUrl)),
                        Positioned(
                          top: 10,
                          right: 10,
                          child: Container(
                            width: 34,
                            height: 34,
                            decoration: BoxDecoration(
                              color: Colors.white.withOpacity(0.92),
                              shape: BoxShape.circle,
                            ),
                            child: const Icon(
                              Icons.play_arrow_rounded,
                              color: _primary,
                              size: 22,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 11),
                Text(
                  title,
                  textAlign: TextAlign.left,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    color: _textDark,
                    fontSize: 13.5,
                    height: 1.25,
                    fontWeight: FontWeight.w900,
                  ),
                ),
                const SizedBox(height: 9),
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 9,
                        vertical: 5,
                      ),
                      decoration: BoxDecoration(
                        color: _softTeal,
                        borderRadius: BorderRadius.circular(30),
                      ),
                      child: const Text(
                        'Belajar',
                        style: TextStyle(
                          color: _primary,
                          fontSize: 10,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                    ),
                    const Spacer(),
                    const Icon(
                      Icons.arrow_forward_rounded,
                      color: _primary,
                      size: 18,
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildThumbnail(String? imageUrl) {
    if (imageUrl == null || imageUrl.isEmpty) {
      return Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [_primary, _darkTeal],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
        ),
        child: Center(
          child: Container(
            width: 62,
            height: 62,
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.18),
              shape: BoxShape.circle,
            ),
            child: const Icon(
              Icons.menu_book_rounded,
              size: 34,
              color: Colors.white,
            ),
          ),
        ),
      );
    }

    return Image.network(
      imageUrl,
      fit: BoxFit.cover,
      loadingBuilder: (ctx, child, progress) {
        if (progress == null) return child;

        return Container(
          color: _softTeal,
          child: const Center(
            child: CircularProgressIndicator(
              strokeWidth: 2,
              color: _primary,
            ),
          ),
        );
      },
      errorBuilder: (_, __, ___) => Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [_primary, _darkTeal],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
        ),
        child: const Center(
          child: Icon(
            Icons.menu_book_rounded,
            size: 42,
            color: Colors.white,
          ),
        ),
      ),
    );
  }

  Widget _buildQuizTab() {
    if (_quizzes.isEmpty) {
      return _buildEmpty(
        icon: Icons.quiz_outlined,
        title: 'Belum ada kuis',
        subtitle: 'Kuis untuk mata kuliah ini belum tersedia.',
      );
    }

    return RefreshIndicator(
      color: _primary,
      onRefresh: _loadData,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(20, 18, 20, 40),
        physics: const BouncingScrollPhysics(),
        children: List.generate(
          _quizzes.length,
          (i) => _buildQuizCard(_quizzes[i], i),
        ),
      ),
    );
  }

  Widget _buildQuizCard(QuizModel quiz, int index) {
    final colors = [
      const Color(0xFFFFEEF5),
      const Color(0xFFFFF7DF),
      const Color(0xFFEAF7FF),
      const Color(0xFFEAFBF5),
    ];

    final iconColors = [
      const Color(0xFFE75480),
      const Color(0xFFF4A62A),
      const Color(0xFF2D9CDB),
      const Color(0xFF2A9D8F),
    ];

    final bgColor = colors[index % colors.length];
    final iconColor = iconColors[index % iconColors.length];

    return Container(
      margin: const EdgeInsets.only(bottom: 15),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(24),
        boxShadow: [
          BoxShadow(
            color: iconColor.withOpacity(0.10),
            blurRadius: 18,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(24),
          onTap: () => Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => QuizScreen(quiz: quiz),
            ),
          ),
          child: Padding(
            padding: const EdgeInsets.all(15),
            child: Row(
              children: [
                Container(
                  width: 58,
                  height: 58,
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.75),
                    borderRadius: BorderRadius.circular(18),
                  ),
                  child: Icon(
                    Icons.psychology_alt_rounded,
                    color: iconColor,
                    size: 29,
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        quiz.title,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          color: _textDark,
                          fontSize: 15,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                      const SizedBox(height: 8),
                      Row(
                        children: [
                          Icon(
                            Icons.timer_outlined,
                            size: 14,
                            color: iconColor,
                          ),
                          const SizedBox(width: 5),
                          Text(
                            '${quiz.durationMinutes} menit',
                            style: TextStyle(
                              color: _textDark.withOpacity(0.48),
                              fontSize: 12,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 10),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 13,
                    vertical: 10,
                  ),
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.8),
                    borderRadius: BorderRadius.circular(15),
                  ),
                  child: Text(
                    'Mulai',
                    style: TextStyle(
                      color: iconColor,
                      fontSize: 12,
                      fontWeight: FontWeight.w900,
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

  Widget _buildEmpty({
    required IconData icon,
    required String title,
    required String subtitle,
  }) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(38),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 88,
              height: 88,
              decoration: const BoxDecoration(
                color: _softTeal,
                shape: BoxShape.circle,
              ),
              child: Icon(icon, size: 42, color: _primary),
            ),
            const SizedBox(height: 10),
            Text(
              title,
              style: const TextStyle(
                fontSize: 17,
                fontWeight: FontWeight.w900,
                color: _textDark,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              subtitle,
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 13,
                color: _textMuted,
                fontWeight: FontWeight.w500,
              ),
            ),
          ],
        ),
      ),
    );
  }
}