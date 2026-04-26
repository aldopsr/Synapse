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
  late Future<List<dynamic>> _materialsFuture;

  @override
  void initState() {
    super.initState();
    _materialsFuture = MaterialService().getMaterials();
  }

  // --- FUNGSI UNTUK MENAMPILKAN BOTTOM SHEET (SLIDE-UP) ---
  void _showAllMaterialsSheet(BuildContext context, List<dynamic> allMaterials) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true, // Agar bisa di-set tingginya melebihi setengah layar
      backgroundColor: Colors.transparent, // Transparan agar ujungnya bisa melengkung
      builder: (context) {
        String localSearchQuery = ''; // State pencarian khusus untuk bottom sheet

        // StatefulBuilder digunakan agar kita bisa merender ulang (setState)
        // HANYA di dalam bottom sheet saat user mengetik di kolom pencarian.
        return StatefulBuilder(
          builder: (BuildContext context, StateSetter setStateSheet) {
            final filteredMaterials = localSearchQuery.isEmpty 
              ? allMaterials 
              : allMaterials.where((item) {
                  final title = (item['title'] ?? '').toString().toLowerCase();
                  return title.contains(localSearchQuery.toLowerCase());
                }).toList();

            return Container(
              height: MediaQuery.of(context).size.height * 0.85, // Tinggi 85% layar
              decoration: const BoxDecoration(
                color: Color(0xFF2A9D8F), // Warna Teal sesuai desain
                borderRadius: BorderRadius.vertical(top: Radius.circular(30)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Gagang kecil di atas bottom sheet (indikator bisa di-swipe turun)
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

  // --- WIDGET HELPER UNTUK DESAIN KARTU MATERI ---
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
            // Bagian Atas: Area Gambar/Ikon (Putih)
            Expanded(
              flex: 3,
              child: Container(
                decoration: const BoxDecoration(
                  borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
                ),
                child: const Center(
                  // CATATAN: Ganti Icon ini dengan Image.network(item['image_url']) 
                  // jika API Kapten sudah mengembalikan link gambar.
                  child: Icon(Icons.data_usage_rounded, size: 60, color: Colors.grey),
                ),
              ),
            ),
            // Bagian Bawah: Judul (Teal Muda)
            Expanded(
              flex: 2,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                decoration: BoxDecoration(
                  color: const Color(0xFFC4E8E2), // Warna background teks (teal muda)
                  borderRadius: const BorderRadius.vertical(bottom: Radius.circular(20)),
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
      body: FutureBuilder<List<dynamic>>(
        future: _materialsFuture, 
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator(color: Colors.teal));
          }

          final materials = snapshot.data ?? [];

          return CustomScrollView(
            physics: const BouncingScrollPhysics(),
            slivers: [
              // --- 1. HEADER TITLE ---
              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.only(left: 20, top: 60, right: 20, bottom: 20),
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

              // --- 2. HEADER REKOMENDASI MATERI & TOMBOL PANAH ---
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
                          // Panggil fungsi slide-up saat panah diklik
                          _showAllMaterialsSheet(context, materials);
                        },
                      )
                    ],
                  ),
                ),
              ),

              // --- 3. SLIDER REKOMENDASI MATERI ---
              SliverToBoxAdapter(
                child: SizedBox(
                  height: 200, // Tinggikan sedikit agar desain card baru muat
                  child: ListView.builder(
                    scrollDirection: Axis.horizontal,
                    physics: const BouncingScrollPhysics(),
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    itemCount: materials.length > 5 ? 5 : materials.length, 
                    itemBuilder: (context, index) {
                      final item = materials[index];
                      return Padding(
                        padding: const EdgeInsets.only(right: 16),
                        child: SizedBox(
                          width: 160,
                          child: _buildMaterialCard(context, item), // Gunakan desain card baru
                        ),
                      );
                    },
                  ),
                ),
              ),

              // --- 4. HEADER REKOMENDASI MINI KUIS ---
              const SliverToBoxAdapter(
                child: Padding(
                  padding: EdgeInsets.fromLTRB(20, 30, 20, 10),
                  child: Text('Rekomendasi Mini Kuis', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                ),
              ),

              // --- 5. DAFTAR MINI KUIS ---
              SliverList(
                delegate: SliverChildBuilderDelegate(
                  (context, index) {
                    final item = materials[index]; 
                    return Container(
                      margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
                      decoration: BoxDecoration(
                        color: Colors.white, // Sesuaikan desain gambar 1 (putih)
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
                  childCount: materials.length > 3 ? 3 : materials.length, 
                ),
              ),
              
              // Memberi jarak sedikit di bawah layar
              const SliverToBoxAdapter(child: SizedBox(height: 40)),
            ],
          );
        },
      ),
    );
  }
}