import 'package:flutter/material.dart';
import '../services/auth_service.dart';
import 'home_screen.dart';
import 'register_screen.dart'; 

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _isLoading = false; 
  bool _isPasswordVisible = false;

  void _handleLogin() async {
    if (_emailController.text.isEmpty || _passwordController.text.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Email dan Password tidak boleh kosong!'), backgroundColor: Colors.redAccent));
      return;
    }

    setState(() => _isLoading = true);

    final auth = AuthService();
    bool success = await auth.login(
      _emailController.text,
      _passwordController.text,
    );

    setState(() => _isLoading = false);

    if (!mounted) return;

    if (success) {
      final userData = await auth.getUserProfile();
      if (!mounted) return;

      if (userData != null) {
        String role = userData['role'] ?? 'public';

        if (role == 'admin' || role == 'dosen') {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Akses Ditolak: Dosen & Admin harap login melalui Website Dashboard!'),
              backgroundColor: Colors.redAccent,
              behavior: SnackBarBehavior.floating,
            ),
          );
          await auth.logout();
        } else {
          // Masuk sebagai Mahasiswa / Public terdaftar
          Navigator.pushReplacement(context, MaterialPageRoute(builder: (context) => const HomeScreen()));
        }
      }
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Login Gagal. Periksa kembali kredensial Anda.'),
          backgroundColor: Colors.redAccent,
          behavior: SnackBarBehavior.floating,
        )
      );
    }
  }

  // Fungsi untuk masuk tanpa login
  void _loginAsGuest() {
    // Kita arahkan ke Home, tapi di Home nanti kita kasih logika kalau tokennya kosong = Tamu
    Navigator.pushReplacement(context, MaterialPageRoute(builder: (context) => const HomeScreen()));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.blueGrey[50], // Latar belakang ala IT
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Logo / Ikon SYNAPSE
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: Colors.teal.withOpacity(0.1),
                ),
                child: const Icon(Icons.memory_rounded, size: 80, color: Colors.teal),
              ),
              const SizedBox(height: 16),
              
              // Judul Aplikasi
              const Text(
                'SYNAPSE',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 36, fontWeight: FontWeight.w900, color: Colors.blueGrey, letterSpacing: 2),
              ),
              const Text(
                'Portal Akses Agen Pembelajaran',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 14, color: Colors.grey, fontWeight: FontWeight.w500),
              ),
              const SizedBox(height: 40),

              // Kartu Form Login
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(24),
                  boxShadow: [
                    BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 20, offset: const Offset(0, 10))
                  ],
                ),
                child: Column(
                  children: [
                    // Input Email
                    TextField(
                      controller: _emailController,
                      keyboardType: TextInputType.emailAddress,
                      decoration: InputDecoration(
                        labelText: 'Email',
                        hintText: 'contoh@apps.ipb.ac.id',
                        prefixIcon: const Icon(Icons.email_rounded, color: Colors.teal),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(16)),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(16),
                          borderSide: const BorderSide(color: Colors.teal, width: 2),
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Input Password
                    TextField(
                      controller: _passwordController,
                      obscureText: !_isPasswordVisible, 
                      decoration: InputDecoration(
                        labelText: 'Kata Sandi',
                        prefixIcon: const Icon(Icons.lock_rounded, color: Colors.teal),
                        suffixIcon: IconButton(
                          icon: Icon(
                            _isPasswordVisible ? Icons.visibility_rounded : Icons.visibility_off_rounded,
                            color: Colors.grey,
                          ),
                          onPressed: () {
                            setState(() {
                              _isPasswordVisible = !_isPasswordVisible;
                            });
                          },
                        ),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(16)),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(16),
                          borderSide: const BorderSide(color: Colors.teal, width: 2),
                        ),
                      ),
                    ),
                    const SizedBox(height: 24),

                    // Tombol Login
                    SizedBox(
                      width: double.infinity,
                      height: 55,
                      child: _isLoading
                          ? const Center(child: CircularProgressIndicator(color: Colors.teal))
                          : ElevatedButton(
                              onPressed: _handleLogin,
                              style: ElevatedButton.styleFrom(
                                backgroundColor: Colors.teal,
                                foregroundColor: Colors.white,
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                                elevation: 2,
                              ),
                              child: const Text('MASUK SISTEM', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, letterSpacing: 1)),
                            ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),

              // Tombol Daftar Akun
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Text('Belum memiliki akses? ', style: TextStyle(color: Colors.blueGrey)),
                  GestureDetector(
                    onTap: () {
                      Navigator.push(context, MaterialPageRoute(builder: (context) => const RegisterScreen()));
                    },
                    child: const Text(
                      'Daftar di sini',
                      style: TextStyle(color: Colors.teal, fontWeight: FontWeight.bold, decoration: TextDecoration.underline),
                    ),
                  ),
                ],
              ),
              
              const SizedBox(height: 32),
              
              // Garis pembatas
              Row(
                children: [
                  Expanded(child: Divider(color: Colors.grey.shade300)),
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    child: Text('ATAU', style: TextStyle(color: Colors.grey.shade500, fontWeight: FontWeight.bold, fontSize: 12)),
                  ),
                  Expanded(child: Divider(color: Colors.grey.shade300)),
                ],
              ),
              const SizedBox(height: 24),

              // Tombol Guest Mode
              TextButton.icon(
                onPressed: _loginAsGuest,
                icon: const Icon(Icons.explore_rounded, color: Colors.blueGrey),
                label: const Text('Eksplorasi sebagai Tamu', style: TextStyle(color: Colors.blueGrey, fontWeight: FontWeight.bold, fontSize: 16)),
                style: TextButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16),
                    side: BorderSide(color: Colors.blueGrey.withOpacity(0.3)),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}