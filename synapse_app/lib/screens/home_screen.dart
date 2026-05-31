// lib/screens/home_screen.dart
// REVISION IMPLEMENTATION: High-Opacity Solid-Glass Navbar & 3D Polished Center Button
// 1. HIGH-OPACITY NAVBAR: Meningkatkan kepekatan warna putih (85% - 92%) agar kontras di atas bg Teal Chatbot.
// 2. 3D POLISHED CENTER BUTTON: Menghapus border, menggunakan gradien sferis, double shadow, dan glossy inner sheen untuk efek 3D premium.
// 3. FIXED GRID SYSTEM: Menjaga navigasi tetap presisi, sinkron, dan anti-terpotong.

import 'package:flutter/material.dart';
import '../services/auth_service.dart';
import 'login_screen.dart';
import 'materials_screen.dart';
import 'chatbot_screen.dart';
import 'profile_screen.dart';
import 'fyp_screen.dart';
import 'duel_screen.dart';
import '../services/fcm_service.dart';
import '../widgets/synapse_fab.dart';
import 'course_selection_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

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

  static const Color _primary = Color(0xFF2A9D8F); // Master Teal

  // PENGATURAN INDEX SINKRONISASI HALAMAN
  final List<Widget> _pages = [
    const CourseSelectionScreen(), // Index 0 -> Materi
    const ChatbotScreen(),         // Index 1 -> Chat (Chatbot)
    const FypScreen(),             // Index 2 -> FYP
    const ProfileScreen(),         // Index 3 -> Profil
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
            Text('Login dulu yuk!', style: TextStyle(fontWeight: FontWeight.bold)),
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
            style: ElevatedButton.styleFrom(backgroundColor: _primary),
            child: const Text('Login / Daftar'),
          ),
        ],
      ),
    );
  }

  void _onItemTapped(int index) {
    if (index == 1 && !_canAccessChatbot) { 
      _showAccessDeniedDialog();
      return;
    }
    if (index == 2 && !_canAccessQuiz) { 
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

      if (type == 'duel_challenge' ||
          type == 'duel_accepted'  ||
          type == 'duel_completed') {
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

    final bool showFab = _selectedIndex != 1;

    return Scaffold(
      extendBody: true,
      body: Stack(
        children: [
          IndexedStack(
            index: _selectedIndex,
            children: _pages,
          ),
          if (showFab) SynapseFab(),
        ],
      ),
      bottomNavigationBar: Container(
        decoration: const BoxDecoration(
          color: Color(0xFF0D2B25),
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
        child: SafeArea(
          top: false,
          child: SizedBox(
            height: 68,
            child: Stack(
              alignment: Alignment.center,
              clipBehavior: Clip.none,
              children: [
                // Row ikon kiri & kanan
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 4),
                  child: Row(
                    children: [
                      Expanded(
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                          children: [
                            _buildNavItem(0, Icons.menu_book_rounded, 'Materi'),
                            _buildNavItem(1, Icons.smart_toy_rounded, 'Chat', isLocked: !_canAccessChatbot),
                          ],
                        ),
                      ),
                      const SizedBox(width: 72),
                      Expanded(
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                          children: [
                            _buildNavItem(2, Icons.explore_rounded, 'FYP', isLocked: !_canAccessQuiz),
                            _buildNavItem(3, Icons.person_rounded, 'Profil'),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),

                // Center button naik ke atas navbar
                Positioned(
                  top: -20,
                  child: _buildCenterButton(),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildNavItem(
    int index,
    IconData icon,
    String label, {
    bool isLocked = false,
  }) {
    final bool isSelected = _selectedIndex == index;
    const Color navInactive = Color(0xFF5A7A8A);

    return GestureDetector(
      onTap: () => _onItemTapped(index),
      behavior: HitTestBehavior.opaque,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 250),
        curve: Curves.easeOutCubic,
        constraints: const BoxConstraints(minWidth: 46, minHeight: 46),
        padding: EdgeInsets.symmetric(
          horizontal: isSelected ? 14 : 6,
          vertical: 6,
        ),
        decoration: BoxDecoration(
          color: isSelected ? const Color(0xFF183C34) : Colors.transparent,
          borderRadius: BorderRadius.circular(20),
          border: isSelected
              ? Border.all(color: _primary.withOpacity(0.30), width: 1)
              : null,
        ),
        child: Stack(
          clipBehavior: Clip.none,
          alignment: Alignment.center,
          children: [
            Column(
              mainAxisSize: MainAxisSize.min,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(
                  icon,
                  color: isSelected ? _primary : navInactive,
                  size: 22,
                ),
                AnimatedSize(
                  duration: const Duration(milliseconds: 200),
                  curve: Curves.easeOutCubic,
                  child: isSelected
                      ? Padding(
                          padding: const EdgeInsets.only(top: 3),
                          child: Text(
                            label,
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 10,
                              fontWeight: FontWeight.w700,
                              letterSpacing: 0.2,
                            ),
                          ),
                        )
                      : const SizedBox.shrink(),
                ),
              ],
            ),
            if (isLocked)
              Positioned(
                top: -2,
                right: -4,
                child: Container(
                  padding: const EdgeInsets.all(2.5),
                  decoration: const BoxDecoration(
                    color: Colors.redAccent,
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.lock_rounded,
                    color: Colors.white,
                    size: 8,
                  ),
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
        MaterialPageRoute(builder: (context) => DuelScreen()),
      ),
      child: Container(
        width: 60,
        height: 60,
        decoration: BoxDecoration(
          shape: BoxShape.circle,
          color: const Color(0xFF0D2B25),
        ),
        child: Padding(
          padding: const EdgeInsets.all(5),
          child: Container(
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: _primary,
              boxShadow: [
                BoxShadow(
                  color: _primary.withOpacity(0.5),
                  blurRadius: 14,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Center(
              child: SizedBox(
                width: 24,
                height: 24,
                child: CustomPaint(
                  painter: CrossedSwordsPainter(color: Colors.white),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

// ===========================================================================
// CUSTOM PAINTER: NOTCHED GLASS PAINTER (ORGANIC BÉZIER CURVE)
// ===========================================================================
class NotchedGlassPainter extends CustomPainter {
  final Color borderColor;
  final List<Color> gradientColors;

  NotchedGlassPainter({required this.borderColor, required this.gradientColors});

  @override
  void paint(Canvas canvas, Size size) {
    final double radius = 32; 
    final double cx = size.width / 2; 
    final double nw = 44; 
    final double nh = 22; 

    final path = Path();
    
    path.moveTo(radius, 0);
    path.lineTo(cx - nw, 0);
    
    path.cubicTo(
      cx - nw + 14, 0,
      cx - 20, nh,
      cx, nh,
    );
    path.cubicTo(
      cx + 20, nh,
      cx + nw - 14, 0,
      cx + nw, 0,
    );
    
    path.lineTo(size.width - radius, 0);
    path.arcToPoint(Offset(size.width, radius), radius: Radius.circular(radius));
    path.lineTo(size.width, size.height - radius);
    path.arcToPoint(Offset(size.width - radius, size.height), radius: Radius.circular(radius));
    path.lineTo(radius, size.height);
    path.arcToPoint(Offset(0, size.height - radius), radius: Radius.circular(radius));
    path.lineTo(0, radius);
    path.arcToPoint(Offset(radius, 0), radius: Radius.circular(radius));
    path.close();

    final paintFill = Paint()
      ..shader = LinearGradient(
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
        colors: gradientColors,
      ).createShader(Rect.fromLTWH(0, 0, size.width, size.height))
      ..style = PaintingStyle.fill;
    canvas.drawPath(path, paintFill);

    final paintStroke = Paint()
      ..color = borderColor
      ..strokeWidth = 1.6
      ..style = PaintingStyle.stroke;
    canvas.drawPath(path, paintStroke);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

// ===========================================================================
// CUSTOM PAINTER: IKON PEDANG SILANG POLOS MINIMALIS
// ===========================================================================
class CrossedSwordsPainter extends CustomPainter {
  final Color color;
  CrossedSwordsPainter({required this.color});

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = color
      ..strokeWidth = 2.2
      ..strokeCap = StrokeCap.round
      ..style = PaintingStyle.stroke;

    final double w = size.width;
    final double h = size.height;

    canvas.drawLine(Offset(w * 0.22, h * 0.22), Offset(w * 0.68, h * 0.68), paint);
    canvas.drawLine(Offset(w * 0.70, h * 0.70), Offset(w * 0.82, h * 0.82), paint);
    canvas.drawLine(Offset(w * 0.62, h * 0.74), Offset(w * 0.74, h * 0.62), paint);

    canvas.drawLine(Offset(w * 0.78, h * 0.22), Offset(w * 0.32, h * 0.68), paint);
    canvas.drawLine(Offset(w * 0.30, h * 0.70), Offset(w * 0.18, h * 0.82), paint);
    canvas.drawLine(Offset(w * 0.38, h * 0.74), Offset(w * 0.26, h * 0.62), paint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}