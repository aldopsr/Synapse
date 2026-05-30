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
import 'duel_waiting_screen.dart';
import '../services/fcm_service.dart';
import '../widgets/synapse_fab.dart';
import 'course_selection_screen.dart';
import 'dart:ui';

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
    const ChatbotScreen(),         // Index 1 -> Tanya AI (Chatbot)
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

      if (type == 'duel_challenge') {
        Navigator.push(context, MaterialPageRoute(
          builder: (_) => DuelWaitingScreen(duelId: duelId, role: 'opponent'),
        ));
      } else if (type == 'duel_accepted') {
        Navigator.push(context, MaterialPageRoute(
          builder: (_) => DuelWaitingScreen(duelId: duelId, role: 'challenger'),
        ));
      } else if (type == 'duel_completed') {
        Navigator.push(context, MaterialPageRoute(
          builder: (_) => DuelScreen(),
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
      bottomNavigationBar: SafeArea(
        child: Padding(
          padding: const EdgeInsets.only(left: 14, right: 14, bottom: 16),
          child: Stack(
            alignment: Alignment.bottomCenter,
            clipBehavior: Clip.none, 
            children: [
              // 1. BACKGROUND GLASS SEKARANG SUPER PEKAT & TEGAS
              ClipRRect(
                borderRadius: BorderRadius.circular(32),
                child: BackdropFilter(
                  filter: ImageFilter.blur(sigmaX: 16, sigmaY: 16),
                  child: CustomPaint(
                    painter: NotchedGlassPainter(
                      borderColor: _primary.withOpacity(0.35), // Border outline lebih solid agar kontras nyata
                      gradientColors: [
                        Colors.white.withOpacity(0.92), // Sangat pekat di atas
                        Colors.white.withOpacity(0.85), // Tetap pekat di bawah
                      ],
                    ),
                    child: const SizedBox(
                      height: 76, 
                      width: double.infinity,
                    ),
                  ),
                ),
              ),

              // 2. FOREGROUND ICON LAYER
              Container(
                height: 76,
                padding: const EdgeInsets.symmetric(horizontal: 4),
                child: Row(
                  children: [
                    Expanded(
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                        children: [
                          _buildNavItem(0, Icons.menu_book_rounded, 'Materi'),
                          _buildNavItem(1, Icons.smart_toy_rounded, 'Tanya AI', isLocked: !_canAccessChatbot),
                        ],
                      ),
                    ),
                    
                    const SizedBox(width: 76), // Spacer pembatas notch tengah

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

              // 3. FLOATING CENTER BUTTON: 3D POLISHED & NO BORDER
              Positioned(
                top: -14, 
                child: _buildCenterButton(),
              ),
            ],
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

    return GestureDetector(
      onTap: () => _onItemTapped(index),
      behavior: HitTestBehavior.opaque,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 250),
        curve: Curves.easeOutCubic,
        constraints: const BoxConstraints(minWidth: 46, minHeight: 46),
        padding: EdgeInsets.symmetric(
          horizontal: isSelected ? 12 : 6,
          vertical: 6,
        ),
        decoration: BoxDecoration(
          color: isSelected ? _primary.withOpacity(0.16) : Colors.transparent,
          borderRadius: BorderRadius.circular(18),
          border: isSelected
              ? Border.all(color: _primary.withOpacity(0.25), width: 1)
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
                  color: isSelected ? const Color(0xFF0F172A) : const Color(0xFF64748B),
                  size: 22,
                ),
                AnimatedSize(
                  duration: const Duration(milliseconds: 200),
                  curve: Curves.easeOutCubic,
                  child: isSelected
                      ? Padding(
                          padding: const EdgeInsets.only(top: 2),
                          child: Text(
                            label,
                            style: const TextStyle(
                              color: _primary,
                              fontSize: 10,
                              fontWeight: FontWeight.w900,
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

  // REVISED: TOMBOL 3D POLISHED - TANPA BORDER, GLOSSY EFFECT AKTIF
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
          // Gradien Sferis Vertikal Menghasilkan Kedalaman Dimensi Fisik (Terang ke Gelap)
          gradient: const LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [
              Color(0xFF3EC3B4), // Top: Light Specular Reflective Teal
              Color(0xFF165950), // Bottom: Deep Shadow Core Teal
            ],
          ),
          // BORDER DIHAPUS TOTAL SESUAI PERMINTAAN KAPTEN!
          boxShadow: [
            // Drop Shadow Bawah: Menendang tombol keluar secara visual (Efek 3D Mengambang)
            BoxShadow(
              color: const Color(0xFF0A2B27).withOpacity(0.45),
              blurRadius: 14,
              spreadRadius: 1,
              offset: const Offset(0, 6),
            ),
            // Inner Glow Soft Top Highlight
            BoxShadow(
              color: Colors.white.withOpacity(0.25),
              blurRadius: 4,
              spreadRadius: -1,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Stack(
          children: [
            // GLOSSY REFLECTION SHEEN LAYER (Efek Poles Kaca/Kapsul Mewah di Bagian Atas)
            Positioned(
              top: 2,
              left: 8,
              right: 8,
              child: Container(
                height: 22,
                decoration: BoxDecoration(
                  borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
                  gradient: LinearGradient(
                    begin: Alignment.topCenter,
                    end: Alignment.bottomCenter,
                    colors: [
                      Colors.white.withOpacity(0.40),
                      Colors.white.withOpacity(0.0),
                    ],
                  ),
                ),
              ),
            ),
            
            // IKON DI TENGAH
            Center(
              child: SizedBox(
                width: 25,
                height: 25,
                child: CustomPaint(
                  painter: CrossedSwordsPainter(color: Colors.white),
                ),
              ),
            ),
          ],
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