// lib/screens/notes_screen.dart
import 'package:flutter/material.dart';
import '../services/note_service.dart';
import '../widgets/synapse_fab.dart';

class NotesScreen extends StatefulWidget {
  const NotesScreen({super.key});

  @override
  State<NotesScreen> createState() => _NotesScreenState();
}

class _NotesScreenState extends State<NotesScreen> {
  static const Color _primary = Color(0xFF2A9D8F);
  static const Color _darkTeal = Color(0xFF16877B);
  static const Color _bg = Color(0xFFF6F7FB);
  static const Color _textDark = Color(0xFF1F2937);
  static const Color _textMuted = Color(0xFF94A3B8);

  static const List<Color> _noteColors = [
    Colors.white,
    Color(0xFFFFF7DF),
    Color(0xFFFFEEF5),
    Color(0xFFEAF7FF),
    Color(0xFFEAFBF5),
    Color(0xFFF2EEFF),
    Color(0xFFFFEFE0),
    Color(0xFFFDE7F0),
  ];

  static const List<String> _colorNames = [
    'Default',
    'Kuning',
    'Pink',
    'Biru',
    'Hijau',
    'Ungu',
    'Oranye',
    'Rose',
  ];

  final NoteService _svc = NoteService();

  List<NoteModel> _notes = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    SynapseFabController.visible.value = false;
    _loadNotes();
  }

  @override
  void dispose() {
    SynapseFabController.visible.value = true;
    super.dispose();
  }

  Future<void> _loadNotes() async {
    final notes = await _svc.getNotes();
    if (mounted) {
      setState(() {
        _notes = notes;
        _loading = false;
      });
    }
  }

  Future<void> _deleteNote(String id) async {
    await _svc.deleteNote(id);
    await _loadNotes();
  }

  String _formatDate(DateTime dt) {
    final now = DateTime.now();
    final diff = now.difference(dt);

    if (diff.inMinutes < 1) return 'Baru saja';
    if (diff.inHours < 1) return '${diff.inMinutes} menit lalu';
    if (diff.inDays < 1) return '${diff.inHours} jam lalu';
    if (diff.inDays < 7) return '${diff.inDays} hari lalu';

    return '${dt.day}/${dt.month}/${dt.year}';
  }

  Color _noteColor(NoteModel note) {
    final idx = note.colorIndex ?? 0;
    if (idx < 0 || idx >= _noteColors.length) return Colors.white;
    return _noteColors[idx];
  }

  void _openNoteSheet({NoteModel? existing}) {
    final ctrl = TextEditingController(text: existing?.content ?? '');
    int selectedColor = existing?.colorIndex ?? 0;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => StatefulBuilder(
        builder: (ctx, setSheet) {
          final bg = _noteColors[selectedColor];

          return Padding(
            padding: EdgeInsets.only(
              bottom: MediaQuery.of(ctx).viewInsets.bottom,
            ),
            child: Container(
              decoration: BoxDecoration(
                color: _bg,
                borderRadius: const BorderRadius.vertical(
                  top: Radius.circular(32),
                ),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.18),
                    blurRadius: 26,
                    offset: const Offset(0, -8),
                  ),
                ],
              ),
              child: SingleChildScrollView(
                physics: const BouncingScrollPhysics(),
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(20, 12, 20, 24),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Container(
                        width: 54,
                        height: 5,
                        decoration: BoxDecoration(
                          color: Colors.grey.shade300,
                          borderRadius: BorderRadius.circular(100),
                        ),
                      ),
                      const SizedBox(height: 18),
                      Row(
                        children: [
                          Container(
                            width: 44,
                            height: 44,
                            decoration: BoxDecoration(
                              color: _primary.withOpacity(0.12),
                              borderRadius: BorderRadius.circular(16),
                            ),
                            child: Icon(
                              existing != null
                                  ? Icons.edit_note_rounded
                                  : Icons.note_add_rounded,
                              color: _primary,
                              size: 26,
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Text(
                              existing != null
                                  ? 'Edit Catatan'
                                  : 'Catatan Baru',
                              style: const TextStyle(
                                color: _textDark,
                                fontSize: 20,
                                fontWeight: FontWeight.w900,
                              ),
                            ),
                          ),
                          IconButton(
                            onPressed: () => Navigator.pop(ctx),
                            icon: const Icon(
                              Icons.close_rounded,
                              color: _textMuted,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 18),
                      AnimatedContainer(
                        duration: const Duration(milliseconds: 180),
                        padding: const EdgeInsets.all(18),
                        decoration: BoxDecoration(
                          color: bg,
                          borderRadius: BorderRadius.circular(24),
                          border: Border.all(
                            color: Colors.black.withOpacity(0.05),
                          ),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withOpacity(0.055),
                              blurRadius: 18,
                              offset: const Offset(0, 8),
                            ),
                          ],
                        ),
                        child: TextField(
                          controller: ctrl,
                          maxLines: 7,
                          minLines: 4,
                          autofocus: true,
                          cursorColor: _primary,
                          style: const TextStyle(
                            fontSize: 15,
                            color: _textDark,
                            height: 1.55,
                            fontWeight: FontWeight.w600,
                          ),
                          decoration: const InputDecoration(
                            hintText: 'Tulis catatanmu di sini...',
                            hintStyle: TextStyle(
                              color: _textMuted,
                              fontSize: 14,
                              fontWeight: FontWeight.w500,
                            ),
                            border: InputBorder.none,
                          ),
                        ),
                      ),
                      const SizedBox(height: 18),
                      Align(
                        alignment: Alignment.centerLeft,
                        child: Text(
                          'Pilih warna catatan',
                          style: TextStyle(
                            color: _textMuted.withOpacity(0.95),
                            fontSize: 12,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                      ),
                      const SizedBox(height: 12),
                      SizedBox(
                        height: 44,
                        child: ListView.separated(
                          scrollDirection: Axis.horizontal,
                          physics: const BouncingScrollPhysics(),
                          itemCount: _noteColors.length,
                          separatorBuilder: (_, __) =>
                              const SizedBox(width: 10),
                          itemBuilder: (_, i) {
                            final isSelected = selectedColor == i;

                            return GestureDetector(
                              onTap: () =>
                                  setSheet(() => selectedColor = i),
                              child: AnimatedContainer(
                                duration: const Duration(milliseconds: 160),
                                width: isSelected ? 44 : 38,
                                height: isSelected ? 44 : 38,
                                decoration: BoxDecoration(
                                  color: _noteColors[i],
                                  shape: BoxShape.circle,
                                  border: Border.all(
                                    color: isSelected
                                        ? _primary
                                        : Colors.black.withOpacity(0.12),
                                    width: isSelected ? 3 : 1,
                                  ),
                                  boxShadow: [
                                    if (isSelected)
                                      BoxShadow(
                                        color: _primary.withOpacity(0.24),
                                        blurRadius: 12,
                                        offset: const Offset(0, 5),
                                      ),
                                  ],
                                ),
                                child: isSelected
                                    ? const Icon(
                                        Icons.check_rounded,
                                        color: _primary,
                                        size: 18,
                                      )
                                    : null,
                              ),
                            );
                          },
                        ),
                      ),
                      const SizedBox(height: 22),
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed: () async {
                            final text = ctrl.text.trim();
                            if (text.isEmpty) return;

                            if (existing != null) {
                              await _svc.updateNote(
                                existing.id,
                                text,
                                colorIndex: selectedColor,
                              );
                            } else {
                              await _svc.createNote(
                                text,
                                colorIndex: selectedColor,
                              );
                            }

                            if (ctx.mounted) Navigator.pop(ctx);
                            await _loadNotes();
                          },
                          style: ElevatedButton.styleFrom(
                            backgroundColor: _primary,
                            foregroundColor: Colors.white,
                            elevation: 0,
                            padding: const EdgeInsets.symmetric(
                              vertical: 15,
                            ),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(18),
                            ),
                          ),
                          child: Text(
                            existing != null
                                ? 'Update Catatan'
                                : 'Simpan Catatan',
                            style: const TextStyle(
                              fontWeight: FontWeight.w900,
                              fontSize: 15,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          );
        },
      ),
    );
  }

  void _confirmDelete(String id) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        backgroundColor: Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        title: const Text(
          'Hapus catatan?',
          style: TextStyle(fontWeight: FontWeight.w900),
        ),
        content: const Text(
          'Catatan ini akan dihapus permanen.',
          style: TextStyle(color: _textMuted),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _deleteNote(id);
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.redAccent,
              foregroundColor: Colors.white,
              elevation: 0,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(14),
              ),
            ),
            child: const Text(
              'Hapus',
              style: TextStyle(fontWeight: FontWeight.w900),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _primary,
      body: Column(
        children: [
          _buildHeader(context),
          Expanded(
            child: ClipRRect(
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(38),
              ),
              child: Container(
                width: double.infinity,
                color: _bg,
                child: _loading
                    ? const Center(
                        child: CircularProgressIndicator(
                          color: _primary,
                          strokeWidth: 2,
                        ),
                      )
                    : _notes.isEmpty
                        ? _buildEmpty()
                        : RefreshIndicator(
                            color: _primary,
                            onRefresh: _loadNotes,
                            child: Padding(
                              padding:
                                  const EdgeInsets.fromLTRB(16, 18, 16, 0),
                              child: MasonryGridView(
                                notes: _notes,
                                noteColor: _noteColor,
                                formatDate: _formatDate,
                                onEdit: (n) =>
                                    _openNoteSheet(existing: n),
                                onDelete: (id) => _confirmDelete(id),
                              ),
                            ),
                          ),
              ),
            ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _openNoteSheet(),
        backgroundColor: _primary,
        foregroundColor: Colors.white,
        elevation: 6,
        icon: const Icon(Icons.add_rounded),
        label: const Text(
          'Catatan',
          style: TextStyle(fontWeight: FontWeight.w900),
        ),
      ),
    );
  }

  Widget _buildHeader(BuildContext context) {
    final top = MediaQuery.of(context).padding.top;

    return Container(
      height: 230,
      width: double.infinity,
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [
            Color(0xFF65C8D0),
            Color(0xFF2A9D8F),
            Color(0xFF16877B),
          ],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      child: Stack(
        children: [
          Positioned(
            top: -70,
            left: -55,
            child: _blob(190, Colors.white.withOpacity(0.12)),
          ),
          Positioned(
            top: 10,
            right: -55,
            child: _blob(165, Colors.teal.shade900.withOpacity(0.16)),
          ),
          Positioned(
            bottom: 18,
            left: 92,
            child: _blob(110, Colors.white.withOpacity(0.12)),
          ),
          Positioned(
            top: top + 12,
            left: 18,
            child: GestureDetector(
              onTap: () => Navigator.pop(context),
              child: Container(
                width: 42,
                height: 42,
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.20),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: const Icon(
                  Icons.arrow_back_rounded,
                  color: Colors.white,
                  size: 24,
                ),
              ),
            ),
          ),
          Positioned(
            top: top + 12,
            right: 18,
            child: Container(
              width: 42,
              height: 42,
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: Colors.white.withOpacity(0.20),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Image.asset(
                'assets/images/logo_synapse.png',
                color: Colors.white,
                errorBuilder: (_, __, ___) => const Icon(
                  Icons.auto_awesome_rounded,
                  color: Colors.white,
                  size: 22,
                ),
              ),
            ),
          ),
          Positioned(
            left: 24,
            right: 24,
            top: top + 78,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'My Notes',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 42,
                    height: 1.04,
                    fontWeight: FontWeight.w900,
                    letterSpacing: -1,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  '${_notes.length} catatan tersimpan',
                  style: TextStyle(
                    color: Colors.white.withOpacity(0.88),
                    fontSize: 15,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _blob(double size, Color color) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(size),
      ),
    );
  }

  Widget _buildEmpty() {
  return Center(
    child: Padding(
      padding: const EdgeInsets.all(38),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            width: 92,
            height: 92,
            decoration: const BoxDecoration(
              color: Color(0xFFEAFBF5),
              shape: BoxShape.circle,
            ),
            child: const Icon(
              Icons.sticky_note_2_outlined,
              size: 46,
              color: _primary,
            ),
          ),
          const SizedBox(height: 18),
          const Text(
            'Belum ada catatan',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w900,
              color: _textDark,
            ),
          ),
          const SizedBox(height: 8),
          const Text(
            'Ketuk tombol Catatan untuk mulai menulis.',
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 13,
              color: _textMuted,
              height: 1.45,
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    ),
  );
}
}

class MasonryGridView extends StatelessWidget {
  final List<NoteModel> notes;
  final Color Function(NoteModel) noteColor;
  final String Function(DateTime) formatDate;
  final void Function(NoteModel) onEdit;
  final void Function(String) onDelete;

  const MasonryGridView({
    super.key,
    required this.notes,
    required this.noteColor,
    required this.formatDate,
    required this.onEdit,
    required this.onDelete,
  });

  @override
  Widget build(BuildContext context) {
    final left = <NoteModel>[];
    final right = <NoteModel>[];

    for (var i = 0; i < notes.length; i++) {
      if (i % 2 == 0) {
        left.add(notes[i]);
      } else {
        right.add(notes[i]);
      }
    }

    return SingleChildScrollView(
      physics: const BouncingScrollPhysics(),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(
            child: Column(
              children: left.map((n) => _buildNoteCard(context, n)).toList(),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              children: right.map((n) => _buildNoteCard(context, n)).toList(),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildNoteCard(BuildContext context, NoteModel note) {
    final bg = noteColor(note);
    final textColor = const Color(0xFF1F2937);
    final subColor = Colors.black.withOpacity(0.38);

    return GestureDetector(
      onTap: () => onEdit(note),
      child: Container(
        margin: const EdgeInsets.only(bottom: 12),
        padding: const EdgeInsets.fromLTRB(14, 14, 12, 12),
        decoration: BoxDecoration(
          color: bg,
          borderRadius: BorderRadius.circular(22),
          border: Border.all(color: Colors.black.withOpacity(0.04)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.055),
              blurRadius: 16,
              offset: const Offset(0, 7),
            ),
          ],
        ),
        child: Stack(
          children: [
            Positioned(
              top: -4,
              right: -2,
              child: Icon(
                Icons.push_pin_rounded,
                size: 18,
                color: Colors.black.withOpacity(0.12),
              ),
            ),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  note.content,
                  style: TextStyle(
                    fontSize: 13,
                    color: textColor,
                    height: 1.55,
                    fontWeight: FontWeight.w600,
                  ),
                  maxLines: 8,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        formatDate(note.updatedAt),
                        style: TextStyle(
                          fontSize: 10,
                          color: subColor,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                    GestureDetector(
                      onTap: () => onDelete(note.id),
                      child: Container(
                        width: 30,
                        height: 30,
                        decoration: BoxDecoration(
                          color: Colors.black.withOpacity(0.055),
                          shape: BoxShape.circle,
                        ),
                        child: Icon(
                          Icons.delete_outline_rounded,
                          size: 16,
                          color: subColor,
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}