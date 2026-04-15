import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart'; // 🌟 TAMBAHAN: Impor flutter_html
import 'practice_screen.dart';
import '../utils/constants.dart';

class MaterialDetailScreen extends StatelessWidget {
  final Map<String, dynamic> material;

  const MaterialDetailScreen({super.key, required this.material});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      
      body: CustomScrollView(
        physics: const BouncingScrollPhysics(),
        slivers: [
          // 👇 HEADER BESAR DENGAN WARNA GRADASI (Tetap sama seperti milik Kapten)
          SliverAppBar(
            expandedHeight: 220.0,
            floating: false,
            pinned: true,
            backgroundColor: Colors.blueAccent,
            foregroundColor: Colors.white,
            elevation: 0,
            flexibleSpace: FlexibleSpaceBar(
              titlePadding: const EdgeInsets.only(left: 20, bottom: 16, right: 20),
              title: Text(
                material['title'] ?? 'Detail Materi',
                style: const TextStyle(
                  fontWeight: FontWeight.bold,
                  fontSize: 18,
                  color: Colors.white,
                  shadows: [Shadow(color: Colors.black26, blurRadius: 4)],
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
              background: Container(
                decoration: const BoxDecoration(
                  gradient: LinearGradient(
                    colors: [Colors.blueAccent, Colors.lightBlue],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                ),
                child: const Stack(
                  children: [
                    Positioned(
                      right: -30,
                      top: 40,
                      child: Icon(Icons.hub_rounded, size: 200, color: Colors.white10),
                    ),
                  ],
                ),
              ),
            ),
          ),

          // 👇 KONTEN MATERI
          SliverToBoxAdapter(
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 30.0),
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.only(
                  topLeft: Radius.circular(30),
                  topRight: Radius.circular(30),
                ),
              ),
              transform: Matrix4.translationValues(0.0, -20.0, 0.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Label "Pendahuluan"
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                        decoration: BoxDecoration(
                          color: Colors.orangeAccent.withOpacity(0.2),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: const Text(
                          'Pendahuluan',
                          style: TextStyle(color: Colors.orange, fontWeight: FontWeight.bold, fontSize: 12),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  
                  // Kotak Deskripsi
                  Text(
                    material['description'] ?? 'Tidak ada deskripsi',
                    style: TextStyle(
                      fontSize: 17,
                      fontStyle: FontStyle.italic,
                      color: Colors.blueGrey[700],
                      height: 1.5,
                    ),
                  ),
                  
                  const Padding(
                    padding: EdgeInsets.symmetric(vertical: 24.0),
                    child: Divider(color: Colors.black12, thickness: 1.5),
                  ),
                  
                  // Label "Isi Materi"
                  const Text(
                    'Isi Materi',
                    style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Colors.black87),
                  ),
                  const SizedBox(height: 16),
                  
                  // 🌟 UPDATE UTAMA: Widget Html yang sudah dipercanggih
                  Html(
                    data: material['content'] ?? '<p>Isi materi belum tersedia.</p>',
                    style: {
                      "body": Style(
                        fontSize: FontSize(16.0),
                        lineHeight: LineHeight(1.8),
                        color: Colors.black87,
                        margin: Margins.zero,
                        padding: HtmlPaddings.zero,
                      ),
                      "table": Style(
                        backgroundColor: Colors.grey.shade50,
                      ),
                      "th": Style(
                        padding: HtmlPaddings.all(8),
                        border: Border.all(color: Colors.grey),
                        backgroundColor: Colors.blue.shade100,
                      ),
                      "td": Style(
                        padding: HtmlPaddings.all(8),
                        border: Border.all(color: Colors.grey),
                      ),
                    },
                    // 👇 BAGIAN PENTING: BENGKEL PERBAIKAN GAMBAR 👇
                    extensions: [
                      TagExtension(
                        tagsToExtend: {"img"},
                        builder: (extensionContext) {
                          String imgUrl = extensionContext.attributes['src'] ?? '';

                          // 👇 KITA BALIK LOGIKANYA! Paksa jadi localhost
                          if (imgUrl.contains('10.0.2.2:8000')) {
                              imgUrl = imgUrl.replaceFirst('10.0.2.2:8000', '127.0.0.1:8000');
                          }
                          
                          print("✅ URL SETELAH DIHAKIMI FLUTTER: $imgUrl");

                          return Padding(
                            padding: const EdgeInsets.symmetric(vertical: 12.0),
                            child: ClipRRect(
                              borderRadius: BorderRadius.circular(12),
                              child: Image.network(
                                imgUrl,
                                width: double.infinity,
                                fit: BoxFit.contain,
                                loadingBuilder: (ctx, child, loadingProgress) {
                                  if (loadingProgress == null) return child;
                                  return Container(
                                    height: 200,
                                    color: Colors.grey[100],
                                    child: const Center(
                                      child: CircularProgressIndicator(strokeWidth: 2),
                                    ),
                                  );
                                },
                                errorBuilder: (ctx, error, stackTrace) => Container(
                                  height: 150,
                                  width: double.infinity,
                                  color: Colors.grey[200],
                                  child: Column(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: const [
                                      Icon(Icons.broken_image_rounded, color: Colors.grey, size: 40),
                                      SizedBox(height: 8),
                                      Text('Gagal memuat gambar', style: TextStyle(color: Colors.grey)),
                                    ],
                                  ),
                                ),
                              ),
                            ),
                          );
                        },
                      ),
                    ],
                  ),
                  
                  const SizedBox(height: 100), // Ruang untuk FAB Latihan
                ],
              ),
            ),
          ),
        ],
      ),

      floatingActionButtonLocation: FloatingActionButtonLocation.centerFloat,
      floatingActionButton: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 24.0),
        child: SizedBox(
          width: double.infinity,
          height: 56,
          child: ElevatedButton(
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (context) => PracticeScreen(
                    materialId: material['id'],
                    materialTitle: material['title'] ?? 'Latihan',
                  ),
                ),
              );
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.greenAccent[700],
              foregroundColor: Colors.white,
              elevation: 8,
              shadowColor: Colors.greenAccent.withOpacity(0.5),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(28),
              ),
            ),
            child: const Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.play_circle_fill_rounded, size: 28),
                SizedBox(width: 10),
                Text(
                  'Mulai Latihan Sekarang',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, letterSpacing: 0.5),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}