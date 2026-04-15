import 'package:flutter/material.dart';
import '../services/auth_service.dart'; // 👈 Tambahan untuk koneksi API
import 'login_screen.dart';             // 👈 Tambahan untuk arahkan ke login

import 'materials_screen.dart'; 
import 'quiz_list_screen.dart'; 
import 'chatbot_screen.dart';   
import 'profile_screen.dart';   

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _selectedIndex = 0;

  // 👇 Variabel Keamanan di balik layar
  bool _isGuest = true;
  bool _isLoading = true;

  final List<Widget> _pages = [
    const MaterialsScreen(),
    const QuizListScreen(),  
    const ChatbotScreen(),   
    const ProfileScreen(),   
  ];

  @override
  void initState() {
    super.initState();
    _loadUserData(); // Cek identitas saat layar pertama kali dibuka
  }

  // 👇 Fungsi Intelijen: Cek Tamu atau Mahasiswa
  Future<void> _loadUserData() async {
    final auth = AuthService();
    final userData = await auth.getUserProfile();

    if (mounted) {
      setState(() {
        _isGuest = userData == null; // Jika null = Tamu!
        _isLoading = false;
      });
    }
  }

  // 👇 Dialog Penolakan Elegan untuk Tamu
  void _showAccessDeniedDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Row(
          children: [
            Icon(Icons.lock_rounded, color: Colors.blueAccent),
            SizedBox(width: 8),
            Text('Akses Dibatasi', style: TextStyle(fontWeight: FontWeight.bold)),
          ],
        ),
        content: const Text('Fitur ini eksklusif untuk Agen Terdaftar. Silakan Masuk atau Daftar untuk menggunakan fitur Kuis, AI, dan AR.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Nanti Saja', style: TextStyle(color: Colors.grey)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.blueAccent,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
            onPressed: () {
              Navigator.pop(context);
              Navigator.pushReplacement(context, MaterialPageRoute(builder: (context) => const LoginScreen()));
            },
            child: const Text('Login / Daftar', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  void _onItemTapped(int index) {
    // 👇 Cegah Tamu masuk ke Kuis (index 1) dan AI (index 2)
    if (_isGuest && (index == 1 || index == 2)) {
      _showAccessDeniedDialog();
      return;
    }

    setState(() {
      _selectedIndex = index;
    });
  }

  @override
  Widget build(BuildContext context) {
    // Tampilkan loading mulus saat aplikasi mengecek token
    if (_isLoading) {
      return Scaffold(
        backgroundColor: Colors.grey[100],
        body: const Center(child: CircularProgressIndicator(color: Colors.blueAccent)),
      );
    }

    return Scaffold(
      backgroundColor: Colors.grey[100],
      extendBody: true, 
      
      body: IndexedStack(
        index: _selectedIndex,
        children: _pages,
      ),

      // NAVBAR GAYA "FLOATING ISLAND" KARYA KAPTEN
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
              _buildNavItem(0, Icons.menu_book_rounded, "Materi", isLocked: false),
              _buildNavItem(1, Icons.quiz_rounded, "Kuis", isLocked: _isGuest),
              
              _buildCenterButton(),
              
              _buildNavItem(2, Icons.smart_toy_rounded, "Tanya AI", isLocked: _isGuest),
              _buildNavItem(3, Icons.person_rounded, "Profil", isLocked: false),
            ],
          ),
        ),
      ),
    );
  }

  // --- WIDGET UNTUK TOMBOL MENU DENGAN ANIMASI ---
  Widget _buildNavItem(int index, IconData icon, String label, {bool isLocked = false}) {
    bool isSelected = _selectedIndex == index;
    
    return GestureDetector(
      onTap: () => _onItemTapped(index),
      behavior: HitTestBehavior.opaque,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 300),
        curve: Curves.easeInOut,
        padding: EdgeInsets.symmetric(horizontal: isSelected ? 12 : 8, vertical: 8),
        decoration: BoxDecoration(
          color: isSelected ? Colors.blueAccent.withOpacity(0.1) : Colors.transparent,
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
                  )
                ]
              ],
            ),
            // 👇 Indikator Gembok Merah Kecil Jika Tamu
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

  // --- WIDGET UNTUK TOMBOL TENGAH (GRADIENT & GLOW) ---
  Widget _buildCenterButton() {
    return GestureDetector(
      onTap: () {
        // 👇 Cegah Tamu buka AR
        if (_isGuest) {
          _showAccessDeniedDialog();
          return;
        }

        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: const Text('Fitur AR sedang dibangun di galangan kapal! 🛠️'),
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            backgroundColor: Colors.blueGrey[800],
          )
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