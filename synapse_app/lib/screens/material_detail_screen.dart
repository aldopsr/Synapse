import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';
import 'practice_screen.dart';
import '../utils/constants.dart';
import 'ar_view_screen.dart'; // 🌟 IMPOR HALAMAN AR KAPTEN
import '../services/auth_service.dart'; // 🌟 UNTUK GET BASE URL

class MaterialDetailScreen extends StatelessWidget {
  final Map<String, dynamic> material;

  const MaterialDetailScreen({super.key, required this.material});

  @override
  Widget build(BuildContext context) {
    // 🌟 DETEKSI RADAR: Apakah materi ini punya file 3D?
    String? modelPath = material['model_3d_path'];
    bool hasAr = modelPath != null && modelPath.toString().isNotEmpty;

    return Scaffold(
      backgroundColor: Colors.white,
      
      body: CustomScrollView(
        physics: const BouncingScrollPhysics(),
        slivers: [
          // 👇 HEADER BESAR (Sama persis dengan milik Kapten)
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

          // 👇 KONTEN MATERI (HTML)
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
                  
                  const Text(
                    'Isi Materi',
                    style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Colors.black87),
                  ),
                  const SizedBox(height: 16),
                  
                  // WIDGET HTML KAPTEN (Tetap dipertahankan agar dosen nyaman)
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
                      "table": Style(backgroundColor: Colors.grey.shade50),
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
                    extensions: [
                      TagExtension(
                        tagsToExtend: {"img"},
                        builder: (extensionContext) {
                          String imgUrl = extensionContext.attributes['src'] ?? '';
                          if (imgUrl.contains('10.0.2.2:8000')) {
                              imgUrl = imgUrl.replaceFirst('10.0.2.2:8000', '192.168.1.12:8000');
                          }
                          
                          return Padding(
                            padding: const EdgeInsets.symmetric(vertical: 12.0),
                            child: ClipRRect(
                              borderRadius: BorderRadius.circular(12),
                              child: Image.network(
                                imgUrl,
                                width: double.infinity,
                                fit: BoxFit.contain,
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
                  
                  // 🌟 Beri ruang ekstra panjang di bawah agar konten tidak tertutup 2 tombol!
                  SizedBox(height: hasAr ? 160 : 100), 
                ],
              ),
            ),
          ),
        ],
      ),

      // 👇 AREA TOMBOL MENGAMBANG
      floatingActionButtonLocation: FloatingActionButtonLocation.centerFloat,
      floatingActionButton: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 24.0),
        child: Column(
          mainAxisSize: MainAxisSize.min, // Penting agar tidak memenuhi layar
          children: [
            // 🌟 TOMBOL AR (Muncul otomatis HANYA jika ada model 3D)
            if (hasAr)
              Padding(
                padding: const EdgeInsets.only(bottom: 12.0),
                child: SizedBox(
                  width: double.infinity,
                  height: 56,
                  child: ElevatedButton(
                    onPressed: () {
                      String baseUrl = getBaseUrl(); 
                      String fullModelUrl = modelPath.startsWith('http') 
                          ? modelPath 
                          : '$baseUrl/download-model/$modelPath';

                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) => ARViewScreen(
                            title: material['title'] ?? 'AR Hologram',
                            modelUrl: fullModelUrl,
                          ),
                        ),
                      );
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.purpleAccent, // Beda warna agar mencolok
                      foregroundColor: Colors.white,
                      elevation: 6,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(28)),
                    ),
                    child: const Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.view_in_ar_rounded, size: 28),
                        SizedBox(width: 10),
                        Text(
                          'Lihat Bentuk 3D / AR',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                        ),
                      ],
                    ),
                  ),
                ),
              ),

            // 👇 TOMBOL LATIHAN (Selalu Muncul)
            SizedBox(
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
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(28)),
                ),
                child: const Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.play_circle_fill_rounded, size: 28),
                    SizedBox(width: 10),
                    Text(
                      'Mulai Latihan Sekarang',
                      style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}