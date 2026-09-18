import 'package:flutter/material.dart';

import '../api.dart';
import '../theme.dart';
import '../widgets/loop_rail.dart';
import 'outcome_screen.dart';

/// The competency check (LL-20 test-out) — six D1/D3/D5 questions. Ace all first-try
/// and the module is mastered without the lesson; any miss hands over to the outcome.
class CheckScreen extends StatefulWidget {
  const CheckScreen({super.key, required this.moduleId, required this.missionId, required this.topic});
  final int moduleId;
  final String missionId;
  final String topic;

  @override
  State<CheckScreen> createState() => _CheckScreenState();
}

class _CheckScreenState extends State<CheckScreen> {
  late Future<Map<String, dynamic>> _future;
  int? _sessionId;
  int _total = 6;
  int _answered = 0;
  Map<String, dynamic>? _question;
  bool _busy = false;

  @override
  void initState() {
    super.initState();
    _future = _start();
  }

  Future<Map<String, dynamic>> _start() async {
    final d = await api.postJson('/child/module/${widget.moduleId}/check/start') as Map<String, dynamic>;
    _sessionId = d['session_id'] as int;
    _total = d['total_questions'] as int? ?? 6;
    _question = d['current_question'] as Map<String, dynamic>;
    return d;
  }

  Future<void> _choose(String choiceId) async {
    if (_busy) return;
    setState(() => _busy = true);
    try {
      final res = await api.postJson('/child/check/$_sessionId/answer', {
        'question_id': _question!['id'],
        'choice_id': choiceId,
      }) as Map<String, dynamic>;
      if (res['done'] == true) {
        if (!mounted) return;
        Navigator.of(context).pushReplacement(MaterialPageRoute(
          builder: (_) => OutcomeScreen(mastered: res['mastered'] == true, moduleId: widget.moduleId, missionId: widget.missionId, topic: widget.topic),
        ));
        return;
      }
      setState(() {
        _answered += 1;
        _question = res['next_question'] as Map<String, dynamic>;
        _busy = false;
      });
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
              child: FutureBuilder<Map<String, dynamic>>(
                future: _future,
                builder: (context, snap) {
                  if (snap.connectionState == ConnectionState.waiting) {
                    return const Center(child: CircularProgressIndicator(color: Sea.gold));
                  }
                  if (snap.hasError) {
                    return Center(child: Padding(padding: const EdgeInsets.all(20), child: Text(snap.error.toString(), style: const TextStyle(color: Sea.ink), textAlign: TextAlign.center)));
                  }
                  final q = _question!;
                  final choices = (q['choices'] as List<dynamic>);
                  return ListView(
                    padding: const EdgeInsets.fromLTRB(20, 0, 20, 24),
                    children: [
                      Align(
                        alignment: Alignment.centerLeft,
                        child: GestureDetector(
                          onTap: () => Navigator.of(context).pop(),
                          child: const Padding(
                            padding: EdgeInsets.symmetric(vertical: 14),
                            child: Text('⛵ Back to my Voyage', style: TextStyle(color: Sea.gold, fontWeight: FontWeight.w800, fontSize: 15)),
                          ),
                        ),
                      ),
                      Text(widget.topic, textAlign: TextAlign.center, style: head(26, height: 1.1)),
                      const SizedBox(height: 16),
                      GlassCard(
                        padding: const EdgeInsets.fromLTRB(18, 16, 18, 18),
                        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                          Text.rich(TextSpan(children: [
                            TextSpan(text: 'Quick check ', style: head(18, color: Sea.cyan)),
                            TextSpan(text: '${_answered + 1} of $_total', style: const TextStyle(color: Color(0xFFA5B4FC), fontWeight: FontWeight.w800, fontSize: 15)),
                          ])),
                          const SizedBox(height: 12),
                          Text('${q['prompt']}', style: const TextStyle(color: Sea.foam, fontSize: 17, height: 1.3, fontWeight: FontWeight.w700)),
                          const SizedBox(height: 14),
                          for (final c in choices)
                            Padding(
                              padding: const EdgeInsets.only(bottom: 10),
                              child: _ChoiceCard(label: '${(c as Map)['id']}.  ${c['text']}', onTap: _busy ? null : () => _choose(c['id'] as String)),
                            ),
                          if (_busy) const Center(child: Padding(padding: EdgeInsets.only(top: 4), child: Text('Checking your answer… 🐢', style: TextStyle(color: Sea.cyan, fontWeight: FontWeight.w700)))),
                        ]),
                      ),
                    ],
                  );
                },
              ),
            ),
          ),
          const LoopRail(stage: 'check'),
        ],
      ),
    );
  }
}

class _ChoiceCard extends StatelessWidget {
  const _ChoiceCard({required this.label, this.onTap});
  final String label;
  final VoidCallback? onTap;
  @override
  Widget build(BuildContext context) {
    return Material(
      color: const Color(0x22061830),
      borderRadius: BorderRadius.circular(14),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(14),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
          decoration: BoxDecoration(borderRadius: BorderRadius.circular(14), border: Border.all(color: Sea.cardBorder, width: 1.5)),
          child: Text(label, style: const TextStyle(color: Sea.foam, fontSize: 16, fontWeight: FontWeight.w600)),
        ),
      ),
    );
  }
}
