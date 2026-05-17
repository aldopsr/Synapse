// lib/screens/ar_gallery_screen.dart

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import '../services/auth_service.dart'; 
import 'ar_view_screen.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/constants.dart';

class ArGalleryScreen extends StatefulWidget {
  @override
  _ArGalleryScreenState createState() => _ArGalleryScreenState();
}

class _ArGalleryScreenState extends State<ArGalleryScreen> {
  List _arAssets = [];
  bool _isLoading = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _fetchArAssets();
  }

  Future<void> _fetchArAssets() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      SharedPreferences prefs = await SharedPreferences.getInstance();
      String? token = prefs.getString('token');

      final response = await http.get(
        Uri.parse('${AppConstants.baseUrl}/ar-assets'),
        headers: {
          'Accept': 'application/json',
          if (token != null) 'Authorization': 'Bearer $token',
        },
      );

      print("===== DEBUG AR GALLERY =====");
      print("Status: ${response.statusCode}");
      print("Body mentah: ${response.body}");
      print("============================");

      if (response.statusCode == 200) {
        final decoded = jsonDecode(response.body);
        final List dataList = decoded['data'] ?? [];

        if (dataList.isNotEmpty) {
          print("Jumlah aset: ${dataList.length}");
          print("Asset pertama: ${dataList[0]}");
          print("image_url: '${dataList[0]['image_url']}'");
          print("model_3d_url: '${dataList[0]['model_3d_url']}'");
        } else {
          print("⚠️ dataList kosong!");
        }

        setState(() {
          _arAssets = dataList;
          _isLoading = false;
        });
      } else {
        print("Server error: ${response.statusCode}");
        print("Pesan: ${response.body}");
        setState(() {
          _isLoading = false;
          _errorMessage = 'Server error (${response.statusCode})';
        });
      }
    } catch (e, stackTrace) {
      print("Error ambil galeri: $e");
      print("Stack: $stackTrace");
      setState(() {
        _isLoading = false;
        _errorMessage = 'Gagal terhubung ke server';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text("Galeri Hologram AR"),
        actions: [
          IconButton(
            icon: Icon(Icons.refresh),
            onPressed: _isLoading ? null : _fetchArAssets,
            tooltip: 'Muat Ulang',
          ),
        ],
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return Center(child: CircularProgressIndicator());
    }

    if (_errorMessage != null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.error_outline, size: 64, color: Colors.red[300]),
            SizedBox(height: 12),
            Text(_errorMessage!, style: TextStyle(color: Colors.grey[700])),
            SizedBox(height: 12),
            ElevatedButton.icon(
              onPressed: _fetchArAssets,
              icon: Icon(Icons.refresh),
              label: Text('Coba Lagi'),
            ),
          ],
        ),
      );
    }

    if (_arAssets.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.view_in_ar_outlined, size: 80, color: Colors.grey[400]),
            SizedBox(height: 16),
            Text(
              'Belum ada aset AR tersedia',
              style: TextStyle(fontSize: 16, color: Colors.grey[600]),
            ),
            SizedBox(height: 8),
            Text(
              'Tunggu dosen menambahkan aset baru ✨',
              style: TextStyle(fontSize: 13, color: Colors.grey[500]),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _fetchArAssets,
      child: GridView.builder(
        padding: EdgeInsets.all(10),
        gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: 2,
          childAspectRatio: 0.75,
          crossAxisSpacing: 10,
          mainAxisSpacing: 10,
        ),
        itemCount: _arAssets.length,
        itemBuilder: (context, index) {
          final asset = _arAssets[index];
          return _buildArCard(asset);
        },
      ),
    );
  }

  Widget _buildArCard(Map<String, dynamic> asset) {
    // 🌟 Pakai field BARU dari endpoint /ar-assets
    final String? imageUrl = asset['image_url'];
    final String? modelUrl = asset['model_3d_url'];
    final String title = asset['title'] ?? 'Tanpa Judul';
    final String? description = asset['description'];

    // Info materi terkait (jika ada)
    final Map<String, dynamic>? material = asset['material'];
    final String? materialTitle = material != null ? material['title'] : null;

    return GestureDetector(
      onTap: () => _openArViewer(modelUrl, title),
      child: Card(
        elevation: 4,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
        clipBehavior: Clip.antiAlias,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // ====== THUMBNAIL ======
            Expanded(
              flex: 3,
              child: _buildThumbnail(imageUrl),
            ),

            // ====== INFO BAWAH ======
            Container(
              padding: EdgeInsets.all(8.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  // Judul AR
                  Text(
                    title,
                    style: TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 14,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  // Materi terkait (kalau ada)
                  if (materialTitle != null) ...[
                    SizedBox(height: 2),
                    Row(
                      children: [
                        Icon(Icons.book_outlined, size: 11, color: Colors.grey[600]),
                        SizedBox(width: 3),
                        Expanded(
                          child: Text(
                            materialTitle,
                            style: TextStyle(
                              fontSize: 11,
                              color: Colors.grey[600],
                            ),
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
    if (imageUrl == null || imageUrl.isEmpty) {
      return Container(
        color: Colors.grey[200],
        child: Icon(
          Icons.view_in_ar,
          size: 50,
          color: Colors.grey[400],
        ),
      );
    }

    return Image.network(
      imageUrl,
      fit: BoxFit.cover,
      width: double.infinity,
      // Loading indicator saat gambar dimuat
      loadingBuilder: (context, child, loadingProgress) {
        if (loadingProgress == null) return child;
        return Container(
          color: Colors.grey[100],
          child: Center(
            child: CircularProgressIndicator(
              strokeWidth: 2,
              value: loadingProgress.expectedTotalBytes != null
                  ? loadingProgress.cumulativeBytesLoaded /
                      loadingProgress.expectedTotalBytes!
                  : null,
            ),
          ),
        );
      },
      // Error fallback
      errorBuilder: (context, error, stackTrace) {
        print('Gagal load thumbnail: $imageUrl -> $error');
        return Container(
          color: Colors.grey[300],
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.broken_image, size: 40, color: Colors.grey[500]),
              SizedBox(height: 4),
              Text(
                'Gambar bermasalah',
                style: TextStyle(fontSize: 10, color: Colors.grey[600]),
              ),
            ],
          ),
        );
      },
    );
  }

  void _openArViewer(String? modelUrl, String title) {
    if (modelUrl == null || modelUrl.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('File 3D belum tersedia untuk aset ini.'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => ARViewScreen(
          title: title,
          modelUrl: modelUrl,
        ),
      ),
    );
  }
}