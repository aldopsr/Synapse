import 'package:flutter/material.dart';
import '../services/auth_service.dart';
import 'login_screen.dart';
import 'materials_screen.dart';
import 'quiz_list_screen.dart';
import 'chatbot_screen.dart';
import 'profile_screen.dart';
import 'ar_gallery_screen.dart';
// ar_view_screen.dart dihapus dari import — tidak dipakai di sini,
// AR dibuka via Navigator.push langsung dari _buildCenterButton.

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int  _selectedIndex = 0;
  bool _isGuest       = true;
  bool _isLoading     = true;

  // ─────────────────────────────────────────────────────────────
  // _pages hanya berisi 4 screen (index 0–3).
  // AR Gallery TIDAK masuk ke sini — dibuka via Navigator.push
  // terpisah dari _buildCenterButton supaya tidak terjadi
  // RangeError pada IndexedStack.
  // ─────────────────────────────────────────────────────────────
  final List<Widget> _pages = [
    const MaterialsScreen(),  // index 0
    const QuizListScreen(),   // index 1
    const ChatbotScreen(),    // index 2
    const ProfileScreen(),    // index 3
  ];

  @override
  void initState() {
    super.initState();
    _loadUserData();
  }

  Future<void> _loadUserData() async {
    final auth     = AuthService();
    final userData = await auth.getUserProfile();

    if (mounted) {
      setState(() {
        _isGuest   = userData == null;
        _isLoading = false;
      });
    }
  }

  // ─────────────────────────────────────────────────────────────
  // DIALOG AKSES GUEST — pesan lebih friendly
  // ─────────────────────────────────────────────────────────────
  void _showAccessDeniedDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Row(
          children: [
            Icon(Icons.lock_rounded, color: Colors.blueAccent),
            SizedBox(width: 8),
            Text('Login dulu yuk!', style: TextStyle(fontWeight: FontWeight.bold)),
          ],
        ),
        content: const Text(
          'Fitur ini hanya untuk pengguna terdaftar. Daftar gratis dan akses semua kuis, AI tutor, dan model AR! 🚀',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Nanti Aja', style: TextStyle(color: Colors.grey)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.blueAccent,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8)),
            ),
            onPressed: () {
              Navigator.pop(context);
              Navigator.pushReplacement(
                context,
                MaterialPageRoute(
                    builder: (context) => const LoginScreen()),
              );
            },
            child: const Text('Login / Daftar',
                style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  // ─────────────────────────────────────────────────────────────
  // _onItemTapped hanya menangani index 0–3 (sesuai _pages).
  // Index di luar range tidak mungkin terjadi karena center button
  // tidak memanggil fungsi ini.
  // ─────────────────────────────────────────────────────────────
  void _onItemTapped(int index) {
    if (_isGuest && (index == 1 || index == 2)) {
      _showAccessDeniedDialog();
      return;
    }
    setState(() => _selectedIndex = index);
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return Scaffold(
        backgroundColor: Colors.grey[100],
        body: const Center(
            child: CircularProgressIndicator(color: Colors.blueAccent)),
      );
    }

    return Scaffold(
      backgroundColor: Colors.grey[100],
      extendBody: true,

      body: IndexedStack(
        index: _selectedIndex,
        children: _pages,
      ),

      bottomNavigationBar: SafeArea(
        child: Container(
          margin: const EdgeInsets.only(left: 20, right: 20, bottom: 20),
          height: 70,
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(35),
            boxShadow: [
              BoxShadow(
                color: Colors.blueAccent.withOpacity(0.15),
                blurRadius: 20,
                offset: const Offset(0, 10),
              ),
            ],
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceEvenly,
            children: [
              _buildNavItem(0, Icons.menu_book_rounded, "Materi",
                  isLocked: false),
              _buildNavItem(1, Icons.quiz_rounded, "Kuis",
                  isLocked: _isGuest),
              _buildCenterButton(),
              _buildNavItem(2, Icons.smart_toy_rounded, "Tanya AI",
                  isLocked: _isGuest),
              _buildNavItem(3, Icons.person_rounded, "Profil",
                  isLocked: false),
            ],
          ),
        ),
      ),
    );
  }

  // ── WIDGET HELPERS — TAMPILAN TIDAK DIUBAH ───────────────────

  Widget _buildNavItem(int index, IconData icon, String label,
      {bool isLocked = false}) {
    final bool isSelected = _selectedIndex == index;

    return GestureDetector(
      onTap: () => _onItemTapped(index),
      behavior: HitTestBehavior.opaque,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 300),
        curve: Curves.easeInOut,
        padding:
            EdgeInsets.symmetric(horizontal: isSelected ? 12 : 8, vertical: 8),
        decoration: BoxDecoration(
          color: isSelected
              ? Colors.blueAccent.withOpacity(0.1)
              : Colors.transparent,
          borderRadius: BorderRadius.circular(20),
        ),
        child: Stack(
          clipBehavior: Clip.none,
          alignment: Alignment.center,
          children: [
            Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(
                  icon,
                  color: isSelected ? Colors.blueAccent : Colors.grey[400],
                  size: isSelected ? 26 : 24,
                ),
                if (isSelected) ...[
                  const SizedBox(height: 2),
                  Text(
                    label,
                    style: const TextStyle(
                      color: Colors.blueAccent,
                      fontSize: 10,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ],
            ),
            if (isLocked)
              Positioned(
                top: -2,
                right: -4,
                child: Container(
                  padding: const EdgeInsets.all(2),
                  decoration: BoxDecoration(
                    color: Colors.redAccent,
                    shape: BoxShape.circle,
                    border: Border.all(color: Colors.white, width: 1.5),
                  ),
                  child: const Icon(Icons.lock, size: 8, color: Colors.white),
                ),
              ),
          ],
        ),
      ),
    );
  }

  // ─────────────────────────────────────────────────────────────
  // CENTER BUTTON — AR Gallery
  // FIX BUG #2: pakai Navigator.push terpisah, BUKAN _onItemTapped,
  // supaya tidak menyentuh IndexedStack yang hanya punya 4 halaman.
  // ─────────────────────────────────────────────────────────────
  Widget _buildCenterButton() {
    return GestureDetector(
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(builder: (context) => ArGalleryScreen()),
        );
      },
      child: Container(
        height: 55,
        width: 55,
        decoration: BoxDecoration(
          gradient: const LinearGradient(
            colors: [Colors.blueAccent, Colors.lightBlue],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          shape: BoxShape.circle,
          boxShadow: [
            BoxShadow(
              color: Colors.blueAccent.withOpacity(0.4),
              blurRadius: 12,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: const Icon(
          Icons.view_in_ar_rounded,
          color: Colors.white,
          size: 28,
        ),
      ),
    );
  }
}