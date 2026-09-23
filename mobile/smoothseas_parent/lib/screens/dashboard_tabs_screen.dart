import 'package:flutter/material.dart';

import '../api.dart';

/// The dashboard detail tabs — Pace, Estimator and Rewards for one child. Reads the
/// honest-layer signals from the mobile API (never recomputed on the client) and
/// presents them calmly: journey-vs-plan, readiness bands over covered material, and
/// the streak-economy rewards the child has earned.
class DashboardTabsScreen extends StatefulWidget {
  const DashboardTabsScreen({super.key, required this.childId, required this.childName});
  final int childId;
  final String childName;

  @override
  State<DashboardTabsScreen> createState() => _DashboardTabsScreenState();
}

class _DashboardTabsScreenState extends State<DashboardTabsScreen> {
  late Future<Map<String, dynamic>> _future;

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<Map<String, dynamic>> _load() async =>
      await api.getJson('/children/${widget.childId}/dashboard') as Map<String, dynamic>;

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 3,
      child: Scaffold(
        appBar: AppBar(
          title: Text('${widget.childName} — progress'),
          bottom: const TabBar(tabs: [Tab(text: 'Pace'), Tab(text: 'Estimator'), Tab(text: 'Rewards')]),
        ),
        body: FutureBuilder<Map<String, dynamic>>(
          future: _future,
          builder: (context, snap) {
            if (snap.connectionState == ConnectionState.waiting) {
              return const Center(child: CircularProgressIndicator());
            }
            if (snap.hasError) {
              return Center(child: Padding(padding: const EdgeInsets.all(20), child: Text(snap.error.toString(), textAlign: TextAlign.center)));
            }
            final d = snap.data!;
            return TabBarView(children: [
              _paceTab(d['pace'] as Map<String, dynamic>? ?? {}),
              _estimatorTab(d['estimator'] as Map<String, dynamic>? ?? {}),
              _rewardsTab(((d['rewards'] as List<dynamic>?) ?? []).cast<Map<String, dynamic>>()),
            ]);
          },
        ),
      ),
    );
  }

  Widget _paceTab(Map<String, dynamic> pace) {
    final subjects = ((pace['subjects'] as List<dynamic>?) ?? []).cast<Map<String, dynamic>>();
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        _statRow('Study week', '${pace['current_week'] ?? '—'}'),
        _statRow('Weeks to exam', '${pace['weeks_to_exam'] ?? '—'}'),
        _statRow('Exam date', '${pace['exam_date'] ?? '—'}'),
        _statRow('Modules behind', '${pace['total_behind'] ?? 0}'),
        const SizedBox(height: 12),
        if (pace['recommendation'] != null)
          Card(child: Padding(padding: const EdgeInsets.all(14), child: Text('${pace['recommendation']}', style: const TextStyle(fontSize: 15)))),
        const SizedBox(height: 12),
        for (final s in subjects)
          Card(
            child: ListTile(
              title: Text('${s['subject']}', style: const TextStyle(fontWeight: FontWeight.w700)),
              subtitle: Text('${s['completed']} of ${s['expected']} expected · ${s['behind_count']} behind'),
              trailing: _statusChip('${s['status']}'),
            ),
          ),
      ],
    );
  }

  Widget _estimatorTab(Map<String, dynamic> est) {
    if (est['has_data'] != true) {
      return const Center(child: Padding(padding: EdgeInsets.all(24), child: Text('Not enough practice yet to estimate readiness. It sharpens as more is practised.', textAlign: TextAlign.center)));
    }
    final subjects = ((est['subjects'] as List<dynamic>?) ?? []).cast<Map<String, dynamic>>();
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        if (est['composite'] != null) _statRow('Overall', '${est['composite']}%'),
        _statRow('Confidence', '${est['confidence'] ?? '—'}'),
        const SizedBox(height: 12),
        for (final s in subjects)
          Card(
            child: ListTile(
              title: Text('${s['label'] ?? s['subject']}', style: const TextStyle(fontWeight: FontWeight.w700)),
              subtitle: Text('${s['attempts']} answers · ${s['mastery_pct'] ?? 0}% mastery'),
              trailing: Text(s['accuracy'] != null ? '${s['accuracy']}%' : '—', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800)),
            ),
          ),
        if (est['covered_note'] != null)
          Padding(padding: const EdgeInsets.only(top: 8), child: Text('${est['covered_note']}', style: const TextStyle(fontSize: 12.5, color: Colors.black45))),
      ],
    );
  }

  Widget _rewardsTab(List<Map<String, dynamic>> rewards) {
    const meta = {
      'shore_leave': ['🏝️', 'Shore Leave'],
      'anchor': ['⚓', 'Anchor'],
      'tailwind': ['🌬️', 'Tailwind'],
      'lifebuoy': ['🛟', 'Lifebuoy'],
    };
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        for (final r in rewards)
          Builder(builder: (context) {
            final m = meta[r['type']] ?? ['🎖️', '${r['type']}'];
            return Card(
              child: ListTile(
                leading: Text(m[0], style: const TextStyle(fontSize: 26)),
                title: Text(m[1], style: const TextStyle(fontWeight: FontWeight.w700)),
                trailing: Text('×${r['held'] ?? 0}', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800)),
              ),
            );
          }),
      ],
    );
  }

  Widget _statRow(String label, String value) => Padding(
        padding: const EdgeInsets.symmetric(vertical: 6),
        child: Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
          Text(label, style: const TextStyle(color: Colors.black54, fontWeight: FontWeight.w600)),
          Text(value, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 16)),
        ]),
      );

  Widget _statusChip(String status) {
    final color = switch (status) {
      'behind' || 'at_risk' || 'needs_attention' => Colors.orange,
      'ahead' || 'on_track' => Colors.green,
      _ => Colors.blueGrey,
    };
    return Chip(label: Text(status.replaceAll('_', ' ')), backgroundColor: color.withValues(alpha: 0.15), labelStyle: TextStyle(color: color, fontWeight: FontWeight.w700, fontSize: 12));
  }
}
