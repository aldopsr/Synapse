import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import 'login_screen.dart';
import 'change_password_screen.dart'; // Import halaman ganti password baru

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  String _name = "Memuat...";
  String _nim = "";
  String _kelas = "";
  bool _isLoading = true;

  // Pastikan baseUrl ini sudah sesuai dengan IP server Kapten!
  final String baseUrl = 'http://192.168.1.12:8000/api'; 

  @override
  void initState() {
    super.initState();
    _fetchUserData();
  }

  Future<void> _fetchUserData() async {
    SharedPreferences prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');

    try {
      final response = await http.get(
        Uri.parse('$baseUrl/auth/me'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        setState(() {
          _name = data['name'] ?? 'Unknown User';
          _nim = data['nim'] ?? '-';
          _kelas = data['kelas'] ?? '-';
          _isLoading = false;
        });
      } else {
        setState(() {
          _name = "Gagal memuat profil";
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _name = "Koneksi terputus";
        _isLoading = false;
      });
    }
  }

  Future<void> _logout(BuildContext context) async {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => const Center(child: CircularProgressIndicator(color: Colors.teal)),
    );

    SharedPreferences prefs = await SharedPreferences.getInstance();
    await prefs.remove('token'); 

    if (context.mounted) {
      Navigator.pop(context); 
      Navigator.pushAndRemoveUntil(
        context,
        MaterialPageRoute(builder: (context) => const LoginScreen()),
        (route) => false,
      );
    }
  }

  void _confirmLogout() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text('Putus Koneksi?'),
        content: const Text('Apakah Kapten yakin ingin keluar dari sistem?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Batal', style: TextStyle(color: Colors.grey)),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _logout(context);
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.redAccent,
              foregroundColor: Colors.white,
            ),
            child: const Text('Ya, Keluar'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[100], 
      appBar: AppBar(
        title: const Text('Profil', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 22)),
        backgroundColor: Colors.grey[100],
        foregroundColor: Colors.blueGrey[900],
        elevation: 0,
      ),
      body: _isLoading 
        ? const Center(child: CircularProgressIndicator(color: Colors.teal)) 
        : SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                // Text Halo Nama Besar
                Text(
                  'Halo, $_name',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 28, 
                    fontWeight: FontWeight.bold, 
                    color: Colors.blueGrey[800]
                  ),
                ),
                const SizedBox(height: 30),

                // Card Informasi Putih
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(20),
                    boxShadow: [
                      BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 15, offset: const Offset(0, 5)),
                    ],
                  ),
                  child: Column(
                    children: [
                      // Banner Status
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
                        decoration: BoxDecoration(
                          color: Colors.teal.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: const Row(
                          children: [
                            Icon(Icons.school_rounded, color: Colors.teal),
                            SizedBox(width: 10),
                            Text(
                              'Status: Pengguna Aktif',
                              style: TextStyle(color: Colors.teal, fontWeight: FontWeight.bold),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 20),

                      // Detail Item
                      _buildInfoItem(Icons.person, "Nama Lengkap", _name),
                      const Divider(height: 30, color: Colors.black12),
                      _buildInfoItem(Icons.badge_rounded, "NIM / ID", _nim),
                      const Divider(height: 30, color: Colors.black12),
                      _buildInfoItem(Icons.class_rounded, "Kelas", _kelas),
                      
                      const SizedBox(height: 30),

                      // Barisan Tombol Bawah
                      Row(
                        children: [
                          Expanded(
                            child: OutlinedButton(
                              onPressed: _confirmLogout,
                              style: OutlinedButton.styleFrom(
                                foregroundColor: Colors.teal,
                                side: const BorderSide(color: Colors.teal, width: 1.5),
                                padding: const EdgeInsets.symmetric(vertical: 14),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                              ),
                              child: const Text('LOGOUT', style: TextStyle(fontWeight: FontWeight.bold)),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: ElevatedButton(
                              onPressed: () {
                                Navigator.push(
                                  context,
                                  MaterialPageRoute(builder: (context) => const ChangePasswordScreen()),
                                );
                              },
                              style: ElevatedButton.styleFrom(
                                backgroundColor: Colors.teal,
                                foregroundColor: Colors.white,
                                padding: const EdgeInsets.symmetric(vertical: 14),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                elevation: 0,
                              ),
                              child: const Text('UBAH PASSWORD', style: TextStyle(fontWeight: FontWeight.bold)),
                            ),
                          ),
                        ],
                      )
                    ],
                  ),
                ),
              ],
            ),
          ),
    );
  }

  // Widget bantuan untuk baris informasi
  Widget _buildInfoItem(IconData icon, String title, String value) {
    return Row(
      children: [
        Icon(icon, color: Colors.teal, size: 28),
        const SizedBox(width: 16),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(title, style: TextStyle(fontSize: 12, color: Colors.grey[600])),
              const SizedBox(height: 4),
              Text(value, style: const TextStyle(fontSize: 16, color: Colors.black87, fontWeight: FontWeight.w600)),
            ],
          ),
        ),
      ],
    );
  }
}