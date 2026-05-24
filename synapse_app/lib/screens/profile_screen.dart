import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import 'login_screen.dart';
import 'change_password_screen.dart';
import '../utils/constants.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  String _name  = "Memuat...";
  String _nim   = "";
  String _kelas = "";
  bool   _isLoading = true;
  bool   _isGuest   = false;

  // TAMBAHAN: simpan role untuk bedakan tampilan
  String _role = '';

  final String baseUrl = AppConstants.baseUrl;

  @override
  void initState() {
    super.initState();
    _fetchUserData();
  }

  Future<void> _fetchUserData() async {
    SharedPreferences prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');

    if (token == null || token.isEmpty) {
      setState(() {
        _isGuest = true;
        _isLoading = false;
      });
      return;
    }

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
        // Backend bisa return { user: {...} } atau langsung { name: ... }
        final user = data['user'] ?? data;
        setState(() {
          _name  = user['name']  ?? 'Unknown User';
          _nim   = user['nim']   ?? '';
          _kelas = user['kelas'] ?? '';
          _role  = user['role']  ?? ''; // TAMBAHAN
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
    await prefs.remove('user'); // TAMBAHAN: bersihkan data user juga

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
    // ================== TAMPILAN TAMU ==================
    if (_isGuest) {
      return Scaffold(
        backgroundColor: Colors.grey[100],
        appBar: AppBar(
          title: const Text('Profil Tamu'),
          backgroundColor: Colors.grey[100],
          foregroundColor: Colors.blueGrey[900],
          elevation: 0,
        ),
        body: SafeArea(
          child: LayoutBuilder(
            builder: (context, constraints) {
              return SingleChildScrollView(
                child: ConstrainedBox(
                  constraints: BoxConstraints(minHeight: constraints.maxHeight),
                  child: IntrinsicHeight(
                    child: Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Icon(Icons.account_circle, size: 100, color: Colors.grey),
                          const SizedBox(height: 20),
                          const Text(
                            'Anda masuk sebagai Tamu',
                            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                          ),
                          const SizedBox(height: 10),
                          const Text(
                            'Silakan login untuk melihat profil dan fitur lengkap.',
                            textAlign: TextAlign.center,
                          ),
                          const SizedBox(height: 30),
                          ElevatedButton.icon(
                            onPressed: () {
                              Navigator.pushReplacement(
                                context,
                                MaterialPageRoute(builder: (context) => const LoginScreen()),
                              );
                            },
                            icon: const Icon(Icons.login),
                            label: const Text('Login / Daftar'),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              );
            },
          ),
        ),
      );
    }

    // ================== TAMPILAN LOGIN ==================
    final bool isMahasiswa = _role == 'mahasiswa';
    final bool isPublic    = _role == 'public';

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
                  // Teks halo nama besar
                  Text(
                    'Halo, $_name',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 28,
                      fontWeight: FontWeight.bold,
                      color: Colors.blueGrey[800],
                    ),
                  ),
                  const SizedBox(height: 30),

                  // Card informasi putih
                  Container(
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(20),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.05),
                          blurRadius: 15,
                          offset: const Offset(0, 5),
                        ),
                      ],
                    ),
                    child: Column(
                      children: [
                        // Banner status — TAMBAHAN: beda warna untuk public vs mahasiswa
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
                          decoration: BoxDecoration(
                            color: isPublic
                                ? Colors.orange.withOpacity(0.1)
                                : Colors.teal.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Row(
                            children: [
                              Icon(
                                isPublic ? Icons.person_rounded : Icons.school_rounded,
                                color: isPublic ? Colors.orange[700] : Colors.teal,
                              ),
                              const SizedBox(width: 10),
                              Text(
                                isPublic ? 'Status: Pengguna Umum' : 'Status: Pengguna Aktif',
                                style: TextStyle(
                                  color: isPublic ? Colors.orange[700] : Colors.teal,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 20),

                        // Detail — nama selalu muncul
                        _buildInfoItem(Icons.person, "Nama Lengkap", _name),

                        // TAMBAHAN: NIM & Kelas hanya untuk mahasiswa
                        if (isMahasiswa && _nim.isNotEmpty) ...[
                          const Divider(height: 30, color: Colors.black12),
                          _buildInfoItem(Icons.badge_rounded, "NIM / ID", _nim),
                        ],
                        if (isMahasiswa && _kelas.isNotEmpty) ...[
                          const Divider(height: 30, color: Colors.black12),
                          _buildInfoItem(Icons.class_rounded, "Kelas", _kelas),
                        ],

                        // TAMBAHAN: info kuota chatbot untuk public
                        if (isPublic) ...[
                          const Divider(height: 30, color: Colors.black12),
                          Container(
                            width: double.infinity,
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: Colors.orange.withOpacity(0.06),
                              borderRadius: BorderRadius.circular(10),
                              border: Border.all(color: Colors.orange.withOpacity(0.2)),
                            ),
                            child: Text(
                              'ℹ️  Akun umum dapat mengakses materi & kuis bertanda "Umum", serta chatbot (maks 5 pesan/hari).',
                              style: TextStyle(fontSize: 12, color: Colors.orange[800]),
                            ),
                          ),
                        ],

                        const SizedBox(height: 30),

                        // Tombol bawah — TIDAK DIUBAH
                        Row(
                          children: [
                            Expanded(
                              child: OutlinedButton(
                                onPressed: _confirmLogout,
                                style: OutlinedButton.styleFrom(
                                  foregroundColor: Colors.teal,
                                  side: const BorderSide(color: Colors.teal, width: 1.5),
                                  padding: const EdgeInsets.symmetric(vertical: 14),
                                  shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(10)),
                                ),
                                child: const Text('LOGOUT',
                                    style: TextStyle(fontWeight: FontWeight.bold)),
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: ElevatedButton(
                                onPressed: () {
                                  Navigator.push(
                                    context,
                                    MaterialPageRoute(
                                        builder: (context) => const ChangePasswordScreen()),
                                  );
                                },
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: Colors.teal,
                                  foregroundColor: Colors.white,
                                  padding: const EdgeInsets.symmetric(vertical: 14),
                                  shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(10)),
                                  elevation: 0,
                                ),
                                child: const Text('UBAH PASSWORD',
                                    style: TextStyle(fontWeight: FontWeight.bold)),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
    );
  }

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
              Text(value,
                  style: const TextStyle(
                      fontSize: 16, color: Colors.black87, fontWeight: FontWeight.w600)),
            ],
          ),
        ),
      ],
    );
  }
}