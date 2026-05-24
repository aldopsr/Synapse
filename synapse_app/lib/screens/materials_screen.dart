import 'package:flutter/material.dart';
import '../services/material_service.dart';
import 'material_detail_screen.dart';
import 'practice_screen.dart';

class MaterialsScreen extends StatefulWidget {
  const MaterialsScreen({super.key});

  @override
  State<MaterialsScreen> createState() => _MaterialsScreenState();
}

class _MaterialsScreenState extends State<MaterialsScreen> {
  // State untuk menyimpan data
  List<dynamic> _courses = [];
  List<dynamic> _materials = [];

  // State untuk loading
  bool _isCoursesLoading = true;
  bool _isMaterialsLoading = true;

  // State untuk mencatat matkul apa yang sedang dipilih (null = Semua)
  String? _selectedCourseId;

  // 🌟 Theme color terpusat
  static const Color _primaryColor = Color(0xFF2A9D8F);
  static const Color _accentBg = Color(0xFFC4E8E2);
  static const Color _softBg = Color(0xFFF0FDFB);

  @override
  void initState() {
    super.initState();
    _loadInitialData();
  }

  Future<void> _loadInitialData() async {
    final courses = await MaterialService().getCourses();
    final materials = await MaterialService().getMaterials();

    if (mounted) {
      setState(() {
        _courses = courses;
        _materials = materials;
        _isCoursesLoading = false;
        _isMaterialsLoading = false;
      });
    }
  }

  Future<void> _onCourseSelected(String? courseId) async {
    if (_selectedCourseId == courseId) return;

    setState(() {
      _selectedCourseId = courseId;
      _isMaterialsLoading = true;
    });

    final materials = courseId == null
        ? await MaterialService().getMaterials()
        : await MaterialService().getMaterialsByCourse(courseId);

    if (mounted) {
      setState(() {
        _materials = materials;
        _isMaterialsLoading = false;
      });
    }
  }

  // ====================================================
  // WIDGET: HIGHLIGHT PILIH MATA KULIAH
  // ====================================================
  Widget _buildCourseSelector() {
    if (_isCoursesLoading) {
      return const Center(child: CircularProgressIndicator(color: Colors.teal));
    }

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: _primaryColor.withOpacity(0.5), width: 2),
        boxShadow: [
          BoxShadow(
              color: _primaryColor.withOpacity(0.15),
              blurRadius: 15,
              offset: const Offset(0, 5))
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(
            children: [
              Icon(Icons.auto_awesome_mosaic_rounded, color: _primaryColor),
              SizedBox(width: 8),
              Text('Pilih Mata Kuliah',
                  style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: _primaryColor)),
            ],
          ),
          const SizedBox(height: 16),
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            physics: const BouncingScrollPhysics(),
            child: Row(
              children: [
                _buildCourseChip(title: "Semua", id: null),
                ..._courses.map((c) {
                  return _buildCourseChip(
                      title: c['title'] ?? c['name'] ?? 'Matkul',
                      id: (c['_id'] ?? c['id'])?.toString());
                }),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCourseChip({required String title, required String? id}) {
    bool isSelected = _selectedCourseId == id;
    return GestureDetector(
      onTap: () => _onCourseSelected(id),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 300),
        margin: const EdgeInsets.only(right: 12),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        decoration: BoxDecoration(
          color: isSelected ? _primaryColor : _softBg,
          borderRadius: BorderRadius.circular(25),
          border: Border.all(
              color:
                  isSelected ? Colors.transparent : _primaryColor.withOpacity(0.5)),
          boxShadow: isSelected
              ? [
                  BoxShadow(
                      color: _primaryColor.withOpacity(0.3),
                      blurRadius: 8,
                      offset: const Offset(0, 3))
                ]
              : [],
        ),
        child: Text(title,
            style: TextStyle(
              color: isSelected ? Colors.white : _primaryColor,
              fontWeight: FontWeight.bold,
              fontSize: 13,
            )),
      ),
    );
  }

  // ====================================================
  // 🌟 BARU: SECTION HEADER DENGAN PILL BUTTON "LIHAT SEMUA"
  // ====================================================
  Widget _buildSectionHeader({
    required String title,
    required int totalCount,
    required int displayedCount,
    required VoidCallback onSeeAll,
    IconData? icon,
  }) {
    final bool hasMore = totalCount > displayedCount;

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          // Judul section
          Expanded(
            child: Row(
              children: [
                if (icon != null) ...[
                  Icon(icon, color: _primaryColor, size: 22),
                  const SizedBox(width: 8),
                ],
                Flexible(
                  child: Text(
                    title,
                    style: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: Colors.black87,
                    ),
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
              ],
            ),
          ),

          // 🌟 Pill button "Lihat Semua" yang lebih eye-catching
          if (hasMore)
            Material(
              color: Colors.transparent,
              child: InkWell(
                onTap: onSeeAll,
                borderRadius: BorderRadius.circular(25),
                child: Container(
                  padding: const EdgeInsets.symmetric(
                      horizontal: 14, vertical: 8),
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [_primaryColor, Color(0xFF1F7A6E)],
                    ),
                    borderRadius: BorderRadius.circular(25),
                    boxShadow: [
                      BoxShadow(
                        color: _primaryColor.withOpacity(0.35),
                        blurRadius: 8,
                        offset: const Offset(0, 3),
                      )
                    ],
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        'Lihat Semua ($totalCount)',
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                          fontSize: 12,
                        ),
                      ),
                      const SizedBox(width: 4),
                      const Icon(Icons.arrow_forward_rounded,
                          color: Colors.white, size: 16),
                    ],
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }

  // ====================================================
  // BOTTOM SHEET: SEMUA MATERI
  // ====================================================
  void _showAllMaterialsSheet(BuildContext context, List<dynamic> allMaterials) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) {
        String localSearchQuery = '';

        return StatefulBuilder(
            builder: (BuildContext context, StateSetter setStateSheet) {
          final filteredMaterials = localSearchQuery.isEmpty
              ? allMaterials
              : allMaterials.where((item) {
                  final title = (item['title'] ?? '').toString().toLowerCase();
                  return title.contains(localSearchQuery.toLowerCase());
                }).toList();

          return Container(
            height: MediaQuery.of(context).size.height * 0.85,
            decoration: const BoxDecoration(
              color: _primaryColor,
              borderRadius: BorderRadius.vertical(top: Radius.circular(30)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Center(
                  child: Container(
                    margin: const EdgeInsets.only(top: 12, bottom: 20),
                    width: 50,
                    height: 5,
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.5),
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                ),
                const Padding(
                  padding: EdgeInsets.symmetric(horizontal: 20),
                  child: Text('Materi Tersedia',
                      style: TextStyle(
                          color: Colors.white,
                          fontSize: 24,
                          fontWeight: FontWeight.bold)),
                ),
                const SizedBox(height: 16),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 20),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(30)),
                    child: TextField(
                      onChanged: (value) {
                        setStateSheet(() {
                          localSearchQuery = value;
                        });
                      },
                      decoration: const InputDecoration(
                          hintText: 'Cari materi...',
                          border: InputBorder.none,
                          suffixIcon:
                              Icon(Icons.search, color: Colors.black54)),
                    ),
                  ),
                ),
                const SizedBox(height: 20),
                Expanded(
                  child: filteredMaterials.isEmpty
                      ? const Center(
                          child: Text("Materi tidak ditemukan",
                              style: TextStyle(color: Colors.white)))
                      : GridView.builder(
                          padding: const EdgeInsets.fromLTRB(20, 0, 20, 20),
                          physics: const BouncingScrollPhysics(),
                          gridDelegate:
                              const SliverGridDelegateWithFixedCrossAxisCount(
                            crossAxisCount: 2,
                            crossAxisSpacing: 16,
                            mainAxisSpacing: 16,
                            childAspectRatio: 0.85,
                          ),
                          itemCount: filteredMaterials.length,
                          itemBuilder: (context, index) {
                            final item = filteredMaterials[index];
                            return _buildMaterialCard(context, item);
                          },
                        ),
                ),
              ],
            ),
          );
        });
      },
    );
  }

  // ====================================================
  // 🌟 BOTTOM SHEET BARU: SEMUA MINI KUIS
  // ====================================================
  void _showAllPracticesSheet(BuildContext context, List<dynamic> allMaterials) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) {
        String localSearchQuery = '';

        return StatefulBuilder(
            builder: (BuildContext context, StateSetter setStateSheet) {
          final filteredMaterials = localSearchQuery.isEmpty
              ? allMaterials
              : allMaterials.where((item) {
                  final title = (item['title'] ?? '').toString().toLowerCase();
                  return title.contains(localSearchQuery.toLowerCase());
                }).toList();

          return Container(
            height: MediaQuery.of(context).size.height * 0.85,
            decoration: const BoxDecoration(
              color: _primaryColor,
              borderRadius: BorderRadius.vertical(top: Radius.circular(30)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Center(
                  child: Container(
                    margin: const EdgeInsets.only(top: 12, bottom: 20),
                    width: 50,
                    height: 5,
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.5),
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                ),
                const Padding(
                  padding: EdgeInsets.symmetric(horizontal: 20),
                  child: Row(
                    children: [
                      Icon(Icons.quiz_rounded, color: Colors.white, size: 28),
                      SizedBox(width: 10),
                      Text('Semua Mini Kuis',
                          style: TextStyle(
                              color: Colors.white,
                              fontSize: 24,
                              fontWeight: FontWeight.bold)),
                    ],
                  ),
                ),
                const SizedBox(height: 16),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 20),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(30)),
                    child: TextField(
                      onChanged: (value) {
                        setStateSheet(() {
                          localSearchQuery = value;
                        });
                      },
                      decoration: const InputDecoration(
                          hintText: 'Cari kuis...',
                          border: InputBorder.none,
                          suffixIcon:
                              Icon(Icons.search, color: Colors.black54)),
                    ),
                  ),
                ),
                const SizedBox(height: 20),
                Expanded(
                  child: filteredMaterials.isEmpty
                      ? const Center(
                          child: Text("Kuis tidak ditemukan",
                              style: TextStyle(color: Colors.white)))
                      : ListView.builder(
                          padding: const EdgeInsets.fromLTRB(16, 0, 16, 20),
                          physics: const BouncingScrollPhysics(),
                          itemCount: filteredMaterials.length,
                          itemBuilder: (context, index) {
                            final item = filteredMaterials[index];
                            return _buildPracticeCard(item, isInSheet: true);
                          },
                        ),
                ),
              ],
            ),
          );
        });
      },
    );
  }

  // ====================================================
  // 🌟 KARTU MATERI - SEKARANG PAKAI THUMBNAIL ASLI
  // ====================================================
  Widget _buildMaterialCard(BuildContext context, dynamic item) {
    final String? imageUrl = item['image'];

    return GestureDetector(
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(
              builder: (context) => MaterialDetailScreen(material: item)),
        );
      },
      child: Container(
        decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(20),
            boxShadow: [
              BoxShadow(
                  color: Colors.black.withOpacity(0.05),
                  blurRadius: 10,
                  offset: const Offset(0, 5))
            ]),
        clipBehavior: Clip.antiAlias,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // 🌟 BARU: Pakai gambar dari API, fallback ke icon kalau kosong
            Expanded(
              flex: 3,
              child: _buildThumbnail(imageUrl),
            ),
            // Title bagian bawah
            Expanded(
              flex: 2,
              child: Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                decoration: const BoxDecoration(
                  color: _accentBg,
                ),
                child: Center(
                  child: Text(
                    item['title'] ?? 'Materi',
                    style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        color: _primaryColor,
                        fontSize: 14),
                    textAlign: TextAlign.center,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // 🌟 HELPER: Thumbnail dengan handling null/error
  Widget _buildThumbnail(String? imageUrl) {
    if (imageUrl == null || imageUrl.isEmpty) {
      // Placeholder yang lebih cantik
      return Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: [_primaryColor.withOpacity(0.7), _primaryColor],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
        ),
        child: const Center(
          child: Icon(Icons.menu_book_rounded, size: 50, color: Colors.white70),
        ),
      );
    }

    return Image.network(
      imageUrl,
      fit: BoxFit.cover,
      width: double.infinity,
      loadingBuilder: (context, child, loadingProgress) {
        if (loadingProgress == null) return child;
        return Container(
          color: Colors.grey[100],
          child: const Center(
            child: CircularProgressIndicator(
              strokeWidth: 2,
              color: _primaryColor,
            ),
          ),
        );
      },
      errorBuilder: (context, error, stackTrace) {
        return Container(
          decoration: BoxDecoration(
            gradient: LinearGradient(
              colors: [_primaryColor.withOpacity(0.7), _primaryColor],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
          ),
          child: const Center(
            child: Icon(Icons.menu_book_rounded,
                size: 50, color: Colors.white70),
          ),
        );
      },
    );
  }

  // ====================================================
  // 🌟 KARTU MINI KUIS - DESAIN BARU LEBIH MENARIK
  // ====================================================
  Widget _buildPracticeCard(dynamic item, {bool isInSheet = false}) {
    final bool hasPractice = item['has_practice'] == true;

    return Container(
      margin: EdgeInsets.symmetric(
          horizontal: isInSheet ? 4 : 20, vertical: 6),
      decoration: BoxDecoration(
        color: isInSheet ? Colors.white : Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
              color: Colors.black.withOpacity(0.04),
              blurRadius: 10,
              offset: const Offset(0, 3))
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(16),
          onTap: () {
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (context) => PracticeScreen(
                  materialId: (item['_id'] ?? item['id'])?.toString() ?? '',
                  materialTitle: item['title'] ?? 'Kuis',
                ),
              ),
            );
          },
          child: Padding(
            padding: const EdgeInsets.all(14),
            child: Row(
              children: [
                // Icon avatar dengan background gradient
                Container(
                  width: 50,
                  height: 50,
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: hasPractice
                          ? [_primaryColor, const Color(0xFF1F7A6E)]
                          : [Colors.grey.shade400, Colors.grey.shade500],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(14),
                    boxShadow: [
                      BoxShadow(
                        color: (hasPractice ? _primaryColor : Colors.grey)
                            .withOpacity(0.3),
                        blurRadius: 6,
                        offset: const Offset(0, 3),
                      )
                    ],
                  ),
                  child: const Icon(Icons.quiz_rounded,
                      color: Colors.white, size: 26),
                ),
                const SizedBox(width: 14),

                // Info teks
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Kuis ${item['title'] ?? 'Materi'}',
                        style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 15,
                            color: Colors.black87),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          Icon(
                            hasPractice
                                ? Icons.check_circle
                                : Icons.lock_clock_outlined,
                            size: 13,
                            color: hasPractice
                                ? _primaryColor
                                : Colors.orange[700],
                          ),
                          const SizedBox(width: 4),
                          Text(
                            hasPractice
                                ? 'Tersedia • Uji pemahamanmu!'
                                : 'Belum ada soal',
                            style: TextStyle(
                              fontSize: 11,
                              color: hasPractice
                                  ? _primaryColor
                                  : Colors.orange[700],
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),

                // Chevron
                Container(
                  padding: const EdgeInsets.all(6),
                  decoration: BoxDecoration(
                    color: _softBg,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: const Icon(Icons.arrow_forward_ios_rounded,
                      color: _primaryColor, size: 14),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  // ====================================================
  // BUILD UTAMA
  // ====================================================
  @override
  Widget build(BuildContext context) {
    // 🌟 Filter materi yang punya kuis (untuk section Mini Kuis)
    final List<dynamic> materialsWithPractice = _materials.where((m) {
      final hasOldFlag = m['has_practice'] == true;
      final hasQuestions = (m['questions'] as List?)?.isNotEmpty ?? false;
      return hasOldFlag || hasQuestions;
    }).toList();

    return Scaffold(
      backgroundColor: Colors.grey[100],
      body: CustomScrollView(
        physics: const BouncingScrollPhysics(),
        slivers: [
          // --- 1. HEADER TITLE ---
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.only(
                  left: 20, top: 60, right: 20, bottom: 10),
              child: RichText(
                text: const TextSpan(
                  style: TextStyle(
                      fontSize: 28,
                      color: Colors.black87,
                      fontWeight: FontWeight.bold),
                  children: [
                    TextSpan(text: 'Ayo Belajar\n'),
                    TextSpan(text: 'di '),
                    TextSpan(
                        text: 'S Y N A P S E !',
                        style: TextStyle(color: _primaryColor)),
                  ],
                ),
              ),
            ),
          ),

          // --- 2. KAPSUL MATA KULIAH ---
          SliverToBoxAdapter(child: _buildCourseSelector()),

          // --- LOADING / EMPTY / CONTENT ---
          if (_isMaterialsLoading)
            const SliverFillRemaining(
              child: Center(child: CircularProgressIndicator(color: Colors.teal)),
            )
          else if (_materials.isEmpty)
            const SliverFillRemaining(
              child: Center(
                child: Text('Belum ada materi untuk mata kuliah ini 🥲',
                    style: TextStyle(color: Colors.grey)),
              ),
            )
          else ...[
            // --- 3. SECTION REKOMENDASI MATERI (header pill) ---
            SliverToBoxAdapter(
              child: _buildSectionHeader(
                title: 'Rekomendasi Materi',
                icon: Icons.auto_stories_rounded,
                totalCount: _materials.length,
                displayedCount: 5,
                onSeeAll: () => _showAllMaterialsSheet(context, _materials),
              ),
            ),

            // --- 4. SLIDER REKOMENDASI MATERI ---
            SliverToBoxAdapter(
              child: SizedBox(
                height: 200,
                child: ListView.builder(
                  scrollDirection: Axis.horizontal,
                  physics: const BouncingScrollPhysics(),
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  itemCount: _materials.length > 5 ? 5 : _materials.length,
                  itemBuilder: (context, index) {
                    final item = _materials[index];
                    return Padding(
                      padding: const EdgeInsets.only(right: 16),
                      child: SizedBox(
                        width: 160,
                        child: _buildMaterialCard(context, item),
                      ),
                    );
                  },
                ),
              ),
            ),

            // --- 5. SECTION MINI KUIS (header pill) ---
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.only(top: 20),
                child: _buildSectionHeader(
                  title: 'Rekomendasi Mini Kuis',
                  icon: Icons.quiz_rounded,
                  totalCount: materialsWithPractice.isNotEmpty
                      ? materialsWithPractice.length
                      : _materials.length,
                  displayedCount: 3,
                  onSeeAll: () => _showAllPracticesSheet(
                      context,
                      materialsWithPractice.isNotEmpty
                          ? materialsWithPractice
                          : _materials),
                ),
              ),
            ),

            // --- 6. DAFTAR MINI KUIS ---
            SliverList(
              delegate: SliverChildBuilderDelegate(
                (context, index) {
                  // Prioritaskan yg punya practice, fallback ke semua materi
                  final source = materialsWithPractice.isNotEmpty
                      ? materialsWithPractice
                      : _materials;
                  final item = source[index];
                  return _buildPracticeCard(item);
                },
                childCount: materialsWithPractice.isNotEmpty
                    ? (materialsWithPractice.length > 3
                        ? 3
                        : materialsWithPractice.length)
                    : (_materials.length > 3 ? 3 : _materials.length),
              ),
            ),
          ],

          const SliverToBoxAdapter(child: SizedBox(height: 40)),
        ],
      ),
    );
  }
}