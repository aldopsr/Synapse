// lib/utils/text_cleaner.dart
//
// Utilitas untuk membersihkan tag HTML & sintaks Markdown dari teks materi
// sebelum diumpankan ke mesin TTS (flutter_tts), supaya suara pembacaan
// tidak ikut membaca tag seperti "kurung p" atau simbol "##".
//
// Catatan desain:
// - Dipakai oleh fitur "Dengarkan Transmisi" di MaterialDetailScreen.
// - Sengaja TIDAK pakai package html parser berat; cukup Regex agar ringan
//   dan tidak menambah dependency. Untuk konten e-modul sederhana ini sudah memadai.
// - Kalau suatu saat konten materi memuat HTML sangat kompleks (nested table,
//   script, dll), pertimbangkan parser DOM. Untuk sekarang ini cukup.

class TextCleaner {
  /// Membersihkan campuran HTML + Markdown menjadi teks polos yang natural
  /// untuk dibacakan TTS.
  static String cleanForSpeech(String? raw) {
    if (raw == null || raw.trim().isEmpty) return '';

    String text = raw;

    // 1. Hapus blok yang isinya tidak untuk dibaca (script/style)
    text = text.replaceAll(
      RegExp(r'<(script|style)[^>]*>.*?</\1>',
          caseSensitive: false, dotAll: true),
      ' ',
    );

    // 2. Ubah elemen blok jadi jeda kalimat supaya TTS tidak menempel.
    //    <br>, </p>, </div>, </li>, heading close -> baris baru.
    text = text.replaceAll(
      RegExp(r'<br\s*/?>', caseSensitive: false),
      '. ',
    );
    text = text.replaceAll(
      RegExp(r'</(p|div|li|h[1-6]|tr|blockquote)>', caseSensitive: false),
      '. ',
    );

    // 3. Hapus SEMUA sisa tag HTML.
    text = text.replaceAll(RegExp(r'<[^>]+>'), ' ');

    // 4. Decode entitas HTML yang umum.
    text = _decodeHtmlEntities(text);

    // ---- Bersihkan sintaks Markdown ----

    // 5. Code block ```...``` dan inline `code` -> ambil isinya saja.
    text = text.replaceAll(RegExp(r'```[a-zA-Z]*'), ' ');
    text = text.replaceAll('`', '');

    // 6. Gambar Markdown ![alt](url) -> buang total (atau bisa baca alt-nya).
    text = text.replaceAll(RegExp(r'!\[[^\]]*\]\([^)]*\)'), ' ');

    // 7. Link Markdown [teks](url) -> sisakan teksnya saja.
    text = text.replaceAllMapped(
      RegExp(r'\[([^\]]*)\]\([^)]*\)'),
      (m) => m.group(1) ?? '',
    );

    // 8. Heading penanda (#, ##, ...) di awal baris -> hapus penanda saja.
    text = text.replaceAll(RegExp(r'(^|\n)\s{0,3}#{1,6}\s*'), ' ');

    // 9. Bold/italic/strikethrough penanda **, __, *, _, ~~ -> hapus penanda.
    text = text.replaceAll(RegExp(r'(\*\*|__|~~|\*|_)'), '');

    // 10. Blockquote ">" dan bullet "-", "+", "*" di awal baris -> hapus.
    text = text.replaceAll(RegExp(r'(^|\n)\s*>\s?'), ' ');
    text = text.replaceAll(RegExp(r'(^|\n)\s*[-+*]\s+'), ' ');

    // 11. List bernomor "1." di awal baris -> hapus penandanya.
    text = text.replaceAll(RegExp(r'(^|\n)\s*\d+\.\s+'), ' ');

    // 12. Garis horizontal --- atau ===
    text = text.replaceAll(RegExp(r'(^|\n)\s*([-=*]\s*){3,}'), ' ');

    // ---- Rapikan whitespace & tanda baca ----

    // 13. Gabungkan whitespace berlebih jadi satu spasi.
    text = text.replaceAll(RegExp(r'\s+'), ' ');

    // 14. Rapikan titik berulang akibat penambahan jeda di langkah 2.
    text = text.replaceAll(RegExp(r'(\.\s*){2,}'), '. ');
    text = text.replaceAll(RegExp(r'\s+\.'), '.');

    return text.trim();
  }

  /// Decode entitas HTML umum. Sengaja minimal — hanya yang sering muncul
  /// di konten e-modul.
  static String _decodeHtmlEntities(String input) {
    const map = {
      '&nbsp;': ' ',
      '&amp;': 'dan',
      '&lt;': ' ',
      '&gt;': ' ',
      '&quot;': '"',
      '&#39;': "'",
      '&apos;': "'",
      '&hellip;': '...',
      '&mdash;': ' ',
      '&ndash;': ' ',
      '&rsquo;': "'",
      '&lsquo;': "'",
      '&rdquo;': '"',
      '&ldquo;': '"',
    };
    String out = input;
    map.forEach((k, v) => out = out.replaceAll(k, v));

    // Decode entitas numerik &#123; jika ada.
    out = out.replaceAllMapped(
      RegExp(r'&#(\d+);'),
      (m) {
        final code = int.tryParse(m.group(1) ?? '');
        if (code == null) return ' ';
        return String.fromCharCode(code);
      },
    );
    return out;
  }
}