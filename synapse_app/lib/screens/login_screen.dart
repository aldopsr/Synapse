import 'package:flutter/material.dart';
import '../services/auth_service.dart';
import 'home_screen.dart';
import 'register_screen.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import '../utils/constants.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _emailController    = TextEditingController();
  final _passwordController = TextEditingController();
  bool _isLoading           = false;
  bool _isPasswordVisible   = false;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  // ─────────────────────────────────────────────────────────────
  // HANDLE LOGIN
  // FIX: - input disabled saat loading (enabled: !_isLoading)
  //      - if (!mounted) return dipasang di tempat yang benar
  //      - pesan error lebih friendly
  //      - semua print() dihapus
  // ─────────────────────────────────────────────────────────────
  void _handleLogin() async {
    if (_emailController.text.isEmpty || _passwordController.text.isEmpty) {
      _showSnackbar('Email dan kata sandi tidak boleh kosong!', Colors.redAccent);
      return;
    }

    setState(() => _isLoading = true);

    final auth    = AuthService();
    bool success  = await auth.login(
      _emailController.text,
      _passwordController.text,
    );

    // FIX: setState dulu, baru cek mounted
    if (!mounted) return;
    setState(() => _isLoading = false);

    if (success) {
      final userData = await auth.getUserProfile();
      if (!mounted) return;

      if (userData != null) {
        final String role = userData['role'] ?? 'public';

        if (role == 'admin' || role == 'dosen') {
          // FIX: pesan lebih jelas dan friendly
          _showSnackbar(
            'Akun ini adalah dosen/admin. Silakan login lewat website Synapse ya!',
            Colors.orange,
          );
          await auth.logout();
        } else {
          Navigator.pushReplacement(
            context,
            MaterialPageRoute(builder: (context) => const HomeScreen()),
          );
        }
      }
    } else {
      // FIX: pesan lebih natural
      _showSnackbar('Email atau kata sandi salah. Coba lagi ya!', Colors.redAccent);
    }
  }

  void _loginAsGuest() {
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(builder: (context) => const HomeScreen()),
    );
  }

  void _showForgotPasswordSheet() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => const ForgotPasswordSheet(),
    );
  }

  void _showSnackbar(String message, Color color) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: color,
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  // ─────────────────────────────────────────────────────────────
  // BUILD — TAMPILAN TIDAK DIUBAH SAMA SEKALI
  // ─────────────────────────────────────────────────────────────
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
              Center(
                child: Image.asset(
                  'assets/images/logo_synapse.png',
                  width: 180,
                ),
              ),
              const SizedBox(height: 16),

              const Text(
                'SYNAPSE',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 36,
                  fontWeight: FontWeight.w900,
                  color: Colors.blueGrey,
                  letterSpacing: 2,
                ),
              ),
              const Text(
                'Portal Akses Agen Pembelajaran',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 14,
                  color: Colors.grey,
                  fontWeight: FontWeight.w500,
                ),
              ),
              const SizedBox(height: 40),

              // Kartu Form Login — tidak diubah
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
                    // Input Email — FIX: enabled: !_isLoading
                    TextField(
                      controller: _emailController,
                      keyboardType: TextInputType.emailAddress,
                      enabled: !_isLoading,
                      decoration: InputDecoration(
                        labelText: 'Email',
                        hintText: 'contoh@apps.ipb.ac.id',
                        prefixIcon: const Icon(Icons.email_rounded, color: Colors.teal),
                        border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(16)),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(16),
                          borderSide:
                              const BorderSide(color: Colors.teal, width: 2),
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Input Password — FIX: enabled: !_isLoading
                    TextField(
                      controller: _passwordController,
                      obscureText: !_isPasswordVisible,
                      enabled: !_isLoading,
                      decoration: InputDecoration(
                        labelText: 'Kata Sandi',
                        prefixIcon:
                            const Icon(Icons.lock_rounded, color: Colors.teal),
                        suffixIcon: IconButton(
                          icon: Icon(
                            _isPasswordVisible
                                ? Icons.visibility_rounded
                                : Icons.visibility_off_rounded,
                            color: Colors.grey,
                          ),
                          onPressed: _isLoading
                              ? null
                              : () => setState(
                                  () => _isPasswordVisible = !_isPasswordVisible),
                        ),
                        border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(16)),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(16),
                          borderSide:
                              const BorderSide(color: Colors.teal, width: 2),
                        ),
                      ),
                    ),

                    // Tombol Lupa Password
                    Align(
                      alignment: Alignment.centerRight,
                      child: TextButton(
                        onPressed: _isLoading ? null : _showForgotPasswordSheet,
                        style: TextButton.styleFrom(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 8, vertical: 4),
                          minimumSize: Size.zero,
                          tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        ),
                        child: const Text(
                          'Lupa Kata Sandi?',
                          style: TextStyle(
                              color: Colors.teal, fontWeight: FontWeight.w600),
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Tombol Login
                    SizedBox(
                      width: double.infinity,
                      height: 55,
                      child: _isLoading
                          ? const Center(
                              child: CircularProgressIndicator(color: Colors.teal))
                          : ElevatedButton(
                              onPressed: _handleLogin,
                              style: ElevatedButton.styleFrom(
                                backgroundColor: Colors.teal,
                                foregroundColor: Colors.white,
                                shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(16)),
                                elevation: 2,
                              ),
                              child: const Text(
                                'MASUK SISTEM',
                                style: TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.bold,
                                    letterSpacing: 1),
                              ),
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
                  const Text('Belum memiliki akses? ',
                      style: TextStyle(color: Colors.blueGrey)),
                  GestureDetector(
                    onTap: _isLoading
                        ? null
                        : () => Navigator.push(
                              context,
                              MaterialPageRoute(
                                  builder: (context) =>
                                      const RegisterScreen()),
                            ),
                    child: const Text(
                      'Daftar di sini',
                      style: TextStyle(
                          color: Colors.teal,
                          fontWeight: FontWeight.bold,
                          decoration: TextDecoration.underline),
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
                    child: Text(
                      'ATAU',
                      style: TextStyle(
                          color: Colors.grey.shade500,
                          fontWeight: FontWeight.bold,
                          fontSize: 12),
                    ),
                  ),
                  Expanded(child: Divider(color: Colors.grey.shade300)),
                ],
              ),
              const SizedBox(height: 24),

              // Tombol Guest Mode
              TextButton.icon(
                onPressed: _isLoading ? null : _loginAsGuest,
                icon: const Icon(Icons.explore_rounded, color: Colors.blueGrey),
                label: const Text(
                  'Eksplorasi sebagai Tamu',
                  style: TextStyle(
                      color: Colors.blueGrey,
                      fontWeight: FontWeight.bold,
                      fontSize: 16),
                ),
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

// ============================================================================
// WIDGET KHUSUS: BOTTOM SHEET LUPA PASSWORD (3 LANGKAH: EMAIL -> OTP -> RESET)
// FIX: - semua print() dihapus
//      - pesan error & sukses lebih friendly
//      - if (!mounted) return di tempat yang benar
//      - input disabled saat loading
// ============================================================================
class ForgotPasswordSheet extends StatefulWidget {
  const ForgotPasswordSheet({super.key});

  @override
  State<ForgotPasswordSheet> createState() => _ForgotPasswordSheetState();
}

class _ForgotPasswordSheetState extends State<ForgotPasswordSheet> {
  int  _step    = 1; // 1: Email, 2: OTP, 3: New Password
  bool _isLoading = false;

  final _emailController       = TextEditingController();
  final _otpController         = TextEditingController();
  final _newPasswordController = TextEditingController();

  bool _isNewPasswordVisible = false;

  final String baseUrl = AppConstants.baseUrl;

  @override
  void dispose() {
    _emailController.dispose();
    _otpController.dispose();
    _newPasswordController.dispose();
    super.dispose();
  }

  // FIX: hapus semua print(), pesan lebih friendly
  void _sendOTP() async {
    if (_emailController.text.isEmpty) {
      _showSnackbar('Masukkan emailmu dulu ya!', Colors.orange);
      return;
    }
    setState(() => _isLoading = true);

    try {
      final response = await http.post(
        Uri.parse('$baseUrl/forgot-password/send-otp'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({'email': _emailController.text.trim()}),
      );

      if (!mounted) return;
      setState(() => _isLoading = false);

      if (response.statusCode == 200) {
        setState(() => _step = 2);
        _showSnackbar('Kode OTP sudah dikirim ke emailmu 📬', Colors.teal);
      } else {
        try {
          final error = jsonDecode(response.body);
          _showSnackbar(
            error['message'] ?? 'Email tidak ditemukan. Cek lagi ya!',
            Colors.orange,
          );
        } catch (_) {
          _showSnackbar('Terjadi kesalahan server. Coba beberapa saat lagi.', Colors.redAccent);
        }
      }
    } catch (e) {
      if (!mounted) return;
      setState(() => _isLoading = false);
      _showSnackbar('Koneksi bermasalah. Cek internet kamu!', Colors.redAccent);
    }
  }

  void _verifyOTP() async {
    if (_otpController.text.isEmpty) {
      _showSnackbar('Masukkan kode OTP-nya dulu!', Colors.orange);
      return;
    }
    setState(() => _isLoading = true);

    try {
      final response = await http.post(
        Uri.parse('$baseUrl/forgot-password/verify-otp'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'email': _emailController.text.trim(),
          'otp':   _otpController.text.trim(),
        }),
      );

      if (!mounted) return;
      setState(() => _isLoading = false);

      if (response.statusCode == 200) {
        setState(() => _step = 3);
      } else {
        final error = jsonDecode(response.body);
        _showSnackbar(
          error['message'] ?? 'Kode OTP salah atau sudah kadaluarsa.',
          Colors.redAccent,
        );
      }
    } catch (e) {
      if (!mounted) return;
      setState(() => _isLoading = false);
      _showSnackbar('Koneksi bermasalah. Cek internet kamu!', Colors.redAccent);
    }
  }

  void _resetPassword() async {
    if (_newPasswordController.text.isEmpty) {
      _showSnackbar('Kata sandi baru tidak boleh kosong!', Colors.orange);
      return;
    }
    if (_newPasswordController.text.length < 6) {
      _showSnackbar('Kata sandi minimal 6 karakter ya!', Colors.orange);
      return;
    }
    setState(() => _isLoading = true);

    try {
      final response = await http.post(
        Uri.parse('$baseUrl/forgot-password/reset'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'email':        _emailController.text.trim(),
          'otp':          _otpController.text.trim(),
          'new_password': _newPasswordController.text,
        }),
      );

      if (!mounted) return;
      setState(() => _isLoading = false);

      if (response.statusCode == 200) {
        Navigator.pop(context);
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Kata sandi berhasil diubah! Silakan login. 🎉'),
            backgroundColor: Colors.teal,
            behavior: SnackBarBehavior.floating,
          ),
        );
      } else {
        final error = jsonDecode(response.body);
        _showSnackbar(
          error['message'] ?? 'Gagal mengubah kata sandi. Coba lagi.',
          Colors.redAccent,
        );
      }
    } catch (e) {
      if (!mounted) return;
      setState(() => _isLoading = false);
      _showSnackbar('Koneksi bermasalah. Cek internet kamu!', Colors.redAccent);
    }
  }

  void _showSnackbar(String message, Color color) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: color,
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  // ── BUILD — TAMPILAN TIDAK DIUBAH ─────────────────────────────
  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(
          bottom: MediaQuery.of(context).viewInsets.bottom),
      child: Container(
        padding: const EdgeInsets.all(24),
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.only(
            topLeft:  Radius.circular(24),
            topRight: Radius.circular(24),
          ),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Handle pill
            Center(
              child: Container(
                width: 40,
                height: 5,
                decoration: BoxDecoration(
                  color: Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
            ),
            const SizedBox(height: 24),

            Text(
              _step == 1
                  ? 'Lupa Kata Sandi'
                  : _step == 2
                      ? 'Verifikasi OTP'
                      : 'Buat Sandi Baru',
              style: const TextStyle(
                fontSize: 22,
                fontWeight: FontWeight.bold,
                color: Colors.blueGrey,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            Text(
              _step == 1
                  ? 'Masukkan email kamu yang terdaftar, nanti kami kirim kode OTP.'
                  : _step == 2
                      ? 'Masukkan kode OTP yang sudah dikirim ke ${_emailController.text}'
                      : 'Yuk buat kata sandi baru untuk akunmu.',
              style: const TextStyle(color: Colors.grey),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 24),

            // Step 1: Email — FIX: enabled: !_isLoading
            if (_step == 1)
              TextField(
                controller: _emailController,
                keyboardType: TextInputType.emailAddress,
                enabled: !_isLoading,
                decoration: InputDecoration(
                  labelText: 'Email',
                  prefixIcon:
                      const Icon(Icons.email_outlined, color: Colors.teal),
                  border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(16)),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(16),
                    borderSide: const BorderSide(color: Colors.teal, width: 2),
                  ),
                ),
              ),

            // Step 2: OTP — FIX: enabled: !_isLoading
            if (_step == 2)
              TextField(
                controller: _otpController,
                keyboardType: TextInputType.number,
                textAlign: TextAlign.center,
                enabled: !_isLoading,
                style: const TextStyle(
                    fontSize: 24,
                    letterSpacing: 8,
                    fontWeight: FontWeight.bold),
                decoration: InputDecoration(
                  hintText: '000000',
                  border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(16)),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(16),
                    borderSide: const BorderSide(color: Colors.teal, width: 2),
                  ),
                ),
              ),

            // Step 3: Password baru — FIX: enabled: !_isLoading
            if (_step == 3)
              TextField(
                controller: _newPasswordController,
                obscureText: !_isNewPasswordVisible,
                enabled: !_isLoading,
                decoration: InputDecoration(
                  labelText: 'Kata Sandi Baru',
                  prefixIcon:
                      const Icon(Icons.lock_reset_rounded, color: Colors.teal),
                  suffixIcon: IconButton(
                    icon: Icon(
                      _isNewPasswordVisible
                          ? Icons.visibility_rounded
                          : Icons.visibility_off_rounded,
                      color: Colors.grey,
                    ),
                    onPressed: _isLoading
                        ? null
                        : () => setState(() =>
                            _isNewPasswordVisible = !_isNewPasswordVisible),
                  ),
                  border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(16)),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(16),
                    borderSide: const BorderSide(color: Colors.teal, width: 2),
                  ),
                ),
              ),

            const SizedBox(height: 24),

            // Tombol aksi
            SizedBox(
              height: 50,
              child: _isLoading
                  ? const Center(
                      child: CircularProgressIndicator(color: Colors.teal))
                  : ElevatedButton(
                      onPressed: _step == 1
                          ? _sendOTP
                          : _step == 2
                              ? _verifyOTP
                              : _resetPassword,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.teal,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(16)),
                      ),
                      child: Text(
                        _step == 1
                            ? 'Kirim OTP'
                            : _step == 2
                                ? 'Verifikasi OTP'
                                : 'Simpan Sandi Baru',
                        style: const TextStyle(
                            fontSize: 16, fontWeight: FontWeight.bold),
                      ),
                    ),
            ),
            const SizedBox(height: 16),
          ],
        ),
      ),
    );
  }
}