import 'package:flutter/material.dart';

import '../api.dart';
import '../theme.dart';
import '../widgets/loop_rail.dart';
import 'lesson_screen.dart';
import 'practice_screen.dart';

/// The check outcome — mirrors ModuleEntry "outcome": a mastered celebration when she
/// tested out, or (on a miss) the choice of lesson / practice (LL-21). The check never
/// masters on a miss.
class OutcomeScreen extends StatefulWidget {
  const OutcomeScreen({super.key, required this.mastered, required this.moduleId, required this.missionId, required this.topic});
  final bool mastered;
  final int moduleId;
  final String missionId;
  final String topic;

  @override
  State<OutcomeScreen> createState() => _OutcomeScreenState();
}

class _OutcomeScreenState extends State<OutcomeScreen> {
  bool _busy = false;

  void _lesson() {
    Navigator.of(context).pushReplacement(MaterialPageRoute(
      builder: (_) => LessonScreen(moduleId: widget.moduleId, missionId: widget.missionId, topic: widget.topic),
    ));
  }

  Future<void> _practice() async {
    if (_busy) return;
    setState(() => _busy = true);
    try {
      final session = await api.postJson('/child/practice/start', {'mission_id': widget.missionId}) as Map<String, dynamic>;
      if (!mounted) return;
      Navigator.of(context).pushReplacement(MaterialPageRoute(builder: (_) => PracticeScreen(session: session)));
    } on ApiException catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
        setState(() => _busy = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Stack(
        children: [
          SeaBackground(
            child: SafeArea(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(20, 0, 20, 24),
                child: widget.mastered ? _mastered() : _choice(),
              ),
            ),
          ),
          LoopRail(stage: widget.mastered ? 'mastered' : 'check'),
        ],
      ),
    );
  }

  Widget _mastered() => Column(
        mainAxisAlignment: MainAxisAlignment.center,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          const Text('⭐', style: TextStyle(fontSize: 64), textAlign: TextAlign.center),
          const SizedBox(height: 12),
          Text('You tested out!', textAlign: TextAlign.center, style: head(30, color: Sea.gold, height: 1.1)),
          const SizedBox(height: 8),
          Text('You aced the quick check, Captain — ${widget.topic} is mastered without even opening the lesson. 🎉',
              textAlign: TextAlign.center, style: const TextStyle(color: Sea.foam, fontSize: 16, height: 1.4, fontWeight: FontWeight.w600)),
          const SizedBox(height: 28),
          GoldButton(label: 'Back to the island ⛵', onPressed: () => Navigator.of(context).pop()),
        ],
      );

  Widget _choice() => ListView(
        children: [
          const SizedBox(height: 10),
          Text('Nice try — not quite tested out', textAlign: TextAlign.center, style: head(26, height: 1.1)),
          const SizedBox(height: 8),
          const Text('No worries at all. Pick how you’d like to learn it — the quick check just showed Smooth where to start you.',
              textAlign: TextAlign.center, style: TextStyle(color: Sea.muted, fontSize: 15, height: 1.4)),
          const SizedBox(height: 24),
          _way('📘', 'Lesson', 'Learn it step by step with Smooth.', _lesson),
          const SizedBox(height: 12),
          _way('🎯', 'Practice', 'Jump straight in and climb easy → tricky.', _busy ? null : _practice),
        ],
      );

  Widget _way(String icon, String title, String sub, VoidCallback? onTap) => GlassCard(
        onTap: onTap,
        padding: const EdgeInsets.all(16),
        child: Row(children: [
          Text(icon, style: const TextStyle(fontSize: 28)),
          const SizedBox(width: 14),
          Expanded(
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(title, style: head(18)),
              const SizedBox(height: 2),
              Text(sub, style: const TextStyle(color: Sea.muted, fontSize: 13)),
            ]),
          ),
          const Icon(Icons.chevron_right, color: Sea.muted),
        ]),
      );
}
