class AppConstants {
  // ============================================================
  // GANTI SESUAI KONDISI:
  // Emulator Android  → 'http://10.0.2.2:8000/api'
  // HP Fisik via WiFi → 'http://192.168.x.x:8000/api'
  // Production        → 'https://api.synapse.app/api'
  // ============================================================
  static const String baseUrl = 'http://192.168.1.10:8000/api';

  // ── Auth ─────────────────────────────────────────────────────
  static const String loginUrl          = '$baseUrl/auth/login';
  static const String registerUrl       = '$baseUrl/auth/register';
  static const String verifyOtpUrl      = '$baseUrl/auth/verify-otp';
  static const String resendOtpUrl      = '$baseUrl/auth/resend-otp';
  static const String logoutUrl         = '$baseUrl/auth/logout';
  static const String getMeUrl          = '$baseUrl/auth/me';
  static const String changePasswordUrl = '$baseUrl/auth/change-password';

  // ── Konten ───────────────────────────────────────────────────
  static const String coursesUrl        = '$baseUrl/public/courses';
  static const String materialsUrl      = '$baseUrl/materials';
  static const String quizzesUrl        = '$baseUrl/quizzes';
  static const String arAssetsUrl       = '$baseUrl/ar-assets';

  // ── Chat ─────────────────────────────────────────────────────
  static const String chatUrl           = '$baseUrl/chat';
  static const String chatQuotaUrl      = '$baseUrl/chat/quota'; // kuota harian public
}