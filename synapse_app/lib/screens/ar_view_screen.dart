import 'package:flutter/material.dart';
import 'package:model_viewer_plus/model_viewer_plus.dart';

class ARViewScreen extends StatefulWidget {
  final String modelUrl;
  final String title;

  const ARViewScreen({
    super.key,
    required this.modelUrl,
    required this.title,
  });

  @override
  State<ARViewScreen> createState() => _ARViewScreenState();
}

class _ARViewScreenState extends State<ARViewScreen> {
  bool isLoading = true;
  bool showHint = true;
  late String finalModelUrl;

  @override
  void initState() {
    super.initState();
    finalModelUrl = _sanitizeUrl(widget.modelUrl);
    print("🚀 URL Aset 3D yang dieksekusi: $finalModelUrl");
  }

  // Fungsi khusus untuk memperbaiki URL dan membelokkan CORS
  String _sanitizeUrl(String url) {
    String cleanUrl = url;

    // 🌟 1. BELOKKAN JALUR STORAGE KE JALUR API BEBAS CORS
    // Backend punya route khusus: /api/download-model/{path}
    // yang memberikan header Access-Control-Allow-Origin: *
    //
    // Contoh:
    // INPUT  : http://192.168.1.14:8000/storage/ar_models/file.glb
    // OUTPUT : http://192.168.1.14:8000/api/download-model/ar_models/file.glb
    if (cleanUrl.contains('/storage/')) {
      cleanUrl = cleanUrl.replaceFirst('/storage/', '/api/download-model/');
    }

    // 2. Ubah localhost / 127.0.0.1 menjadi 10.0.2.2 (Khusus Emulator Android)
    if (cleanUrl.contains('127.0.0.1')) {
      cleanUrl = cleanUrl.replaceAll('127.0.0.1', '10.0.2.2');
    } else if (cleanUrl.contains('localhost')) {
      cleanUrl = cleanUrl.replaceAll('localhost', '10.0.2.2');
    }

    // 3. Pastikan URL di-encode dengan benar
    return Uri.encodeFull(cleanUrl);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.title),
        backgroundColor: Colors.blueAccent,
      ),
      body: Stack(
        children: [
          // 🔥 Model Viewer
          ModelViewer(
            src: finalModelUrl,
            backgroundColor: Colors.white,
            ar: true,
            arModes: const ['scene-viewer', 'webxr', 'quick-look'],
            autoRotate: true,
            cameraControls: true,

            // simulasi selesai load
            onWebViewCreated: (controller) async {
              await Future.delayed(const Duration(seconds: 2));
              if (mounted) {
                setState(() => isLoading = false);
              }
            },
          ),

          // 🔥 Loading overlay
          if (isLoading)
            Container(
              color: Colors.white,
              child: Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const CircularProgressIndicator(
                      color: Colors.blueAccent,
                    ),
                    const SizedBox(height: 16),
                    Text(
                      "Memuat Hologram...\nMohon tunggu sebentar",
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        color: Colors.grey[700],
                        fontSize: 16,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
              ),
            ),

          // 🔥 Hint tombol AR
          if (!isLoading && showHint)
            Positioned(
              bottom: 90,
              right: 16,
              child: GestureDetector(
                onTap: () => setState(() => showHint = false),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 12, vertical: 8),
                      decoration: BoxDecoration(
                        color: Colors.black87,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: const Text(
                        "Tekan untuk lihat AR",
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 12,
                        ),
                      ),
                    ),
                    const SizedBox(height: 4),
                    const Icon(
                      Icons.arrow_downward,
                      color: Colors.black,
                    ),
                  ],
                ),
              ),
            ),
        ],
      ),
    );
  }
}