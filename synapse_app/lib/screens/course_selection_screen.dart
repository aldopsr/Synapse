// lib/screens/course_selection_screen.dart
import 'dart:math';
import 'package:flutter/material.dart';
import '../services/material_service.dart';
import 'material_list_screen.dart';

class CourseSelectionScreen extends StatefulWidget {
  const CourseSelectionScreen({super.key});

  @override
  State<CourseSelectionScreen> createState() => _CourseSelectionScreenState();
}

class _CourseSelectionScreenState extends State<CourseSelectionScreen> {
  static const Color _primary = Color(0xFF2A9D8F);
  static const Color _bg = Color(0xFFF6F7FB);
  static const Color _darkText = Color(0xFF1F2937);
  static const Color _mutedText = Color(0xFF94A3B8);

  final TextEditingController _searchCtrl = TextEditingController();
  final TextEditingController _sheetSearchCtrl = TextEditingController();

  List<dynamic> _courses = [];
  List<dynamic> _homeCourses = [];

  bool _isLoading = true;
  String _search = '';
  String _sheetSearch = '';

  final List<Color> _cardColors = const [
    Color(0xFFFFEEF5),
    Color(0xFFFFF7DF),
    Color(0xFFEAF7FF),
    Color(0xFFEAFBF5),
    Color(0xFFF1EEFF),
  ];

  final List<Color> _iconColors = const [
    Color(0xFFE75480),
    Color(0xFFF4A62A),
    Color(0xFF2D9CDB),
    Color(0xFF2A9D8F),
    Color(0xFF7C3AED),
  ];

  final List<IconData> _icons = const [
    Icons.phone_android_rounded,
    Icons.lan_rounded,
    Icons.storage_rounded,
    Icons.router_rounded,
    Icons.memory_rounded,
  ];

  @override
  void initState() {
    super.initState();
    _loadCourses();
    
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    _sheetSearchCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadCourses() async {
    final courses = await MaterialService().getCourses();

    final shuffled = List<dynamic>.from(courses);
    shuffled.shuffle(Random());

    if (!mounted) return;

    setState(() {
      _courses = courses;
      _homeCourses = shuffled.take(3).toList();
      _isLoading = false;
    });
  }

  List<dynamic> get _filteredHomeCourses {
    if (_search.trim().isEmpty) {
      final source = _homeCourses.isNotEmpty ? _homeCourses : _courses;
      return source.take(3).toList();
    }

    return _courses.where((course) {
      final title = (course['title'] ?? '').toString().toLowerCase();
      final desc = (course['description'] ?? '').toString().toLowerCase();
      final keyword = _search.trim().toLowerCase();

      return title.contains(keyword) || desc.contains(keyword);
    }).take(3).toList();
  }

  List<dynamic> get _filteredSheetCourses {
    if (_sheetSearch.isEmpty) return _courses;

    return _courses.where((course) {
      final title = (course['title'] ?? '').toString().toLowerCase();
      final desc = (course['description'] ?? '').toString().toLowerCase();
      final keyword = _sheetSearch.toLowerCase();

      return title.contains(keyword) || desc.contains(keyword);
    }).toList();
  }

  void _openCourse(dynamic course) {
    final id = (course['_id'] ?? course['id'])?.toString() ?? '';
    final title = course['title']?.toString() ?? 'Mata Kuliah';

    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => MaterialListScreen(
          courseId: id,
          courseTitle: title,
        ),
      ),
    );
  }

  void _showAllCoursesSheet() {
    _sheetSearchCtrl.clear();
    _sheetSearch = '';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) {
        return StatefulBuilder(
          builder: (context, setSheetState) {
            return DraggableScrollableSheet(
              initialChildSize: 0.9,
              minChildSize: 0.55,
              maxChildSize: 0.95,
              builder: (context, scrollController) {
                return Container(
                  decoration: const BoxDecoration(
                    color: _primary,
                    borderRadius: BorderRadius.vertical(
                      top: Radius.circular(34),
                    ),
                  ),
                  child: Column(
                    children: [
                      const SizedBox(height: 12),
                      Container(
                        width: 54,
                        height: 5,
                        decoration: BoxDecoration(
                          color: Colors.white54,
                          borderRadius: BorderRadius.circular(100),
                        ),
                      ),
                      Padding(
                        padding: const EdgeInsets.fromLTRB(22, 22, 16, 14),
                        child: Row(
                          children: [
                            const Expanded(
                              child: Text(
                                'All Courses',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontSize: 28,
                                  fontWeight: FontWeight.w900,
                                ),
                              ),
                            ),
                            GestureDetector(
                              onTap: () => Navigator.pop(context),
                              child: Container(
                                width: 42,
                                height: 42,
                                decoration: BoxDecoration(
                                  color: Colors.white.withOpacity(0.18),
                                  shape: BoxShape.circle,
                                ),
                                child: const Icon(
                                  Icons.close_rounded,
                                  color: Colors.white,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 20),
                        child: Container(
                          height: 52,
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(18),
                          ),
                          child: TextField(
                            controller: _sheetSearchCtrl,
                            cursorColor: _primary,
                            onChanged: (value) {
                              setSheetState(() => _sheetSearch = value);
                            },
                            decoration: InputDecoration(
                              hintText: 'Search course...',
                              hintStyle: const TextStyle(
                                color: _mutedText,
                                fontSize: 14,
                              ),
                              prefixIcon: const Icon(
                                Icons.search_rounded,
                                color: _primary,
                              ),
                              suffixIcon: _sheetSearch.isNotEmpty
                                  ? IconButton(
                                      onPressed: () {
                                        _sheetSearchCtrl.clear();
                                        setSheetState(() => _sheetSearch = '');
                                      },
                                      icon: const Icon(
                                        Icons.close_rounded,
                                        color: _mutedText,
                                      ),
                                    )
                                  : null,
                              border: InputBorder.none,
                              contentPadding:
                                  const EdgeInsets.symmetric(vertical: 15),
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(height: 18),
                      Expanded(
                        child: Container(
                          decoration: const BoxDecoration(
                            color: _bg,
                            borderRadius: BorderRadius.vertical(
                              top: Radius.circular(28),
                            ),
                          ),
                          child: _filteredSheetCourses.isEmpty
                              ? _buildStateView(
                                  icon: Icons.search_off_rounded,
                                  title: 'Course tidak ditemukan',
                                  subtitle: 'Coba kata kunci lain ya.',
                                )
                              : ListView.builder(
                                  controller: scrollController,
                                  padding:
                                      const EdgeInsets.fromLTRB(20, 22, 20, 34),
                                  itemCount: _filteredSheetCourses.length,
                                  itemBuilder: (context, index) {
                                    return Padding(
                                      padding:
                                          const EdgeInsets.only(bottom: 14),
                                      child: _buildCourseCard(
                                        _filteredSheetCourses[index],
                                        index,
                                      ),
                                    );
                                  },
                                ),
                        ),
                      ),
                    ],
                  ),
                );
              },
            );
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _bg,
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(
                color: _primary,
                strokeWidth: 2,
              ),
            )
          : RefreshIndicator(
              color: _primary,
              onRefresh: _loadCourses,
              child: CustomScrollView(
                physics: const BouncingScrollPhysics(),
                slivers: [
                  SliverToBoxAdapter(child: _buildHero()),
                  SliverToBoxAdapter(child: _buildSearchBar()),
                  SliverToBoxAdapter(child: _buildSectionTitle()),
                  _courses.isEmpty
                      ? SliverFillRemaining(
                          child: _buildStateView(
                            icon: Icons.school_outlined,
                            title: 'Belum ada course',
                            subtitle: 'Course akan muncul di sini.',
                          ),
                        )
                      : _filteredHomeCourses.isEmpty
                          ? SliverFillRemaining(
                              child: _buildStateView(
                                icon: Icons.search_off_rounded,
                                title: 'Course tidak ditemukan',
                                subtitle: 'Coba kata kunci lain ya.',
                              ),
                            )
                          : SliverList(
                              delegate: SliverChildBuilderDelegate(
                                (context, index) {
                                  return Padding(
                                    padding: EdgeInsets.fromLTRB(
                                      20,
                                      index == 0 ? 4 : 0,
                                      20,
                                      index == _filteredHomeCourses.length - 1
                                          ? 36
                                          : 14,
                                    ),
                                    child: _buildCourseCard(
                                      _filteredHomeCourses[index],
                                      index,
                                    ),
                                  );
                                },
                                childCount: _filteredHomeCourses.length,
                              ),
                            ),
                ],
              ),
            ),
    );
  }

  Widget _buildHero() {
    final top = MediaQuery.of(context).padding.top;

    return Container(
      height: 310,
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
        borderRadius: BorderRadius.only(
          bottomLeft: Radius.circular(44),
          bottomRight: Radius.circular(44),
        ),
      ),
      child: Stack(
        children: [
          Positioned(
            top: -74,
            left: -54,
            child: _blob(188, Colors.white.withOpacity(0.12)),
          ),
          Positioned(
            top: -18,
            right: -38,
            child: _blob(150, Colors.teal.shade900.withOpacity(0.16)),
          ),
          Positioned(
            bottom: 42,
            right: 34,
            child: _blob(102, Colors.white.withOpacity(0.14)),
          ),
          Positioned(
            top: top + 52,
            left: 24,
            right: 24,
            child: Row(
              children: [
                const Expanded(
                  child: Text(
                    'Welcome\nback!',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 44,
                      height: 1.04,
                      fontWeight: FontWeight.w900,
                      letterSpacing: -1,
                    ),
                  ),
                ),
                Container(
                  width: 128,
                  height: 128,
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.18),
                    shape: BoxShape.circle,
                  ),
                  child: Center(
                    child: Image.asset(
                      'assets/images/logo_synapse.png',
                      width: 86,
                      height: 86,
                      errorBuilder: (_, __, ___) => const Icon(
                        Icons.auto_awesome_rounded,
                        size: 76,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
          Positioned(
            left: 24,
            right: 24,
            top: top + 186,
            child: Text(
              'Mau belajar apa hari ini?',
              style: TextStyle(
                color: Colors.white.withOpacity(0.94),
                fontSize: 18,
                fontWeight: FontWeight.w700,
              ),
            ),
          ),
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
    return Padding(
      padding: const EdgeInsets.fromLTRB(22, 20, 22, 20),
      child: Container(
        height: 56,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.07),
              blurRadius: 20,
              offset: const Offset(0, 8),
            ),
          ],
        ),
        child: TextField(
          controller: _searchCtrl,
          onChanged: (value) => setState(() => _search = value),
          cursorColor: _primary,
          decoration: InputDecoration(
            hintText: 'Search for new knowledge!',
            hintStyle: const TextStyle(color: _mutedText, fontSize: 14),
            prefixIcon: const Icon(Icons.search_rounded, color: _primary),
            suffixIcon: _search.isNotEmpty
                ? IconButton(
                    onPressed: () {
                      _searchCtrl.clear();
                      setState(() => _search = '');
                    },
                    icon: const Icon(
                      Icons.close_rounded,
                      color: _mutedText,
                    ),
                  )
                : null,
            border: InputBorder.none,
            contentPadding: const EdgeInsets.symmetric(vertical: 16),
          ),
        ),
      ),
    );
  }

  Widget _buildSectionTitle() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(22, 0, 22, 14),
      child: Row(
        children: [
          const Expanded(
            child: Text(
              'Courses',
              style: TextStyle(
                color: _darkText,
                fontSize: 21,
                fontWeight: FontWeight.w900,
              ),
            ),
          ),
          if (_courses.length > 1)
            GestureDetector(
              onTap: _showAllCoursesSheet,
              child: const Text(
                'See all',
                style: TextStyle(
                  color: _primary,
                  fontSize: 14,
                  fontWeight: FontWeight.w900,
                ),
              ),
            )
          else
            Text(
              '${_courses.length} tersedia',
              style: const TextStyle(
                color: _mutedText,
                fontSize: 12,
                fontWeight: FontWeight.w700,
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildCourseCard(dynamic course, int index) {
    final id = (course['_id'] ?? course['id'])?.toString() ?? '';
    final title = course['title']?.toString() ?? 'Mata Kuliah';
    final desc = course['description']?.toString() ?? 'Mulai belajar sekarang';
    final code = course['code']?.toString() ?? '';

    final bgColor = _cardColors[index % _cardColors.length];
    final iconColor = _iconColors[index % _iconColors.length];
    final icon = _icons[index % _icons.length];

    return Material(
      color: Colors.transparent,
      child: InkWell(
        borderRadius: BorderRadius.circular(22),
        onTap: () => _openCourse(course),
        child: Container(
          constraints: const BoxConstraints(minHeight: 96),
          padding: const EdgeInsets.all(15),
          decoration: BoxDecoration(
            color: bgColor,
            borderRadius: BorderRadius.circular(22),
            boxShadow: [
              BoxShadow(
                color: iconColor.withOpacity(0.11),
                blurRadius: 18,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          child: Row(
            children: [
              Container(
                width: 58,
                height: 58,
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.75),
                  borderRadius: BorderRadius.circular(18),
                ),
                child: Icon(icon, color: iconColor, size: 30),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    if (code.isNotEmpty) ...[
                      Text(
                        code.toUpperCase(),
                        style: TextStyle(
                          color: iconColor,
                          fontSize: 10,
                          fontWeight: FontWeight.w900,
                          letterSpacing: 0.8,
                        ),
                      ),
                      const SizedBox(height: 3),
                    ],
                    Text(
                      title,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        color: _darkText,
                        fontSize: 15,
                        height: 1.2,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    const SizedBox(height: 5),
                    Text(
                      desc,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        color: _darkText.withOpacity(0.45),
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
              ),
              Container(
                width: 36,
                height: 36,
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.85),
                  shape: BoxShape.circle,
                ),
                child: Icon(
                  Icons.play_arrow_rounded,
                  color: iconColor,
                  size: 22,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildStateView({
    required IconData icon,
    required String title,
    required String subtitle,
  }) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(34),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, size: 64, color: Colors.grey[300]),
            const SizedBox(height: 16),
            Text(
              title,
              style: const TextStyle(
                color: _darkText,
                fontSize: 16,
                fontWeight: FontWeight.w900,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              subtitle,
              textAlign: TextAlign.center,
              style: const TextStyle(
                color: _mutedText,
                fontSize: 13,
                height: 1.4,
              ),
            ),
          ],
        ),
      ),
    );
  }
}