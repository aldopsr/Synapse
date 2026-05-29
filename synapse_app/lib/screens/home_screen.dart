// home_screen.dart
// PERUBAHAN RESPONSIVENESS:
// FIX #1 — BottomNav: ganti height: 70 hardcoded → IntrinsicHeight (aman untuk large text)
// FIX #2 — NavItem: tambah minimum touch target 44×44px via ConstrainedBox
// FIX #3 — expose static navBarHeight agar pages bisa hitung bottom padding sendiri
// FIX #4 — CenterButton: ukuran tetap 56px, sudah aman (tidak perlu diubah)
// FAB — SynapseFab assistive touch ditambahkan di Stack body

import 'package:flutter/material.dart';
import '../services/auth_service.dart';
import 'login_screen.dart';
import 'materials_screen.dart';
import 'chatbot_screen.dart';
import 'profile_screen.dart';
import 'fyp_screen.dart';
import 'duel_screen.dart';
import 'duel_waiting_screen.dart';
import '../services/fcm_service.dart';
import '../widgets/synapse_fab.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  // FIX #3: konstanta tinggi nav bar — dipakai pages untuk bottom padding
  // total = tinggi nav (64) + margin bawah (20) + sedikit buffer (8) = 92
  static const double navBarHeight = 92.0;

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int    _selectedIndex = 0;
  bool   _isGuest       = true;
  bool   _isPublic      = false;
  bool   _isLoading     = true;
  String _userRole      = '';

  static const Color _primary = Color(0xFF2A9D8F);

  final List<Widget> _pages = [
    const MaterialsScreen(),
    const FypScreen(),
    const ChatbotScreen(),
    const ProfileScreen(),
  ];

  @override
  void initState() {
    super.initState();
    _loadUserData();
    _setupFcmHandler();
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
      SynapseFabController.isGuest = _isGuest;
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

  void _setupFcmHandler() {
    FcmService.onDuelNotification = (data) {
      final type   = data['type']?.toString() ?? '';
      final duelId = data['duel_id']?.toString() ?? '';
      if (!mounted || duelId.isEmpty) return;

      if (type == 'duel_challenge') {
        Navigator.push(context, MaterialPageRoute(
          builder: (_) => DuelWaitingScreen(
            duelId: duelId, role: 'opponent'),
        ));
      } else if (type == 'duel_accepted') {
        Navigator.push(context, MaterialPageRoute(
          builder: (_) => DuelWaitingScreen(
            duelId: duelId, role: 'challenger'),
        ));
      } else if (type == 'duel_completed') {
        Navigator.push(context, MaterialPageRoute(
          builder: (_) => const DuelScreen(),
        ));
      }
    };
  }

  @override
  void dispose() {
    FcmService.onDuelNotification = null;
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(
        body: Center(child: CircularProgressIndicator(color: _primary)),
      );
    }

    // Sembunyikan FAB di tab Chatbot (index 2)
    final bool showFab = _selectedIndex != 2;

    return Scaffold(
      extendBody: true,
      body: Stack(
        children: [
          IndexedStack(
            index: _selectedIndex,
            children: _pages,
          ),
          // Assistive Touch FAB — muncul di semua tab kecuali Chatbot
          if (showFab) SynapseFab(),
        ],
      ),
      bottomNavigationBar: SafeArea(
        child: Container(
          margin: const EdgeInsets.only(left: 20, right: 20, bottom: 20),
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
          // FIX #1: IntrinsicHeight — tinggi menyesuaikan konten,
          // tidak akan terpotong saat font aksesibilitas besar.
          child: Padding(
            padding: const EdgeInsets.symmetric(vertical: 8),
            child: IntrinsicHeight(
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                children: [
                  _buildNavItem(0, Icons.menu_book_rounded, 'Materi',
                      isLocked: false),
                  _buildNavItem(1, Icons.psychology_rounded, 'FYP',
                      isLocked: !_canAccessQuiz),
                  _buildCenterButton(),
                  _buildNavItem(2, Icons.smart_toy_rounded, 'Tanya AI',
                      isLocked: !_canAccessChatbot),
                  _buildNavItem(3, Icons.person_rounded, 'Profil',
                      isLocked: false),
                ],
              ),
            ),
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
        // FIX #2: ConstrainedBox memastikan touch target min 44×44px
        constraints: const BoxConstraints(minWidth: 44, minHeight: 44),
        padding: EdgeInsets.symmetric(
          horizontal: isSelected ? 12 : 8,
          vertical: 8,
        ),
        decoration: BoxDecoration(
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
                  color: isSelected ? _primary : Colors.blueGrey[400],
                  size: 22,
                ),
                const SizedBox(height: 2),
                if (isSelected)
                  Text(
                    label,
                    style: const TextStyle(
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
    onTap: () {
      if (_isGuest) {
        _showAccessDeniedDialog();
        return;
      }
      Navigator.push(
        context,
        MaterialPageRoute(builder: (context) => const DuelScreen()),
      );
    },
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
        child: const Icon(Icons.sports_martial_arts_rounded,
            color: Colors.white, size: 26),
      ),
    );
  }
}