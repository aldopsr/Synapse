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
  List<dynamic> _courses   = [];
  List<dynamic> _materials = [];
  bool _isCoursesLoading   = true;
  bool _isMaterialsLoading = true;
  String? _selectedCourseId;

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
    setState(() { _selectedCourseId = courseId; _isMaterialsLoading = true; });
    final materials = courseId == null
        ? await MaterialService().getMaterials()
        : await MaterialService().getMaterialsByCourse(courseId);
    if (mounted) {
      setState(() { _materials = materials; _isMaterialsLoading = false; });
    }
  }

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

  void _showAllPracticesSheet(BuildContext context, List<dynamic> allMaterials) {
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
                  child: Row(children: [
                    Icon(Icons.quiz_rounded, color: Colors.white, size: 24),
                    SizedBox(width: 10),
                    Text('Semua Mini Kuis',
                        style: TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.bold)),
                  ]),
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
                        hintText: 'Cari kuis...',
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
                      ? const Center(child: Text('Kuis tidak ditemukan', style: TextStyle(color: Colors.white)))
                      : ListView.builder(
                          padding: const EdgeInsets.fromLTRB(16, 0, 16, 20),
                          physics: const BouncingScrollPhysics(),
                          itemCount: filtered.length,
                          itemBuilder: (ctx, i) => _buildPracticeCard(filtered[i], isInSheet: true),
                        ),
                ),
              ],
            ),
          );
        });
      },
    );
  }

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

  Widget _buildPracticeCard(dynamic item, {bool isInSheet = false}) {
    final bool hasPractice = (item['has_practice'] == true) ||
        ((item['questions'] as List?)?.isNotEmpty ?? false);
    return Container(
      margin: EdgeInsets.symmetric(horizontal: isInSheet ? 4 : 20, vertical: 6),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 10, offset: const Offset(0, 3))],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(16),
          onTap: () => Navigator.push(context,
              MaterialPageRoute(builder: (_) => PracticeScreen(
                materialId: (item['_id'] ?? item['id'])?.toString() ?? '',
                materialTitle: item['title'] ?? 'Kuis',
              ))),
          child: Padding(
            padding: const EdgeInsets.all(14),
            child: Row(
              children: [
                Container(
                  width: 48, height: 48,
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: hasPractice
                          ? [_primaryColor, const Color(0xFF1F7A6E)]
                          : [Colors.grey.shade400, Colors.grey.shade500],
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
                        'Kuis ${item['title'] ?? 'Materi'}',
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Colors.black87),
                        maxLines: 1, overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          Icon(
                            hasPractice ? Icons.check_circle : Icons.lock_clock_outlined,
                            size: 12,
                            color: hasPractice ? _primaryColor : Colors.orange[700],
                          ),
                          const SizedBox(width: 4),
                          Text(
                            hasPractice ? 'Tersedia • Uji pemahamanmu!' : 'Belum ada soal',
                            style: TextStyle(
                              fontSize: 11,
                              color: hasPractice ? _primaryColor : Colors.orange[700],
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ],
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

  @override
  Widget build(BuildContext context) {
    final List<dynamic> materialsWithPractice = _materials.where((m) {
      return m['has_practice'] == true || ((m['questions'] as List?)?.isNotEmpty ?? false);
    }).toList();

    return Scaffold(
      backgroundColor: Colors.grey[100],
      body: CustomScrollView(
        physics: const BouncingScrollPhysics(),
        slivers: [
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.only(left: 20, top: 60, right: 20, bottom: 10),
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

            // Section header Rekomendasi Materi
            SliverToBoxAdapter(
              child: _buildSectionHeader(
                title: 'Rekomendasi Materi',
                icon: Icons.auto_stories_rounded,
                count: _materials.length,
                onTap: () => _showAllMaterialsSheet(context, _materials),
              ),
            ),

            // Slider horizontal materi
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

            // Section Mini Kuis
            if (materialsWithPractice.isNotEmpty) ...[
              SliverToBoxAdapter(
                child: _buildSectionHeader(
                  title: 'Rekomendasi Mini Kuis',
                  icon: Icons.quiz_rounded,
                  count: materialsWithPractice.length,
                  onTap: () => _showAllPracticesSheet(context, materialsWithPractice),
                ),
              ),
              SliverList(
                delegate: SliverChildBuilderDelegate(
                  (ctx, i) => _buildPracticeCard(materialsWithPractice[i]),
                  childCount: materialsWithPractice.length > 3 ? 3 : materialsWithPractice.length,
                ),
              ),
            ],
          ],

          const SliverToBoxAdapter(child: SizedBox(height: 120)),
        ],
      ),
    );
  }
}