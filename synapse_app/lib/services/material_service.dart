import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/constants.dart';

class MaterialService {
  // Fungsi mengambil daftar materi
  Future<List<dynamic>> getMaterials() async {
    SharedPreferences prefs = await SharedPreferences.getInstance();
    String? token = prefs.getString('token');

    if (token == null) return []; // Jika tidak ada token, kembalikan list kosong

    try {
      final response = await http.get(
        Uri.parse('${AppConstants.baseUrl}/materials'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer $token', // Tunjukkan KTP!
        },
      );

      if (response.statusCode == 200) {
        // Ubah teks JSON dari Laravel menjadi List yang dimengerti Flutter
        return json.decode(response.body); 
      }
      return [];
    } catch (e) {
      print('Error Get Materials: $e');
      return [];
    }
  }
}