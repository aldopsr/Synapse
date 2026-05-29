// lib/screens/notes_screen.dart
// Halaman full list catatan — diakses dari ProfileScreen
import 'package:flutter/material.dart';
import '../services/note_service.dart';
import '../widgets/synapse_fab.dart';

class NotesScreen extends StatefulWidget {
  const NotesScreen({super.key});

  @override
  State<NotesScreen> createState() => _NotesScreenState();
}

class _NotesScreenState extends State<NotesScreen> {
  static const Color _primary    = Color(0xFF2A9D8F);
  static const Color _noteColor  = Color(0xFFFFF9E6);
  static const Color _noteAccent = Color(0xFFFF9800);

  final NoteService _svc  = NoteService();
  List<NoteModel> _notes  = [];
  bool _loading           = true;

  @override
  void initState() {
    super.initState();
    // Sembunyikan FAB di halaman ini karena sudah ada akses notes
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
    if (mounted) setState(() { _notes = notes; _loading = false; });
  }

  Future<void> _deleteNote(String id) async {
    await _svc.deleteNote(id);
    await _loadNotes();
  }

  String _formatDate(DateTime dt) {
    final now  = DateTime.now();
    final diff = now.difference(dt);
    if (diff.inMinutes < 1)  return 'Baru saja';
    if (diff.inHours < 1)    return '${diff.inMinutes} menit lalu';
    if (diff.inDays < 1)     return '${diff.inHours} jam lalu';
    if (diff.inDays < 7)     return '${diff.inDays} hari lalu';
    return '${dt.day}/${dt.month}/${dt.year}';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F6F8),
      body: SafeArea(
        bottom: false,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 20, 20, 16),
              child: Row(
                children: [
                  GestureDetector(
                    onTap: () => Navigator.pop(context),
                    child: Container(
                      width: 40, height: 40,
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFFE2E8F0)),
                      ),
                      child: const Icon(Icons.arrow_back_ios_new_rounded,
                          size: 16, color: Color(0xFF1A1A2E)),
                    ),
                  ),
                  const SizedBox(width: 16),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Catatan Saya',
                          style: TextStyle(
                              fontSize: 20,
                              fontWeight: FontWeight.bold,
                              color: Color(0xFF1A1A2E))),
                      Text('${_notes.length} catatan tersimpan',
                          style: TextStyle(
                              fontSize: 12, color: Colors.grey[500])),
                    ],
                  ),
                ],
              ),
            ),

            // List
            Expanded(
              child: _loading
                  ? const Center(
                      child: CircularProgressIndicator(color: _primary))
                  : _notes.isEmpty
                      ? _buildEmpty()
                      : RefreshIndicator(
                          color: _primary,
                          onRefresh: _loadNotes,
                          child: ListView.builder(
                            padding: const EdgeInsets.fromLTRB(20, 4, 20, 40),
                            itemCount: _notes.length,
                            itemBuilder: (ctx, i) =>
                                _buildNoteCard(_notes[i]),
                          ),
                        ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmpty() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            width: 80, height: 80,
            decoration: BoxDecoration(
              color: _noteAccent.withOpacity(0.1),
              shape: BoxShape.circle,
            ),
            child: const Icon(Icons.sticky_note_2_outlined,
                size: 40, color: _noteAccent),
          ),
          const SizedBox(height: 16),
          const Text('Belum ada catatan',
              style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF1A1A2E))),
          const SizedBox(height: 6),
          Text('Buat catatan dari tombol Synapse\ndi halaman utama',
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 13, color: Colors.grey[500])),
        ],
      ),
    );
  }

  Widget _buildNoteCard(NoteModel note) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: _noteColor,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFFFE082)),
        boxShadow: [
          BoxShadow(
            color: _noteAccent.withOpacity(0.08),
            blurRadius: 8,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(16),
          onTap: () => _showEditSheet(note),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(note.content,
                    style: const TextStyle(
                        fontSize: 14,
                        color: Color(0xFF1A1A2E),
                        height: 1.6)),
                const SizedBox(height: 10),
                Row(
                  children: [
                    Icon(Icons.access_time_rounded,
                        size: 12, color: Colors.grey[400]),
                    const SizedBox(width: 4),
                    Text(_formatDate(note.updatedAt),
                        style: TextStyle(
                            fontSize: 11, color: Colors.grey[400])),
                    const Spacer(),
                    GestureDetector(
                      onTap: () => _showEditSheet(note),
                      child: Padding(
                        padding: const EdgeInsets.all(4),
                        child: Icon(Icons.edit_rounded,
                            size: 16, color: Colors.grey[400]),
                      ),
                    ),
                    const SizedBox(width: 4),
                    GestureDetector(
                      onTap: () => _confirmDelete(note),
                      child: Padding(
                        padding: const EdgeInsets.all(4),
                        child: Icon(Icons.delete_outline_rounded,
                            size: 16, color: Colors.red[300]),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  void _showEditSheet(NoteModel note) {
    final ctrl = TextEditingController(text: note.content);
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => Padding(
        padding: EdgeInsets.only(
            bottom: MediaQuery.of(context).viewInsets.bottom),
        child: Container(
          padding: const EdgeInsets.all(20),
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('Edit Catatan',
                  style: TextStyle(
                      fontSize: 16, fontWeight: FontWeight.bold)),
              const SizedBox(height: 12),
              TextField(
                controller: ctrl,
                maxLines: 5,
                autofocus: true,
                decoration: InputDecoration(
                  border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12)),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide:
                        const BorderSide(color: _primary, width: 1.5),
                  ),
                ),
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton(
                      onPressed: () => Navigator.pop(context),
                      style: OutlinedButton.styleFrom(
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(10))),
                      child: const Text('Batal'),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: ElevatedButton(
                      onPressed: () async {
                        final text = ctrl.text.trim();
                        if (text.isEmpty) return;
                        await _svc.updateNote(note.id, text);
                        if (context.mounted) Navigator.pop(context);
                        await _loadNotes();
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: _primary,
                        foregroundColor: Colors.white,
                        elevation: 0,
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(10)),
                      ),
                      child: const Text('Simpan',
                          style: TextStyle(fontWeight: FontWeight.bold)),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
            ],
          ),
        ),
      ),
    );
  }

  void _confirmDelete(NoteModel note) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16)),
        title: const Text('Hapus catatan?',
            style: TextStyle(fontWeight: FontWeight.bold)),
        content: const Text('Catatan ini akan dihapus permanen.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _deleteNote(note.id);
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red,
              foregroundColor: Colors.white,
              elevation: 0,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8)),
            ),
            child: const Text('Hapus'),
          ),
        ],
      ),
    );
  }
}