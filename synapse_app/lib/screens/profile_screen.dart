// profile_screen.dart
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import 'login_screen.dart';
import 'change_password_screen.dart';
import '../utils/constants.dart';
import 'quiz_statistic_screen.dart';
import 'notes_screen.dart';

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
  String _role = '';

  final String baseUrl = AppConstants.baseUrl;

  static const Color _primaryColor = Color(0xFF2A9D8F);
  static const Color _softBg       = Color(0xFFF0FDFB);

  @override
  void initState() {
    super.initState();
    _fetchUserData();
  }

  Future<void> _fetchUserData() async {
    SharedPreferences prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');

    if (token == null || token.isEmpty) {
      setState(() { _isGuest = true; _isLoading = false; });
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
        final user = data['user'] ?? data;
        setState(() {
          _name  = user['name']  ?? 'Unknown User';
          _nim   = user['nim']   ?? '';
          _kelas = user['kelas'] ?? '';
          _role  = user['role']  ?? '';
          _isLoading = false;
        });
      } else {
        setState(() { _name = "Gagal memuat profil"; _isLoading = false; });
      }
    } catch (e) {
      setState(() { _name = "Koneksi terputus"; _isLoading = false; });
    }
  }

  Future<void> _logout(BuildContext context) async {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => const Center(child: CircularProgressIndicator(color: _primaryColor)),
    );

    SharedPreferences prefs = await SharedPreferences.getInstance();
    await prefs.remove('token');
    await prefs.remove('user');

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
        title: const Text('Putus Koneksi?', style: TextStyle(fontWeight: FontWeight.bold)),
        content: const Text('Apakah Kapten yakin ingin keluar dari sistem?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Batal', style: TextStyle(color: Colors.grey, fontWeight: FontWeight.bold)),
          ),
          ElevatedButton(
            onPressed: () { Navigator.pop(context); _logout(context); },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.redAccent,
              foregroundColor: Colors.white,
              elevation: 0,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            child: const Text('Ya, Keluar', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_isGuest) {
      return Scaffold(
        backgroundColor: Colors.grey[100],
        appBar: AppBar(
          title: const Text('Profil Tamu', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 20)),
          backgroundColor: Colors.grey[100],
          foregroundColor: Colors.blueGrey[900],
          elevation: 0,
        ),
        body: SafeArea(
          child: LayoutBuilder(
            builder: (context, constraints) {
              final double iconSize =
                  (MediaQuery.of(context).size.width * 0.28).clamp(80.0, 120.0);
              return SingleChildScrollView(
                child: ConstrainedBox(
                  constraints: BoxConstraints(minHeight: constraints.maxHeight),
                  child: IntrinsicHeight(
                    child: Center(
                      child: Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 32),
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Container(
                              padding: const EdgeInsets.all(20),
                              decoration: BoxDecoration(
                                color: _primaryColor.withOpacity(0.08),
                                shape: BoxShape.circle,
                              ),
                              child: Icon(Icons.account_circle_rounded, size: iconSize, color: _primaryColor.withOpacity(0.6)),
                            ),
                            const SizedBox(height: 24),
                            const Text(
                              'Anda masuk sebagai Tamu',
                              style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Color(0xFF1A1A2E)),
                              textAlign: TextAlign.center,
                            ),
                            const SizedBox(height: 10),
                            Text(
                              'Silakan login untuk melihat profil dan fitur lengkap aplikasi SYNAPSE.',
                              textAlign: TextAlign.center,
                              style: TextStyle(color: Colors.grey[600], fontSize: 14, height: 1.4),
                            ),
                            const SizedBox(height: 32),
                            ElevatedButton.icon(
                              onPressed: () => Navigator.pushReplacement(
                                context,
                                MaterialPageRoute(builder: (context) => const LoginScreen()),
                              ),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: _primaryColor,
                                foregroundColor: Colors.white,
                                minimumSize: const Size(200, 48),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                                elevation: 0,
                              ),
                              icon: const Icon(Icons.login_rounded),
                              label: const Text('Login / Daftar', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                            ),
                          ],
                        ),
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

    final bool isMahasiswa = _role == 'mahasiswa';
    final bool isPublic    = _role == 'public';

    return Scaffold(
      backgroundColor: Colors.grey[100],
      appBar: AppBar(
        title: const Text('Profil',
            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 22)),
        backgroundColor: Colors.grey[100],
        foregroundColor: Colors.blueGrey[900],
        elevation: 0,
      ),
      body: SafeArea(
        child: _isLoading
            ? const Center(child: CircularProgressIndicator(color: _primaryColor))
            : Center(
                child: ConstrainedBox(
                  constraints: const BoxConstraints(maxWidth: 480),
                  child: SingleChildScrollView(
                    physics: const BouncingScrollPhysics(),
                    padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 10),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.center,
                      children: [
                        Container(
                          width: 84, height: 84,
                          decoration: BoxDecoration(
                            color: _primaryColor.withOpacity(0.12),
                            shape: BoxShape.circle,
                            border: Border.all(color: _primaryColor.withOpacity(0.3), width: 2),
                          ),
                          child: Center(
                            child: Text(
                              _name.isNotEmpty ? _name[0].toUpperCase() : '?',
                              style: const TextStyle(fontSize: 32, fontWeight: FontWeight.bold, color: _primaryColor),
                            ),
                          ),
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'Halo, $_name',
                          textAlign: TextAlign.center,
                          overflow: TextOverflow.ellipsis,
                          maxLines: 2,
                          style: TextStyle(
                            fontSize: 24,
                            fontWeight: FontWeight.bold,
                            color: Colors.blueGrey[900],
                          ),
                        ),
                        const SizedBox(height: 24),
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 16),
                          decoration: BoxDecoration(
                            color: isPublic ? Colors.orange.withOpacity(0.08) : _softBg,
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(
                              color: isPublic ? Colors.orange.withOpacity(0.2) : _primaryColor.withOpacity(0.2),
                              width: 1.5,
                            ),
                          ),
                          child: Row(
                            children: [
                              Icon(
                                isPublic ? Icons.person_rounded : Icons.school_rounded,
                                color: isPublic ? Colors.orange[700] : _primaryColor,
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Text(
                                  isPublic ? 'Status: Pengguna Umum' : 'Status: Pengguna Aktif',
                                  style: TextStyle(
                                    color: isPublic ? Colors.orange[700] : _primaryColor,
                                    fontWeight: FontWeight.bold,
                                    fontSize: 14,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 16),
                        _buildInfoItem(Icons.person_outline_rounded, "Nama Lengkap", _name),
                        if (isMahasiswa && _nim.isNotEmpty)
                          _buildInfoItem(Icons.badge_outlined, "NIM / ID", _nim),
                        if (isMahasiswa && _kelas.isNotEmpty)
                          _buildInfoItem(Icons.class_outlined, "Kelas", _kelas),
                        if (isPublic) ...[
                          const SizedBox(height: 6),
                          Container(
                            width: double.infinity,
                            padding: const EdgeInsets.all(14),
                            decoration: BoxDecoration(
                              color: Colors.orange.withOpacity(0.05),
                              borderRadius: BorderRadius.circular(14),
                              border: Border.all(color: Colors.orange.withOpacity(0.15)),
                            ),
                            child: Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Icon(Icons.info_outline_rounded, color: Colors.orange[800], size: 18),
                                const SizedBox(width: 10),
                                const Expanded(
                                  child: Text(
                                    'ℹ️ Akun umum dapat mengakses materi & kuis bertanda "Umum", serta chatbot (maks 5 pesan/hari).',
                                    style: TextStyle(fontSize: 13, color: Colors.black87, height: 1.4),
                                    softWrap: true,
                                    overflow: TextOverflow.visible,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                        const SizedBox(height: 32),

                        // ── Catatan Saya → NotesScreen ───────────────────
                        GestureDetector(
                          onTap: () => Navigator.push(
                            context,
                            MaterialPageRoute(
                                builder: (_) => const NotesScreen()),
                          ),
                          child: Container(
                            margin: const EdgeInsets.only(bottom: 16),
                            padding: const EdgeInsets.all(16),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(16),
                              border: Border.all(color: const Color(0xFFE2E8F0)),
                            ),
                            child: Row(
                              children: [
                                Container(
                                  width: 44, height: 44,
                                  decoration: BoxDecoration(
                                    color: const Color(0xFFFFF9E6),
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: const Icon(Icons.sticky_note_2_rounded,
                                      color: Color(0xFFFF9800), size: 22),
                                ),
                                const SizedBox(width: 14),
                                const Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text('Catatan Saya',
                                          style: TextStyle(
                                            fontWeight: FontWeight.w700,
                                            fontSize: 14,
                                            color: Color(0xFF0F172A),
                                          )),
                                      SizedBox(height: 2),
                                      Text('Lihat & kelola semua catatanmu',
                                          style: TextStyle(
                                            fontSize: 12,
                                            color: Color(0xFF94A3B8),
                                          )),
                                    ],
                                  ),
                                ),
                                const Icon(Icons.arrow_forward_ios_rounded,
                                    size: 14, color: Color(0xFF94A3B8)),
                              ],
                            ),
                          ),
                        ),

                        // ── Riwayat Kuis → QuizStatisticScreen ───────────
                        GestureDetector(
                          onTap: () => Navigator.push(
                            context,
                            MaterialPageRoute(
                                builder: (_) => const QuizStatisticScreen()),
                          ),
                          child: Container(
                            margin: const EdgeInsets.only(bottom: 16),
                            padding: const EdgeInsets.all(16),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(16),
                              border: Border.all(color: const Color(0xFFE2E8F0)),
                            ),
                            child: Row(
                              children: [
                                Container(
                                  width: 44, height: 44,
                                  decoration: BoxDecoration(
                                    color: const Color(0xFFE6F4F2),
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: const Icon(Icons.history_rounded,
                                      color: Color(0xFF2A9D8F), size: 22),
                                ),
                                const SizedBox(width: 14),
                                const Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text('Riwayat Kuis',
                                          style: TextStyle(
                                            fontWeight: FontWeight.w700,
                                            fontSize: 14,
                                            color: Color(0xFF0F172A),
                                          )),
                                      SizedBox(height: 2),
                                      Text('Lihat hasil, statistik & review jawaban',
                                          style: TextStyle(
                                            fontSize: 12,
                                            color: Color(0xFF94A3B8),
                                          )),
                                    ],
                                  ),
                                ),
                                const Icon(Icons.arrow_forward_ios_rounded,
                                    size: 14, color: Color(0xFF94A3B8)),
                              ],
                            ),
                          ),
                        ),

                        Row(
                          children: [
                            Expanded(
                              child: OutlinedButton(
                                onPressed: _confirmLogout,
                                style: OutlinedButton.styleFrom(
                                  foregroundColor: Colors.redAccent,
                                  side: const BorderSide(color: Colors.redAccent, width: 1.5),
                                  minimumSize: const Size(0, 48),
                                  shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(14)),
                                ),
                                child: const Text('LOGOUT',
                                    style: TextStyle(fontWeight: FontWeight.bold, letterSpacing: 0.5)),
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: ElevatedButton(
                                onPressed: () => Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                      builder: (context) => const ChangePasswordScreen()),
                                ),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: _primaryColor,
                                  foregroundColor: Colors.white,
                                  minimumSize: const Size(0, 48),
                                  shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(14)),
                                  elevation: 0,
                                ),
                                child: const Text('UBAH PASSWORD',
                                    style: TextStyle(fontWeight: FontWeight.bold, letterSpacing: 0.5)),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
              ),
      ),
    );
  }

  Widget _buildInfoItem(IconData icon, String title, String value) {
    return Container(
      margin: const EdgeInsets.symmetric(vertical: 6),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.03),
            blurRadius: 10,
            offset: const Offset(0, 2),
          )
        ],
      ),
      child: Row(
        children: [
          Container(
            width: 42, height: 42,
            decoration: BoxDecoration(
              color: _primaryColor.withOpacity(0.08),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(icon, color: _primaryColor, size: 20),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: TextStyle(fontSize: 11, color: Colors.grey[500], fontWeight: FontWeight.w600)),
                const SizedBox(height: 4),
                Text(
                  value,
                  style: const TextStyle(
                    fontSize: 15,
                    color: Color(0xFF1A1A2E),
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}