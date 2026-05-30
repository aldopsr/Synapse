// lib/services/note_service.dart
import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/constants.dart';

class NoteModel {
  final String id;
  final String content;
  final int colorIndex;
  final DateTime createdAt;
  final DateTime updatedAt;

  NoteModel({
    required this.id,
    required this.content,
    this.colorIndex = 0,
    required this.createdAt,
    required this.updatedAt,
  });

  factory NoteModel.fromJson(Map<String, dynamic> json) {
    return NoteModel(
      id: (json['_id'] ?? json['id'] ?? '').toString(),
      content: json['content'] ?? '',
      colorIndex: (json['color_index'] ?? 0) as int,
      createdAt: DateTime.tryParse(json['created_at'] ?? '') ?? DateTime.now(),
      updatedAt: DateTime.tryParse(json['updated_at'] ?? '') ?? DateTime.now(),
    );
  }
}

class NoteService {
  static String get baseUrl => AppConstants.baseUrl;

  Future<String?> _getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('token');
  }

  Future<List<NoteModel>> getNotes() async {
    final token = await _getToken();
    if (token == null) return [];
    try {
      final res = await http.get(
        Uri.parse('$baseUrl/notes'),
        headers: {'Authorization': 'Bearer $token', 'Accept': 'application/json'},
      );
      if (res.statusCode == 200) {
        final List data = jsonDecode(res.body)['data'] ?? [];
        return data.map((e) => NoteModel.fromJson(e)).toList();
      }
      return [];
    } catch (e) {
      debugPrint('getNotes error: $e');
      return [];
    }
  }

  Future<NoteModel?> createNote(String content, {int colorIndex = 0}) async {
    final token = await _getToken();
    if (token == null) return null;
    try {
      final res = await http.post(
        Uri.parse('$baseUrl/notes'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({'content': content, 'color_index': colorIndex}),
      );
      if (res.statusCode == 201) {
        return NoteModel.fromJson(jsonDecode(res.body)['data']);
      }
      return null;
    } catch (e) {
      debugPrint('createNote error: $e');
      return null;
    }
  }

  Future<bool> updateNote(String id, String content, {int colorIndex = 0}) async {
    final token = await _getToken();
    if (token == null) return false;
    try {
      final res = await http.put(
        Uri.parse('$baseUrl/notes/$id'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({'content': content, 'color_index': colorIndex}),
      );
      return res.statusCode == 200;
    } catch (e) {
      debugPrint('updateNote error: $e');
      return false;
    }
  }

  Future<bool> deleteNote(String id) async {
    final token = await _getToken();
    if (token == null) return false;
    try {
      final res = await http.delete(
        Uri.parse('$baseUrl/notes/$id'),
        headers: {'Authorization': 'Bearer $token', 'Accept': 'application/json'},
      );
      return res.statusCode == 200;
    } catch (e) {
      debugPrint('deleteNote error: $e');
      return false;
    }
  }
}