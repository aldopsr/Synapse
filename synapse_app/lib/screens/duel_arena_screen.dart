// duel_arena_screen.dart
import 'package:flutter/material.dart';

class DuelArenaScreen extends StatefulWidget {
  const DuelArenaScreen({super.key});

  @override
  State<DuelArenaScreen> createState() => _DuelArenaScreenState();
}

class _DuelArenaScreenState extends State<DuelArenaScreen> {
  // ===========================================================================
  // DATA DUMMY (Akan diganti API Laravel di tahap backend)
  // ===========================================================================
  final String _pangkat = "Komandan Sektor";
  final int _combatPoints = 2850;
  final String _winRate = "78%";
  final int _totalDuel = 54;

  // List simulasi pertempuran aktif (Style Asinkron)
  final List<Map<String, dynamic>> _activeDuels = [
    {
      "id": "1",
      "lawan": "Kapten Rex",
      "matkul": "Anatomi Jaringan Saraf",
      "status_teks": "GILIRAN ANDA",
      "status_tipe": "active", // active, wait, win, lose
    },
    {
      "id": "2",
      "lawan": "Kapten Marvela",
      "matkul": "Sistem Operasi Cloud",
      "status_teks": "MENUNGGU LAWAN",
      "status_tipe": "wait",
    },
    {
      "id": "3",
      "lawan": "Al-Khawarizmi Bot",
      "matkul": "Algoritma & Dekripsi",
      "status_teks": "ANDA MENANG",
      "status_tipe": "win",
    },
  ];

  // ===========================================================================
  // THEME KONSISTEN SYNAPSE
  // ===========================================================================
  static const Color _primaryColor = Color(0xFF2A9D8F); // Teal
  static const Color _softBg       = Color(0xFFF0FDFB); // Light Teal Bg
  static const Color _textColor    = Color(0xFF1A1A2E); // Navi Dark

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[100],
      // APPBAR CLEAN STYLE (SESUAI MATERIALS SCREEN)
      appBar: AppBar(
        title: const Text('ARENA SYNAPSE', 
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18, letterSpacing: 1)
        ),
        centerTitle: true,
        backgroundColor: Colors.grey[100],
        foregroundColor: _textColor,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.leaderboard_rounded, color: Colors.amber, size: 22),
            onPressed: () {
              // Rencana: Halaman Peringkat Global Duel
            },
          )
        ],
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          physics: const BouncingScrollPhysics(),
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // HEADER SALAM (BRANDING KAPTEN)
              RichText(
                text: const TextSpan(
                  style: TextStyle(fontSize: 24, color: _textColor, fontWeight: FontWeight.bold),
                  children: [
                    TextSpan(text: 'Pusat Komando\n'),
                    TextSpan(text: 'Pertempuran Taktis', style: TextStyle(color: _primaryColor)),
                  ],
                ),
              ),
              const SizedBox(height: 25),

              // 1. KARTU STATUS KAPTEN (CLEAN MODULAR STYLE)
              _buildPlayerStatusCard(),

              const SizedBox(height: 30),

              // SECTION HEADER: MODE TEMPUR
              _buildSectionHeader('Mode Simulasi Tempur', Icons.bolt_rounded),
              const SizedBox(height: 12),
              
              // TOMBOL MATCHMAKING (CLEAN MODULAR STYLE)
              Row(
                children: [
                  Expanded(
                    child: _buildMatchButton(
                      title: "CARI LAWAN",
                      subtitle: "Auto Matchmaking",
                      icon: Icons.psychology_rounded,
                      color: _primaryColor,
                      onTap: () => _showMatchmakingDialog(context),
                    ),
                  ),
                  const SizedBox(width: 15),
                  Expanded(
                    child: _buildMatchButton(
                      title: "UNDANG TEMAN",
                      subtitle: "By 1 via ID",
                      icon: Icons.groups_rounded,
                      color: Colors.indigo[400]!,
                      onTap: () {
                        // Logika undang teman
                      },
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 30),

              // SECTION HEADER: LOG PERTEMPURAN
              _buildSectionHeader('Log Sektor Aktif', Icons.history_edu_rounded),
              const SizedBox(height: 12),

              // DAFTAR PERTEMPURAN (LIST CLEAN STYLE - MIRIP KUIS LIST)
              _activeDuels.isEmpty
                  ? _buildEmptyLog()
                  : ListView.builder(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      itemCount: _activeDuels.length,
                      itemBuilder: (context, index) {
                        return _buildDuelLogItem(_activeDuels[index]);
                      },
                    ),
              
              // Spacer bawah agar tidak mentok navbar
              const SizedBox(height: 100),
            ],
          ),
        ),
      ),
    );
  }

  // ===========================================================================
  // WIDGET BUILDERS (CLEAN & MINIMALIST STYLE)
  // ===========================================================================

  Widget _buildSectionHeader(String title, IconData icon) {
    return Row(
      children: [
        Container(
          padding: const EdgeInsets.all(6),
          decoration: BoxDecoration(color: _softBg, borderRadius: BorderRadius.circular(8)),
          child: Icon(icon, color: _primaryColor, size: 18),
        ),
        const SizedBox(width: 10),
        Text(
          title.toUpperCase(),
          style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.grey[600], letterSpacing: 1),
        ),
      ],
    );
  }

  // KARTU STATUS KAPTEN (CLEAN & MODULAR)
  Widget _buildPlayerStatusCard() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(color: Colors.black.withOpacity(0.03), blurRadius: 15, offset: const Offset(0, 5))
        ],
      ),
      child: Column(
        children: [
          // Row Atas: Avatar + Info Utama
          Row(
            children: [
              Container(
                width: 60, height: 60,
                decoration: BoxDecoration(
                  color: _softBg,
                  shape: BoxShape.circle,
                  border: Border.all(color: _primaryColor.withOpacity(0.2), width: 2),
                ),
                child: const Center(child: Icon(Icons.shield_rounded, color: _primaryColor, size: 30)),
              ),
              const SizedBox(width: 15),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(_pangkat.toUpperCase(), style: const TextStyle(color: _primaryColor, fontSize: 11, fontWeight: FontWeight.bold, letterSpacing: 1)),
                    const SizedBox(height: 4),
                    const Text('Status Tempur Anda', style: TextStyle(color: Colors.grey, fontSize: 12)),
                  ],
                ),
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text('$_combatPoints', style: const TextStyle(color: Colors.amber, fontSize: 24, fontWeight: FontWeight.bold)),
                  const Text('Combat Points', style: TextStyle(color: Colors.grey, fontSize: 10)),
                ],
              )
            ],
          ),
          const SizedBox(height: 20),
          const Divider(color: Colors.black12, height: 1),
          const SizedBox(height: 20),
          // Row Bawah: Statistik
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [
              _buildStatItem("Win Rate", _winRate),
              _buildStatItem("Total Duel", "$_totalDuel Match"),
            ],
          )
        ],
      ),
    );
  }

  Widget _buildStatItem(String label, String value) {
    return Column(
      children: [
        Text(value, style: const TextStyle(color: _textColor, fontSize: 16, fontWeight: FontWeight.bold)),
        const SizedBox(height: 4),
        Text(label, style: TextStyle(color: Colors.grey[500], fontSize: 11)),
      ],
    );
  }

  // TOMBOL MODE TEMPUR (CLEAN MODULAR)
  Widget _buildMatchButton({
    required String title,
    required String subtitle,
    required IconData icon,
    required Color color,
    required VoidCallback onTap,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.02), blurRadius: 10, offset: const Offset(0, 3))],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(18),
          child: Padding(
            padding: const EdgeInsets.all(18.0),
            child: Column(
              children: [
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(color: color.withOpacity(0.1), shape: BoxShape.circle),
                  child: Icon(icon, color: color, size: 26),
                ),
                const SizedBox(height: 16),
                Text(title, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: _textColor)),
                const SizedBox(height: 4),
                Text(subtitle, style: TextStyle(fontSize: 10, color: Colors.grey[500])),
              ],
            ),
          ),
        ),
      ),
    );
  }

  // ITEM LOG PERTEMPURAN (CLEAN LIST STYLE - SESUAI KUIS LIST)
  Widget _buildDuelLogItem(Map<String, dynamic> duel) {
    IconData statusIcon;
    Color statusColor;
    bool canPlay = false;

    switch (duel["status_tipe"]) {
      case "active": statusIcon = Icons.play_circle_fill_rounded; statusColor = _primaryColor; canPlay = true; break;
      case "wait":   statusIcon = Icons.hourglass_top_rounded;     statusColor = Colors.orange; break;
      case "win":    statusIcon = Icons.check_circle_rounded;       statusColor = Colors.green; break;
      case "lose":   statusIcon = Icons.cancel_rounded;             statusColor = Colors.redAccent; break;
      default:       statusIcon = Icons.help_center_rounded;        statusColor = Colors.grey;
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.02), blurRadius: 8, offset: const Offset(0, 2))],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(16),
          onTap: canPlay ? () { /* Logika masuk kuis duel */ } : null,
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Row(
              children: [
                // Icon Status di Kiri (Soft Bg Style)
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(color: statusColor.withOpacity(0.1), borderRadius: BorderRadius.circular(12)),
                  child: Icon(statusIcon, color: statusColor, size: 22),
                ),
                const SizedBox(width: 16),
                // Info Teks
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Lawan: ${duel["lawan"]}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: _textColor)),
                      const SizedBox(height: 4),
                      Text(duel["matkul"], style: TextStyle(fontSize: 11, color: Colors.grey[600])),
                    ],
                  ),
                ),
                // Teks Status / Panah di Kanan
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(
                      duel["status_teks"], 
                      style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: statusColor, letterSpacing: 0.5)
                    ),
                    if(canPlay) const SizedBox(height: 4),
                    if(canPlay) const Icon(Icons.arrow_forward_ios_rounded, size: 12, color: _primaryColor),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildEmptyLog() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16)),
      child: Column(
        children: [
          Icon(Icons.history_toggle_off_rounded, size: 40, color: Colors.grey[300]),
          const SizedBox(height: 12),
          Text('Belum ada riwayat pertempuran.', style: TextStyle(color: Colors.grey[500], fontSize: 12)),
        ],
      ),
    );
  }

  // SIMULASI MATCHMAKING DIALOG (CLEAN STYLE)
  void _showMatchmakingDialog(BuildContext context) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) {
        return AlertDialog(
          backgroundColor: Colors.white,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          content: Padding(
            padding: const EdgeInsets.symmetric(vertical: 10),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const SizedBox(
                  width: 50, height: 50,
                  child: CircularProgressIndicator(color: _primaryColor, strokeWidth: 3),
                ),
                const SizedBox(height: 25),
                const Text("MENCARI KAPTEN LAWAN...", style: TextStyle(color: _textColor, fontWeight: FontWeight.bold, fontSize: 14, letterSpacing: 1)),
                const SizedBox(height: 8),
                Text("Menghubungkan ke Synapse Core...", style: TextStyle(color: Colors.grey[600], fontSize: 12)),
                const SizedBox(height: 30),
                OutlinedButton(
                  onPressed: () => Navigator.pop(context),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: Colors.redAccent,
                    side: const BorderSide(color: Colors.redAccent),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                  child: const Text("BATALKAN MISI", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                )
              ],
            ),
          ),
        );
      },
    );
  }
}