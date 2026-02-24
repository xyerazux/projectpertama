/// API Configuration — change baseUrl for production deployment
class ApiConstants {
  // ── Development (Emulator) ──
  // static const String baseUrl = 'http://10.0.2.2:8000/api';

  // ── Development (Physical Device via LAN) ──
  static const String baseUrl = 'https://productivityapp.up.railway.app/api';

  // ── Production ──
  // static const String baseUrl = 'https://yourdomain.com/api';

  static const Duration timeout = Duration(seconds: 15);
}
