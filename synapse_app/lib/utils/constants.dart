class AppConstants {
  // ============================================================
  // GANTI SESUAI KONDISI KAMU:
  //
  // Kalau pakai EMULATOR Android → pakai baris pertama
  // Kalau pakai HP FISIK via WiFi → pakai baris kedua
  //                                  (ganti IP sesuai IP WiFi laptop)
  // Kalau sudah PRODUCTION        → pakai baris ketiga
  //
  // Cara pakai: hapus "//" di depan baris yang mau dipakai,
  //             tambah "//" di depan baris yang tidak dipakai
  // ============================================================

  // Emulator Android
  // static const String baseUrl = 'http://10.0.2.2:8000/api';

  // HP Fisik via WiFi (ganti 192.168.1.14 dengan IP WiFi laptop kamu)
  static const String baseUrl = 'http://192.168.1.22:8000/api';

  // Production (uncomment ini kalau sudah punya server)
  // static const String baseUrl = 'https://api.synapse.app/api';

  // ============================================================
  // JANGAN UBAH DI BAWAH INI
  // ============================================================

  static const String loginUrl          = '$baseUrl/auth/login';
  static const String registerUrl       = '$baseUrl/auth/register';
  static const String verifyOtpUrl      = '$baseUrl/auth/verify-otp';
  static const String resendOtpUrl      = '$baseUrl/auth/resend-otp';
  static const String logoutUrl         = '$baseUrl/auth/logout';
  static const String getMeUrl          = '$baseUrl/auth/me';
  static const String changePasswordUrl = '$baseUrl/auth/change-password';
  static const String coursesUrl        = '$baseUrl/public/courses';
  static const String materialsUrl      = '$baseUrl/materials';
  static const String quizzesUrl        = '$baseUrl/quizzes';
  static const String chatUrl           = '$baseUrl/chat';
}