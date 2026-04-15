import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import 'quiz_history_screen.dart'; 
import 'login_screen.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  String _name = "Memuat identitas...";
  String _nim = "";
  String _kelas = "";
  bool _isLoading = true;

  final String baseUrl = 'http://127.0.0.1:8000/api'; 

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
          _nim = data['nim'] ?? 'NIM/ID Tidak Terdaftar';
          _kelas = data['kelas'] ?? 'Kelas Tidak Valid';
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
        _name = "Koneksi ke server terputus";
        _isLoading = false;
      });
    }
  }

  // Fungsi untuk membuang token dan kembali ke pelabuhan (Login)
  Future<void> _logout(BuildContext context) async {
    // Tampilkan loading dialog
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => const Center(child: CircularProgressIndicator(color: Colors.teal)),
    );

    SharedPreferences prefs = await SharedPreferences.getInstance();
    await prefs.remove('token'); // 🗑️ Buang kunci aksesnya!

    if (context.mounted) {
      Navigator.pop(context); // Tutup loading
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
        title: const Row(
          children: [
            Icon(Icons.exit_to_app_rounded, color: Colors.redAccent),
            SizedBox(width: 10),
            Text('Putus Koneksi?'),
          ],
        ),
        content: const Text('Apakah Kapten yakin ingin keluar dari sistem SYNAPSE?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Batal', style: TextStyle(color: Colors.grey)),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context); // Tutup dialog
              _logout(context); // Eksekusi fungsi logout
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.redAccent,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
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
      backgroundColor: Colors.grey[50], // Latar belakang abu bersih
      appBar: AppBar(
        title: const Text('Profil', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 20)),
        backgroundColor: Colors.white,
        foregroundColor: Colors.blueGrey[900],
        elevation: 1,
        shadowColor: Colors.black12,
        centerTitle: false,
        actions: [
          Padding(
            padding: const EdgeInsets.only(right: 16.0),
            child: Icon(Icons.admin_panel_settings_rounded, color: Colors.teal[400], size: 28),
          )
        ],
      ),
      body: _isLoading 
        ? const Center(child: CircularProgressIndicator(color: Colors.teal)) 
        : ListView(
            padding: const EdgeInsets.all(20),
            physics: const BouncingScrollPhysics(),
            children: [
              // --- KARTU IDENTITAS UTAMA ---
              Container(
                padding: const EdgeInsets.symmetric(vertical: 30, horizontal: 20),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(24),
                  border: Border.all(color: Colors.teal.withOpacity(0.1)),
                  boxShadow: [
                    BoxShadow(color: Colors.teal.withOpacity(0.05), blurRadius: 20, offset: const Offset(0, 10)),
                  ],
                ),
                child: Column(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(4), // Jarak border
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        border: Border.all(color: Colors.teal, width: 3),
                      ),
                      child: const CircleAvatar(
                        radius: 45,
                        backgroundColor: Colors.teal,
                        child: Icon(Icons.person_rounded, size: 50, color: Colors.white),
                      ),
                    ),
                    const SizedBox(height: 16),
                    
                    Text(
                      _name,
                      textAlign: TextAlign.center,
                      style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Colors.black87),
                    ),
                    const SizedBox(height: 8),
                    
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
                      decoration: BoxDecoration(
                        color: Colors.blueGrey[50],
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(
                        '$_nim • $_kelas',
                        style: TextStyle(fontSize: 14, color: Colors.blueGrey[700], fontWeight: FontWeight.w600, fontFamily: 'Courier'), // Font ala IT
                      ),
                    ),
                  ],
                ),
              ),
              
              const SizedBox(height: 32),

              const Padding(
                padding: EdgeInsets.only(left: 8.0),
                child: Text(
                  'Akses Sistem',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.blueGrey),
                ),
              ),
              const SizedBox(height: 12),

              // --- TOMBOL RIWAYAT KUIS ---
              _buildMenuCard(
                icon: Icons.history_edu_rounded,
                iconColor: Colors.amber[700]!,
                title: 'Log Evaluasi & Kuis',
                subtitle: 'Lihat rekam jejak nilai & analisis AI',
                onTap: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (context) => const QuizHistoryScreen(),
                    ),
                  );
                },
              ),

              const SizedBox(height: 12),

              // --- TOMBOL LOGOUT ---
              _buildMenuCard(
                icon: Icons.power_settings_new_rounded,
                iconColor: Colors.redAccent,
                title: 'Putus Koneksi (Logout)',
                subtitle: 'Keluar dari terminal SYNAPSE',
                onTap: _confirmLogout, // Panggil pop up konfirmasi dulu
              ),

              // 👇 PENYELAMAT DARI BOTTOM NAVBAR (Ganjar ruang kosong di bawah)
              const SizedBox(height: 100),
            ],
          ),
    );
  }

  // Widget bantuan untuk membuat tombol menu yang seragam dan elegan
  Widget _buildMenuCard({
    required IconData icon, 
    required Color iconColor, 
    required String title, 
    required String subtitle, 
    required VoidCallback onTap
  }) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.grey.shade200),
        boxShadow: [
          BoxShadow(color: Colors.black.withOpacity(0.02), blurRadius: 8, offset: const Offset(0, 4)),
        ],
      ),
      child: ListTile(
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        leading: Container(
          padding: const EdgeInsets.all(10),
          decoration: BoxDecoration(
            color: iconColor.withOpacity(0.1),
            borderRadius: BorderRadius.circular(12),
          ),
          child: Icon(icon, color: iconColor, size: 26),
        ),
        title: Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Colors.black87)),
        subtitle: Text(subtitle, style: TextStyle(color: Colors.grey[600], fontSize: 13)),
        trailing: Icon(Icons.arrow_forward_ios_rounded, size: 16, color: Colors.grey[400]),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        onTap: onTap,
      ),
    );
  }
}