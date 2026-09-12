import 'package:flutter/material.dart';

import '../api.dart';
import '../theme.dart';
import 'practice_screen.dart';

/// The teaching stage for a level: renders the lesson blocks, then gates practice
/// behind a "Practise this topic" button (LE-03 sequence). No lesson → straight to practice.
class LessonScreen extends StatefulWidget {
  const LessonScreen({super.key, required this.moduleId, required this.missionId, required this.topic});
  final int moduleId;
  final String missionId;
  final String topic;

  @override
  State<LessonScreen> createState() => _LessonScreenState();
}

class _LessonScreenState extends State<LessonScreen> {
  late Future<Map<String, dynamic>> _future;
  bool _starting = false;

  @override
  void initState() {
    super.initState();
    _future = api.getJson('/child/module/${widget.moduleId}/lesson').then((d) => d as Map<String, dynamic>);
  }

  Future<void> _practise() async {
    if (_starting) return;
    setState(() => _starting = true);
    try {
      final session = await api.postJson('/child/practice/start', {'mission_id': widget.missionId}) as Map<String, dynamic>;
      if (!mounted) return;
      Navigator.of(context).pushReplacement(MaterialPageRoute(builder: (_) => PracticeScreen(session: session)));
    } on ApiException catch (e) {
      if (mounted) {
        setState(() => _starting = false);
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SeaBackground(
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
              final d = snap.data!;
              final blocks = (d['blocks'] as List<dynamic>);
              final title = d['title'] as String? ?? widget.topic;
              return Column(
                children: [
                  Row(children: [
                    IconButton(icon: const Icon(Icons.arrow_back, color: Sea.foam), onPressed: () => Navigator.of(context).pop()),
                    Expanded(child: Text(title, style: head(18))),
                  ]),
                  Expanded(
                    child: blocks.isEmpty
                        ? _noLesson()
                        : ListView(
                            padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
                            children: [for (final b in blocks) _Block(block: b as Map<String, dynamic>)],
                          ),
                  ),
                  Padding(
                    padding: const EdgeInsets.all(16),
                    child: _starting
                        ? const CircularProgressIndicator(color: Sea.gold)
                        : GoldButton(label: 'Practise this topic →', onPressed: _practise),
                  ),
                ],
              );
            },
          ),
        ),
      ),
    );
  }

  Widget _noLesson() => Center(
        child: Padding(
          padding: const EdgeInsets.all(28),
          child: Column(mainAxisSize: MainAxisSize.min, children: [
            const Text('🧭', style: TextStyle(fontSize: 44)),
            const SizedBox(height: 12),
            Text('Ready to practise ${widget.topic}!', style: head(18), textAlign: TextAlign.center),
          ]),
        ),
      );
}

String _strip(dynamic raw) => (raw == null ? '' : raw.toString()).replaceAll(RegExp(r'<[^>]+>'), '').replaceAll('&nbsp;', ' ').trim();

class _Block extends StatelessWidget {
  const _Block({required this.block});
  final Map<String, dynamic> block;

  @override
  Widget build(BuildContext context) {
    final type = block['type'] as String? ?? 'text';
    switch (type) {
      case 'heading':
        return Padding(padding: const EdgeInsets.only(top: 12, bottom: 6), child: Text(_strip(block['content']), style: head(19)));
      case 'key':
        return _card(const Color(0x33F6B71E), Sea.gold, '💡 Key idea', _strip(block['content']));
      case 'example':
        return _example();
      case 'check':
        return _reveal('✅ Quick check', _strip(block['question']), _optionsText(), _checkAnswer());
      case 'fillblank':
        return _reveal('✏️ Fill the blank', _strip(block['prompt']), null, _strip(block['answer']));
      case 'markwords':
      case 'matchpairs':
      case 'ordersteps':
        return _card(Sea.cardFill, Sea.cardBorder, '🎯 ${_strip(block['instruction'])}', _activityBody());
      default:
        final content = _strip(block['content'] ?? block['question'] ?? block['prompt']);
        return content.isEmpty ? const SizedBox.shrink() : Padding(padding: const EdgeInsets.symmetric(vertical: 8), child: Text(content, style: const TextStyle(color: Sea.ink, fontSize: 16, height: 1.4)));
    }
  }

  /// A check's answer may be the option text or a numeric index into options.
  String _checkAnswer() {
    final ans = block['answer'];
    final opts = block['options'];
    if (ans is int && opts is List && ans >= 0 && ans < opts.length) {
      return _strip(opts[ans]);
    }
    return _strip(ans);
  }

  String? _optionsText() {
    final opts = block['options'];
    if (opts is List && opts.isNotEmpty) return opts.map((o) => '• ${_strip(o.toString())}').join('\n');
    return null;
  }

  String _activityBody() {
    if (block['text'] != null) return _strip(block['text']);
    final items = block['items'];
    if (items is List) return items.map((i) => '• ${_strip(i.toString())}').join('\n');
    final pairs = block['pairs'];
    if (pairs is List) {
      return pairs.map((p) => p is Map ? '• ${_strip(p.values.map((v) => v.toString()).join(' ↔ '))}' : '• ${_strip(p.toString())}').join('\n');
    }
    return '';
  }

  Widget _example() {
    final steps = (block['steps'] as List<dynamic>? ?? []);
    return _card(Sea.cardFill, Sea.aqua, '📘 Example', _strip(block['content']), extra: steps.isEmpty
        ? null
        : Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            for (var i = 0; i < steps.length; i++)
              Padding(padding: const EdgeInsets.only(top: 6), child: Text('${i + 1}. ${_strip(steps[i].toString())}', style: const TextStyle(color: Sea.ink))),
          ]));
  }

  Widget _card(Color bg, Color border, String label, String body, {Widget? extra}) => Padding(
        padding: const EdgeInsets.symmetric(vertical: 8),
        child: Container(
          width: double.infinity,
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(14), border: Border.all(color: border)),
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(label, style: const TextStyle(color: Sea.aqua, fontWeight: FontWeight.w800)),
            if (body.isNotEmpty) ...[const SizedBox(height: 6), Text(body, style: const TextStyle(color: Sea.ink, fontSize: 15, height: 1.4))],
            ?extra,
          ]),
        ),
      );

  Widget _reveal(String label, String question, String? options, String answer) => Padding(
        padding: const EdgeInsets.symmetric(vertical: 8),
        child: Theme(
          data: ThemeData.dark().copyWith(dividerColor: Colors.transparent),
          child: Container(
            decoration: BoxDecoration(color: Sea.cardFill, borderRadius: BorderRadius.circular(14), border: Border.all(color: Sea.cardBorder)),
            child: ExpansionTile(
              tilePadding: const EdgeInsets.symmetric(horizontal: 14),
              childrenPadding: const EdgeInsets.fromLTRB(14, 0, 14, 14),
              iconColor: Sea.gold,
              collapsedIconColor: Sea.gold,
              title: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(label, style: const TextStyle(color: Sea.aqua, fontWeight: FontWeight.w800, fontSize: 13)),
                const SizedBox(height: 4),
                Text(question, style: const TextStyle(color: Sea.ink, fontSize: 15)),
                if (options != null) ...[const SizedBox(height: 6), Text(options, style: const TextStyle(color: Sea.muted, fontSize: 14))],
              ]),
              children: [Align(alignment: Alignment.centerLeft, child: Text('Answer: $answer', style: head(15, color: Sea.gold)))],
            ),
          ),
        ),
      );
}
