import 'package:flutter/material.dart';
import '../services/auth_service.dart';
import 'email_verification_screen.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _nameController            = TextEditingController();
  final _emailController           = TextEditingController();
  final _passwordController        = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  final _nimController             = TextEditingController();
  final _kelasController           = TextEditingController();

  bool _isLoading                = false;
  bool _isPasswordVisible        = false;
  bool _isConfirmPasswordVisible = false;
  bool _isMahasiswa              = true;

  // ─────────────────────────────────────────────────────────
  // FIX BUG #1 — double SnackBar & double setState
  // Semua kode simulasi lama dihapus. Hanya 1 alur:
  //   sukses → 1 snackbar → navigate
  //   gagal  → 1 snackbar error
  // ─────────────────────────────────────────────────────────
  void _handleRegister() async {
    final name            = _nameController.text.trim();
    final email           = _emailController.text.trim();
    final password        = _passwordController.text;
    final confirmPassword = _confirmPasswordController.text;
    final nim             = _nimController.text.trim();
    final kelas           = _kelasController.text.trim();

    // Validasi input kosong
    if (name.isEmpty || email.isEmpty || password.isEmpty) {
      _showError('Nama, Email, dan Password wajib diisi!');
      return;
    }

    // Validasi password sama
    if (password != confirmPassword) {
      _showError('Kata Sandi dan Konfirmasi tidak cocok!');
      return;
    }

    // Validasi mahasiswa IPB
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
      name:     name,
      email:    email,
      password: password,
      role:     _isMahasiswa ? 'mahasiswa' : 'public',
      nim:      _isMahasiswa ? nim   : null,
      kelas:    _isMahasiswa ? kelas : null,
    );

    // FIX: guard mounted sebelum setState & Navigator
    if (!mounted) return;

    setState(() => _isLoading = false);

    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Akun berhasil dibuat! Cek emailmu ya 📬'),
          backgroundColor: Colors.teal,
          behavior: SnackBarBehavior.floating,
        ),
      );
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(
          builder: (context) => EmailVerificationScreen(email: email),
        ),
      );
    } else {
      _showError('Registrasi gagal. Email atau NIM mungkin sudah terdaftar.');
    }
    // ↑ Selesai di sini. Tidak ada kode lagi.
    // Blok Future.delayed + SnackBar kedua yang lama sudah dihapus.
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: Colors.redAccent,
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.blueGrey[50],
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Icon(Icons.person_add_alt_1_rounded, size: 64, color: Colors.teal),
              const SizedBox(height: 16),
              const Text(
                'Pendaftaran',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 28, fontWeight: FontWeight.w900, color: Colors.blueGrey),
              ),
              const SizedBox(height: 30),

              // Toggle Mahasiswa / Umum — TIDAK DIUBAH
              Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.05),
                      blurRadius: 10,
                    )
                  ],
                ),
                child: Row(
                  children: [
                    Expanded(
                      child: GestureDetector(
                        onTap: _isLoading
                            ? null
                            : () => setState(() => _isMahasiswa = true),
                        child: Container(
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          decoration: BoxDecoration(
                            color: _isMahasiswa ? Colors.teal : Colors.transparent,
                            borderRadius: BorderRadius.circular(16),
                          ),
                          child: Text(
                            'Mahasiswa IPB',
                            textAlign: TextAlign.center,
                            style: TextStyle(
                              color: _isMahasiswa ? Colors.white : Colors.grey,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ),
                    ),
                    Expanded(
                      child: GestureDetector(
                        onTap: _isLoading
                            ? null
                            : () => setState(() => _isMahasiswa = false),
                        child: Container(
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          decoration: BoxDecoration(
                            color: !_isMahasiswa ? Colors.teal : Colors.transparent,
                            borderRadius: BorderRadius.circular(16),
                          ),
                          child: Text(
                            'Umum',
                            textAlign: TextAlign.center,
                            style: TextStyle(
                              color: !_isMahasiswa ? Colors.white : Colors.grey,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 20),

              // Form card — TIDAK DIUBAH
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(24),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.05),
                      blurRadius: 20,
                      offset: const Offset(0, 10),
                    )
                  ],
                ),
                child: Column(
                  children: [
                    // FIX BUG #2: enabled: !_isLoading di semua field
                    _buildTextField(
                      controller: _nameController,
                      label: 'Nama Lengkap',
                      icon: Icons.person_outline_rounded,
                      enabled: !_isLoading,
                    ),
                    const SizedBox(height: 14),
                    _buildTextField(
                      controller: _emailController,
                      label: _isMahasiswa
                          ? 'Email IPB (@apps.ipb.ac.id)'
                          : 'Email',
                      icon: Icons.email_outlined,
                      keyboardType: TextInputType.emailAddress,
                      hintText: _isMahasiswa ? 'nama@apps.ipb.ac.id' : null,
                      enabled: !_isLoading,
                    ),
                    if (_isMahasiswa) ...[
                      const SizedBox(height: 14),
                      _buildTextField(
                        controller: _nimController,
                        label: 'NIM',
                        icon: Icons.badge_outlined,
                        keyboardType: TextInputType.text,
                        enabled: !_isLoading,
                      ),
                      const SizedBox(height: 14),
                      _buildTextField(
                        controller: _kelasController,
                        label: 'Kelas',
                        icon: Icons.class_outlined,
                        hintText: 'Contoh: A, B, K1...',
                        enabled: !_isLoading,
                      ),
                    ],
                    const SizedBox(height: 14),
                    _buildPasswordField(
                      controller: _passwordController,
                      label: 'Kata Sandi',
                      isVisible: _isPasswordVisible,
                      onToggle: () => setState(
                          () => _isPasswordVisible = !_isPasswordVisible),
                      enabled: !_isLoading,
                    ),
                    const SizedBox(height: 14),
                    _buildPasswordField(
                      controller: _confirmPasswordController,
                      label: 'Konfirmasi Kata Sandi',
                      isVisible: _isConfirmPasswordVisible,
                      onToggle: () => setState(() =>
                          _isConfirmPasswordVisible = !_isConfirmPasswordVisible),
                      enabled: !_isLoading,
                    ),
                    const SizedBox(height: 20),

                    // Tombol — TIDAK DIUBAH tampilannya
                    SizedBox(
                      width: double.infinity,
                      height: 55,
                      child: _isLoading
                          ? const Center(
                              child: CircularProgressIndicator(color: Colors.teal))
                          : ElevatedButton(
                              onPressed: _handleRegister,
                              style: ElevatedButton.styleFrom(
                                backgroundColor: Colors.teal,
                                foregroundColor: Colors.white,
                                shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(16)),
                                elevation: 2,
                              ),
                              child: const Text(
                                'DAFTARKAN IDENTITAS',
                                style: TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                  letterSpacing: 1,
                                ),
                              ),
                            ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),

              // Link ke login — TIDAK DIUBAH
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Text(
                    'Sudah punya akses? ',
                    style: TextStyle(color: Colors.blueGrey),
                  ),
                  GestureDetector(
                    onTap: _isLoading ? null : () => Navigator.pop(context),
                    child: const Text(
                      'Masuk di sini',
                      style: TextStyle(
                        color: Colors.teal,
                        fontWeight: FontWeight.bold,
                        decoration: TextDecoration.underline,
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ── WIDGET HELPERS — TIDAK DIUBAH, hanya tambah enabled ───────

  Widget _buildTextField({
    required TextEditingController controller,
    required String label,
    required IconData icon,
    TextInputType? keyboardType,
    String? hintText,
    required bool enabled,
  }) {
    return TextField(
      controller: controller,
      keyboardType: keyboardType,
      enabled: enabled,
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

  Widget _buildPasswordField({
    required TextEditingController controller,
    required String label,
    required bool isVisible,
    required VoidCallback onToggle,
    required bool enabled,
  }) {
    return TextField(
      controller: controller,
      obscureText: !isVisible,
      enabled: enabled,
      decoration: InputDecoration(
        labelText: label,
        prefixIcon: const Icon(Icons.lock_rounded, color: Colors.teal),
        suffixIcon: IconButton(
          icon: Icon(
            isVisible ? Icons.visibility_rounded : Icons.visibility_off_rounded,
            color: Colors.grey,
          ),
          onPressed: enabled ? onToggle : null,
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