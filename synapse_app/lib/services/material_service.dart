import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:io';

// 🔥 copy dari AuthService
String getBaseUrl() {
  if (kIsWeb) {
    return 'http://127.0.0.1:8000/api';
  }

  if (Platform.isAndroid) {
    const bool isEmulator =
        bool.fromEnvironment('ANDROID_EMULATOR', defaultValue: false);

    return isEmulator
        ? 'http://10.0.2.2:8000/api'
        : 'http://192.168.1.12:8000/api';
  }

  return 'http://127.0.0.1:8000/api';
}

class MaterialService {
  Future<List<dynamic>> getMaterials() async {
    SharedPreferences prefs = await SharedPreferences.getInstance();
    String? token = prefs.getString('token');

    if (token == null) return [];

    try {
      final response = await http.get(
        Uri.parse('${getBaseUrl()}/materials'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        debugPrint('Gagal Get Materials: ${response.body}');
        return [];
      }
    } catch (e) {
      debugPrint('Error Get Materials: $e');
      return [];
    }
  }
}