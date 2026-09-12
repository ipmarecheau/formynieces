import 'package:flutter/material.dart';

import '../api.dart';
import '../theme.dart';

/// Captain's Orders — the parchment daily-brief sheet (mirrors the web bottom sheet).
Future<void> showCaptainsOrders(BuildContext context) {
  return showModalBottomSheet(
    context: context,
    backgroundColor: const Color(0xFFF3E7C8), // parchment
    showDragHandle: true,
    isScrollControlled: true,
    shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(22))),
    builder: (_) => const _CaptainsOrders(),
  );
}

const _brown = Color(0xFF5B4420);
const _brownMuted = Color(0xFF8A6E3E);

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
    return Padding(
      padding: EdgeInsets.only(
        left: 20, right: 20, top: 4,
        bottom: 20 + MediaQuery.of(context).viewInsets.bottom,
      ),
      child: FutureBuilder<Map<String, dynamic>>(
        future: _future,
        builder: (context, snap) {
          if (snap.connectionState == ConnectionState.waiting) {
            return const Padding(padding: EdgeInsets.all(30), child: Center(child: CircularProgressIndicator(color: _brown)));
          }
          if (snap.hasError) {
            return Padding(padding: const EdgeInsets.all(24), child: Text(snap.error.toString(), style: const TextStyle(color: _brown)));
          }
          final d = snap.data!;
          final duties = (d['duties'] as List<dynamic>);
          final rest = d['rest'] == true;
          return Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(children: [
                const Text('🐢', style: TextStyle(fontSize: 30)),
                const SizedBox(width: 10),
                Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Text('${d['title']}', style: head(20, color: _brown)),
                  Text('MORNING MUSTER', style: TextStyle(color: _brownMuted, fontSize: 11, letterSpacing: 1.5, fontWeight: FontWeight.w800)),
                ]),
              ]),
              const SizedBox(height: 16),
              if (rest) ...[
                const Center(child: Icon(Icons.anchor, size: 44, color: Color(0xFF7FB3C4))),
                const SizedBox(height: 14),
                Text('${d['message']}', textAlign: TextAlign.center, style: const TextStyle(color: _brown, fontSize: 16)),
              ] else ...[
                Text('${d['message']}', style: const TextStyle(color: _brown, fontSize: 14)),
                const SizedBox(height: 12),
                for (final duty in duties) _dutyRow(duty as Map<String, dynamic>),
              ],
              const SizedBox(height: 18),
              Center(
                child: Text('🔥 ${(d['streak'] as Map<String, dynamic>)['label'] ?? ''}',
                    style: const TextStyle(color: _brownMuted, fontWeight: FontWeight.w800)),
              ),
              const SizedBox(height: 8),
            ],
          );
        },
      ),
    );
  }

  Widget _dutyRow(Map<String, dynamic> duty) {
    final done = duty['done'] == true;
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(children: [
        Icon(done ? Icons.check_circle : Icons.radio_button_unchecked, color: done ? const Color(0xFF2E7D32) : _brownMuted),
        const SizedBox(width: 10),
        Text('${duty['label']}',
            style: TextStyle(
              color: _brown,
              fontSize: 16,
              decoration: done ? TextDecoration.lineThrough : null,
            )),
      ]),
    );
  }
}
