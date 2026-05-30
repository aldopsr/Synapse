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
  String _name = '';
  String _nim = '';
  String _kelas = '';
  String _role = '';
  bool _isLoading = true;
  bool _isGuest = false;

  static const Color _primary = Color(0xFF2A9D8F);
  static const Color _darkTeal = Color(0xFF16877B);
  static const Color _bg = Color(0xFFF6F7FB);
  static const Color _textDark = Color(0xFF1F2937);
  static const Color _textMuted = Color(0xFF94A3B8);
  static const Color _softTeal = Color(0xFFEAFBF5);
  static const Color _purple = Color(0xFF7C3AED);
  static const Color _orange = Color(0xFFF4A62A);

  final String baseUrl = AppConstants.baseUrl;

  @override
  void initState() {
    super.initState();
    _fetchUserData();
  }

  Future<void> _fetchUserData() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');

    if (token == null || token.isEmpty) {
      setState(() {
        _isGuest = true;
        _isLoading = false;
      });
      return;
    }

    try {
      final res = await http.get(
        Uri.parse('$baseUrl/auth/me'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );

      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        final user = data['user'] ?? data;

        setState(() {
          _name = user['name'] ?? '';
          _nim = user['nim'] ?? '';
          _kelas = user['kelas'] ?? '';
          _role = user['role'] ?? '';
          _isLoading = false;
        });
      } else {
        setState(() {
          _name = 'Gagal memuat';
          _isLoading = false;
        });
      }
    } catch (_) {
      setState(() {
        _name = 'Koneksi terputus';
        _isLoading = false;
      });
    }
  }

  Future<void> _logout() async {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => const Center(
        child: CircularProgressIndicator(color: _primary),
      ),
    );

    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('token');
    await prefs.remove('user');

    if (mounted) {
      Navigator.pop(context);
      Navigator.pushAndRemoveUntil(
        context,
        MaterialPageRoute(builder: (_) => const LoginScreen()),
        (r) => false,
      );
    }
  }

  void _confirmLogout() {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        backgroundColor: Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        title: const Text(
          'Keluar dari SYNAPSE?',
          style: TextStyle(fontWeight: FontWeight.w900),
        ),
        content: const Text('Kamu bisa login kembali kapan saja.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _logout();
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.redAccent,
              foregroundColor: Colors.white,
              elevation: 0,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(14),
              ),
            ),
            child: const Text(
              'Keluar',
              style: TextStyle(fontWeight: FontWeight.w900),
            ),
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

  Widget _buildMainView() {
    final bool isMahasiswa = _role == 'mahasiswa';
    final bool isPublic = _role == 'public';
    final String initial = _name.isNotEmpty ? _name[0].toUpperCase() : '?';
    final String roleLabel = isPublic ? 'PUBLIC USER' : 'STUDENT ID';

    return SafeArea(
      child: ListView(
        padding: const EdgeInsets.fromLTRB(20, 18, 20, 120),
        physics: const BouncingScrollPhysics(),
        children: [
          _buildTopBar(),
          const SizedBox(height: 18),
          _buildIdCard(
            initial: initial,
            roleLabel: roleLabel,
            isPublic: isPublic,
          ),
          const SizedBox(height: 18),
          if (isPublic) ...[
            _buildPublicInfo(),
            const SizedBox(height: 18),
          ],
          _buildMenuSection(),
          const SizedBox(height: 18),
          _buildActionButtons(),
        ],
      ),
    );
  }

  Widget _buildTopBar() {
    return Row(
      children: [
        Image.asset(
          'assets/images/logo_synapse.png',
          width: 36,
          height: 36,
          color: _primary,
          errorBuilder: (_, __, ___) => const Icon(
            Icons.auto_awesome_rounded,
            color: _primary,
            size: 32,
          ),
        ),
        const SizedBox(width: 10),
        const Expanded(
          child: Text(
            'Profile',
            style: TextStyle(
              color: _textDark,
              fontSize: 24,
              fontWeight: FontWeight.w900,
            ),
          ),
        ),
        Container(
          width: 42,
          height: 42,
          decoration: BoxDecoration(
            color: _softTeal,
            borderRadius: BorderRadius.circular(14),
          ),
          child: const Icon(
            Icons.verified_user_rounded,
            color: _primary,
          ),
        ),
      ],
    );
  }

  Widget _buildIdCard({
  required String initial,
  required String roleLabel,
  required bool isPublic,
}) {
  return Container(
    height: 235,
    padding: const EdgeInsets.all(18),
    decoration: BoxDecoration(
      gradient: const LinearGradient(
        colors: [
          Color(0xFF65C8D0),
          Color(0xFF2A9D8F),
          Color(0xFF16877B),
        ],
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
      ),
      borderRadius: BorderRadius.circular(28),
      boxShadow: [
        BoxShadow(
          color: _primary.withOpacity(0.25),
          blurRadius: 24,
          offset: const Offset(0, 12),
        ),
      ],
    ),
    child: Stack(
      children: [
        Positioned(
          right: -40,
          bottom: -45,
          child: Image.asset(
            'assets/images/logo_synapse.png',
            width: 150,
            height: 150,
            color: Colors.white.withOpacity(0.08),
          ),
        ),

        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Image.asset(
                  'assets/images/logo_synapse.png',
                  width: 28,
                  height: 28,
                  color: Colors.white,
                ),
                const SizedBox(width: 9),
                const Text(
                  'SYNAPSE ID CARD',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 12,
                    fontWeight: FontWeight.w900,
                    letterSpacing: 1.2,
                  ),
                ),
                const Spacer(),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 10,
                    vertical: 5,
                  ),
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.22),
                    borderRadius: BorderRadius.circular(99),
                  ),
                  child: Text(
                    roleLabel,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 10,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                ),
              ],
            ),

            const SizedBox(height: 24),

            Row(
              children: [
                Container(
                  width: 76,
                  height: 92,
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.22),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(
                      color: Colors.white.withOpacity(0.55),
                      width: 2,
                    ),
                  ),
                  child: Center(
                    child: Text(
                      initial,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 34,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'NAME',
                        style: TextStyle(
                          color: Colors.white70,
                          fontSize: 9,
                          fontWeight: FontWeight.w900,
                          letterSpacing: 1,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        _name,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 21,
                          height: 1.1,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                      const SizedBox(height: 10),
                      Text(
                        isPublic
                            ? 'Pengguna Umum'
                            : (_kelas.isNotEmpty ? _kelas : 'Mahasiswa'),
                        style: TextStyle(
                          color: Colors.white.withOpacity(0.86),
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),

            const Spacer(),

            Container(
              padding: const EdgeInsets.symmetric(vertical: 10),
              decoration: BoxDecoration(
                border: Border(
                  top: BorderSide(
                    color: Colors.white.withOpacity(0.22),
                  ),
                ),
              ),
              child: Row(
                children: [
                  Expanded(
                    child: _buildMiniIdInfo(
                      label: 'ROLE',
                      value: isPublic ? 'PUBLIC' : 'MAHASISWA',
                    ),
                  ),
                  Expanded(
                    child: _buildMiniIdInfo(
                      label: 'NIM',
                      value: _nim.isNotEmpty ? _nim : '-',
                    ),
                  ),
                  Expanded(
                    child: _buildMiniIdInfo(
                      label: 'CLASS',
                      value: _kelas.isNotEmpty ? _kelas : '-',
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ],
    ),
  );
}

  Widget _buildMiniIdInfo({
    required String label,
    required String value,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: TextStyle(
            color: Colors.white.withOpacity(0.58),
            fontSize: 9,
            fontWeight: FontWeight.w900,
            letterSpacing: 0.9,
          ),
        ),
        const SizedBox(height: 4),
        Text(
          value,
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
          style: const TextStyle(
            color: Colors.white,
            fontSize: 12,
            fontWeight: FontWeight.w900,
          ),
        ),
      ],
    );
  }

  Widget _buildDetailGrid({
    required bool isMahasiswa,
    required bool isPublic,
  }) {
    final items = <Widget>[
      _buildDetailTile(
        icon: Icons.person_outline_rounded,
        label: 'Nama',
        value: _name,
        color: _primary,
        bgColor: _softTeal,
      ),
      _buildDetailTile(
        icon: Icons.verified_user_outlined,
        label: 'Role',
        value: isPublic ? 'Pengguna Umum' : 'Mahasiswa',
        color: isPublic ? _orange : _primary,
        bgColor: isPublic ? const Color(0xFFFFF7DF) : _softTeal,
      ),
      if (isMahasiswa && _nim.isNotEmpty)
        _buildDetailTile(
          icon: Icons.badge_outlined,
          label: 'NIM',
          value: _nim,
          color: _purple,
          bgColor: const Color(0xFFF2EEFF),
        ),
      if (isMahasiswa && _kelas.isNotEmpty)
        _buildDetailTile(
          icon: Icons.class_outlined,
          label: 'Kelas',
          value: _kelas,
          color: const Color(0xFF2D9CDB),
          bgColor: const Color(0xFFEAF7FF),
        ),
    ];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Account Details',
          style: TextStyle(
            color: _textDark,
            fontSize: 18,
            fontWeight: FontWeight.w900,
          ),
        ),
        const SizedBox(height: 12),
        GridView.count(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          crossAxisCount: 2,
          mainAxisSpacing: 12,
          crossAxisSpacing: 12,
          childAspectRatio: 1.72,
          children: items,
        ),
      ],
    );
  }

  Widget _buildDetailTile({
    required IconData icon,
    required String label,
    required String value,
    required Color color,
    required Color bgColor,
  }) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.045),
            blurRadius: 16,
            offset: const Offset(0, 7),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            width: 42,
            height: 42,
            decoration: BoxDecoration(
              color: bgColor,
              borderRadius: BorderRadius.circular(15),
            ),
            child: Icon(icon, color: color, size: 22),
          ),
          const SizedBox(width: 11),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  label,
                  style: const TextStyle(
                    color: _textMuted,
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 3),
                Text(
                  value,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    color: _textDark,
                    fontSize: 13,
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPublicInfo() {
    return Container(
      padding: const EdgeInsets.all(17),
      decoration: BoxDecoration(
        color: const Color(0xFFFFF7DF),
        borderRadius: BorderRadius.circular(24),
      ),
      child: const Row(
        children: [
          Icon(Icons.info_outline_rounded, color: _orange, size: 28),
          SizedBox(width: 13),
          Expanded(
            child: Text(
              'Akun umum dapat mengakses materi dan kuis yang tersedia untuk publik.',
              style: TextStyle(
                color: _textDark,
                fontSize: 13,
                height: 1.45,
                fontWeight: FontWeight.w700,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMenuSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Learning Tools',
          style: TextStyle(
            color: _textDark,
            fontSize: 18,
            fontWeight: FontWeight.w900,
          ),
        ),
        const SizedBox(height: 12),
        _buildMenuCard(
          icon: Icons.sticky_note_2_rounded,
          color: _primary,
          bgColor: _softTeal,
          title: 'Catatan Saya',
          subtitle: 'Lihat & kelola semua catatan',
          onTap: () => Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const NotesScreen()),
          ),
        ),
        const SizedBox(height: 12),
        _buildMenuCard(
          icon: Icons.history_edu_rounded,
          color: _purple,
          bgColor: const Color(0xFFF2EEFF),
          title: 'Riwayat Kuis',
          subtitle: 'Hasil, statistik & review jawaban',
          onTap: () => Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const QuizStatisticScreen()),
          ),
        ),
      ],
    );
  }

  Widget _buildMenuCard({
    required IconData icon,
    required Color color,
    required Color bgColor,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
  }) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        borderRadius: BorderRadius.circular(24),
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.all(15),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(24),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.045),
                blurRadius: 16,
                offset: const Offset(0, 7),
              ),
            ],
          ),
          child: Row(
            children: [
              Container(
                width: 54,
                height: 54,
                decoration: BoxDecoration(
                  color: bgColor,
                  borderRadius: BorderRadius.circular(18),
                ),
                child: Icon(icon, color: color, size: 25),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: const TextStyle(
                        color: _textDark,
                        fontSize: 15,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      subtitle,
                      style: const TextStyle(
                        color: _textMuted,
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
              ),
              Icon(Icons.chevron_right_rounded,
                  color: Colors.grey[350], size: 26),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildActionButtons() {
    return Row(
      children: [
        Expanded(
          child: ElevatedButton.icon(
            onPressed: () => Navigator.push(
              context,
              MaterialPageRoute(
                builder: (_) => const ChangePasswordScreen(),
              ),
            ),
            icon: const Icon(Icons.lock_outline_rounded, size: 16),
            label: const Text('Password'),
            style: ElevatedButton.styleFrom(
              backgroundColor: _primary,
              foregroundColor: Colors.white,
              elevation: 0,
              padding: const EdgeInsets.symmetric(vertical: 14),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(16),
              ),
            ),
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: OutlinedButton.icon(
            onPressed: _confirmLogout,
            icon: const Icon(Icons.logout_rounded, size: 16),
            label: const Text('Keluar'),
            style: OutlinedButton.styleFrom(
              foregroundColor: Colors.redAccent,
              side: const BorderSide(color: Colors.redAccent),
              padding: const EdgeInsets.symmetric(vertical: 14),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(16),
              ),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildGuestView() {
    return Scaffold(
      backgroundColor: _bg,
      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.fromLTRB(22, 24, 22, 120),
          physics: const BouncingScrollPhysics(),
          children: [
            _buildTopBar(),
            const SizedBox(height: 24),
            Container(
              padding: const EdgeInsets.all(22),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [
                    Color(0xFF65C8D0),
                    Color(0xFF2A9D8F),
                    Color(0xFF16877B),
                  ],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(32),
              ),
              child: Column(
                children: [
                  Container(
                    width: 94,
                    height: 94,
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.22),
                      shape: BoxShape.circle,
                      border: Border.all(
                        color: Colors.white.withOpacity(0.55),
                        width: 2,
                      ),
                    ),
                    child: const Icon(
                      Icons.person_outline_rounded,
                      size: 48,
                      color: Colors.white,
                    ),
                  ),
                  const SizedBox(height: 18),
                  const Text(
                    'Mode Tamu',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 26,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Masuk untuk menyimpan progres, catatan, dan statistik belajarmu.',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      color: Colors.white.withOpacity(0.88),
                      fontSize: 13,
                      height: 1.45,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 22),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: () => Navigator.pushReplacement(
                        context,
                        MaterialPageRoute(
                            builder: (_) => const LoginScreen()),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.white,
                        foregroundColor: _primary,
                        elevation: 0,
                        padding: const EdgeInsets.symmetric(vertical: 15),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(18),
                        ),
                      ),
                      child: const Text(
                        'Login / Daftar',
                        style: TextStyle(
                          fontWeight: FontWeight.w900,
                          fontSize: 15,
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 18),
            _buildMenuCard(
              icon: Icons.auto_awesome_rounded,
              color: const Color(0xFF2D9CDB),
              bgColor: const Color(0xFFEAF7FF),
              title: 'Gabung ke SYNAPSE',
              subtitle: 'Buka fitur belajar yang lebih lengkap',
              onTap: () => Navigator.pushReplacement(
                context,
                MaterialPageRoute(builder: (_) => const LoginScreen()),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _blob(double size, Color color) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(size),
      ),
    );
  }
}