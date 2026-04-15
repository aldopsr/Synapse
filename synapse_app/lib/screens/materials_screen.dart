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
  // 1. Simpan Future di sini agar tidak memanggil API berkali-kali saat mengetik
  late Future<List<dynamic>> _materialsFuture;
  
  // 2. Variabel penyimpan teks dari kolom pencarian
  String _searchQuery = '';

  @override
  void initState() {
    super.initState();
    // Panggil API HANYA 1x saat halaman pertama dibuka
    _materialsFuture = MaterialService().getMaterials();
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

          // 3. JAGA-JAGA NULL: Ini yang mencegah error 'length' merah tadi
          final materials = snapshot.data ?? [];

          // 4. FILTER MATERI: Cek apakah ada teks di pencarian
          final filteredMaterials = _searchQuery.isEmpty 
              ? materials 
              : materials.where((item) {
                  final title = (item['title'] ?? '').toString().toLowerCase();
                  return title.contains(_searchQuery.toLowerCase());
                }).toList();

          return CustomScrollView(
            slivers: [
              // --- 1. HEADER TITLE (Ayo Belajar di SYNAPSE) ---
              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.only(left: 20, top: 60, right: 20, bottom: 20),
                  child: RichText(
                    text: const TextSpan(
                      style: TextStyle(fontSize: 28, color: Colors.black87, fontWeight: FontWeight.bold),
                      children: [
                        TextSpan(text: 'Ayo Belajar\n'),
                        TextSpan(text: 'di '),
                        TextSpan(text: 'S Y N A P S E !', style: TextStyle(color: Colors.teal)),
                      ],
                    ),
                  ),
                ),
              ),

              // --- 2. KOTAK ATAS: SLIDER MATERI (Tampilkan data asli) ---
              SliverToBoxAdapter(
                child: SizedBox(
                  height: 200,
                  child: ListView.builder(
                    scrollDirection: Axis.horizontal,
                    physics: const BouncingScrollPhysics(),
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    itemCount: materials.length > 5 ? 5 : materials.length, 
                    itemBuilder: (context, index) {
                      final item = materials[index];
                      return GestureDetector(
                        onTap: () => Navigator.push(
                          context,
                          MaterialPageRoute(builder: (context) => MaterialDetailScreen(material: item)),
                        ),
                        child: Container(
                          width: 160,
                          margin: const EdgeInsets.only(right: 16),
                          decoration: BoxDecoration(
                            color: Colors.grey[300], 
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Padding(
                            padding: const EdgeInsets.all(16.0),
                            child: Align(
                              alignment: Alignment.bottomLeft,
                              child: Text(
                                item['title'] ?? 'Materi',
                                style: TextStyle(fontWeight: FontWeight.bold, color: Colors.grey[800], fontSize: 16),
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ),
                        ),
                      );
                    },
                  ),
                ),
              ),

              // --- 3. DAFTAR MINI QUIZ (Vertikal) ---
              const SliverToBoxAdapter(
                child: Padding(
                  padding: EdgeInsets.fromLTRB(20, 30, 20, 10),
                  child: Text('Kuis Tersedia', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                ),
              ),
              SliverList(
                delegate: SliverChildBuilderDelegate(
                  (context, index) {
                    final item = materials[index]; 
                    return Container(
                      margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
                      decoration: BoxDecoration(
                        color: Colors.grey[300],
                        borderRadius: BorderRadius.circular(15),
                      ),
                      child: ListTile(
                        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                        title: Text('Kuis: ${item['title']}', style: const TextStyle(fontWeight: FontWeight.bold)),
                        subtitle: const Text('Uji pemahamanmu di sini'),
                        trailing: const Icon(Icons.arrow_forward_rounded, color: Colors.black87),
                        onTap: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (context) => PracticeScreen(
                                // Catatan: Jika ID di DB berupa angka (int), .toString() ini sangat membantu mencegah error
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

              // --- 4. BAGIAN BAWAH TEAL (Semua Materi dengan Filter) ---
              SliverToBoxAdapter(
                child: Container(
                  margin: const EdgeInsets.only(top: 20),
                  padding: const EdgeInsets.all(20),
                  decoration: const BoxDecoration(
                    color: Color(0xFF2A9D8F), 
                    borderRadius: BorderRadius.only(topLeft: Radius.circular(30), topRight: Radius.circular(30)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Materi Tersedia', style: TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold)),
                      const SizedBox(height: 16),
                      
                      // 👇 KOLOM PENCARIAN (Sudah terhubung dengan variabel) 👇
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(30)),
                        child: TextField(
                          // Setiap kali diketik, kita update nilai _searchQuery
                          onChanged: (value) {
                            setState(() {
                              _searchQuery = value;
                            });
                          },
                          decoration: const InputDecoration(
                            hintText: 'Cari materi...', 
                            border: InputBorder.none, 
                            suffixIcon: Icon(Icons.search, color: Colors.teal)
                          ),
                        ),
                      ),
                      const SizedBox(height: 20),
                      
                      // 👇 GRID ITEM MENAMPILKAN DATA FILTER 👇
                      filteredMaterials.isEmpty 
                      ? const Padding(
                          padding: EdgeInsets.symmetric(vertical: 20),
                          child: Center(child: Text("Materi tidak ditemukan", style: TextStyle(color: Colors.white))),
                        )
                      : GridView.builder(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                            crossAxisCount: 2, 
                            crossAxisSpacing: 16, 
                            mainAxisSpacing: 16, 
                            childAspectRatio: 0.85, 
                          ),
                          // Gunakan panjang array dari data yang sudah difilter
                          itemCount: filteredMaterials.length, 
                          itemBuilder: (context, index) {
                            // Ambil data dari list yang sudah difilter
                            final item = filteredMaterials[index]; 
                            
                            return GestureDetector(
                              onTap: () {
                                Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                    builder: (context) => MaterialDetailScreen(material: item),
                                  ),
                                );
                              },
                              child: Container(
                                padding: const EdgeInsets.all(12),
                                decoration: BoxDecoration(
                                  color: Colors.white, 
                                  borderRadius: BorderRadius.circular(20),
                                  boxShadow: [
                                    BoxShadow(
                                      color: Colors.black.withOpacity(0.05),
                                      blurRadius: 10,
                                      offset: const Offset(0, 5),
                                    )
                                  ]
                                ),
                                child: Column(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.all(12),
                                      decoration: BoxDecoration(
                                        color: Colors.teal.withOpacity(0.1),
                                        shape: BoxShape.circle,
                                      ),
                                      child: const Icon(Icons.menu_book_rounded, size: 32, color: Colors.teal),
                                    ),
                                    const SizedBox(height: 12),
                                    Text(
                                      item['title'] ?? 'Materi Tanpa Judul', 
                                      style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.black87, fontSize: 13),
                                      textAlign: TextAlign.center,
                                      maxLines: 3,
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ],
                                ),
                              ),
                            );
                          },
                        ),
                      const SizedBox(height: 80), 
                    ],
                  ),
                ),
              ),
            ],
          );
        },
      ),
    );
  }
}