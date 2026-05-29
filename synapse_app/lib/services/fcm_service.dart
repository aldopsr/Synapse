// lib/services/fcm_service.dart
import 'dart:convert';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/constants.dart';

@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  debugPrint('FCM background: ${message.notification?.title}');
}

class FcmService {
  static final FcmService _instance = FcmService._internal();
  factory FcmService() => _instance;
  FcmService._internal();

  final FirebaseMessaging _fcm = FirebaseMessaging.instance;

  /// Callback yang dipasang oleh HomeScreen.
  /// Dipanggil saat notifikasi duel di-tap atau masuk saat app foreground.
  static void Function(Map<String, dynamic> data)? onDuelNotification;

  Future<void> init() async {
    final settings = await _fcm.requestPermission(
      alert: true, badge: true, sound: true,
    );
    if (settings.authorizationStatus == AuthorizationStatus.denied) {
      debugPrint('FCM: izin ditolak');
      return;
    }

    FirebaseMessaging.onBackgroundMessage(
        firebaseMessagingBackgroundHandler);

    await _registerToken();

    _fcm.onTokenRefresh.listen((newToken) async {
      await _sendTokenToBackend(newToken);
    });

    // ── Notifikasi masuk saat app FOREGROUND ──────────────────
    FirebaseMessaging.onMessage.listen((message) {
      debugPrint('FCM foreground: ${message.notification?.title}');
      // Panggil callback langsung — HomeScreen akan buka halaman yang tepat
      if (message.data.isNotEmpty) {
        _handleData(message.data);
      }
    });

    // ── App dibuka dari notifikasi (BACKGROUND → foreground) ──
    FirebaseMessaging.onMessageOpenedApp.listen((message) {
      debugPrint('FCM onMessageOpenedApp: ${message.data}');
      if (message.data.isNotEmpty) {
        _handleData(message.data);
      }
    });

    // ── App dibuka dari notifikasi (TERMINATED → buka) ────────
    final initialMessage = await _fcm.getInitialMessage();
    if (initialMessage != null && initialMessage.data.isNotEmpty) {
      // Delay kecil agar HomeScreen sudah mount
      await Future.delayed(const Duration(milliseconds: 500));
      _handleData(initialMessage.data);
    }
  }

  void _handleData(Map<String, dynamic> data) {
    final type   = data['type']?.toString() ?? '';
    final duelId = data['duel_id']?.toString() ?? '';

    // Hanya forward notifikasi yang berkaitan dengan duel
    const duelTypes = [
      'duel_challenge', 'duel_accepted', 'duel_completed',
      'duel_declined',  'duel_cancelled', 'duel_expired',
    ];

    if (duelTypes.contains(type) && duelId.isNotEmpty) {
      onDuelNotification?.call({'type': type, 'duel_id': duelId});
    }
  }

  Future<void> _registerToken() async {
    try {
      final token = await _fcm.getToken();
      if (token == null) return;
      debugPrint('FCM token: $token');
      await _sendTokenToBackend(token);
    } catch (e) {
      debugPrint('FCM _registerToken error: $e');
    }
  }

  Future<void> _sendTokenToBackend(String token) async {
    try {
      final prefs     = await SharedPreferences.getInstance();
      final authToken = prefs.getString('token');
      if (authToken == null) return;

      final response = await http.post(
        Uri.parse('${AppConstants.baseUrl}/fcm-token'),
        headers: {
          'Authorization': 'Bearer $authToken',
          'Accept':        'application/json',
          'Content-Type':  'application/json',
        },
        body: jsonEncode({'token': token}),
      );

      if (response.statusCode == 200) {
        debugPrint('FCM token berhasil dikirim ke backend');
      }
    } catch (e) {
      debugPrint('FCM _sendTokenToBackend error: $e');
    }
  }

  Future<void> deleteToken() async {
    try {
      final prefs     = await SharedPreferences.getInstance();
      final authToken = prefs.getString('token');
      if (authToken == null) return;

      await http.delete(
        Uri.parse('${AppConstants.baseUrl}/fcm-token'),
        headers: {
          'Authorization': 'Bearer $authToken',
          'Accept':        'application/json',
        },
      );
      await _fcm.deleteToken();
      debugPrint('FCM token dihapus');
    } catch (e) {
      debugPrint('FCM deleteToken error: $e');
    }
  }
}