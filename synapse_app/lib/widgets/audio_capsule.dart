import 'package:flutter/material.dart';
import '../services/tts_service.dart';
import '../utils/text_cleaner.dart';

class AudioCapsule extends StatefulWidget {
  final String rawContent;

  const AudioCapsule({super.key, required this.rawContent});

  static Future<void> stopActiveAudio() async {
    await _AudioCapsuleState._activeState?._stopAudio();
  }

  @override
  State<AudioCapsule> createState() => _AudioCapsuleState();
}

class _AudioCapsuleState extends State<AudioCapsule>
    with AutomaticKeepAliveClientMixin {
  static _AudioCapsuleState? _activeState;

  static const Color _primary = Color(0xFF2A9D8F);
  static const Color _accentPurple = Color(0xFFA855F7);

  final TtsService _tts = TtsService();
  TtsState _state = TtsState.stopped;

  @override
  bool get wantKeepAlive => true;

  @override
  void initState() {
    super.initState();

    _tts.onStateChanged = (s) {
      if (mounted) setState(() => _state = s);
    };
  }

  Future<void> _stopAudio() async {
    await _tts.stop();

    if (mounted) {
      setState(() => _state = TtsState.stopped);
    }
  }

  @override
  void dispose() {
    if (_activeState == this) {
      _activeState = null;
    }

    _tts.dispose();
    super.dispose();
  }

  Future<void> _toggle() async {
    if (_state == TtsState.playing) {
      await _stopAudio();
      return;
    }

    if (_activeState != null && _activeState != this) {
      await _activeState!._stopAudio();
    }

    final clean = TextCleaner.cleanForSpeech(widget.rawContent);

    if (clean.isEmpty) {
      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Tidak ada teks untuk dibacakan.')),
      );
      return;
    }

    _activeState = this;
    await _tts.speak(clean);
  }

  @override
  Widget build(BuildContext context) {
    super.build(context);

    final bool playing = _state == TtsState.playing;

    return Container(
      margin: const EdgeInsets.fromLTRB(0, 0, 0, 0),
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
                        playing ? 'Sedang Diputar' : 'Dengarkan Transmisi',
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