import 'package:flutter/material.dart';

import '../api.dart';
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
  Map<String, dynamic>? _feedback; // {correct, feedback, next_question, progress}
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
    // No more questions — finish.
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
      appBar: AppBar(
        title: Text('Question ${answered + (fb == null ? 1 : 0)} of $_total'),
        // MC-07: home control back to Today
        leading: IconButton(icon: const Icon(Icons.home), onPressed: () => Navigator.of(context).pop()),
      ),
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              LinearProgressIndicator(value: _total == 0 ? 0 : answered / _total),
              const SizedBox(height: 20),
              Expanded(
                child: SingleChildScrollView(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      _HtmlishText(q['prompt'] as String),
                      const SizedBox(height: 20),
                      for (final c in choices)
                        Padding(
                          padding: const EdgeInsets.only(bottom: 10),
                          child: OutlinedButton(
                            onPressed: fb == null ? () => _choose(c['id'] as String) : null,
                            style: OutlinedButton.styleFrom(
                              alignment: Alignment.centerLeft,
                              padding: const EdgeInsets.all(16),
                            ),
                            child: Text('${c['id']}.  ${c['text']}', style: const TextStyle(fontSize: 16)),
                          ),
                        ),
                    ],
                  ),
                ),
              ),
              if (fb != null) _FeedbackPanel(feedback: fb, onContinue: _busy ? null : _continue),
            ],
          ),
        ),
      ),
    );
  }
}

class _FeedbackPanel extends StatelessWidget {
  const _FeedbackPanel({required this.feedback, required this.onContinue});
  final Map<String, dynamic> feedback;
  final VoidCallback? onContinue;

  @override
  Widget build(BuildContext context) {
    final correct = feedback['correct'] == true;
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: correct ? const Color(0xFFE3F5E9) : const Color(0xFFFDEBEB),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text(correct ? 'Nice work! ⭐' : 'Not yet 🌱',
            style: TextStyle(fontWeight: FontWeight.bold, color: correct ? const Color(0xFF15803D) : const Color(0xFFB45309))),
        const SizedBox(height: 6),
        _HtmlishText('${feedback['feedback']}'),
        const SizedBox(height: 12),
        SizedBox(
          width: double.infinity,
          child: FilledButton(
            onPressed: onContinue,
            child: Text(feedback['next_question'] == null ? 'Finish' : 'Continue'),
          ),
        ),
      ]),
    );
  }
}

/// The question bank stores prompts with light HTML (<p>, <sup>…). Strip tags for a
/// clean mobile read (a full HTML renderer can come later).
class _HtmlishText extends StatelessWidget {
  const _HtmlishText(this.raw);
  final String raw;

  @override
  Widget build(BuildContext context) {
    final text = raw.replaceAll(RegExp(r'<[^>]+>'), '').replaceAll('&nbsp;', ' ').trim();
    return Text(text, style: const TextStyle(fontSize: 17));
  }
}
