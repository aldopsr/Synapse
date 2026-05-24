import 'package:flutter/material.dart';
import '../services/auth_service.dart';
import 'login_screen.dart';
import 'materials_screen.dart';
import 'quiz_list_screen.dart';
import 'chatbot_screen.dart';
import 'profile_screen.dart';
import 'ar_gallery_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int    _selectedIndex = 0;
  bool   _isGuest       = true;
  bool   _isPublic      = false;
  bool   _isLoading     = true;
  String _userRole      = '';

  // FIX: ganti blueAccent ke teal SYNAPSE
  static const Color _primary = Color(0xFF2A9D8F);

  final List<Widget> _pages = [
    const MaterialsScreen(),
    const QuizListScreen(),
    const ChatbotScreen(),
    const ProfileScreen(),
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
        if (userData == null) {
          _isGuest  = true;
          _isPublic = false;
          _userRole = '';
        } else {
          _userRole = userData['role'] ?? '';
          _isGuest  = false;
          _isPublic = _userRole == 'public';
        }
        _isLoading = false;
      });
    }
  }

  bool get _canAccessQuiz    => !_isGuest;
  bool get _canAccessChatbot => !_isGuest;

  void _showAccessDeniedDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Row(
          children: [
            Icon(Icons.lock_rounded, color: _primary),
            SizedBox(width: 8),
            Text('Login dulu yuk!',
                style: TextStyle(fontWeight: FontWeight.bold)),
          ],
        ),
        content: const Text(
          'Fitur ini hanya untuk pengguna terdaftar. '
          'Daftar gratis untuk akses kuis, AI tutor, dan model AR!',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Nanti', style: TextStyle(color: Colors.grey)),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              Navigator.push(
                context,
                MaterialPageRoute(builder: (context) => const LoginScreen()),
              );
            },
            // FIX: pakai theme otomatis (sudah set di main.dart)
            child: const Text('Login / Daftar'),
          ),
        ],
      ),
    );
  }

  void _onItemTapped(int index) {
    if (index == 1 && !_canAccessQuiz) {
      _showAccessDeniedDialog();
      return;
    }
    if (index == 2 && !_canAccessChatbot) {
      _showAccessDeniedDialog();
      return;
    }
    setState(() => _selectedIndex = index);
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(
        body: Center(
          child: CircularProgressIndicator(color: _primary),
        ),
      );
    }

    return Scaffold(
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
                color: _primary.withOpacity(0.15),
                blurRadius: 20,
                offset: const Offset(0, 10),
              ),
            ],
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceEvenly,
            children: [
              _buildNavItem(0, Icons.menu_book_rounded, 'Materi',   isLocked: false),
              _buildNavItem(1, Icons.quiz_rounded,      'Kuis',     isLocked: !_canAccessQuiz),
              _buildCenterButton(),
              _buildNavItem(2, Icons.smart_toy_rounded, 'Tanya AI', isLocked: !_canAccessChatbot),
              _buildNavItem(3, Icons.person_rounded,    'Profil',   isLocked: false),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildNavItem(int index, IconData icon, String label,
      {bool isLocked = false}) {
    final bool isSelected = _selectedIndex == index;

    return GestureDetector(
      onTap: () => _onItemTapped(index),
      behavior: HitTestBehavior.opaque,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 300),
        curve: Curves.easeInOut,
        padding: EdgeInsets.symmetric(
            horizontal: isSelected ? 12 : 8, vertical: 8),
        decoration: BoxDecoration(
          // FIX: pakai _primary bukan blueAccent
          color: isSelected
              ? _primary.withOpacity(0.1)
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
                  // FIX: pakai _primary bukan blueAccent
                  color: isSelected ? _primary : Colors.blueGrey[400],
                  size: 22,
                ),
                const SizedBox(height: 2),
                if (isSelected)
                  Text(
                    label,
                    style: const TextStyle(
                      // FIX: pakai _primary bukan blueAccent
                      color: _primary,
                      fontSize: 10,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
              ],
            ),
            if (isLocked)
              Positioned(
                top: -2,
                right: -4,
                child: Container(
                  padding: const EdgeInsets.all(2),
                  decoration: const BoxDecoration(
                    color: Colors.redAccent,
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(Icons.lock, color: Colors.white, size: 8),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildCenterButton() {
    return GestureDetector(
      onTap: () => Navigator.push(
        context,
        MaterialPageRoute(builder: (context) => const ArGalleryScreen()),
      ),
      child: Container(
        width: 56,
        height: 56,
        decoration: BoxDecoration(
          gradient: const LinearGradient(
            colors: [Color(0xFF2A9D8F), Color(0xFF1F7A6D)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          shape: BoxShape.circle,
          boxShadow: [
            BoxShadow(
              color: _primary.withOpacity(0.4),
              blurRadius: 12,
              offset: const Offset(0, 6),
            ),
          ],
        ),
        child: const Icon(Icons.view_in_ar_rounded, color: Colors.white, size: 26),
      ),
    );
  }
}