// lib/services/tts_service.dart
//
// Pembungkus tipis di atas flutter_tts.
// Tujuan: menjaga konfigurasi TTS terpusat & lifecycle aman (init sekali,
// dispose rapi), sehingga widget pemanggil tidak perlu tahu detail engine.
//
// Bahasa default diset ke Indonesia ("id-ID"). Jika engine HP user tidak
// punya paket suara id-ID, flutter_tts akan fallback ke default engine —
// kita tidak crash, hanya logika onError yang menangani.

import 'package:flutter/foundation.dart';
import 'package:flutter_tts/flutter_tts.dart';

enum TtsState { stopped, playing, paused }

class TtsService {
  final FlutterTts _tts = FlutterTts();

  bool _initialized = false;

  /// Callback opsional supaya UI bisa update tombol (play/stop).
  void Function(TtsState state)? onStateChanged;

  TtsState _state = TtsState.stopped;
  TtsState get state => _state;

  void _setState(TtsState s) {
    _state = s;
    onStateChanged?.call(s);
  }

  /// Inisialisasi sekali. Aman dipanggil berkali-kali (idempotent).
  Future<void> init() async {
    if (_initialized) return;

    try {
      // Bahasa Indonesia jika tersedia; jika tidak, biarkan default engine.
      final bool idAvailable =
          (await _tts.isLanguageAvailable('id-ID')) == true;
      if (idAvailable) {
        await _tts.setLanguage('id-ID');
      }

      await _tts.setSpeechRate(0.5); // 0.5 = kecepatan natural di Android
      await _tts.setPitch(1.0);
      await _tts.setVolume(1.0);

      // iOS perlu shared audio session agar suara keluar konsisten.
      await _tts.awaitSpeakCompletion(true);

      _tts.setStartHandler(() => _setState(TtsState.playing));
      _tts.setCompletionHandler(() => _setState(TtsState.stopped));
      _tts.setCancelHandler(() => _setState(TtsState.stopped));
      _tts.setPauseHandler(() => _setState(TtsState.paused));
      _tts.setContinueHandler(() => _setState(TtsState.playing));
      _tts.setErrorHandler((msg) {
        debugPrint('TTS error: $msg');
        _setState(TtsState.stopped);
      });

      _initialized = true;
    } catch (e) {
      debugPrint('TTS init exception: $e');
      // Tetap tandai initialized agar tidak loop init; speak akan no-op aman.
      _initialized = true;
    }
  }

  /// Mulai membaca teks (yang SUDAH dibersihkan dari HTML/Markdown).
  Future<void> speak(String cleanText) async {
    if (cleanText.trim().isEmpty) return;
    await init();
    await _tts.stop(); // hentikan apa pun yang sedang berjalan
    await _tts.speak(cleanText);
  }

  Future<void> stop() async {
    await _tts.stop();
    _setState(TtsState.stopped);
  }

  Future<void> pause() async {
    await _tts.pause();
  }

  /// Wajib dipanggil di dispose() widget pemanggil agar suara berhenti
  /// saat halaman ditutup.
  Future<void> dispose() async {
    await _tts.stop();
  }
}