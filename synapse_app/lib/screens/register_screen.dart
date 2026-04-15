import 'package:flutter/material.dart';
import '../services/auth_service.dart';
import 'email_verification_screen.dart'; 

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  final _nimController = TextEditingController();
  final _kelasController = TextEditingController();
  
  bool _isLoading = false;
  bool _isPasswordVisible = false;
  bool _isConfirmPasswordVisible = false;
  
  // 👇 Sakelar sakti penentu nasib (Mahasiswa atau Public)
  bool _isMahasiswa = true; 

  void _handleRegister() async {
    final name = _nameController.text.trim();
    final email = _emailController.text.trim();
    final password = _passwordController.text;
    final confirmPassword = _confirmPasswordController.text;
    final nim = _nimController.text.trim();
    final kelas = _kelasController.text.trim();

    // 1. Validasi Input Kosong
    if (name.isEmpty || email.isEmpty || password.isEmpty) {
      _showError('Nama, Email, dan Kata Sandi wajib diisi!');
      return;
    }

    // 2. Validasi Password Sama
    if (password != confirmPassword) {
      _showError('Kata Sandi dan Konfirmasi Kata Sandi tidak cocok!');
      return;
    }

    // 3. Validasi Mahasiswa IPB
    if (_isMahasiswa) {
      if (!email.endsWith('@apps.ipb.ac.id')) {
        _showError('Mahasiswa wajib menggunakan email @apps.ipb.ac.id!');
        return;
      }
      if (nim.isEmpty || kelas.isEmpty) {
        _showError('NIM dan Kelas wajib diisi untuk Mahasiswa!');
        return;
      }
    }

    setState(() => _isLoading = true);

    bool success = await AuthService().register(
      name: name,
      email: email,
      password: password,
      role: _isMahasiswa ? 'mahasiswa' : 'public',
      nim: _isMahasiswa ? nim : null,
      kelas: _isMahasiswa ? kelas : null,
    );
    
    setState(() => _isLoading = false);

    if (!mounted) return;

    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Registrasi sukses! Silakan cek email.'), backgroundColor: Colors.teal));
      Navigator.pushReplacement(context, MaterialPageRoute(builder: (context) => EmailVerificationScreen(email: email)));
    } else {
      _showError('Registrasi gagal. Email mungkin sudah terdaftar.');
    }
    
    // Simulasi loading sejenak sebelum kita punya API sungguhan
    await Future.delayed(const Duration(seconds: 2));
    
    setState(() => _isLoading = false);

    if (!mounted) return;

    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Registrasi sukses! Silakan cek email Anda untuk Verifikasi.'),
        backgroundColor: Colors.teal,
      )
    );
    Navigator.pushReplacement(context, MaterialPageRoute(builder: (context) => EmailVerificationScreen(email: email)));
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message), backgroundColor: Colors.redAccent, behavior: SnackBarBehavior.floating),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.blueGrey[50],
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        foregroundColor: Colors.teal,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded),
          onPressed: () => Navigator.pop(context), // Kembali ke Login
        ),
      ),
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 10.0),
          physics: const BouncingScrollPhysics(),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Header
              const Icon(Icons.person_add_alt_1_rounded, size: 64, color: Colors.teal),
              const SizedBox(height: 16),
              const Text(
                'Pendaftaran',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 28, fontWeight: FontWeight.w900, color: Colors.blueGrey),
              ),
              const Text(
                'Bergabunglah dengan jaringan SYNAPSE',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 14, color: Colors.grey),
              ),
              const SizedBox(height: 32),

              // Kartu Form
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(24),
                  boxShadow: [
                    BoxShadow(color: Colors.teal.withOpacity(0.05), blurRadius: 20, offset: const Offset(0, 10))
                  ],
                ),
                child: Column(
                  children: [
                    // --- SAKELAR ROLE (Mahasiswa / Public) ---
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                      decoration: BoxDecoration(
                        color: _isMahasiswa ? Colors.teal.withOpacity(0.1) : Colors.grey.shade100,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: _isMahasiswa ? Colors.teal.withOpacity(0.5) : Colors.transparent),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Row(
                            children: [
                              Icon(Icons.school_rounded, color: Colors.teal),
                              SizedBox(width: 8),
                              Text('Saya Mahasiswa IPB', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.blueGrey)),
                            ],
                          ),
                          Switch(
                            value: _isMahasiswa,
                            activeColor: Colors.teal,
                            onChanged: (value) {
                              setState(() {
                                _isMahasiswa = value;
                                // Jika diubah ke public, bersihkan emailnya agar tidak bingung
                                if (!value && _emailController.text.endsWith('@apps.ipb.ac.id')) {
                                  _emailController.clear();
                                }
                              });
                            },
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 20),

                    // --- FORM UMUM ---
                    _buildTextField(
                      controller: _nameController,
                      label: 'Nama Lengkap',
                      icon: Icons.person_rounded,
                    ),
                    const SizedBox(height: 16),

                    _buildTextField(
                      controller: _emailController,
                      label: 'Email',
                      hintText: _isMahasiswa ? 'wajib @apps.ipb.ac.id' : 'contoh@gmail.com',
                      icon: Icons.email_rounded,
                      keyboardType: TextInputType.emailAddress,
                    ),
                    const SizedBox(height: 16),

                    // --- FORM KHUSUS MAHASISWA (Disembunyikan jika Public) ---
                    if (_isMahasiswa) ...[
                      Row(
                        children: [
                          Expanded(
                            flex: 2,
                            child: _buildTextField(
                              controller: _nimController,
                              label: 'NIM',
                              icon: Icons.badge_rounded,
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            flex: 1,
                            child: _buildTextField(
                              controller: _kelasController,
                              label: 'Kelas',
                              icon: Icons.class_rounded,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),
                    ],

                    // --- FORM PASSWORD ---
                    _buildPasswordField(
                      controller: _passwordController,
                      label: 'Kata Sandi',
                      isVisible: _isPasswordVisible,
                      onToggle: () => setState(() => _isPasswordVisible = !_isPasswordVisible),
                    ),
                    const SizedBox(height: 16),
                    
                    _buildPasswordField(
                      controller: _confirmPasswordController,
                      label: 'Konfirmasi Sandi',
                      isVisible: _isConfirmPasswordVisible,
                      onToggle: () => setState(() => _isConfirmPasswordVisible = !_isConfirmPasswordVisible),
                    ),
                    const SizedBox(height: 24),

                    // --- TOMBOL DAFTAR ---
                    SizedBox(
                      width: double.infinity,
                      height: 55,
                      child: _isLoading
                          ? const Center(child: CircularProgressIndicator(color: Colors.teal))
                          : ElevatedButton(
                              onPressed: _handleRegister,
                              style: ElevatedButton.styleFrom(
                                backgroundColor: Colors.teal,
                                foregroundColor: Colors.white,
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                                elevation: 2,
                              ),
                              child: const Text('DAFTARKAN IDENTITAS', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, letterSpacing: 1)),
                            ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // Widget Bantuan agar kodenya tidak kepanjangan
  Widget _buildTextField({required TextEditingController controller, required String label, required IconData icon, String? hintText, TextInputType? keyboardType}) {
    return TextField(
      controller: controller,
      keyboardType: keyboardType,
      decoration: InputDecoration(
        labelText: label,
        hintText: hintText,
        hintStyle: TextStyle(color: Colors.grey[400], fontSize: 13),
        prefixIcon: Icon(icon, color: Colors.teal),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(16)),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: Colors.teal, width: 2),
        ),
      ),
    );
  }

  Widget _buildPasswordField({required TextEditingController controller, required String label, required bool isVisible, required VoidCallback onToggle}) {
    return TextField(
      controller: controller,
      obscureText: !isVisible,
      decoration: InputDecoration(
        labelText: label,
        prefixIcon: const Icon(Icons.lock_rounded, color: Colors.teal),
        suffixIcon: IconButton(
          icon: Icon(isVisible ? Icons.visibility_rounded : Icons.visibility_off_rounded, color: Colors.grey),
          onPressed: onToggle,
        ),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(16)),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: Colors.teal, width: 2),
        ),
      ),
    );
  }
}