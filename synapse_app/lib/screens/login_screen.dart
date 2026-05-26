// login_screen.dart
// PERUBAHAN RESPONSIVENESS:
// FIX #1 — resizeToAvoidBottomInset: true (keyboard tidak nutup form)
// FIX #2 — body: SafeArea + Center (notch atas aman)
// FIX #3 — Logo fluid: (width * 0.38).clamp(100, 160)
// FIX #4 — textInputAction next/done + onSubmitted di password
// FIX #5 — ForgotPasswordSheet: padding bottom += viewInsets.bottom
// Import & logika IDENTIK dengan aslinya: '../utils/constants.dart'

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import 'home_screen.dart';
import 'register_screen.dart';
import '../services/auth_service.dart';
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

  void _login() async {
    final email    = _emailController.text.trim();
    final password = _passwordController.text.trim();

    if (email.isEmpty || password.isEmpty) {
      _showSnackbar('Email dan kata sandi tidak boleh kosong!', Colors.orange);
      return;
    }

    setState(() => _isLoading = true);
    final auth    = AuthService();
    final success = await auth.login(email, password);
    if (!mounted) return;
    setState(() => _isLoading = false);

    if (success) {
      final userData = await auth.getUserProfile();
      if (!mounted) return;
      final role = userData?['role'] ?? 'public';

      if (role == 'admin' || role == 'dosen') {
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
    } else {
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

  @override
  Widget build(BuildContext context) {
    // FIX #3
    final double logoSize =
        (MediaQuery.of(context).size.width * 0.38).clamp(100.0, 160.0);

    return Scaffold(
      backgroundColor: Colors.blueGrey[50],
      // FIX #1
      resizeToAvoidBottomInset: true,
      // FIX #2
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24.0),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Center(
                  child: Image.asset(
                    'assets/images/logo_synapse.png',
                    width: logoSize,
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
                      // FIX #4: textInputAction next
                      TextField(
                        controller: _emailController,
                        keyboardType: TextInputType.emailAddress,
                        enabled: !_isLoading,
                        textInputAction: TextInputAction.next,
                        decoration: InputDecoration(
                          labelText: 'Email',
                          hintText: 'contoh@apps.ipb.ac.id',
                          prefixIcon: const Icon(Icons.email_rounded, color: Colors.teal),
                          border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(16)),
                          focusedBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(16),
                            borderSide: const BorderSide(color: Colors.teal, width: 2),
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),
                      // FIX #4: textInputAction done + onSubmitted
                      TextField(
                        controller: _passwordController,
                        obscureText: !_isPasswordVisible,
                        enabled: !_isLoading,
                        textInputAction: TextInputAction.done,
                        onSubmitted: (_) => _isLoading ? null : _login(),
                        decoration: InputDecoration(
                          labelText: 'Kata Sandi',
                          prefixIcon: const Icon(Icons.lock_rounded, color: Colors.teal),
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
                            borderSide: const BorderSide(color: Colors.teal, width: 2),
                          ),
                        ),
                      ),
                      Align(
                        alignment: Alignment.centerRight,
                        child: TextButton(
                          onPressed: _isLoading ? null : _showForgotPasswordSheet,
                          child: const Text('Lupa Kata Sandi?',
                              style: TextStyle(color: Colors.teal)),
                        ),
                      ),
                      SizedBox(
                        width: double.infinity,
                        height: 54,
                        child: ElevatedButton(
                          onPressed: _isLoading ? null : _login,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.teal,
                            foregroundColor: Colors.white,
                            shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(16)),
                            elevation: 0,
                          ),
                          child: _isLoading
                              ? const SizedBox(
                                  width: 22, height: 22,
                                  child: CircularProgressIndicator(
                                      color: Colors.white, strokeWidth: 2.5),
                                )
                              : const Text('MASUK',
                                  style: TextStyle(
                                      fontSize: 16,
                                      fontWeight: FontWeight.bold,
                                      letterSpacing: 1)),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 24),

                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Text('Belum punya akun? ',
                        style: TextStyle(color: Colors.blueGrey)),
                    GestureDetector(
                      onTap: _isLoading
                          ? null
                          : () => Navigator.push(
                                context,
                                MaterialPageRoute(
                                    builder: (context) => const RegisterScreen()),
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

                Row(
                  children: [
                    Expanded(child: Divider(color: Colors.grey.shade300)),
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      child: Text('ATAU',
                          style: TextStyle(
                              color: Colors.grey.shade500,
                              fontWeight: FontWeight.bold,
                              fontSize: 12)),
                    ),
                    Expanded(child: Divider(color: Colors.grey.shade300)),
                  ],
                ),
                const SizedBox(height: 24),

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
      ),
    );
  }
}

// ============================================================================
// BOTTOM SHEET LUPA PASSWORD — logika identik asli, + FIX #5 viewInsets
// ============================================================================
class ForgotPasswordSheet extends StatefulWidget {
  const ForgotPasswordSheet({super.key});

  @override
  State<ForgotPasswordSheet> createState() => _ForgotPasswordSheetState();
}

class _ForgotPasswordSheetState extends State<ForgotPasswordSheet> {
  int    _step       = 1;
  bool   _isLoading  = false;
  String _resetToken = '';

  final _emailController       = TextEditingController();
  final _otpController         = TextEditingController();
  final _newPasswordController = TextEditingController();
  bool _isNewPasswordVisible   = false;

  final String baseUrl = AppConstants.baseUrl;

  @override
  void dispose() {
    _emailController.dispose();
    _otpController.dispose();
    _newPasswordController.dispose();
    super.dispose();
  }

  void _showSnackbar(String message, Color color) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
          content: Text(message),
          backgroundColor: color,
          behavior: SnackBarBehavior.floating),
    );
  }

  void _sendOTP() async {
    if (_emailController.text.isEmpty) {
      _showSnackbar('Masukkan emailmu dulu ya!', Colors.orange);
      return;
    }
    setState(() => _isLoading = true);
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/forgot-password/send-otp'),
        headers: {'Accept': 'application/json', 'Content-Type': 'application/json'},
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
          _showSnackbar(error['message'] ?? 'Email tidak ditemukan. Cek lagi ya!', Colors.redAccent);
        } catch (_) {
          _showSnackbar('Gagal kirim OTP. Coba lagi ya!', Colors.redAccent);
        }
      }
    } catch (_) {
      if (!mounted) return;
      setState(() => _isLoading = false);
      _showSnackbar('Koneksi bermasalah. Coba lagi ya!', Colors.redAccent);
    }
  }

  void _verifyOTP() async {
    if (_otpController.text.length < 6) {
      _showSnackbar('Masukkan 6 digit kode OTP ya!', Colors.orange);
      return;
    }
    setState(() => _isLoading = true);
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/forgot-password/verify-otp'),
        headers: {'Accept': 'application/json', 'Content-Type': 'application/json'},
        body: jsonEncode({
          'email': _emailController.text.trim(),
          'otp': _otpController.text.trim(),
        }),
      );
      if (!mounted) return;
      setState(() => _isLoading = false);
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        _resetToken = data['reset_token'] ?? '';
        setState(() => _step = 3);
      } else {
        _showSnackbar('Kode OTP salah atau sudah kadaluarsa.', Colors.redAccent);
      }
    } catch (_) {
      if (!mounted) return;
      setState(() => _isLoading = false);
      _showSnackbar('Koneksi bermasalah. Coba lagi ya!', Colors.redAccent);
    }
  }

  void _resetPassword() async {
    if (_newPasswordController.text.length < 8) {
      _showSnackbar('Kata sandi minimal 8 karakter ya!', Colors.orange);
      return;
    }
    setState(() => _isLoading = true);
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/forgot-password/reset'),
        headers: {'Accept': 'application/json', 'Content-Type': 'application/json'},
        body: jsonEncode({
          'reset_token': _resetToken,
          'password': _newPasswordController.text,
          'password_confirmation': _newPasswordController.text,
        }),
      );
      if (!mounted) return;
      setState(() => _isLoading = false);
      if (response.statusCode == 200) {
        _showSnackbar('Kata sandi berhasil diubah! Silakan login.', Colors.teal);
        Navigator.pop(context);
      } else {
        _showSnackbar('Gagal reset kata sandi. Coba lagi ya!', Colors.redAccent);
      }
    } catch (_) {
      if (!mounted) return;
      setState(() => _isLoading = false);
      _showSnackbar('Koneksi bermasalah. Coba lagi ya!', Colors.redAccent);
    }
  }

  @override
  Widget build(BuildContext context) {
    // FIX #5: sheet naik saat keyboard muncul
    final bottomInset = MediaQuery.of(context).viewInsets.bottom;

    return Container(
      padding: EdgeInsets.fromLTRB(24, 24, 24, 24 + bottomInset),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 40, height: 4,
              decoration: BoxDecoration(
                  color: Colors.grey[300], borderRadius: BorderRadius.circular(2)),
            ),
            const SizedBox(height: 20),
            Text(
              _step == 1 ? 'Lupa Kata Sandi?' : _step == 2 ? 'Masukkan Kode OTP' : 'Buat Kata Sandi Baru',
              style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
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

            if (_step == 1)
              TextField(
                controller: _emailController,
                keyboardType: TextInputType.emailAddress,
                enabled: !_isLoading,
                decoration: InputDecoration(
                  labelText: 'Email',
                  prefixIcon: const Icon(Icons.email_outlined, color: Colors.teal),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(16)),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(16),
                    borderSide: const BorderSide(color: Colors.teal, width: 2),
                  ),
                ),
              ),

            if (_step == 2)
              TextField(
                controller: _otpController,
                keyboardType: TextInputType.number,
                textAlign: TextAlign.center,
                enabled: !_isLoading,
                style: const TextStyle(fontSize: 24, letterSpacing: 8, fontWeight: FontWeight.bold),
                decoration: InputDecoration(
                  hintText: '000000',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(16)),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(16),
                    borderSide: const BorderSide(color: Colors.teal, width: 2),
                  ),
                ),
              ),

            if (_step == 3)
              TextField(
                controller: _newPasswordController,
                obscureText: !_isNewPasswordVisible,
                enabled: !_isLoading,
                decoration: InputDecoration(
                  labelText: 'Kata Sandi Baru',
                  prefixIcon: const Icon(Icons.lock_reset_rounded, color: Colors.teal),
                  suffixIcon: IconButton(
                    icon: Icon(
                      _isNewPasswordVisible ? Icons.visibility_rounded : Icons.visibility_off_rounded,
                      color: Colors.grey,
                    ),
                    onPressed: _isLoading
                        ? null
                        : () => setState(() => _isNewPasswordVisible = !_isNewPasswordVisible),
                  ),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(16)),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(16),
                    borderSide: const BorderSide(color: Colors.teal, width: 2),
                  ),
                ),
              ),

            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              height: 54,
              child: ElevatedButton(
                onPressed: _isLoading
                    ? null
                    : _step == 1 ? _sendOTP : _step == 2 ? _verifyOTP : _resetPassword,
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.teal,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                  elevation: 0,
                ),
                child: _isLoading
                    ? const SizedBox(
                        width: 22, height: 22,
                        child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2.5),
                      )
                    : Text(
                        _step == 1 ? 'Kirim OTP' : _step == 2 ? 'Verifikasi OTP' : 'Simpan Kata Sandi',
                        style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                      ),
              ),
            ),
            const SizedBox(height: 8),
          ],
        ),
      ),
    );
  }
}