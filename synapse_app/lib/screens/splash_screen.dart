import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'home_screen.dart';
import 'login_screen.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  @override
  void initState() {
    super.initState();
    _checkLoginStatus(); // Jalankan pengecekan saat aplikasi baru dibuka
  }

  void _checkLoginStatus() async {
    // Beri waktu 2 detik agar logo aplikasi kita sempat terlihat keren 😎
    await Future.delayed(const Duration(seconds: 2));

    // Buka brankas HP dan cari token
    SharedPreferences prefs = await SharedPreferences.getInstance();
    String? token = prefs.getString('token');

    // Syarat wajib Flutter sebelum pindah halaman
    if (!mounted) return;

    if (token != null) {
      // Jika KTP (Token) ADA -> Langsung ke Dashboard!
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (context) => const HomeScreen()),
      );
    } else {
      // Jika KTP (Token) TIDAK ADA -> Lempar ke halaman Login!
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (context) => const LoginScreen()),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return const Scaffold(
      backgroundColor: Colors.blue, // Latar belakang biru
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            // Logo Icon sederhana
            Icon(Icons.school, size: 100, color: Colors.white), 
            SizedBox(height: 20),
            // Nama Aplikasi
            Text(
              'SYNAPSE',
              style: TextStyle(
                fontSize: 36, 
                fontWeight: FontWeight.bold, 
                color: Colors.white,
                letterSpacing: 2.0,
              ),
            ),
            SizedBox(height: 40),
            // Animasi Loading muter-muter
            CircularProgressIndicator(color: Colors.white),
          ],
        ),
      ),
    );
  }
}