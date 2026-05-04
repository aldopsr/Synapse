// lib/screens/ar_gallery_screen.dart

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import '../services/auth_service.dart'; // Untuk ambil getBaseUrl
import 'ar_view_screen.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ArGalleryScreen extends StatefulWidget {
  @override
  _ArGalleryScreenState createState() => _ArGalleryScreenState();
}

class _ArGalleryScreenState extends State<ArGalleryScreen> {
  List _arAssets = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _fetchArAssets();
  }

  Future<void> _fetchArAssets() async {
  try {
    SharedPreferences prefs = await SharedPreferences.getInstance();
    String? token = prefs.getString('token');

    final response = await http.get(
      Uri.parse('${getBaseUrl()}/ar-gallery'),
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

      // 🌟 PRINT setelah data di-decode, SEBELUM setState
      if (dataList.isNotEmpty) {
        print("Jumlah aset: ${dataList.length}");
        print("Asset pertama LENGKAP: ${dataList[0]}");
        print("Field 'image' pertama: '${dataList[0]['image']}'");
        print("Tipe field image: ${dataList[0]['image'].runtimeType}");
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
      setState(() => _isLoading = false);
    }
  } catch (e, stackTrace) {
    print("Error ambil galeri: $e");
    print("Stack: $stackTrace");
    setState(() => _isLoading = false);
  }
}

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text("Galeri Hologram AR")),
      body: _isLoading 
        ? Center(child: CircularProgressIndicator())
        : GridView.builder(
            padding: EdgeInsets.all(10),
            gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2, // 2 kolom
              childAspectRatio: 0.8,
              crossAxisSpacing: 10,
              mainAxisSpacing: 10,
            ),
            itemCount: _arAssets.length,
            itemBuilder: (context, index) {
              final asset = _arAssets[index];
              return GestureDetector(
                onTap: () {
  // 1. Ambil path model 3D dari database (misal: "models/DRKr1P6...glb")
  String modelPath = asset['model_3d_path'] ?? '';
  
  if (modelPath.isNotEmpty) {
    // 2. Rangkai URL menggunakan JALUR RESMI yang baru dibuat
    // Kita gunakan getBaseUrl() yang mengarah ke '/api'
    String baseUrl = getBaseUrl(); 
    
    String fullModelUrl = modelPath.startsWith('http') 
        ? modelPath 
        : '$baseUrl/download-model/$modelPath'; // 🌟 Mengarah ke route Laravel yang baru

    // 3. Luncurkan halaman AR Viewer!
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => ARViewScreen( // Pastikan nama class-nya sesuai dengan punya Kapten
          title: asset['title'] ?? 'AR Hologram',
          modelUrl: fullModelUrl,
        ),
      ),
    );
  } else {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('File 3D belum tersedia untuk aset ini.')),
    );
  }
},
                child: Card(
  elevation: 4,
  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
  child: Column(
    children: [
      Expanded(
        child: ClipRRect(
          borderRadius: BorderRadius.vertical(top: Radius.circular(15)),
          // 🌟 PERBAIKAN: Cek apakah gambar null atau tidak
          child: asset['image'] != null
              ? Image.network(
                  asset['image'], 
                  fit: BoxFit.cover,
                  width: double.infinity,
                  // Tambahan pengaman jika URL salah/gambar gagal dimuat
                  errorBuilder: (context, error, stackTrace) {
                    return Container(
                      color: Colors.grey[300],
                      child: Icon(Icons.broken_image, size: 50, color: Colors.grey),
                    );
                  },
                )
              : Container(
                  color: Colors.grey[300],
                  width: double.infinity,
                  child: Icon(Icons.image_not_supported, size: 50, color: Colors.grey),
                ),
        ),
      ),
      Padding(
        padding: EdgeInsets.all(8.0),
        // 🌟 PERBAIKAN: Beri nilai default jika title null
        child: Text(
          asset['title'] ?? 'Tanpa Judul', 
          style: TextStyle(fontWeight: FontWeight.bold),
          textAlign: TextAlign.center,
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
        ),
      ),
    ],
  ),
),
              );
            },
          ),
    );
  }
}