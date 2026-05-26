import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'dart:io' show Platform;
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
  bool _showArHint = true;
  late String finalModelUrl;

  // Apakah platform berpotensi mendukung AR (Android/iOS).
  // Ini hanya cek platform — bukan jaminan ARCore berfungsi.
  bool get _platformMaybeSupportsAr {
    if (kIsWeb) return false;
    try {
      return Platform.isAndroid || Platform.isIOS;
    } catch (_) {
      return false;
    }
  }

  static const Color _primary = Color(0xFF2A9D8F);

  @override
  void initState() {
    super.initState();
    finalModelUrl = _sanitizeUrl(widget.modelUrl);
  }

  String _sanitizeUrl(String url) {
    String cleanUrl = url;
    if (cleanUrl.contains('/storage/')) {
      cleanUrl = cleanUrl.replaceFirst('/storage/', '/api/download-model/');
    }
    cleanUrl = cleanUrl
        .replaceAll('127.0.0.1', '10.0.2.2')
        .replaceAll('localhost', '10.0.2.2');
    return cleanUrl; // JANGAN encode — model_viewer_plus butuh URL plain
  }

  // Dialog jujur soal keterbatasan AR
  void _showArInfoDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.info_outline_rounded, color: _primary),
            SizedBox(width: 8),
            Expanded(
              child: Text('Tentang Mode AR',
                  style: TextStyle(fontWeight: FontWeight.bold, fontSize: 17)),
            ),
          ],
        ),
        content: const Text(
          'Mode AR menampilkan objek 3D di kameramu menggunakan Google '
          'Play Services for AR (ARCore).\n\n'
          'Fitur ini hanya berfungsi di perangkat yang mendukung ARCore. '
          'Jika layar menjadi hitam atau keluar sendiri, berarti perangkatmu '
          'belum mendukung AR sepenuhnya — kamu tetap bisa melihat model 3D '
          'secara interaktif di sini.',
          style: TextStyle(height: 1.5, fontSize: 14),
        ),
        actions: [
          ElevatedButton(
            onPressed: () => Navigator.pop(context),
            style: ElevatedButton.styleFrom(
              backgroundColor: _primary,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10)),
            ),
            child: const Text('Mengerti'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final bool showArButton = _platformMaybeSupportsAr;

    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        title: Text(
          widget.title,
          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
          overflow: TextOverflow.ellipsis,
        ),
        backgroundColor: _primary,
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          if (showArButton)
            IconButton(
              icon: const Icon(Icons.help_outline_rounded),
              tooltip: 'Info Mode AR',
              onPressed: _showArInfoDialog,
            ),
        ],
      ),
      body: Stack(
        children: [
          // 3D viewer — selalu jalan di semua device.
          // ar: true membuat model_viewer_plus menampilkan tombol AR
          // bawaan HANYA jika WebView mendeteksi dukungan AR.
          ModelViewer(
            src: finalModelUrl,
            backgroundColor: Colors.white,
            ar: showArButton,
            arModes: const ['scene-viewer', 'webxr', 'quick-look'],
            autoRotate: true,
            cameraControls: true,
            onWebViewCreated: (controller) async {
              await Future.delayed(const Duration(seconds: 3));
              if (mounted) setState(() => isLoading = false);
            },
          ),

          // Loading overlay
          if (isLoading)
            Container(
              color: Colors.white,
              child: Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const CircularProgressIndicator(color: _primary),
                    const SizedBox(height: 20),
                    Text(
                      'Memuat Model 3D...',
                      style: TextStyle(
                        color: Colors.grey[700],
                        fontSize: 15,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'Putar dan zoom dengan jari',
                      style: TextStyle(color: Colors.grey[400], fontSize: 13),
                    ),
                  ],
                ),
              ),
            ),

          // Hint kontrol 3D — di bawah tengah
          if (!isLoading)
            Positioned(
              bottom: 20,
              left: 0,
              right: 0,
              child: Center(
                child: Container(
                  padding: const EdgeInsets.symmetric(
                      horizontal: 16, vertical: 8),
                  decoration: BoxDecoration(
                    color: Colors.black.withOpacity(0.6),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: const Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.threed_rotation_rounded,
                          color: Colors.white, size: 16),
                      SizedBox(width: 6),
                      Text(
                        'Seret untuk memutar • Cubit untuk zoom',
                        style: TextStyle(color: Colors.white, fontSize: 12),
                      ),
                    ],
                  ),
                ),
              ),
            ),

          // Petunjuk tombol AR — mengarah ke tombol bawaan di kanan bawah.
          if (!isLoading && showArButton && _showArHint)
            Positioned(
              bottom: 55,
              right: 8,
              child: GestureDetector(
                onTap: () => setState(() => _showArHint = false),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 14, vertical: 10),
                      decoration: BoxDecoration(
                        color: _primary,
                        borderRadius: BorderRadius.circular(12),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.25),
                            blurRadius: 10,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: const Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.view_in_ar_rounded,
                              color: Colors.white, size: 18),
                          SizedBox(width: 8),
                          Text(
                            'Tap tombol di bawah untuk\nlihat di kamera (AR)',
                            style: TextStyle(
                                color: Colors.white,
                                fontSize: 12,
                                fontWeight: FontWeight.w600,
                                height: 1.3),
                          ),
                          SizedBox(width: 4),
                          Icon(Icons.close_rounded,
                              color: Colors.white70, size: 14),
                        ],
                      ),
                    ),
                    const SizedBox(height: 2),
                    // Panah mengarah lurus ke bawah ke tombol AR bawaan
                    const Padding(
                      padding: EdgeInsets.only(right: 10),
                      child: Icon(Icons.keyboard_arrow_down_rounded,
                          color: _primary, size: 32),
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