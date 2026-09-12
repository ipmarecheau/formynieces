import 'package:flutter/material.dart';

import '../api.dart';
import '../theme.dart';
import 'practice_screen.dart';

/// The teaching stage: renders lesson blocks (with real interactive widgets) then
/// gates practice behind a "Practise this topic" button (LE-01/LE-03).
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
                            children: [for (final b in blocks) _block(b as Map<String, dynamic>)],
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

  Widget _block(Map<String, dynamic> b) {
    switch (b['type'] as String? ?? 'text') {
      case 'heading':
        return Padding(padding: const EdgeInsets.only(top: 12, bottom: 6), child: Text(strip(b['content']), style: head(19)));
      case 'key':
        return _KeyCard(text: strip(b['content']));
      case 'example':
        return _ExampleCard(content: strip(b['content']), steps: (b['steps'] as List<dynamic>? ?? []).map(strip).toList());
      case 'check':
        return _CheckBlock(block: b);
      case 'fillblank':
        return _FillBlankBlock(block: b);
      case 'markwords':
        return _MarkWordsBlock(block: b);
      case 'matchpairs':
        return _MatchPairsBlock(block: b);
      case 'ordersteps':
        return _OrderStepsBlock(block: b);
      default:
        final content = strip(b['content'] ?? b['question'] ?? b['prompt']);
        return content.isEmpty ? const SizedBox.shrink() : Padding(padding: const EdgeInsets.symmetric(vertical: 8), child: Text(content, style: const TextStyle(color: Sea.ink, fontSize: 16, height: 1.4)));
    }
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

String strip(dynamic raw) => (raw == null ? '' : raw.toString()).replaceAll(RegExp(r'<[^>]+>'), '').replaceAll('&nbsp;', ' ').trim();

/// A framed interactive card with a coloured accent label.
class _ActivityFrame extends StatelessWidget {
  const _ActivityFrame({required this.label, required this.child});
  final String label;
  final Widget child;
  @override
  Widget build(BuildContext context) => Padding(
        padding: const EdgeInsets.symmetric(vertical: 8),
        child: Container(
          width: double.infinity,
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(color: Sea.cardFill, borderRadius: BorderRadius.circular(14), border: Border.all(color: Sea.cardBorder)),
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(label, style: const TextStyle(color: Sea.aqua, fontWeight: FontWeight.w800, fontSize: 13)),
            const SizedBox(height: 8),
            child,
          ]),
        ),
      );
}

class _KeyCard extends StatelessWidget {
  const _KeyCard({required this.text});
  final String text;
  @override
  Widget build(BuildContext context) => _ActivityFrame(label: '💡 Key idea', child: Text(text, style: const TextStyle(color: Sea.ink, fontSize: 15, height: 1.4)));
}

class _ExampleCard extends StatelessWidget {
  const _ExampleCard({required this.content, required this.steps});
  final String content;
  final List<String> steps;
  @override
  Widget build(BuildContext context) => _ActivityFrame(
        label: '📘 Example',
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          if (content.isNotEmpty) Text(content, style: const TextStyle(color: Sea.ink, fontSize: 15)),
          for (var i = 0; i < steps.length; i++)
            Padding(padding: const EdgeInsets.only(top: 6), child: Text('${i + 1}. ${steps[i]}', style: const TextStyle(color: Sea.ink))),
        ]),
      );
}

/// Multiple-choice check — tap an option to answer.
class _CheckBlock extends StatefulWidget {
  const _CheckBlock({required this.block});
  final Map<String, dynamic> block;
  @override
  State<_CheckBlock> createState() => _CheckBlockState();
}

class _CheckBlockState extends State<_CheckBlock> {
  int? _picked;

  @override
  Widget build(BuildContext context) {
    final b = widget.block;
    final options = (b['options'] as List<dynamic>? ?? []);
    final ans = b['answer'];
    final correctIndex = ans is int
        ? ans
        : options.indexWhere((o) => strip(o).toLowerCase() == strip(ans).toLowerCase());
    return _ActivityFrame(
      label: '✅ Quick check',
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text(strip(b['question']), style: const TextStyle(color: Sea.ink, fontSize: 15)),
        const SizedBox(height: 10),
        for (var i = 0; i < options.length; i++)
          _OptionTile(
            label: strip(options[i]),
            state: _picked == null ? _Opt.idle : (i == correctIndex ? _Opt.correct : (i == _picked ? _Opt.wrong : _Opt.idle)),
            onTap: _picked == null ? () => setState(() => _picked = i) : null,
          ),
        if (_picked != null) _FeedbackLine(correct: _picked == correctIndex, explain: strip(b['explain'])),
      ]),
    );
  }
}

/// Fill the blank — type the answer (or pick from options when given).
class _FillBlankBlock extends StatefulWidget {
  const _FillBlankBlock({required this.block});
  final Map<String, dynamic> block;
  @override
  State<_FillBlankBlock> createState() => _FillBlankBlockState();
}

class _FillBlankBlockState extends State<_FillBlankBlock> {
  final _ctrl = TextEditingController();
  bool? _correct;
  int? _picked;

  bool _matches(String v, String answer) => v.trim().toLowerCase() == answer.trim().toLowerCase();

  @override
  Widget build(BuildContext context) {
    final b = widget.block;
    final answer = strip(b['answer']);
    final options = (b['options'] as List<dynamic>? ?? []);
    return _ActivityFrame(
      label: '✏️ Fill the blank',
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text(strip(b['prompt']), style: const TextStyle(color: Sea.ink, fontSize: 15)),
        const SizedBox(height: 10),
        if (options.isNotEmpty)
          for (var i = 0; i < options.length; i++)
            _OptionTile(
              label: strip(options[i]),
              state: _picked == null ? _Opt.idle : (_matches(strip(options[i]), answer) ? _Opt.correct : (i == _picked ? _Opt.wrong : _Opt.idle)),
              onTap: _picked == null ? () => setState(() { _picked = i; _correct = _matches(strip(options[i]), answer); }) : null,
            )
        else ...[
          Row(children: [
            Expanded(
              child: TextField(
                controller: _ctrl,
                enabled: _correct == null,
                style: const TextStyle(color: Sea.ink),
                decoration: const InputDecoration(hintText: 'Type your answer'),
              ),
            ),
            const SizedBox(width: 10),
            if (_correct == null) GoldButton(label: 'Check', expand: false, onPressed: () => setState(() => _correct = _matches(_ctrl.text, answer))),
          ]),
        ],
        if (_correct != null) _FeedbackLine(correct: _correct!, explain: _correct! ? strip(b['explain']) : 'Answer: $answer. ${strip(b['explain'])}'),
      ]),
    );
  }
}

/// Tap the target words (marked with *stars* in the source text).
class _MarkWordsBlock extends StatefulWidget {
  const _MarkWordsBlock({required this.block});
  final Map<String, dynamic> block;
  @override
  State<_MarkWordsBlock> createState() => _MarkWordsBlockState();
}

class _MarkWordsBlockState extends State<_MarkWordsBlock> {
  final Set<int> _selected = {};
  bool _checked = false;

  @override
  Widget build(BuildContext context) {
    final b = widget.block;
    final tokens = strip(b['text']).split(RegExp(r'\s+'));
    final targets = <int>{};
    final display = <String>[];
    for (var i = 0; i < tokens.length; i++) {
      final t = tokens[i];
      final isTarget = t.startsWith('*') && t.endsWith('*') && t.length > 1;
      if (isTarget) targets.add(i);
      display.add(t.replaceAll('*', ''));
    }
    final correct = _checked && _selected.length == targets.length && _selected.containsAll(targets);
    return _ActivityFrame(
      label: '🎯 ${strip(b['instruction'])}',
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Wrap(spacing: 6, runSpacing: 6, children: [
          for (var i = 0; i < display.length; i++)
            GestureDetector(
              onTap: _checked ? null : () => setState(() => _selected.contains(i) ? _selected.remove(i) : _selected.add(i)),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 5),
                decoration: BoxDecoration(
                  color: _wordColor(i, targets),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: _selected.contains(i) ? Sea.gold : Sea.cardBorder),
                ),
                child: Text(display[i], style: const TextStyle(color: Sea.foam, fontSize: 15)),
              ),
            ),
        ]),
        const SizedBox(height: 10),
        if (!_checked)
          GoldButton(label: 'Check', expand: false, onPressed: _selected.isEmpty ? null : () => setState(() => _checked = true))
        else
          _FeedbackLine(correct: correct, explain: strip(b['explain'])),
      ]),
    );
  }

  Color _wordColor(int i, Set<int> targets) {
    if (_checked && targets.contains(i)) return const Color(0x5515803D);
    if (_selected.contains(i)) return const Color(0x33F6B71E);
    return const Color(0x2267E8F9);
  }
}

/// Match each left item to its right (tap a left, then its match).
class _MatchPairsBlock extends StatefulWidget {
  const _MatchPairsBlock({required this.block});
  final Map<String, dynamic> block;
  @override
  State<_MatchPairsBlock> createState() => _MatchPairsBlockState();
}

class _MatchPairsBlockState extends State<_MatchPairsBlock> {
  late final List<Map<String, String>> _pairs;
  late final List<String> _rights;
  final Map<String, String> _matched = {}; // left -> right
  String? _activeLeft;

  @override
  void initState() {
    super.initState();
    _pairs = ((widget.block['pairs'] as List<dynamic>? ?? []))
        .map((p) => {'left': strip((p as Map)['left']), 'right': strip(p['right'])})
        .toList();
    _rights = _pairs.map((p) => p['right']!).toList()..shuffle();
  }

  @override
  Widget build(BuildContext context) {
    final done = _matched.length == _pairs.length;
    return _ActivityFrame(
      label: '🔗 ${strip(widget.block['instruction'])}',
      child: Column(children: [
        Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Expanded(child: Column(children: [for (final p in _pairs) _chip(p['left']!, isLeft: true)])),
          const SizedBox(width: 10),
          Expanded(child: Column(children: [for (final r in _rights) _chip(r, isLeft: false)])),
        ]),
        if (done) const Padding(padding: EdgeInsets.only(top: 10), child: _FeedbackLine(correct: true, explain: 'All matched — well done!')),
      ]),
    );
  }

  Widget _chip(String text, {required bool isLeft}) {
    final matched = isLeft ? _matched.containsKey(text) : _matched.containsValue(text);
    final active = isLeft && _activeLeft == text;
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: GestureDetector(
        onTap: matched ? null : () => _tap(text, isLeft),
        child: Container(
          width: double.infinity,
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
          decoration: BoxDecoration(
            color: matched ? const Color(0x5515803D) : (active ? const Color(0x33F6B71E) : const Color(0x2267E8F9)),
            borderRadius: BorderRadius.circular(10),
            border: Border.all(color: active ? Sea.gold : Sea.cardBorder),
          ),
          child: Text(text, textAlign: TextAlign.center, style: const TextStyle(color: Sea.foam)),
        ),
      ),
    );
  }

  void _tap(String text, bool isLeft) {
    setState(() {
      if (isLeft) {
        _activeLeft = text;
      } else if (_activeLeft != null) {
        final correctRight = _pairs.firstWhere((p) => p['left'] == _activeLeft)['right'];
        if (correctRight == text) {
          _matched[_activeLeft!] = text;
        }
        _activeLeft = null;
      }
    });
  }
}

/// Drag the steps into the correct order.
class _OrderStepsBlock extends StatefulWidget {
  const _OrderStepsBlock({required this.block});
  final Map<String, dynamic> block;
  @override
  State<_OrderStepsBlock> createState() => _OrderStepsBlockState();
}

class _OrderStepsBlockState extends State<_OrderStepsBlock> {
  late final List<String> _correct;
  late List<String> _order;
  bool _checked = false;

  @override
  void initState() {
    super.initState();
    _correct = ((widget.block['items'] as List<dynamic>? ?? [])).map(strip).toList();
    _order = List.of(_correct)..shuffle();
    if (_order.length > 1 && _listEquals(_order, _correct)) _order = _order.reversed.toList();
  }

  bool _listEquals(List<String> a, List<String> b) {
    if (a.length != b.length) return false;
    for (var i = 0; i < a.length; i++) {
      if (a[i] != b[i]) return false;
    }
    return true;
  }

  @override
  Widget build(BuildContext context) {
    final correct = _checked && _listEquals(_order, _correct);
    return _ActivityFrame(
      label: '↕️ ${strip(widget.block['instruction'])}',
      child: Column(children: [
        ReorderableListView(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          buildDefaultDragHandles: !_checked,
          onReorderItem: (oldI, newI) => setState(() => _order.insert(newI, _order.removeAt(oldI))),
          children: [
            for (var i = 0; i < _order.length; i++)
              Container(
                key: ValueKey(_order[i]),
                margin: const EdgeInsets.only(bottom: 6),
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                decoration: BoxDecoration(color: const Color(0x2267E8F9), borderRadius: BorderRadius.circular(10), border: Border.all(color: Sea.cardBorder)),
                child: Row(children: [
                  Expanded(child: Text('${i + 1}. ${_order[i]}', style: const TextStyle(color: Sea.foam))),
                  if (!_checked) const Icon(Icons.drag_handle, color: Sea.muted, size: 18),
                ]),
              ),
          ],
        ),
        const SizedBox(height: 8),
        if (!_checked)
          GoldButton(label: 'Check order', expand: false, onPressed: () => setState(() => _checked = true))
        else
          _FeedbackLine(correct: correct, explain: correct ? 'Perfect order!' : 'Not quite — the right order is:\n${_correct.asMap().entries.map((e) => '${e.key + 1}. ${e.value}').join('\n')}'),
      ]),
    );
  }
}

enum _Opt { idle, correct, wrong }

class _OptionTile extends StatelessWidget {
  const _OptionTile({required this.label, required this.state, required this.onTap});
  final String label;
  final _Opt state;
  final VoidCallback? onTap;
  @override
  Widget build(BuildContext context) {
    final (bg, border) = switch (state) {
      _Opt.correct => (const Color(0x5515803D), const Color(0xFF6EE7B7)),
      _Opt.wrong => (const Color(0x55B45309), Sea.gold),
      _Opt.idle => (const Color(0x2267E8F9), Sea.cardBorder),
    };
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          width: double.infinity,
          padding: const EdgeInsets.all(13),
          decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(12), border: Border.all(color: border)),
          child: Row(children: [
            Expanded(child: Text(label, style: const TextStyle(color: Sea.foam, fontSize: 15))),
            if (state == _Opt.correct) const Icon(Icons.check, color: Color(0xFF6EE7B7), size: 18),
            if (state == _Opt.wrong) const Icon(Icons.close, color: Sea.gold, size: 18),
          ]),
        ),
      ),
    );
  }
}

class _FeedbackLine extends StatelessWidget {
  const _FeedbackLine({required this.correct, required this.explain});
  final bool correct;
  final String explain;
  @override
  Widget build(BuildContext context) => Padding(
        padding: const EdgeInsets.only(top: 10),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(correct ? 'Correct! ⭐' : 'Not yet 🌱', style: head(14, color: correct ? const Color(0xFF6EE7B7) : Sea.gold)),
          if (explain.trim().isNotEmpty) Padding(padding: const EdgeInsets.only(top: 4), child: Text(explain, style: const TextStyle(color: Sea.ink, fontSize: 14))),
        ]),
      );
}
