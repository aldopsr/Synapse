import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/constants.dart';
import 'ar_view_screen.dart';

class ArGalleryScreen extends StatefulWidget {
  @override
  _ArGalleryScreenState createState() => _ArGalleryScreenState();
}

class _ArGalleryScreenState extends State<ArGalleryScreen> {
  List   _arAssets    = [];
  bool   _isLoading   = true;
  String? _errorMessage;

  // Warna konsisten dengan screen lain
  static const Color _primary = Color(0xFF2A9D8F);

  @override
  void initState() {
    super.initState();
    _fetchArAssets();
  }

  // ─────────────────────────────────────────────────────────────
  // FETCH — semua print() diganti debugPrint() / dihapus
  // ─────────────────────────────────────────────────────────────
  Future<void> _fetchArAssets() async {
    setState(() {
      _isLoading    = true;
      _errorMessage = null;
    });

    try {
      SharedPreferences prefs = await SharedPreferences.getInstance();
      final String? token     = prefs.getString('token');

      final response = await http.get(
        Uri.parse('${AppConstants.baseUrl}/ar-assets'),
        headers: {
          'Accept': 'application/json',
          if (token != null) 'Authorization': 'Bearer $token',
        },
      );

      if (!mounted) return;

      if (response.statusCode == 200) {
        final decoded       = jsonDecode(response.body);
        final List dataList = decoded['data'] ?? [];
        setState(() {
          _arAssets  = dataList;
          _isLoading = false;
        });
      } else {
        setState(() {
          _isLoading    = false;
          _errorMessage = 'Gagal memuat data (${response.statusCode})';
        });
      }
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isLoading    = false;
        _errorMessage = 'Koneksi bermasalah. Cek internet kamu!';
      });
    }
  }

  void _openArViewer(String? modelUrl, String title) {
    if (modelUrl == null || modelUrl.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('File 3D belum tersedia untuk aset ini.'),
          backgroundColor: Colors.orange,
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => ARViewScreen(title: title, modelUrl: modelUrl),
      ),
    );
  }

  // ─────────────────────────────────────────────────────────────
  // BUILD
  // ─────────────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],

      // AppBar — konsisten dengan QuizResultScreen & screen lain
      appBar: AppBar(
        title: const Text(
          'Galeri AR',
          style: TextStyle(
            fontWeight: FontWeight.bold,
            fontSize: 18,
            color: Color(0xFF334155),
          ),
        ),
        backgroundColor: Colors.white,
        elevation: 0,
        iconTheme: const IconThemeData(color: Color(0xFF334155)),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            onPressed: _isLoading ? null : _fetchArAssets,
            tooltip: 'Muat Ulang',
          ),
        ],
      ),

      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    // Loading
    if (_isLoading) {
      return const Center(
        child: CircularProgressIndicator(color: _primary),
      );
    }

    // Error
    if (_errorMessage != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.wifi_off_rounded, size: 64, color: Colors.grey[400]),
              const SizedBox(height: 16),
              Text(
                _errorMessage!,
                style: TextStyle(color: Colors.grey[600], fontSize: 14),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 20),
              ElevatedButton.icon(
                onPressed: _fetchArAssets,
                icon: const Icon(Icons.refresh_rounded),
                label: const Text('Coba Lagi'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: _primary,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12)),
                ),
              ),
            ],
          ),
        ),
      );
    }

    // Empty state
    if (_arAssets.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.view_in_ar_outlined, size: 80, color: Colors.grey[300]),
              const SizedBox(height: 20),
              const Text(
                'Belum ada model AR',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF334155),
                ),
              ),
              const SizedBox(height: 8),
              Text(
                'Nantikan model 3D baru dari dosenmu ✨',
                style: TextStyle(fontSize: 13, color: Colors.grey[500]),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
      );
    }

    // Grid
    return RefreshIndicator(
      onRefresh: _fetchArAssets,
      color: _primary,
      child: GridView.builder(
        padding: const EdgeInsets.all(16),
        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: 2,
          childAspectRatio: 0.78,
          crossAxisSpacing: 12,
          mainAxisSpacing: 12,
        ),
        itemCount: _arAssets.length,
        itemBuilder: (context, index) => _buildArCard(_arAssets[index]),
      ),
    );
  }

  // ─────────────────────────────────────────────────────────────
  // CARD — putih, clean, konsisten dengan card di screen lain
  // ─────────────────────────────────────────────────────────────
  Widget _buildArCard(Map<String, dynamic> asset) {
    final String? imageUrl    = asset['image_url'];
    final String? modelUrl    = asset['model_3d_url'];
    final String  title       = asset['title'] ?? 'Tanpa Judul';

    final Map<String, dynamic>? material     = asset['material'];
    final String?               materialTitle =
        material != null ? material['title'] : null;

    return GestureDetector(
      onTap: () => _openArViewer(modelUrl, title),
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.06),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        clipBehavior: Clip.antiAlias,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Thumbnail
            Expanded(
              flex: 3,
              child: _buildThumbnail(imageUrl),
            ),

            // Info bawah
            Padding(
              padding: const EdgeInsets.all(10),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  // Badge AR kecil
                  Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 7, vertical: 2),
                    decoration: BoxDecoration(
                      color: _primary.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: const Text(
                      'AR 3D',
                      style: TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.bold,
                        color: _primary,
                      ),
                    ),
                  ),
                  const SizedBox(height: 5),

                  // Judul
                  Text(
                    title,
                    style: const TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 13,
                      color: Color(0xFF334155),
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),

                  // Materi terkait
                  if (materialTitle != null) ...[
                    const SizedBox(height: 3),
                    Row(
                      children: [
                        Icon(Icons.book_outlined,
                            size: 11, color: Colors.grey[500]),
                        const SizedBox(width: 3),
                        Expanded(
                          child: Text(
                            materialTitle,
                            style: TextStyle(
                                fontSize: 11, color: Colors.grey[500]),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                    ),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildThumbnail(String? imageUrl) {
    // Tidak ada gambar
    if (imageUrl == null || imageUrl.isEmpty) {
      return Container(
        color: const Color(0xFFF0FDFB),
        child: Center(
          child: Icon(
            Icons.view_in_ar_rounded,
            size: 48,
            color: _primary.withOpacity(0.4),
          ),
        ),
      );
    }

    return Image.network(
      imageUrl,
      fit: BoxFit.cover,
      width: double.infinity,
      loadingBuilder: (context, child, loadingProgress) {
        if (loadingProgress == null) return child;
        return Container(
          color: Colors.grey[100],
          child: Center(
            child: CircularProgressIndicator(
              strokeWidth: 2,
              color: _primary,
              value: loadingProgress.expectedTotalBytes != null
                  ? loadingProgress.cumulativeBytesLoaded /
                      loadingProgress.expectedTotalBytes!
                  : null,
            ),
          ),
        );
      },
      errorBuilder: (context, error, stackTrace) {
        // print() dihapus — pakai debugPrint kalau perlu debug
        return Container(
          color: Colors.grey[100],
          child: Center(
            child: Icon(
              Icons.broken_image_outlined,
              size: 36,
              color: Colors.grey[400],
            ),
          ),
        );
      },
    );
  }
}