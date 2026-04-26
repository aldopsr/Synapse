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
            src: widget.modelUrl,
            backgroundColor: Colors.white,
            ar: true,
            arModes: const ['scene-viewer', 'webxr', 'quick-look'],
            autoRotate: true,
            cameraControls: true,

            // simulasi selesai load
            onWebViewCreated: (controller) async {
              await Future.delayed(const Duration(seconds: 2));
              setState(() => isLoading = false);
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