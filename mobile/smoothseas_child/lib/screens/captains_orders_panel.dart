import 'package:flutter/material.dart';

import '../api.dart';
import '../theme.dart';

/// Captain's Orders — the parchment brief, mirrors livewire/captains-orders.blade.php.
/// On the Voyage it sits open as a bottom sheet by default (CO-12), collapsible to a rail.
class CaptainsOrdersPanel extends StatefulWidget {
  const CaptainsOrdersPanel({super.key});
  @override
  State<CaptainsOrdersPanel> createState() => _CaptainsOrdersPanelState();
}

// Parchment palette lifted verbatim from the blade.
class _P {
  static const parch1 = Color(0xFFF4E8C8);
  static const parch2 = Color(0xFFECDCB0);
  static const parch3 = Color(0xFFE3CF9C);
  static const wood = Color(0xFF5A3D21);
  static const ropeA = Color(0xFFB98A4B);
  static const ropeB = Color(0xFF8A6531);
  static const ink = Color(0xFF3D2B16);
  static const titleInk = Color(0xFF4A3119);
  static const subInk = Color(0xFF8A6531);
  static const crestA = Color(0xFFFBE9C0);
  static const crestB = Color(0xFFC9791F);
  static const crestBorder = Color(0xFF7A4A1A);
  static const tabIdle = Color(0xFF7A5A2E);
  static const tabIdleBg = Color(0x145A3D21);
  static const tabActiveA = Color(0xFF6B4A2B);
  static const doneGreen = Color(0xFF2F7D5B);
  static const doneInk = Color(0xFF245C43);
  static const soon = Color(0xFF9E3B23);
  static const gateBg = Color(0x29C9791F);
}

class _CaptainsOrdersPanelState extends State<CaptainsOrdersPanel> {
  late Future<Map<String, dynamic>> _future;
  int _tab = 0; // 0 orders, 1 locker, 2 journal, 3 logs
  bool _collapsed = false;

  @override
  void initState() {
    super.initState();
    _future = api.getJson('/child/captains-orders').then((d) => d as Map<String, dynamic>);
  }

  @override
  Widget build(BuildContext context) {
    if (_collapsed) {
      return Align(
        alignment: Alignment.bottomLeft,
        child: GestureDetector(
          onTap: () => setState(() => _collapsed = false),
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
            decoration: const BoxDecoration(
              gradient: LinearGradient(colors: [Color(0xFF6B4A2B), Color(0xFF4A3119)], begin: Alignment.topCenter, end: Alignment.bottomCenter),
              border: Border(top: BorderSide(color: Color(0xFF3A2712), width: 2), right: BorderSide(color: Color(0xFF3A2712), width: 2)),
              borderRadius: BorderRadius.only(topLeft: Radius.circular(14), topRight: Radius.circular(14)),
            ),
            child: Row(mainAxisSize: MainAxisSize.min, children: [
              const Text('📜', style: TextStyle(fontSize: 20)),
              const SizedBox(width: 8),
              Text("Captain's Orders", style: head(15, color: const Color(0xFFF0E0BD))),
              const SizedBox(width: 8),
              const Text('▸', style: TextStyle(color: Color(0xFFF6B71E), fontWeight: FontWeight.w900)),
            ]),
          ),
        ),
      );
    }
    return DecoratedBox(
      decoration: const BoxDecoration(
        gradient: LinearGradient(begin: Alignment(0.36, -0.93), end: Alignment(-0.36, 0.93), colors: [_P.parch1, _P.parch2, _P.parch3], stops: [0.0, 0.55, 1.0]), // web 160deg
        border: Border(top: BorderSide(color: _P.wood, width: 6), left: BorderSide(color: _P.wood, width: 6), right: BorderSide(color: _P.wood, width: 6)),
        borderRadius: BorderRadius.only(topLeft: Radius.circular(14), topRight: Radius.circular(14)),
        boxShadow: [BoxShadow(color: Color(0x73000000), blurRadius: 18, offset: Offset(0, -4))],
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Rope band (::before).
          Container(height: 5, decoration: const BoxDecoration(gradient: LinearGradient(colors: [_P.ropeA, _P.ropeB]))),
          Flexible(
            child: FutureBuilder<Map<String, dynamic>>(
              future: _future,
              builder: (context, snap) {
                if (snap.connectionState == ConnectionState.waiting) {
                  return const Padding(padding: EdgeInsets.all(30), child: Center(child: CircularProgressIndicator(color: _P.wood)));
                }
                if (snap.hasError) {
                  return Padding(padding: const EdgeInsets.all(20), child: Text(snap.error.toString(), style: const TextStyle(color: _P.ink)));
                }
                final d = snap.data!;
                final evening = d['is_evening'] == true;
                return Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    _header(evening),
                    _tabs(),
                    Flexible(child: _tabBody(d, evening)),
                  ],
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _header(bool evening) => Padding(
        padding: const EdgeInsets.fromLTRB(12, 10, 12, 4),
        child: Row(children: [
          Container(
            width: 40, height: 40,
            alignment: Alignment.center,
            decoration: BoxDecoration(
              gradient: const RadialGradient(center: Alignment(-0.3, -0.4), colors: [_P.crestA, _P.crestB]),
              shape: BoxShape.circle,
              border: Border.all(color: _P.crestBorder, width: 2),
            ),
            child: const Text('🐢', style: TextStyle(fontSize: 20)),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text("Captain's Orders", style: head(18, color: _P.titleInk, height: 1.0)),
              Text(evening ? 'EVENING WATCH' : 'MORNING MUSTER', style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800, color: _P.subInk, letterSpacing: 1)),
            ]),
          ),
          GestureDetector(
            onTap: () => setState(() => _collapsed = true),
            child: Container(
              width: 22, height: 44, alignment: Alignment.center,
              decoration: BoxDecoration(color: const Color(0xFF6B4A2B), borderRadius: BorderRadius.circular(8)),
              child: const Text('▶', style: TextStyle(color: Color(0xFFF6B71E), fontSize: 12)),
            ),
          ),
        ]),
      );

  Widget _tabs() {
    const labels = ['Orders', 'Locker', 'Journal', 'Logs'];
    return Padding(
      padding: const EdgeInsets.fromLTRB(10, 0, 10, 6),
      child: Row(children: [
        for (var i = 0; i < 4; i++) ...[
          if (i > 0) const SizedBox(width: 3),
          Expanded(
            child: GestureDetector(
              onTap: () => setState(() => _tab = i),
              child: Container(
                padding: const EdgeInsets.symmetric(vertical: 6),
                alignment: Alignment.center,
                decoration: BoxDecoration(
                  gradient: _tab == i ? const LinearGradient(begin: Alignment.topLeft, end: Alignment.bottomRight, colors: [_P.tabActiveA, _P.ropeB]) : null,
                  color: _tab == i ? null : _P.tabIdleBg,
                  border: Border.all(color: _P.ropeA, width: 1.5),
                  borderRadius: BorderRadius.circular(7),
                ),
                child: Text(labels[i], style: TextStyle(fontFamily: 'FredokaOne', fontSize: 11.5, color: _tab == i ? _P.parch1 : _P.tabIdle)),
              ),
            ),
          ),
        ],
      ]),
    );
  }

  Widget _tabBody(Map<String, dynamic> d, bool evening) {
    switch (_tab) {
      case 1:
        return _locker((d['locker'] as List<dynamic>?) ?? []);
      case 2:
        return _journal((d['journal'] as List<dynamic>?) ?? [], d['streak'] as Map<String, dynamic>? ?? {});
      case 3:
        return _logs((d['logs'] as List<dynamic>?) ?? []);
      default:
        return _orders(d['orders'] as Map<String, dynamic>? ?? {}, evening);
    }
  }

  Widget _pad(List<Widget> c) => ListView(padding: const EdgeInsets.fromLTRB(14, 0, 14, 14), shrinkWrap: true, children: c);

  Widget _orders(Map<String, dynamic> o, bool evening) {
    final rest = o['rest'] == true;
    final duties = (o['duties'] as List<dynamic>?) ?? [];
    final tasks = (o['lesson_tasks'] as List<dynamic>?) ?? [];
    final writingDay = o['is_writing_day'] == true;
    final writingDone = duties.any((x) => (x as Map)['key'] == 'writing' && x['done'] == true);
    final allDone = duties.isNotEmpty && duties.every((x) => (x as Map)['done'] == true);
    if (rest) {
      return _pad([
        const SizedBox(height: 10),
        const Center(child: Text('⚓', style: TextStyle(fontSize: 35))),
        const SizedBox(height: 8),
        Text('${o['message']}', textAlign: TextAlign.center, style: const TextStyle(color: _P.ink, fontSize: 14.5, fontWeight: FontWeight.w700)),
      ]);
    }
    return _pad([
      Text(evening ? "Evening watch — here's what's still on your orders." : "Today's orders, Captain. Clear them to keep the Voyage sailing.",
          style: const TextStyle(color: _P.ink, fontSize: 13.76, fontWeight: FontWeight.w700)),
      const SizedBox(height: 6),
      for (final duty in duties) _duty(duty as Map<String, dynamic>),
      if (tasks.isNotEmpty) ...[
        const SizedBox(height: 14),
        Text('🧭 Today’s lessons — finish these to stay on course', style: head(13, color: const Color(0xFF7A4A1A))),
        const SizedBox(height: 7),
        for (final t in tasks) _lessonTask(t as Map<String, dynamic>),
      ],
      if (writingDay && !writingDone) ...[
        const SizedBox(height: 12),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 9),
          decoration: const BoxDecoration(
            color: _P.gateBg,
            border: Border(left: BorderSide(color: _P.crestB, width: 4)),
            borderRadius: BorderRadius.only(topRight: Radius.circular(6), bottomRight: Radius.circular(6)),
          ),
          child: const Text('✍️ Finish today’s writing to open the next island on the map.',
              style: TextStyle(color: Color(0xFF7A4A1A), fontSize: 13, fontWeight: FontWeight.w700)),
        ),
      ],
      if (allDone) ...[
        const SizedBox(height: 12),
        Center(child: Text('All orders cleared — a fine day’s sailing! 🌊', style: head(14, color: _P.doneGreen))),
      ] else if (evening) ...[
        const SizedBox(height: 12),
        GestureDetector(
          onTap: () => setState(() => _tab = 3),
          child: Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(vertical: 7),
            alignment: Alignment.center,
            decoration: BoxDecoration(border: Border.all(color: _P.ropeA, width: 1.5), borderRadius: BorderRadius.circular(8)),
            child: const Text('Look back on today ›', style: TextStyle(color: _P.tabIdle, fontWeight: FontWeight.w800, fontSize: 13)),
          ),
        ),
      ],
    ]);
  }

  // Duty labels + trailing actions mirror the web captains-orders Livewire component.
  static const _webLabel = {
    'morning_tide': 'Morning Tide', 'map': 'Sail the map', 'writing': "Ship's writing",
    'practice': 'Practice a topic', 'reading': 'Morning reading', 'vocabulary': 'Daily vocabulary',
  };

  Widget _duty(Map<String, dynamic> duty) {
    final done = duty['done'] == true;
    final key = duty['key'] as String? ?? '';
    final label = _webLabel[key] ?? '${duty['label']}';
    return Container(
      margin: const EdgeInsets.only(bottom: 7),
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
      decoration: BoxDecoration(
        color: done ? const Color(0x292F7D5B) : const Color(0x66FFFFFF),
        border: Border.all(color: done ? _P.doneGreen : _P.ropeA, width: 1.5),
        borderRadius: BorderRadius.circular(9),
      ),
      child: Row(children: [
        Container(
          width: 20, height: 20, alignment: Alignment.center,
          decoration: BoxDecoration(shape: BoxShape.circle, color: done ? _P.doneGreen : null, border: Border.all(color: done ? _P.doneGreen : _P.ropeA, width: 2)),
          child: done ? const Text('✓', style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w900)) : null,
        ),
        const SizedBox(width: 9),
        Expanded(child: Text(label, style: TextStyle(color: done ? _P.doneInk : _P.ink, fontSize: 13.76, fontWeight: FontWeight.w700))),
        if (!done) ...[
          if (key == 'map')
            const Text('AT SEA', style: TextStyle(color: _P.subInk, fontSize: 11, fontWeight: FontWeight.w800))
          else if (key == 'writing') ...[
            _doButton('mark done'),
            const SizedBox(width: 6),
            const Text('SOON', style: TextStyle(color: _P.soon, fontSize: 9.6, fontWeight: FontWeight.w800)),
          ] else
            _doButton('start'),
        ],
      ]),
    );
  }

  Widget _lessonTask(Map<String, dynamic> t) {
    final done = t['done'] == true;
    return Container(
      margin: const EdgeInsets.only(bottom: 7),
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
      decoration: BoxDecoration(
        color: done ? const Color(0x292F7D5B) : const Color(0x66FFFFFF),
        border: Border.all(color: done ? _P.doneGreen : _P.ropeA, width: 1.5),
        borderRadius: BorderRadius.circular(9),
      ),
      child: Row(children: [
        Container(
          width: 20, height: 20, alignment: Alignment.center,
          decoration: BoxDecoration(shape: BoxShape.circle, color: done ? _P.doneGreen : null, border: Border.all(color: done ? _P.doneGreen : _P.ropeA, width: 2)),
          child: done ? const Text('✓', style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w900)) : null,
        ),
        const SizedBox(width: 9),
        Expanded(child: Text('${t['subject']}: ${t['topic']}', style: TextStyle(color: done ? _P.doneInk : _P.ink, fontSize: 14, fontWeight: FontWeight.w700))),
        if (!done) _doButton('start'),
      ]),
    );
  }

  Widget _doButton(String label) => Container(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
        decoration: BoxDecoration(color: const Color(0xFF6B4A2B), borderRadius: BorderRadius.circular(6)),
        child: Text(label, style: const TextStyle(color: _P.parch1, fontSize: 11.5, fontWeight: FontWeight.w800)),
      );

  Widget _locker(List<dynamic> rewards) {
    final empty = rewards.every((r) => ((r as Map)['held'] as int? ?? 0) == 0);
    return _pad([
      if (empty)
        const Padding(
          padding: EdgeInsets.only(bottom: 10),
          child: Text('Your Locker is empty for now — these are rewards to sail toward. Get ahead and reach milestones to earn them! ⛵',
              style: TextStyle(color: Color(0xFF6B4A2B), fontSize: 12.5, fontWeight: FontWeight.w700, height: 1.45)),
        ),
      for (final r in rewards) _reward(r as Map<String, dynamic>),
    ]);
  }

  Widget _reward(Map<String, dynamic> r) {
    final held = r['held'] as int? ?? 0;
    const icons = {'shore_leave': '🏝️', 'anchor': '⚓', 'tailwind': '🌬️', 'lifebuoy': '🛟'};
    return Container(
      margin: const EdgeInsets.only(bottom: 7),
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
      decoration: BoxDecoration(color: const Color(0x73FFFFFF), border: Border.all(color: _P.ropeA, width: 1.5), borderRadius: BorderRadius.circular(9)),
      child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text(icons[r['type']] ?? '🎁', style: const TextStyle(fontSize: 21)),
        const SizedBox(width: 9),
        Expanded(
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text.rich(TextSpan(children: [
              TextSpan(text: '${r['label']} ', style: const TextStyle(color: _P.titleInk, fontWeight: FontWeight.w800, fontSize: 13)),
              TextSpan(text: '×$held', style: const TextStyle(color: _P.soon, fontWeight: FontWeight.w800, fontSize: 13)),
            ])),
            Text('${r['blurb']}', style: const TextStyle(color: Color(0xFF7A5A2E), fontSize: 11.5)),
            if (held == 0) Padding(padding: const EdgeInsets.only(top: 3), child: Text('How to earn: ${r['earn']}', style: const TextStyle(color: Color(0xFF9E6A2E), fontSize: 11, fontWeight: FontWeight.w700))),
          ]),
        ),
      ]),
    );
  }

  Widget _journal(List<dynamic> entries, Map<String, dynamic> streak) {
    return _pad([
      Center(child: Text('${streak['days'] ?? 0}', style: head(42, color: _P.crestB, height: 1.0))),
      const Center(child: Text('DAY VOYAGE STREAK', style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w800, color: Color(0xFF7A5A2E), letterSpacing: 1))),
      const SizedBox(height: 12),
      if (entries.isEmpty)
        const Text('Master your first topic and it’ll be logged here, Captain. ⚓', textAlign: TextAlign.center, style: TextStyle(color: Color(0xFF7A5A2E), fontWeight: FontWeight.w700))
      else
        for (final e in entries)
          Padding(
            padding: const EdgeInsets.only(bottom: 8),
            child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
              const Text('✓ ', style: TextStyle(color: _P.doneGreen, fontWeight: FontWeight.w900)),
              Expanded(child: Text('${(e as Map)['topic']}  ·  ${e['subject']}', style: const TextStyle(color: _P.ink))),
            ]),
          ),
    ]);
  }

  Widget _logs(List<dynamic> logs) {
    return _pad([
      if (logs.isEmpty)
        const Padding(padding: EdgeInsets.symmetric(vertical: 14), child: Text('Your voyage log begins today. Sail on! ⛵', textAlign: TextAlign.center, style: TextStyle(color: Color(0xFF7A5A2E), fontWeight: FontWeight.w700)))
      else
        for (final l in logs)
          Container(
            margin: const EdgeInsets.only(bottom: 6),
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
            decoration: BoxDecoration(color: const Color(0x66FFFFFF), border: Border.all(color: const Color(0xFFD9C396)), borderRadius: BorderRadius.circular(7)),
            child: Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
              Text('${(l as Map)['label'] ?? l['date'] ?? ''}', style: const TextStyle(color: _P.titleInk, fontWeight: FontWeight.w700, fontSize: 13)),
              Text('${l['count'] != null ? '🔥 ${l['count']}' : ''}', style: const TextStyle(color: _P.ink, fontWeight: FontWeight.w800, fontSize: 13)),
            ]),
          ),
    ]);
  }
}
