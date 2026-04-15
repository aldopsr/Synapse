import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../services/auth_service.dart';
import 'login_screen.dart';

class EmailVerificationScreen extends StatefulWidget {
  final String email; // Menerima email dari layar register

  const EmailVerificationScreen({super.key, required this.email});

  @override
  State<EmailVerificationScreen> createState() => _EmailVerificationScreenState();
}

class _EmailVerificationScreenState extends State<EmailVerificationScreen> {
  final _otpController = TextEditingController();
  bool _isLoading = false;
  bool _isResending = false;

  void _verifyOTP() async {
    final otp = _otpController.text.trim();

    // 1. Validasi Input
    if (otp.length < 6) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Masukkan 6 digit kode OTP dengan benar!'),
          backgroundColor: Colors.redAccent,
        ),
      );
      return;
    }

    // 2. Nyalakan Animasi Loading
    setState(() => _isLoading = true);

    // 3. Tembak API Asli ke Laravel
    bool success = await AuthService().verifyEmail(widget.email, otp);
    
    // 4. Pastikan aplikasi belum ditutup saat menunggu balasan API
    if (!mounted) return;

    // 5. Matikan Animasi Loading
    setState(() => _isLoading = false);

    // 6. Cek Hasilnya
    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Verifikasi Berhasil! Silakan Login.'),
          backgroundColor: Colors.teal,
        ),
      );
      // Kembali ke Login Screen dan hapus semua riwayat layar sebelumnya
      Navigator.pushAndRemoveUntil(
        context,
        MaterialPageRoute(builder: (context) => const LoginScreen()),
        (route) => false,
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Kode OTP salah atau sudah kadaluarsa.'),
          backgroundColor: Colors.redAccent,
        ),
      );
    }
  }

  void _resendOTP() async {
    setState(() => _isResending = true);
    await AuthService().resendOTP(widget.email);
    setState(() => _isResending = false);
    
    await Future.delayed(const Duration(seconds: 2));

    if (!mounted) return;
    setState(() => _isResending = false);

    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Kode OTP baru telah dikirim ke email Anda!'),
        backgroundColor: Colors.teal,
      ),
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
          onPressed: () => Navigator.pop(context), 
        ),
      ),
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 16.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Icon Animasi / Header
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: Colors.teal.withOpacity(0.1),
                ),
                child: const Icon(Icons.mark_email_read_rounded, size: 80, color: Colors.teal),
              ),
              const SizedBox(height: 24),
              
              const Text(
                'Otorisasi Keamanan',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 26, fontWeight: FontWeight.w900, color: Colors.blueGrey),
              ),
              const SizedBox(height: 12),
              
              RichText(
                textAlign: TextAlign.center,
                text: TextSpan(
                  style: const TextStyle(fontSize: 15, color: Colors.grey, height: 1.5),
                  children: [
                    const TextSpan(text: 'Sistem telah mengirimkan 6 digit kode OTP ke \n'),
                    TextSpan(text: widget.email, style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.teal)),
                  ],
                ),
              ),
              const SizedBox(height: 40),

              // Kotak Input OTP
              Container(
                padding: const EdgeInsets.all(24),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(24),
                  boxShadow: [
                    BoxShadow(color: Colors.teal.withOpacity(0.05), blurRadius: 20, offset: const Offset(0, 10))
                  ],
                ),
                child: Column(
                  children: [
                    const Text(
                      'KODE VERIFIKASI',
                      style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.blueGrey, letterSpacing: 2),
                    ),
                    const SizedBox(height: 16),
                    
                    // Teknik cerdas untuk membuat kotak OTP menggunakan 1 TextField
                    TextField(
                      controller: _otpController,
                      keyboardType: TextInputType.number,
                      textAlign: TextAlign.center,
                      maxLength: 6,
                      style: const TextStyle(
                        fontSize: 32, 
                        fontWeight: FontWeight.bold, 
                        letterSpacing: 16.0, // Jarak antar angka agar lebar
                        color: Colors.teal,
                      ),
                      inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                      decoration: InputDecoration(
                        counterText: "", // Sembunyikan tulisan '0/6' di pojok bawah
                        filled: true,
                        fillColor: Colors.grey.shade50,
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(16),
                          borderSide: BorderSide.none,
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(16),
                          borderSide: const BorderSide(color: Colors.teal, width: 2),
                        ),
                      ),
                    ),
                    const SizedBox(height: 24),

                    // Tombol Verifikasi
                    SizedBox(
                      width: double.infinity,
                      height: 55,
                      child: _isLoading
                          ? const Center(child: CircularProgressIndicator(color: Colors.teal))
                          : ElevatedButton(
                              onPressed: _verifyOTP,
                              style: ElevatedButton.styleFrom(
                                backgroundColor: Colors.teal,
                                foregroundColor: Colors.white,
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                                elevation: 2,
                              ),
                              child: const Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(Icons.verified_user_rounded),
                                  SizedBox(width: 8),
                                  Text('VERIFIKASI SISTEM', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, letterSpacing: 1)),
                                ],
                              ),
                            ),
                    ),
                  ],
                ),
              ),
              
              const SizedBox(height: 32),

              // Tombol Kirim Ulang
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Text('Tidak menerima email? ', style: TextStyle(color: Colors.blueGrey)),
                  _isResending 
                    ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.teal))
                    : GestureDetector(
                        onTap: _resendOTP,
                        child: const Text(
                          'Kirim Ulang OTP',
                          style: TextStyle(color: Colors.amber, fontWeight: FontWeight.bold, decoration: TextDecoration.underline),
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
}