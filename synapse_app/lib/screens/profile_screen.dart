// lib/screens/profile_screen.dart
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
  String _name  = '';
  String _nim   = '';
  String _kelas = '';
  String _role  = '';
  bool _isLoading = true;
  bool _isGuest   = false;

  static const Color _primary = Color(0xFF2A9D8F);
  static const Color _bg      = Color(0xFFF5F7FA);

  final String baseUrl = AppConstants.baseUrl;

  @override
  void initState() { super.initState(); _fetchUserData(); }

  Future<void> _fetchUserData() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');
    if (token == null || token.isEmpty) {
      setState(() { _isGuest = true; _isLoading = false; });
      return;
    }
    try {
      final res = await http.get(
        Uri.parse('$baseUrl/auth/me'),
        headers: {'Authorization': 'Bearer $token', 'Accept': 'application/json'},
      );
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        final user = data['user'] ?? data;
        setState(() {
          _name  = user['name']  ?? '';
          _nim   = user['nim']   ?? '';
          _kelas = user['kelas'] ?? '';
          _role  = user['role']  ?? '';
          _isLoading = false;
        });
      } else {
        setState(() { _name = 'Gagal memuat'; _isLoading = false; });
      }
    } catch (_) {
      setState(() { _name = 'Koneksi terputus'; _isLoading = false; });
    }
  }

  Future<void> _logout() async {
    showDialog(
      context: context, barrierDismissible: false,
      builder: (_) => const Center(
          child: CircularProgressIndicator(color: _primary)));
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('token');
    await prefs.remove('user');
    if (mounted) {
      Navigator.pop(context);
      Navigator.pushAndRemoveUntil(context,
          MaterialPageRoute(builder: (_) => const LoginScreen()),
          (r) => false);
    }
  }

  void _confirmLogout() {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text('Keluar?',
            style: TextStyle(fontWeight: FontWeight.bold)),
        content: const Text('Yakin ingin keluar dari SYNAPSE?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Batal',
                style: TextStyle(color: Colors.grey, fontWeight: FontWeight.w600)),
          ),
          ElevatedButton(
            onPressed: () { Navigator.pop(context); _logout(); },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red,
              foregroundColor: Colors.white,
              elevation: 0,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10)),
            ),
            child: const Text('Keluar',
                style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_isGuest) return _buildGuestView();

    return Scaffold(
      backgroundColor: _bg,
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: _primary))
          : _buildMainView(),
    );
  }

  // ── Guest View ─────────────────────────────────────────────
  Widget _buildGuestView() {
    return Scaffold(
      backgroundColor: _bg,
      body: SafeArea(
        child: Center(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 32),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Container(
                  width: 96, height: 96,
                  decoration: BoxDecoration(
                    color: _primary.withOpacity(0.1),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(Icons.person_outline_rounded,
                      size: 48, color: _primary.withOpacity(0.6)),
                ),
                const SizedBox(height: 24),
                const Text('Masuk sebagai Tamu',
                    style: TextStyle(
                        fontSize: 20, fontWeight: FontWeight.bold,
                        color: Color(0xFF1A1A2E))),
                const SizedBox(height: 10),
                Text('Login untuk akses profil dan fitur lengkap.',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                        fontSize: 14, color: Colors.grey[500], height: 1.5)),
                const SizedBox(height: 32),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: () => Navigator.pushReplacement(context,
                        MaterialPageRoute(builder: (_) => const LoginScreen())),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: _primary,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(14)),
                      elevation: 0,
                    ),
                    child: const Text('Login / Daftar',
                        style: TextStyle(
                            fontWeight: FontWeight.bold, fontSize: 15)),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  // ── Main View ──────────────────────────────────────────────
  Widget _buildMainView() {
    final bool isMahasiswa = _role == 'mahasiswa';
    final bool isPublic    = _role == 'public';
    final String initial   = _name.isNotEmpty ? _name[0].toUpperCase() : '?';

    return CustomScrollView(
      physics: const BouncingScrollPhysics(),
      slivers: [
        // Header
        SliverToBoxAdapter(
          child: Container(
            color: Colors.white,
            padding: EdgeInsets.fromLTRB(
                24, MediaQuery.of(context).padding.top + 20, 24, 28),
            child: Column(children: [
              // Avatar
              Container(
                width: 80, height: 80,
                decoration: BoxDecoration(
                  color: _primary.withOpacity(0.12),
                  shape: BoxShape.circle,
                  border: Border.all(
                      color: _primary.withOpacity(0.3), width: 2),
                ),
                child: Center(
                  child: Text(initial,
                      style: const TextStyle(
                          fontSize: 30, fontWeight: FontWeight.bold,
                          color: _primary)),
                ),
              ),
              const SizedBox(height: 14),
              Text(_name,
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                      fontSize: 20, fontWeight: FontWeight.bold,
                      color: Color(0xFF0F172A))),
              const SizedBox(height: 6),
              // Role badge
              Container(
                padding: const EdgeInsets.symmetric(
                    horizontal: 12, vertical: 4),
                decoration: BoxDecoration(
                  color: isPublic
                      ? Colors.orange.withOpacity(0.1)
                      : _primary.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(99),
                ),
                child: Text(
                  isPublic ? 'Pengguna Umum' : 'Mahasiswa',
                  style: TextStyle(
                    fontSize: 12, fontWeight: FontWeight.w600,
                    color: isPublic ? Colors.orange[700] : _primary,
                  ),
                ),
              ),
            ]),
          ),
        ),

        SliverToBoxAdapter(child: const SizedBox(height: 12)),

        // Info section
        SliverToBoxAdapter(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: Column(children: [
              _buildSection('Informasi Akun', [
                _buildInfoRow(Icons.person_outline_rounded, 'Nama', _name),
                if (isMahasiswa && _nim.isNotEmpty)
                  _buildInfoRow(Icons.badge_outlined, 'NIM', _nim),
                if (isMahasiswa && _kelas.isNotEmpty)
                  _buildInfoRow(Icons.class_outlined, 'Kelas', _kelas),
              ]),

              if (isPublic) ...[
                const SizedBox(height: 8),
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: Colors.orange.withOpacity(0.06),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                        color: Colors.orange.withOpacity(0.2)),
                  ),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Icon(Icons.info_outline_rounded,
                          color: Colors.orange[700], size: 16),
                      const SizedBox(width: 8),
                      const Expanded(
                        child: Text(
                          'Akun umum dapat akses materi & kuis bertanda "Umum", serta chatbot (maks 5 pesan/hari).',
                          style: TextStyle(
                              fontSize: 12, color: Colors.black87,
                              height: 1.5),
                        ),
                      ),
                    ],
                  ),
                ),
              ],

              const SizedBox(height: 8),

              // Menu items
              _buildSection('Aktivitas', [
                _buildMenuRow(
                  icon: Icons.sticky_note_2_rounded,
                  color: const Color(0xFF2A9D8F),
                  bgColor: const Color(0xFFE6F4F2),
                  title: 'Catatan Saya',
                  subtitle: 'Lihat & kelola semua catatan',
                  onTap: () => Navigator.push(context,
                      MaterialPageRoute(builder: (_) => const NotesScreen())),
                ),
                _buildMenuRow(
                  icon: Icons.history_edu_rounded,
                  color: const Color(0xFF7C3AED),
                  bgColor: const Color(0xFFF0EEFF),
                  title: 'Riwayat Kuis',
                  subtitle: 'Hasil, statistik & review jawaban',
                  onTap: () => Navigator.push(context,
                      MaterialPageRoute(
                          builder: (_) => const QuizStatisticScreen())),
                ),
              ]),

              const SizedBox(height: 8),

              // Action buttons
              Row(children: [
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: _confirmLogout,
                    icon: const Icon(Icons.logout_rounded, size: 16),
                    label: const Text('Keluar'),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: Colors.red,
                      side: const BorderSide(color: Colors.red),
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: ElevatedButton.icon(
                    onPressed: () => Navigator.push(context,
                        MaterialPageRoute(
                            builder: (_) => const ChangePasswordScreen())),
                    icon: const Icon(Icons.lock_outline_rounded, size: 16),
                    label: const Text('Password'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: _primary,
                      foregroundColor: Colors.white,
                      elevation: 0,
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
                ),
              ]),

              const SizedBox(height: 100),
            ]),
          ),
        ),
      ],
    );
  }

  // ── Helpers ────────────────────────────────────────────────
  Widget _buildSection(String title, List<Widget> children) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 14, 16, 8),
            child: Text(title,
                style: TextStyle(
                    fontSize: 11, fontWeight: FontWeight.w700,
                    color: Colors.grey[500],
                    letterSpacing: 0.5)),
          ),
          const Divider(height: 1, indent: 16, endIndent: 16),
          ...children,
        ],
      ),
    );
  }

  Widget _buildInfoRow(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 12),
      child: Row(children: [
        Icon(icon, size: 18, color: _primary),
        const SizedBox(width: 12),
        Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(label,
              style: TextStyle(
                  fontSize: 11, color: Colors.grey[500],
                  fontWeight: FontWeight.w500)),
          const SizedBox(height: 2),
          Text(value,
              style: const TextStyle(
                  fontSize: 14, fontWeight: FontWeight.w600,
                  color: Color(0xFF0F172A))),
        ]),
      ]),
    );
  }

  Widget _buildMenuRow({
    required IconData icon,
    required Color color,
    required Color bgColor,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Padding(
        padding: const EdgeInsets.fromLTRB(16, 12, 16, 12),
        child: Row(children: [
          Container(
            width: 40, height: 40,
            decoration: BoxDecoration(
                color: bgColor, borderRadius: BorderRadius.circular(10)),
            child: Icon(icon, color: color, size: 20),
          ),
          const SizedBox(width: 12),
          Expanded(child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(title,
                  style: const TextStyle(
                      fontSize: 14, fontWeight: FontWeight.w600,
                      color: Color(0xFF0F172A))),
              Text(subtitle,
                  style: TextStyle(fontSize: 12, color: Colors.grey[500])),
            ],
          )),
          Icon(Icons.chevron_right_rounded,
              color: Colors.grey[400], size: 20),
        ]),
      ),
    );
  }
}