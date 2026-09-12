import 'package:flutter/material.dart';

import '../api.dart';
import '../theme.dart';
import 'result_screen.dart';

/// MC-03/04 — one short session: a question, touch choices, immediate feedback.
class PracticeScreen extends StatefulWidget {
  const PracticeScreen({super.key, required this.session});
  final Map<String, dynamic> session;

  @override
  State<PracticeScreen> createState() => _PracticeScreenState();
}

class _PracticeScreenState extends State<PracticeScreen> {
  late int _sessionId;
  late int _total;
  Map<String, dynamic>? _question;
  Map<String, dynamic>? _feedback;
  bool _busy = false;

  @override
  void initState() {
    super.initState();
    _sessionId = widget.session['session_id'] as int;
    _total = widget.session['total_questions'] as int;
    _question = widget.session['current_question'] as Map<String, dynamic>;
  }

  Future<void> _choose(String choiceId) async {
    if (_busy || _feedback != null) return;
    setState(() => _busy = true);
    try {
      final res = await api.postJson('/child/practice/$_sessionId/answer', {
        'question_id': _question!['id'],
        'choice_id': choiceId,
      }) as Map<String, dynamic>;
      setState(() => _feedback = res);
    } on ApiException catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  Future<void> _continue() async {
    final next = _feedback?['next_question'] as Map<String, dynamic>?;
    if (next != null) {
      setState(() {
        _question = next;
        _feedback = null;
      });
      return;
    }
    setState(() => _busy = true);
    try {
      final result = await api.postJson('/child/practice/$_sessionId/finish') as Map<String, dynamic>;
      if (!mounted) return;
      Navigator.of(context).pushReplacement(MaterialPageRoute(builder: (_) => ResultScreen(result: result)));
    } on ApiException catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final q = _question!;
    final choices = (q['choices'] as List<dynamic>);
    final fb = _feedback;
    final progress = fb?['progress'] as Map<String, dynamic>?;
    final answered = progress?['answered'] as int? ?? 0;

    return Scaffold(
      body: SeaBackground(
        child: SafeArea(
          child: Padding(
            padding: const EdgeInsets.all(18),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Row(children: [
                  IconButton(icon: const Icon(Icons.home, color: Sea.foam), onPressed: () => Navigator.of(context).pop()),
                  Expanded(child: Text('Question ${answered + (fb == null ? 1 : 0)} of $_total', style: head(16))),
                ]),
                const SizedBox(height: 8),
                ClipRRect(
                  borderRadius: BorderRadius.circular(999),
                  child: LinearProgressIndicator(
                    value: _total == 0 ? 0 : answered / _total,
                    minHeight: 8,
                    backgroundColor: const Color(0x3322D3EE),
                    valueColor: const AlwaysStoppedAnimation(Sea.gold),
                  ),
                ),
                const SizedBox(height: 18),
                Expanded(
                  child: SingleChildScrollView(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        GlassCard(child: Text(_strip(q['prompt'] as String), style: head(18, weight: FontWeight.w500))),
                        const SizedBox(height: 16),
                        for (final c in choices)
                          Padding(
                            padding: const EdgeInsets.only(bottom: 10),
                            child: _ChoiceButton(
                              label: '${c['id']}.  ${_strip(c['text'] as String)}',
                              enabled: fb == null,
                              onTap: () => _choose(c['id'] as String),
                            ),
                          ),
                      ],
                    ),
                  ),
                ),
                if (fb != null) _feedbackPanel(fb),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _feedbackPanel(Map<String, dynamic> fb) {
    final correct = fb['correct'] == true;
    return Container(
      margin: const EdgeInsets.only(top: 8),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: correct ? const Color(0x3315803D) : const Color(0x33B45309),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: correct ? const Color(0xFF6EE7B7) : Sea.gold, width: 1.2),
      ),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text(correct ? 'Nice work! ⭐' : 'Not yet 🌱', style: head(16, color: correct ? const Color(0xFF6EE7B7) : Sea.gold)),
        const SizedBox(height: 6),
        Text(_strip('${fb['feedback']}'), style: const TextStyle(color: Sea.ink)),
        const SizedBox(height: 12),
        GoldButton(label: fb['next_question'] == null ? 'Finish' : 'Continue', onPressed: _busy ? null : _continue),
      ]),
    );
  }

  String _strip(String raw) => raw.replaceAll(RegExp(r'<[^>]+>'), '').replaceAll('&nbsp;', ' ').trim();
}

class _ChoiceButton extends StatelessWidget {
  const _ChoiceButton({required this.label, required this.enabled, required this.onTap});
  final String label;
  final bool enabled;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: const Color(0x2267E8F9),
      borderRadius: BorderRadius.circular(14),
      child: InkWell(
        onTap: enabled ? onTap : null,
        borderRadius: BorderRadius.circular(14),
        child: Container(
          width: double.infinity,
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: Sea.cardBorder),
          ),
          child: Text(label, style: const TextStyle(color: Sea.foam, fontSize: 16)),
        ),
      ),
    );
  }
}
