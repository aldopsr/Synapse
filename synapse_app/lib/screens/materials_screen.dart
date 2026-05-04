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

  @override
  void initState() {
    super.initState();
    _loadInitialData();
  }

  // Fungsi mengambil Matkul & Materi saat layar pertama kali dibuka
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

  // Fungsi saat tombol Kapsul Matkul ditekan
  Future<void> _onCourseSelected(String? courseId) async {
    if (_selectedCourseId == courseId) return; // Abaikan jika menekan matkul yang sama

    setState(() {
      _selectedCourseId = courseId;
      _isMaterialsLoading = true; // Putar loading hanya di bagian materi
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

  // --- WIDGET HELPER: HIGHLIGHT PILIH MATA KULIAH ---
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
        border: Border.all(color: const Color(0xFF2A9D8F).withOpacity(0.5), width: 2), // Bingkai Highlight
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF2A9D8F).withOpacity(0.15), 
            blurRadius: 15, 
            offset: const Offset(0, 5)
          ) // Shadow Teal agar menonjol
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(
            children: [
              Icon(Icons.auto_awesome_mosaic_rounded, color: Color(0xFF2A9D8F)),
              SizedBox(width: 8),
              Text(
                'Pilih Mata Kuliah', 
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF2A9D8F))
              ),
            ],
          ),
          const SizedBox(height: 16),
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            physics: const BouncingScrollPhysics(),
            child: Row(
              children: [
                // Tombol "Semua"
                _buildCourseChip(title: "Semua", id: null),
                
                // Looping Tombol Matkul dari API
                ..._courses.map((c) {
                  return _buildCourseChip(
                    title: c['title'] ?? c['name'] ?? 'Matkul', 
                    id: c['id'].toString()
                  );
                }),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // Desain Kapsul per Mata Kuliah
  Widget _buildCourseChip({required String title, required String? id}) {
    bool isSelected = _selectedCourseId == id;
    return GestureDetector(
      onTap: () => _onCourseSelected(id),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 300),
        margin: const EdgeInsets.only(right: 12),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        decoration: BoxDecoration(
          color: isSelected ? const Color(0xFF2A9D8F) : const Color(0xFFF0FDFB),
          borderRadius: BorderRadius.circular(25),
          border: Border.all(
            color: isSelected ? Colors.transparent : const Color(0xFF2A9D8F).withOpacity(0.5)
          ),
          boxShadow: isSelected ? [
            BoxShadow(color: const Color(0xFF2A9D8F).withOpacity(0.3), blurRadius: 8, offset: const Offset(0, 3))
          ] : [],
        ),
        child: Text(
          title, 
          style: TextStyle(
            color: isSelected ? Colors.white : const Color(0xFF2A9D8F),
            fontWeight: FontWeight.bold,
            fontSize: 13,
          )
        ),
      ),
    );
  }

  // --- FUNGSI UNTUK MENAMPILKAN BOTTOM SHEET (SLIDE-UP) ---
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
                color: Color(0xFF2A9D8F), 
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
                    child: Text('Materi Tersedia', style: TextStyle(color: Colors.white, fontSize: 24, fontWeight: FontWeight.bold)),
                  ),
                  const SizedBox(height: 16),
                  
                  // Kolom Pencarian
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 20),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(30)),
                      child: TextField(
                        onChanged: (value) {
                          setStateSheet(() {
                            localSearchQuery = value;
                          });
                        },
                        decoration: const InputDecoration(
                          hintText: 'Search here...', 
                          border: InputBorder.none, 
                          suffixIcon: Icon(Icons.search, color: Colors.black54)
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 20),
                  
                  // Grid Materi
                  Expanded(
                    child: filteredMaterials.isEmpty 
                    ? const Center(child: Text("Materi tidak ditemukan", style: TextStyle(color: Colors.white)))
                    : GridView.builder(
                        padding: const EdgeInsets.fromLTRB(20, 0, 20, 20),
                        physics: const BouncingScrollPhysics(),
                        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
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
          }
        );
      },
    );
  }

  // --- WIDGET HELPER: DESAIN KARTU MATERI ORISINAL KAPTEN ---
  Widget _buildMaterialCard(BuildContext context, dynamic item) {
    return GestureDetector(
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(builder: (context) => MaterialDetailScreen(material: item)),
        );
      },
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white, 
          borderRadius: BorderRadius.circular(20),
          boxShadow: [
            BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 10, offset: const Offset(0, 5))
          ]
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Expanded(
              flex: 3,
              child: Container(
                decoration: const BoxDecoration(
                  borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
                ),
                child: const Center(
                  child: Icon(Icons.data_usage_rounded, size: 60, color: Colors.grey),
                ),
              ),
            ),
            Expanded(
              flex: 2,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                decoration: const BoxDecoration(
                  color: Color(0xFFC4E8E2), 
                  borderRadius: BorderRadius.vertical(bottom: Radius.circular(20)),
                ),
                child: Center(
                  child: Text(
                    item['title'] ?? 'Materi', 
                    style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF2A9D8F), fontSize: 14),
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[100],
      body: CustomScrollView(
        physics: const BouncingScrollPhysics(),
        slivers: [
          // --- 1. HEADER TITLE ---
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.only(left: 20, top: 60, right: 20, bottom: 10),
              child: RichText(
                text: const TextSpan(
                  style: TextStyle(fontSize: 28, color: Colors.black87, fontWeight: FontWeight.bold),
                  children: [
                    TextSpan(text: 'Ayo Belajar\n'),
                    TextSpan(text: 'di '),
                    TextSpan(text: 'S Y N A P S E !', style: TextStyle(color: Color(0xFF2A9D8F))),
                  ],
                ),
              ),
            ),
          ),

          // --- 2. HIGHLIGHT KAPSUL PILIH MATA KULIAH ---
          SliverToBoxAdapter(
            child: _buildCourseSelector(),
          ),

          // Jika materi sedang dimuat, tampilkan loading. Jika tidak, tampilkan materi.
          if (_isMaterialsLoading)
            const SliverFillRemaining(
              child: Center(child: CircularProgressIndicator(color: Colors.teal)),
            )
          else if (_materials.isEmpty)
            const SliverFillRemaining(
              child: Center(
                child: Text('Belum ada materi untuk mata kuliah ini 🥲', style: TextStyle(color: Colors.grey)),
              ),
            )
          else ...[
            // --- 3. HEADER REKOMENDASI MATERI & TOMBOL PANAH ---
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('Rekomendasi Materi', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                    IconButton(
                      icon: const Icon(Icons.arrow_forward_rounded, color: Colors.black87),
                      onPressed: () {
                        _showAllMaterialsSheet(context, _materials);
                      },
                    )
                  ],
                ),
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

            // --- 5. HEADER REKOMENDASI MINI KUIS ---
            const SliverToBoxAdapter(
              child: Padding(
                padding: EdgeInsets.fromLTRB(20, 30, 20, 10),
                child: Text('Rekomendasi Mini Kuis', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
              ),
            ),

            // --- 6. DAFTAR MINI KUIS ---
            SliverList(
              delegate: SliverChildBuilderDelegate(
                (context, index) {
                  final item = _materials[index]; 
                  return Container(
                    margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
                    decoration: BoxDecoration(
                      color: Colors.white, 
                      borderRadius: BorderRadius.circular(15),
                      boxShadow: [
                        BoxShadow(color: Colors.black.withOpacity(0.03), blurRadius: 8, offset: const Offset(0, 2))
                      ]
                    ),
                    child: ListTile(
                      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                      title: Text('Kuis ${item['title']}', style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.black87)),
                      subtitle: const Text('Uji pemahamanmu di sini!', style: TextStyle(fontSize: 12)),
                      trailing: const Icon(Icons.arrow_forward_rounded, color: Colors.black87),
                      onTap: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (context) => PracticeScreen(
                              materialId: item['id'].toString(), 
                              materialTitle: item['title'] ?? 'Kuis',
                            ),
                          ),
                        );
                      },
                    ),
                  );
                },
                childCount: _materials.length > 3 ? 3 : _materials.length, 
              ),
            ),
          ],
          
          const SliverToBoxAdapter(child: SizedBox(height: 40)),
        ],
      ),
    );
  }
}