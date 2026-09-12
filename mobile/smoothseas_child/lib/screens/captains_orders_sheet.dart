import 'package:flutter/material.dart';

import '../api.dart';
import '../theme.dart';

/// Captain's Orders — the full parchment sheet with Orders / Locker / Logs / Journal tabs.
Future<void> showCaptainsOrders(BuildContext context) {
  return showModalBottomSheet(
    context: context,
    backgroundColor: const Color(0xFFF3E7C8),
    showDragHandle: true,
    isScrollControlled: true,
    shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(22))),
    builder: (_) => const _CaptainsOrders(),
  );
}

const _brown = Color(0xFF5B4420);
const _brownMuted = Color(0xFF8A6E3E);
const _sand = Color(0xFFE9D6AC);

class _CaptainsOrders extends StatefulWidget {
  const _CaptainsOrders();
  @override
  State<_CaptainsOrders> createState() => _CaptainsOrdersState();
}

class _CaptainsOrdersState extends State<_CaptainsOrders> {
  late Future<Map<String, dynamic>> _future;

  @override
  void initState() {
    super.initState();
    _future = api.getJson('/child/captains-orders').then((d) => d as Map<String, dynamic>);
  }

  @override
  Widget build(BuildContext context) {
    return FractionallySizedBox(
      heightFactor: 0.8,
      child: FutureBuilder<Map<String, dynamic>>(
        future: _future,
        builder: (context, snap) {
          if (snap.connectionState == ConnectionState.waiting) {
            return const Padding(padding: EdgeInsets.all(40), child: Center(child: CircularProgressIndicator(color: _brown)));
          }
          if (snap.hasError) {
            return Padding(padding: const EdgeInsets.all(24), child: Text(snap.error.toString(), style: const TextStyle(color: _brown)));
          }
          final d = snap.data!;
          return DefaultTabController(
            length: 4,
            child: Column(
              children: [
                Padding(
                  padding: const EdgeInsets.fromLTRB(20, 0, 20, 4),
                  child: Row(children: [
                    const Text('🐢', style: TextStyle(fontSize: 26)),
                    const SizedBox(width: 10),
                    Text("Captain’s Orders", style: head(20, color: _brown)),
                  ]),
                ),
                const TabBar(
                  labelColor: _brown,
                  unselectedLabelColor: _brownMuted,
                  indicatorColor: _brown,
                  tabs: [Tab(text: 'Orders'), Tab(text: 'Locker'), Tab(text: 'Logs'), Tab(text: 'Journal')],
                ),
                Expanded(
                  child: TabBarView(children: [
                    _orders(d['orders'] as Map<String, dynamic>),
                    _locker(d['locker'] as List<dynamic>),
                    _logs(d['logs'] as List<dynamic>, d['streak'] as Map<String, dynamic>? ?? {}),
                    _journal(d['journal'] as List<dynamic>),
                  ]),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _pad(List<Widget> children) => ListView(padding: const EdgeInsets.all(20), children: children);

  Widget _orders(Map<String, dynamic> o) {
    final rest = o['rest'] == true;
    final duties = (o['duties'] as List<dynamic>);
    final tasks = (o['lesson_tasks'] as List<dynamic>? ?? []);
    return _pad([
      Text('${o['title']}', style: head(18, color: _brown)),
      const SizedBox(height: 8),
      if (rest) ...[
        const Center(child: Icon(Icons.anchor, size: 44, color: Color(0xFF7FB3C4))),
        const SizedBox(height: 12),
        Text('${o['message']}', textAlign: TextAlign.center, style: const TextStyle(color: _brown, fontSize: 16)),
      ] else ...[
        Text('${o['message']}', style: const TextStyle(color: _brown, fontSize: 14)),
        const SizedBox(height: 10),
        for (final duty in duties) _check('${(duty as Map)['label']}', duty['done'] == true),
        if (tasks.isNotEmpty) ...[
          const SizedBox(height: 12),
          Text('🧭 Today’s lessons', style: head(14, color: _brown)),
          const SizedBox(height: 4),
          for (final t in tasks) _check('${(t as Map)['subject']}: ${t['topic']}', t['done'] == true),
        ],
      ],
    ]);
  }

  Widget _locker(List<dynamic> rewards) => _pad([
        for (final r in rewards) _reward(r as Map<String, dynamic>),
      ]);

  Widget _logs(List<dynamic> logs, Map<String, dynamic> streak) => _pad([
        Center(child: Text('🔥 ${streak['label'] ?? ''}', style: head(16, color: _brown))),
        const SizedBox(height: 12),
        if (logs.isEmpty)
          const Text('Your streaks will grow as you sail each day.', style: TextStyle(color: _brownMuted))
        else
          for (final l in logs)
            Container(
              margin: const EdgeInsets.only(bottom: 6),
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
              decoration: BoxDecoration(color: _sand, borderRadius: BorderRadius.circular(8)),
              child: Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
                Text('${(l as Map)['label']}', style: const TextStyle(color: _brown, fontWeight: FontWeight.w700)),
                Text('🔥 ${l['count']}', style: const TextStyle(color: _brown, fontWeight: FontWeight.w800)),
              ]),
            ),
      ]);

  Widget _journal(List<dynamic> entries) => _pad([
        Text('Your conquests', style: head(16, color: _brown)),
        const SizedBox(height: 10),
        if (entries.isEmpty)
          const Text('Master your first topic and it’ll be logged here, Captain. ⚓', style: TextStyle(color: _brownMuted))
        else
          for (final e in entries)
            Padding(
              padding: const EdgeInsets.only(bottom: 8),
              child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
                const Text('✓ ', style: TextStyle(color: Color(0xFF2E7D32), fontWeight: FontWeight.w900)),
                Expanded(child: Text('${(e as Map)['topic']}  ·  ${e['subject']}', style: const TextStyle(color: _brown))),
              ]),
            ),
      ]);

  Widget _check(String label, bool done) => Padding(
        padding: const EdgeInsets.symmetric(vertical: 6),
        child: Row(children: [
          Icon(done ? Icons.check_circle : Icons.radio_button_unchecked, color: done ? const Color(0xFF2E7D32) : _brownMuted),
          const SizedBox(width: 10),
          Expanded(child: Text(label, style: TextStyle(color: _brown, fontSize: 15, decoration: done ? TextDecoration.lineThrough : null))),
        ]),
      );

  Widget _reward(Map<String, dynamic> r) {
    final held = r['held'] as int? ?? 0;
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(color: _sand, borderRadius: BorderRadius.circular(12)),
      child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text('${r['icon']}', style: const TextStyle(fontSize: 26)),
        const SizedBox(width: 12),
        Expanded(
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text('${r['label']}  ×$held', style: const TextStyle(color: _brown, fontWeight: FontWeight.w800)),
            Text('${r['blurb']}', style: const TextStyle(color: _brown, fontSize: 13)),
            if (held == 0) Padding(padding: const EdgeInsets.only(top: 3), child: Text('How to earn: ${r['earn']}', style: const TextStyle(color: _brownMuted, fontSize: 12))),
          ]),
        ),
      ]),
    );
  }
}
