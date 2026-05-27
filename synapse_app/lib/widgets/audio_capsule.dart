// lib/widgets/audio_capsule.dart
//
// Tombol "Dengarkan Transmisi" (Audio Capsule) untuk Detail Materi.
// Membersihkan konten HTML/Markdown via TextCleaner lalu memutarnya
// dengan TtsService. Self-contained: kelola state play/stop sendiri.
//
// Pemakaian di MaterialDetailScreen:
//   AudioCapsule(rawContent: material['content'] ?? material['body'] ?? '')

import 'package:flutter/material.dart';
import '../services/tts_service.dart';
import '../utils/text_cleaner.dart';

class AudioCapsule extends StatefulWidget {
  final String rawContent;

  const AudioCapsule({super.key, required this.rawContent});

  @override
  State<AudioCapsule> createState() => _AudioCapsuleState();
}

class _AudioCapsuleState extends State<AudioCapsule> {
  static const Color _primary = Color(0xFF2A9D8F);
  static const Color _accentPurple = Color(0xFFA855F7);

  final TtsService _tts = TtsService();
  TtsState _state = TtsState.stopped;

  @override
  void initState() {
    super.initState();
    _tts.onStateChanged = (s) {
      if (mounted) setState(() => _state = s);
    };
  }

  @override
  void dispose() {
    _tts.dispose(); // hentikan suara saat halaman ditutup
    super.dispose();
  }

  Future<void> _toggle() async {
    if (_state == TtsState.playing) {
      await _tts.stop();
      return;
    }
    final clean = TextCleaner.cleanForSpeech(widget.rawContent);
    if (clean.isEmpty) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Tidak ada teks untuk dibacakan.')),
      );
      return;
    }
    await _tts.speak(clean);
  }

  @override
  Widget build(BuildContext context) {
    final bool playing = _state == TtsState.playing;

    return Container(
      margin: const EdgeInsets.fromLTRB(20, 16, 20, 0),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(18),
          onTap: _toggle,
          child: AnimatedContainer(
            duration: const Duration(milliseconds: 250),
            padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: playing
                    ? [_accentPurple, const Color(0xFF7C3AED)]
                    : [_primary, const Color(0xFF1F7A6D)],
              ),
              borderRadius: BorderRadius.circular(18),
              boxShadow: [
                BoxShadow(
                  color: (playing ? _accentPurple : _primary).withOpacity(0.35),
                  blurRadius: 14,
                  offset: const Offset(0, 6),
                ),
              ],
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.2),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(
                    playing ? Icons.stop_rounded : Icons.headphones_rounded,
                    color: Colors.white,
                    size: 22,
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        playing ? 'Menghentikan...' : 'Dengarkan Transmisi',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 15,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        playing
                            ? 'Ketuk untuk berhenti'
                            : 'Putar materi dalam bentuk audio',
                        style: TextStyle(
                          color: Colors.white.withOpacity(0.8),
                          fontSize: 11.5,
                        ),
                      ),
                    ],
                  ),
                ),
                if (playing)
                  const SizedBox(
                    width: 18,
                    height: 18,
                    child: CircularProgressIndicator(
                      color: Colors.white,
                      strokeWidth: 2,
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