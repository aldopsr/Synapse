// lib/screens/materials_screen.dart
import 'package:flutter/material.dart';
import '../services/material_service.dart';
import '../services/quiz_service.dart';
import '../models/quiz_model.dart';
import 'material_detail_screen.dart';
import 'home_screen.dart';
import 'quiz_list_screen.dart';
import 'quiz_screen.dart';

class MaterialsScreen extends StatefulWidget {
  const MaterialsScreen({super.key});

  @override
  State<MaterialsScreen> createState() => _MaterialsScreenState();
}

class _MaterialsScreenState extends State<MaterialsScreen> {
  List<dynamic> _courses   = [];
  List<dynamic> _materials = [];
  bool _isCoursesLoading   = true;
  bool _isMaterialsLoading = true;
  String? _selectedCourseId;
  List<QuizModel> _quizzes        = [];
  bool _isQuizzesLoading          = false;

  static const Color _primaryColor = Color(0xFF2A9D8F);
  static const Color _softBg       = Color(0xFFF0FDFB);

  @override
  void initState() {
    super.initState();
    _loadInitialData();
  }

  Future<void> _loadInitialData() async {
    final courses   = await MaterialService().getCourses();
    final materials = await MaterialService().getMaterials();
    if (mounted) {
      materials.shuffle();
      setState(() {
        _courses   = courses;
        _materials = materials;
        _isCoursesLoading   = false;
        _isMaterialsLoading = false;
      });
    }
  }

  Future<void> _onCourseSelected(String? courseId) async {
    if (_selectedCourseId == courseId) return;

    // Reset dulu — pastikan UI kosong sebelum fetch baru dimulai
    setState(() {
      _selectedCourseId   = courseId;
      _isMaterialsLoading = true;
      _quizzes            = [];
      _isQuizzesLoading   = courseId != null;
    });

    // Simpan courseId lokal untuk guard race condition
    final String? targetCourse = courseId;

    // Fetch materi dan kuis secara paralel
    final results = await Future.wait([
      courseId == null
          ? MaterialService().getMaterials()
          : MaterialService().getMaterialsByCourse(courseId),
      if (courseId != null)
        QuizService().getQuizzes(courseId: courseId)
      else
        Future.value(<QuizModel>[]),
    ]);

    if (!mounted) return;

    // Guard: kalau user sudah pindah matkul lagi, abaikan hasil ini
    if (_selectedCourseId != targetCourse) return;

    final newMaterials = results[0] as List;
    newMaterials.shuffle();
    setState(() {
      _materials          = newMaterials;
      _isMaterialsLoading = false;
      _quizzes            = List<QuizModel>.from(results[1]);
      _isQuizzesLoading   = false;
    });
  }

  // ── Course selector chip ──────────────────────────────────
  Widget _buildCourseSelector() {
    if (_isCoursesLoading) {
      return const Padding(
        padding: EdgeInsets.all(20),
        child: Center(child: CircularProgressIndicator(color: _primaryColor)),
      );
    }
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: _primaryColor.withOpacity(0.5), width: 2),
        boxShadow: [BoxShadow(color: _primaryColor.withOpacity(0.15), blurRadius: 15, offset: const Offset(0, 5))],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(children: [
            Icon(Icons.auto_awesome_mosaic_rounded, color: _primaryColor),
            SizedBox(width: 8),
            Text('Pilih Mata Kuliah',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: _primaryColor)),
          ]),
          const SizedBox(height: 16),
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            physics: const BouncingScrollPhysics(),
            child: Row(
              children: [
                _buildCourseChip(title: "Semua", id: null),
                ..._courses.map((c) => _buildCourseChip(
                  title: c['title'] ?? c['name'] ?? 'Matkul',
                  id: (c['_id'] ?? c['id'])?.toString(),
                )),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCourseChip({required String title, required String? id}) {
    final bool isSelected = _selectedCourseId == id;
    return GestureDetector(
      onTap: () => _onCourseSelected(id),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 300),
        margin: const EdgeInsets.only(right: 12),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        decoration: BoxDecoration(
          color: isSelected ? _primaryColor : _softBg,
          borderRadius: BorderRadius.circular(25),
          border: Border.all(color: isSelected ? Colors.transparent : _primaryColor.withOpacity(0.5)),
          boxShadow: isSelected
              ? [BoxShadow(color: _primaryColor.withOpacity(0.3), blurRadius: 8, offset: const Offset(0, 3))]
              : [],
        ),
        child: Text(title,
            style: TextStyle(
              color: isSelected ? Colors.white : _primaryColor,
              fontWeight: FontWeight.bold, fontSize: 13,
            )),
      ),
    );
  }

  Widget _buildSectionHeader({
    required String title,
    required IconData icon,
    required VoidCallback onTap,
    int? count,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.fromLTRB(20, 20, 20, 10),
        child: Row(
          children: [
            Icon(icon, color: _primaryColor, size: 20),
            const SizedBox(width: 8),
            Expanded(
              child: Text(title,
                  style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: Colors.black87)),
            ),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
              decoration: BoxDecoration(
                color: _primaryColor.withOpacity(0.08),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: _primaryColor.withOpacity(0.25)),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  if (count != null) ...[
                    Text('$count', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: _primaryColor)),
                    const SizedBox(width: 4),
                  ],
                  const Icon(Icons.arrow_forward_ios_rounded, color: _primaryColor, size: 11),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ── Bottom sheet semua materi ─────────────────────────────
  void _showAllMaterialsSheet(BuildContext context, List<dynamic> allMaterials) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) {
        String q = '';
        return StatefulBuilder(builder: (context, setSheet) {
          final filtered = q.isEmpty
              ? allMaterials
              : allMaterials.where((m) =>
                  (m['title'] ?? '').toString().toLowerCase().contains(q.toLowerCase())).toList();
          return Container(
            height: MediaQuery.of(context).size.height * 0.85,
            decoration: const BoxDecoration(
              color: _primaryColor,
              borderRadius: BorderRadius.vertical(top: Radius.circular(30)),
            ),
            child: Column(
              children: [
                Center(
                  child: Container(
                    margin: const EdgeInsets.only(top: 12, bottom: 20),
                    width: 40, height: 4,
                    decoration: BoxDecoration(color: Colors.white.withOpacity(0.5), borderRadius: BorderRadius.circular(10)),
                  ),
                ),
                const Padding(
                  padding: EdgeInsets.symmetric(horizontal: 20),
                  child: Align(
                    alignment: Alignment.centerLeft,
                    child: Text('Materi Tersedia',
                        style: TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.bold)),
                  ),
                ),
                const SizedBox(height: 16),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 20),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(30)),
                    child: TextField(
                      onChanged: (v) => setSheet(() => q = v),
                      decoration: const InputDecoration(
                        hintText: 'Cari materi...',
                        border: InputBorder.none,
                        enabledBorder: InputBorder.none,
                        focusedBorder: InputBorder.none,
                        suffixIcon: Icon(Icons.search, color: Colors.black54),
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                Expanded(
                  child: filtered.isEmpty
                      ? const Center(child: Text('Materi tidak ditemukan', style: TextStyle(color: Colors.white)))
                      : GridView.builder(
                          padding: const EdgeInsets.fromLTRB(20, 0, 20, 20),
                          physics: const BouncingScrollPhysics(),
                          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                            crossAxisCount: 2, crossAxisSpacing: 14, mainAxisSpacing: 14, childAspectRatio: 0.82,
                          ),
                          itemCount: filtered.length,
                          itemBuilder: (ctx, i) => _buildMaterialCard(ctx, filtered[i]),
                        ),
                ),
              ],
            ),
          );
        });
      },
    );
  }

  // ── Bottom sheet semua kuis matkul ────────────────────────
  void _showAllQuizzesSheet(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) {
        return Container(
          height: MediaQuery.of(context).size.height * 0.75,
          decoration: const BoxDecoration(
            color: _primaryColor,
            borderRadius: BorderRadius.vertical(top: Radius.circular(30)),
          ),
          child: Column(
            children: [
              Center(
                child: Container(
                  margin: const EdgeInsets.only(top: 12, bottom: 20),
                  width: 40, height: 4,
                  decoration: BoxDecoration(color: Colors.white.withOpacity(0.5), borderRadius: BorderRadius.circular(10)),
                ),
              ),
              const Padding(
                padding: EdgeInsets.symmetric(horizontal: 20),
                child: Row(children: [
                  Icon(Icons.quiz_rounded, color: Colors.white, size: 24),
                  SizedBox(width: 10),
                  Text('Kuis per Mata Kuliah',
                      style: TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.bold)),
                ]),
              ),
              const SizedBox(height: 16),
              Expanded(
                child: _courses.isEmpty
                    ? const Center(child: Text('Belum ada mata kuliah', style: TextStyle(color: Colors.white)))
                    : ListView.builder(
                        padding: const EdgeInsets.fromLTRB(16, 0, 16, 20),
                        itemCount: _courses.length,
                        itemBuilder: (ctx, i) => _buildCourseQuizCard(_courses[i]),
                      ),
              ),
            ],
          ),
        );
      },
    );
  }

  // ── Card materi ───────────────────────────────────────────
  Widget _buildMaterialCard(BuildContext context, dynamic item) {
    final String? imageUrl = item['image'];
    return GestureDetector(
      onTap: () => Navigator.push(context,
          MaterialPageRoute(builder: (_) => MaterialDetailScreen(material: item))),
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.07), blurRadius: 12, offset: const Offset(0, 4))],
        ),
        clipBehavior: Clip.antiAlias,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Expanded(child: _buildThumbnail(imageUrl)),
            Padding(
              padding: const EdgeInsets.fromLTRB(10, 8, 10, 10),
              child: Text(
                item['title'] ?? 'Materi',
                style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF1A1A2E), fontSize: 13),
                textAlign: TextAlign.center,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildThumbnail(String? imageUrl) {
    if (imageUrl == null || imageUrl.isEmpty) {
      return Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: [_primaryColor.withOpacity(0.7), _primaryColor],
            begin: Alignment.topLeft, end: Alignment.bottomRight,
          ),
        ),
        child: const Center(child: Icon(Icons.menu_book_rounded, size: 48, color: Colors.white70)),
      );
    }
    return Image.network(
      imageUrl, fit: BoxFit.cover, width: double.infinity,
      loadingBuilder: (ctx, child, progress) {
        if (progress == null) return child;
        return Container(
          color: Colors.grey[100],
          child: const Center(child: CircularProgressIndicator(strokeWidth: 2, color: _primaryColor)),
        );
      },
      errorBuilder: (_, __, ___) => Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: [_primaryColor.withOpacity(0.7), _primaryColor],
            begin: Alignment.topLeft, end: Alignment.bottomRight,
          ),
        ),
        child: const Center(child: Icon(Icons.menu_book_rounded, size: 48, color: Colors.white70)),
      ),
    );
  }

  // ── Card kuis per matkul (BARU — iterate _courses bukan _materials) ──
  Widget _buildCourseQuizCard(dynamic course) {
    final courseId    = (course['_id'] ?? course['id'])?.toString() ?? '';
    final courseTitle = course['title'] ?? course['name'] ?? 'Matkul';

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 4, vertical: 6),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 10, offset: const Offset(0, 3))],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(16),
          onTap: () => Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => QuizListScreen(
                courseId: courseId,
                courseTitle: courseTitle,
              ),
            ),
          ),
          child: Padding(
            padding: const EdgeInsets.all(14),
            child: Row(
              children: [
                Container(
                  width: 48, height: 48,
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [_primaryColor, Color(0xFF1F7A6E)],
                      begin: Alignment.topLeft, end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.quiz_rounded, color: Colors.white, size: 24),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Kuis $courseTitle',
                        style: const TextStyle(
                          fontWeight: FontWeight.bold, fontSize: 14, color: Colors.black87),
                        maxLines: 1, overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 4),
                      const Text(
                        'Lihat semua kuis matkul ini',
                        style: TextStyle(fontSize: 11, color: _primaryColor, fontWeight: FontWeight.w500),
                      ),
                    ],
                  ),
                ),
                const Icon(Icons.chevron_right_rounded, color: _primaryColor, size: 20),
              ],
            ),
          ),
        ),
      ),
    );
  }

  // ── Card kuis inline (saat filter matkul spesifik) ─────
  Widget _buildInlineQuizCard(QuizModel quiz) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 6),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 10, offset: const Offset(0, 3))],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(16),
          onTap: () => Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => QuizScreen(quiz: quiz)),
          ),
          child: Padding(
            padding: const EdgeInsets.all(14),
            child: Row(
              children: [
                Container(
                  width: 48, height: 48,
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [_primaryColor, Color(0xFF1F7A6E)],
                      begin: Alignment.topLeft, end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.quiz_rounded, color: Colors.white, size: 24),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(quiz.title,
                        style: const TextStyle(
                          fontWeight: FontWeight.bold, fontSize: 14, color: Colors.black87),
                        maxLines: 2, overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          const Icon(Icons.timer_outlined, size: 12, color: Color(0xFF94A3B8)),
                          const SizedBox(width: 4),
                          Text('${quiz.durationMinutes} menit',
                            style: const TextStyle(fontSize: 11, color: Color(0xFF94A3B8))),
                        ],
                      ),
                    ],
                  ),
                ),
                const Icon(Icons.arrow_forward_ios_rounded, size: 14, color: _primaryColor),
              ],
            ),
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[100],
      body: CustomScrollView(
        physics: const BouncingScrollPhysics(),
        slivers: [
          SliverToBoxAdapter(
            child: Padding(
              padding: EdgeInsets.only(
                  left: 20,
                  top: MediaQuery.of(context).padding.top + 16,
                  right: 20,
                  bottom: 10),
              child: RichText(
                text: const TextSpan(
                  style: TextStyle(fontSize: 28, color: Colors.black87, fontWeight: FontWeight.bold),
                  children: [
                    TextSpan(text: 'Ayo Belajar\n'),
                    TextSpan(text: 'di '),
                    TextSpan(text: 'S Y N A P S E !', style: TextStyle(color: _primaryColor)),
                  ],
                ),
              ),
            ),
          ),

          SliverToBoxAdapter(child: _buildCourseSelector()),

          if (_isMaterialsLoading)
            const SliverFillRemaining(
              child: Center(child: CircularProgressIndicator(color: _primaryColor)),
            )
          else if (_materials.isEmpty)
            const SliverFillRemaining(
              child: Center(
                child: Text('Belum ada materi untuk mata kuliah ini 🥲',
                    style: TextStyle(color: Colors.grey)),
              ),
            )
          else ...[

            // ── Section Materi ──────────────────────────────
            SliverToBoxAdapter(
              child: _buildSectionHeader(
                title: 'Rekomendasi Materi',
                icon: Icons.auto_stories_rounded,
                count: _materials.length,
                onTap: () => _showAllMaterialsSheet(context, _materials),
              ),
            ),
            SliverToBoxAdapter(
              child: SizedBox(
                height: 200,
                child: ListView.builder(
                  scrollDirection: Axis.horizontal,
                  physics: const BouncingScrollPhysics(),
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  itemCount: _materials.length > 5 ? 5 : _materials.length,
                  itemBuilder: (context, index) => Padding(
                    padding: const EdgeInsets.only(right: 16),
                    child: SizedBox(
                      width: 160,
                      child: _buildMaterialCard(context, _materials[index]),
                    ),
                  ),
                ),
              ),
            ),

            // ── Section Kuis ─────────────────────────────────
            // Filter semua: card per matkul
            // Filter spesifik: list kuis langsung
            if (_selectedCourseId == null) ...[
              if (_courses.isNotEmpty) ...[
                SliverToBoxAdapter(
                  child: _buildSectionHeader(
                    title: 'Kuis Mata Kuliah',
                    icon: Icons.quiz_rounded,
                    count: _courses.length,
                    onTap: () => _showAllQuizzesSheet(context),
                  ),
                ),
                SliverList(
                  delegate: SliverChildBuilderDelegate(
                    (ctx, i) => _buildCourseQuizCard(_courses[i]),
                    childCount: _courses.length > 3 ? 3 : _courses.length,
                  ),
                ),
              ],
            ] else ...[
              SliverToBoxAdapter(
                child: _buildSectionHeader(
                  title: 'Kuis',
                  icon: Icons.quiz_rounded,
                  count: _isQuizzesLoading ? null : _quizzes.length,
                  onTap: () {},
                ),
              ),
              if (_isQuizzesLoading)
                const SliverToBoxAdapter(
                  child: Padding(
                    padding: EdgeInsets.all(20),
                    child: Center(child: CircularProgressIndicator(color: _primaryColor)),
                  ),
                )
              else if (_quizzes.isEmpty)
                const SliverToBoxAdapter(
                  child: Padding(
                    padding: EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                    child: Text('Belum ada kuis untuk matkul ini.',
                        style: TextStyle(color: Colors.grey, fontSize: 13)),
                  ),
                )
              else
                SliverList(
                  delegate: SliverChildBuilderDelegate(
                    (ctx, i) => _buildInlineQuizCard(_quizzes[i]),
                    childCount: _quizzes.length,
                  ),
                ),
            ],
          ],

          SliverToBoxAdapter(
            child: SizedBox(
              height: HomeScreen.navBarHeight + MediaQuery.of(context).padding.bottom + 16,
            ),
          ),
        ],
      ),
    );
  }
}