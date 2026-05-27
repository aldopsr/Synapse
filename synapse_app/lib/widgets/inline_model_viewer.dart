// lib/widgets/inline_model_viewer.dart
//
// 3D Model Viewer yang di-EMBED langsung di halaman Detail Materi.
// Pengganti tombol "Lihat AR" -> sekarang model 3D tampil inline di Canvas.
//
// Keputusan teknis penting:
// - Memakai model_viewer_plus (SUDAH ada di pubspec, terbukti jalan).
// - ar: false  -> mematikan handoff ARCore yang menjadi tersangka utama
//   black screen sebelumnya. Render 3D murni jauh lebih stabil.
// - URL di-normalisasi sesuai AppConstants.baseUrl (jangan hardcode 10.0.2.2).
//
// Widget ini self-contained: punya state loading sendiri, tinggi tetap,
// dan border bertema premium SYNAPSE.

import 'package:flutter/material.dart';
import 'package:model_viewer_plus/model_viewer_plus.dart';
import '../utils/constants.dart';

class InlineModelViewer extends StatefulWidget {
  /// URL/relatif path model dari backend. Bisa berupa:
  ///  - URL penuh (http...)
  ///  - path "/storage/..." (akan diubah ke endpoint download-model)
  ///  - nama file relatif (akan diprefix baseUrl/download-model/)
  final String rawModelSource;
  final String title;

  /// Tinggi area viewer. Default cukup besar untuk "wow" tapi tidak makan
  /// seluruh layar.
  final double height;

  const InlineModelViewer({
    super.key,
    required this.rawModelSource,
    required this.title,
    this.height = 320,
  });

  @override
  State<InlineModelViewer> createState() => _InlineModelViewerState();
}

class _InlineModelViewerState extends State<InlineModelViewer> {
  static const Color _primary = Color(0xFF2A9D8F);
  static const Color _accentPurple = Color(0xFFA855F7);

  bool _isLoading = true;
  late final String _finalUrl;

  @override
  void initState() {
    super.initState();
    _finalUrl = _normalizeUrl(widget.rawModelSource);
  }

  /// Normalisasi URL model agar konsisten dengan host backend yang aktif.
  /// PENTING: mengikuti AppConstants.baseUrl — TIDAK lagi memaksa 10.0.2.2,
  /// karena project sekarang memakai IP fisik di constants.dart.
  String _normalizeUrl(String src) {
    if (src.isEmpty) return src;

    // Host backend tanpa "/api" untuk asset, dengan "/api" untuk endpoint.
    final String apiBase = AppConstants.baseUrl; // ...:8000/api
    final String host = apiBase.replaceAll('/api', ''); // ...:8000

    String url = src;

    // Jika path storage -> arahkan ke endpoint streaming model.
    if (url.contains('/storage/')) {
      url = url.replaceFirst('/storage/', '/api/download-model/');
    }

    if (url.startsWith('http')) {
      // Sudah URL penuh. Jangan paksa ganti host — kecuali jelas localhost.
      url = url
          .replaceAll('http://localhost', host)
          .replaceAll('http://127.0.0.1', host);
      return url;
    }

    // Path/relatif: prefix dengan endpoint download-model.
    if (url.startsWith('/')) {
      return '$host$url';
    }
    return '$apiBase/download-model/$url';
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.fromLTRB(20, 16, 20, 0),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(24),
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [Color(0xFF0F172A), Color(0xFF1E293B)],
        ),
        border: Border.all(color: _accentPurple.withOpacity(0.35), width: 1.5),
        boxShadow: [
          BoxShadow(
            color: _accentPurple.withOpacity(0.18),
            blurRadius: 20,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header bar bertema sci-fi
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 14, 16, 10),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: _accentPurple.withOpacity(0.2),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.threed_rotation_rounded,
                        color: _accentPurple, size: 18),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'PROYEKSI HOLOGRAM 3D',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                            letterSpacing: 1.2,
                          ),
                        ),
                        Text(
                          widget.title,
                          style: TextStyle(
                            color: Colors.white.withOpacity(0.6),
                            fontSize: 11,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),

            // Area render 3D
            SizedBox(
              height: widget.height,
              width: double.infinity,
              child: Stack(
                children: [
                  ModelViewer(
                    key: ValueKey(_finalUrl),
                    src: _finalUrl,
                    backgroundColor: const Color(0xFF0F172A),
                    ar: false, // <-- KUNCI: AR dimatikan, hindari crash ARCore
                    autoRotate: true,
                    cameraControls: true,
                    disableZoom: false,
                    onWebViewCreated: (controller) async {
                      // Beri waktu WebView memuat model lalu lepas overlay.
                      await Future.delayed(const Duration(seconds: 2));
                      if (mounted) setState(() => _isLoading = false);
                    },
                  ),
                  if (_isLoading)
                    Container(
                      color: const Color(0xFF0F172A),
                      child: const Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            CircularProgressIndicator(
                                color: _accentPurple, strokeWidth: 2.5),
                            SizedBox(height: 16),
                            Text(
                              'Memuat proyeksi...',
                              style: TextStyle(
                                  color: Colors.white70, fontSize: 13),
                            ),
                          ],
                        ),
                      ),
                    ),
                ],
              ),
            ),

            // Hint kontrol
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 10, 16, 14),
              child: Row(
                children: [
                  Icon(Icons.touch_app_rounded,
                      color: Colors.white.withOpacity(0.5), size: 14),
                  const SizedBox(width: 6),
                  Text(
                    'Seret untuk memutar • Cubit untuk zoom',
                    style: TextStyle(
                        color: Colors.white.withOpacity(0.5), fontSize: 11),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}