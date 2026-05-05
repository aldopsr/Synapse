import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';
import 'practice_screen.dart';
import '../utils/constants.dart';
import 'ar_view_screen.dart'; 
import '../services/auth_service.dart'; 

class MaterialDetailScreen extends StatelessWidget {
  final Map<String, dynamic> material;

  const MaterialDetailScreen({super.key, required this.material});

  
  @override
  Widget build(BuildContext context) {
    print("Isi Data Materi: $material");

    // 🌟 DETEKSI RADAR AR (Jalur Ganda)
    // Cek versi baru (ar_assets) ATAU versi lama (has_ar)
    bool hasArNew = material['ar_assets'] != null && (material['ar_assets'] as List).isNotEmpty;
    bool hasArOld = material['has_ar'] == true || (material['model_3d_path'] != null && material['model_3d_path'].toString().isNotEmpty);
    bool hasAr = hasArNew || hasArOld;

    // 🌟 DETEKSI RADAR PRACTICE (Jalur Ganda)
    // Cek versi baru (questions) ATAU versi lama (has_practice)
    bool hasPracticeNew = material['questions'] != null && (material['questions'] as List).isNotEmpty;
    bool hasPracticeOld = material['has_practice'] == true;
    bool hasPractice = hasPracticeNew || hasPracticeOld;

    return Scaffold(
      
      body: CustomScrollView(
        physics: const BouncingScrollPhysics(),
        slivers: [
          // 👇 HEADER BESAR 
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
                  
                  // 👇 FIX: Coba ambil 'content', kalau null coba 'body', kalau null lagi baru default
                  Html(
                    data: material['content'] ?? material['body'] ?? '<p>Isi materi belum tersedia.</p>',
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
                          
                          // 👇 FIX URL GAMBAR: Ambil baseUrl tanpa '/api' di belakangnya
                          String serverDomain = getBaseUrl().replaceAll('/api', '');

                          if (imgUrl.isNotEmpty) {
                            // 1. Jika URL cuma /storage/... (Relatif dari database)
                            if (imgUrl.startsWith('/storage')) {
                              imgUrl = '$serverDomain$imgUrl';
                            } 
                            // 2. Jika URL masih nyangkut localhost/127.0.0.1
                            else if (imgUrl.contains('localhost') || imgUrl.contains('127.0.0.1') || imgUrl.contains('10.0.2.2')) {
                              imgUrl = imgUrl.replaceAll(RegExp(r'http://(localhost|127\.0\.0\.1|10\.0\.2\.2)(:\d+)?'), serverDomain);
                            }
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
                                    children: [
                                      const Icon(Icons.broken_image_rounded, color: Colors.grey, size: 40),
                                      const SizedBox(height: 8),
                                      Text('Gagal memuat gambar:\n$imgUrl', 
                                        style: const TextStyle(color: Colors.grey, fontSize: 10),
                                        textAlign: TextAlign.center,
                                      ),
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
                  
                  // Beri ruang ekstra agar konten tidak tertutup tombol di bawah
                  SizedBox(height: (hasAr && hasPractice) ? 160 : 100), 
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
          mainAxisSize: MainAxisSize.min, 
          children: [
            // 🌟 TOMBOL AR
            if (hasAr)
              Padding(
                padding: EdgeInsets.only(bottom: hasPractice ? 12.0 : 0),
                child: SizedBox(
                  width: double.infinity,
                  height: 56,
                  child: ElevatedButton(
                    onPressed: () {
                      String fullModelUrl = '';
                      String baseUrl = getBaseUrl();

                      // 1. Coba ambil dari data baru (ar_assets)
                      if (material['ar_assets'] != null && (material['ar_assets'] as List).isNotEmpty) {
                        final firstAr = (material['ar_assets'] as List).first;
                        String mPath = firstAr['model_3d_path'] ?? '';
                        fullModelUrl = mPath.startsWith('http') ? mPath : '$baseUrl/download-model/$mPath';
                      } 
                      // 2. Jika tidak ada, ambil dari data lama (model_3d_url / model_3d_path)
                      else {
                        if (material['model_3d_url'] != null) {
                          fullModelUrl = material['model_3d_url'];
                        } else {
                          String mPath = material['model_3d_path'] ?? '';
                          fullModelUrl = mPath.startsWith('http') ? mPath : '$baseUrl/download-model/$mPath';
                        }
                      }

                      // Pindah ke layar AR
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
                      backgroundColor: Colors.purpleAccent, 
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

            // 👇 TOMBOL LATIHAN (Hanya muncul jika hasPractice == true)
            if (hasPractice)
              SizedBox(
                width: double.infinity,
                height: 56,
                child: ElevatedButton(
                  onPressed: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (context) => PracticeScreen(
                          materialId: material['id'].toString(),
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