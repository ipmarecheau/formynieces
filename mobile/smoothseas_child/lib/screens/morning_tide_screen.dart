import 'package:flutter/material.dart';

import '../api.dart';
import '../theme.dart';

/// The Morning Tide (DR + DV): read today's passage, chart it with comprehension
/// questions, then choose two words to build sentences with. One guided ritual that
/// satisfies the daily morning duty. Logic-identical to the web MorningTide — it drives
/// the same reading + vocabulary services through the mobile API.
class MorningTideScreen extends StatefulWidget {
  const MorningTideScreen({super.key});

  @override
  State<MorningTideScreen> createState() => _MorningTideScreenState();
}

/// read | check | pick | vocab | done | empty
enum _Phase { read, check, pick, vocab, done, empty }

class _MorningTideScreenState extends State<MorningTideScreen> {
  late Future<void> _loadFuture;
  _Phase _phase = _Phase.read;
  bool _busy = false;

  int? _assignmentId;
  String _title = '';
  String _body = '';
  List<Map<String, dynamic>> _questions = [];

  // Comprehension.
  int _qIndex = 0;
  final Map<int, dynamic> _answers = {};
  int? _score;
  String? _feedback;

  // Vocabulary.
  List<Map<String, dynamic>> _words = [];
  final List<int> _chosen = [];
  int _vocabIndex = 0;
  final Map<int, String> _sentences = {};
  final TextEditingController _input = TextEditingController();
  List<Map<String, dynamic>> _usage = [];

  @override
  void initState() {
    super.initState();
    _loadFuture = _load();
  }

  @override
  void dispose() {
    _input.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    final d = await api.getJson('/child/morning-tide') as Map<String, dynamic>;
    if (d['has_passage'] != true) {
      _phase = _Phase.empty;
      return;
    }
    _assignmentId = d['assignment_id'] as int;
    final passage = d['passage'] as Map<String, dynamic>;
    _title = passage['title'] as String? ?? 'Today’s passage';
    _body = _strip(passage['body'] as String? ?? '');
    _questions = ((d['questions'] as List<dynamic>?) ?? []).cast<Map<String, dynamic>>();
    if (d['completed'] == true) {
      _score = d['score'] as int?;
      _phase = _Phase.done;
    }
  }

  Future<void> _submitComprehension() async {
    setState(() => _busy = true);
    try {
      final ordered = [for (var i = 0; i < _questions.length; i++) _answers[i]];
      final res = await api.postJson('/child/morning-tide/comprehension', {
        'assignment_id': _assignmentId,
        'answers': ordered,
      }) as Map<String, dynamic>;
      _score = res['score'] as int?;
      _feedback = res['feedback'] as String?;
      _words = ((res['words'] as List<dynamic>?) ?? []).cast<Map<String, dynamic>>();
      setState(() {
        _busy = false;
        _phase = _words.isEmpty ? _Phase.done : _Phase.pick;
      });
      if (_words.isEmpty) await _finishVocab();
    } on ApiException catch (e) {
      _fail(e);
    }
  }

  Future<void> _finishVocab() async {
    setState(() => _busy = true);
    try {
      final res = await api.postJson('/child/morning-tide/vocabulary', {
        'assignment_id': _assignmentId,
        'sentences': [
          for (final id in _chosen)
            if ((_sentences[id] ?? '').trim().isNotEmpty) {'word_id': id, 'sentence': _sentences[id]},
        ],
      }) as Map<String, dynamic>;
      _usage = ((res['word_usage'] as List<dynamic>?) ?? []).cast<Map<String, dynamic>>();
      setState(() {
        _busy = false;
        _phase = _Phase.done;
      });
    } on ApiException catch (e) {
      _fail(e);
    }
  }

  void _fail(ApiException e) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    setState(() => _busy = false);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SeaBackground(
        child: SafeArea(
          child: FutureBuilder<void>(
            future: _loadFuture,
            builder: (context, snap) {
              if (snap.connectionState == ConnectionState.waiting) {
                return const Center(child: CircularProgressIndicator(color: Sea.gold));
              }
              if (snap.hasError) {
                return _centered(snap.error.toString());
              }
              return switch (_phase) {
                _Phase.empty => _centered('No new passage today — check back tomorrow for your Morning Tide! 🌅'),
                _Phase.read => _readView(),
                _Phase.check => _checkView(),
                _Phase.pick => _pickView(),
                _Phase.vocab => _vocabView(),
                _Phase.done => _doneView(),
              };
            },
          ),
        ),
      ),
    );
  }

  Widget _centered(String text) => Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Text(text, textAlign: TextAlign.center, style: const TextStyle(color: Sea.ink, fontSize: 16, fontWeight: FontWeight.w700)),
        ),
      );

  Widget _back() => Align(
        alignment: Alignment.centerLeft,
        child: GestureDetector(
          onTap: () => Navigator.of(context).maybePop(),
          child: const Padding(
            padding: EdgeInsets.symmetric(vertical: 12),
            child: Text('⛵ Back to my Voyage', style: TextStyle(color: Sea.gold, fontWeight: FontWeight.w800, fontSize: 15)),
          ),
        ),
      );

  Widget _readView() => ListView(
        padding: const EdgeInsets.fromLTRB(20, 0, 20, 24),
        children: [
          _back(),
          Text('🌅 Morning Tide', textAlign: TextAlign.center, style: head(26, color: Sea.gold)),
          const SizedBox(height: 4),
          Text(_title, textAlign: TextAlign.center, style: head(22, height: 1.15)),
          const SizedBox(height: 14),
          GlassCard(
            padding: const EdgeInsets.fromLTRB(18, 16, 18, 18),
            child: Text(_body, style: const TextStyle(color: Sea.foam, fontSize: 16, height: 1.5)),
          ),
          const SizedBox(height: 16),
          _GoldButton(label: 'Start the questions', onTap: () => setState(() => _phase = _Phase.check)),
        ],
      );

  Widget _checkView() {
    final q = _questions[_qIndex];
    final type = q['type'] as String? ?? 'mc';
    final options = ((q['options'] as List<dynamic>?) ?? []).cast<dynamic>();
    final isLast = _qIndex == _questions.length - 1;
    return ListView(
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 24),
      children: [
        _back(),
        Text('Question ${_qIndex + 1} of ${_questions.length}', textAlign: TextAlign.center, style: head(18, color: Sea.cyan)),
        const SizedBox(height: 12),
        GlassCard(
          padding: const EdgeInsets.fromLTRB(18, 16, 18, 18),
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(_strip('${q['prompt']}'), style: const TextStyle(color: Sea.foam, fontSize: 17, height: 1.3, fontWeight: FontWeight.w700)),
            const SizedBox(height: 14),
            if (type == 'mc')
              for (var i = 0; i < options.length; i++)
                Padding(
                  padding: const EdgeInsets.only(bottom: 10),
                  child: _ChoiceCard(
                    label: _strip('${options[i]}'),
                    selected: _answers[_qIndex] == i,
                    onTap: () => setState(() => _answers[_qIndex] = i),
                  ),
                )
            else
              TextField(
                minLines: 2,
                maxLines: 4,
                style: const TextStyle(color: Sea.foam),
                onChanged: (v) => _answers[_qIndex] = v,
                decoration: _fieldDecoration('Write your answer…'),
              ),
          ]),
        ),
        const SizedBox(height: 16),
        _GoldButton(
          label: isLast ? 'Finish reading' : 'Next question',
          onTap: _busy || _answers[_qIndex] == null
              ? null
              : () {
                  if (isLast) {
                    _submitComprehension();
                  } else {
                    setState(() => _qIndex += 1);
                  }
                },
        ),
      ],
    );
  }

  Widget _pickView() => ListView(
        padding: const EdgeInsets.fromLTRB(20, 0, 20, 24),
        children: [
          _back(),
          Text('Choose two words', textAlign: TextAlign.center, style: head(24, color: Sea.gold)),
          const SizedBox(height: 4),
          const Text('You scored — now build sentences with two new words.', textAlign: TextAlign.center, style: TextStyle(color: Sea.muted, fontWeight: FontWeight.w700)),
          const SizedBox(height: 14),
          for (final w in _words)
            Padding(
              padding: const EdgeInsets.only(bottom: 10),
              child: _ChoiceCard(
                label: '${w['word']} — ${w['definition']}',
                selected: _chosen.contains(w['id']),
                onTap: () => setState(() {
                  final id = w['id'] as int;
                  if (_chosen.contains(id)) {
                    _chosen.remove(id);
                  } else if (_chosen.length < 2) {
                    _chosen.add(id);
                  }
                }),
              ),
            ),
          const SizedBox(height: 12),
          _GoldButton(
            label: 'Start writing',
            onTap: _chosen.isEmpty
                ? null
                : () => setState(() {
                      _vocabIndex = 0;
                      _input.text = '';
                      _phase = _Phase.vocab;
                    }),
          ),
        ],
      );

  Widget _vocabView() {
    final wordId = _chosen[_vocabIndex];
    final word = _words.firstWhere((w) => w['id'] == wordId, orElse: () => {'word': '', 'definition': ''});
    final isLast = _vocabIndex == _chosen.length - 1;
    return ListView(
      padding: const EdgeInsets.fromLTRB(20, 0, 20, 24),
      children: [
        _back(),
        Text('Word ${_vocabIndex + 1} of ${_chosen.length}', textAlign: TextAlign.center, style: head(18, color: Sea.cyan)),
        const SizedBox(height: 12),
        GlassCard(
          padding: const EdgeInsets.fromLTRB(18, 16, 18, 18),
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text('${word['word']}', style: head(22, color: Sea.gold)),
            const SizedBox(height: 4),
            Text('${word['definition']}', style: const TextStyle(color: Sea.muted, fontWeight: FontWeight.w700)),
            const SizedBox(height: 14),
            TextField(
              controller: _input,
              minLines: 2,
              maxLines: 4,
              style: const TextStyle(color: Sea.foam),
              decoration: _fieldDecoration('Write a sentence using “${word['word']}”…'),
            ),
          ]),
        ),
        const SizedBox(height: 16),
        _GoldButton(
          label: isLast ? 'Finish Morning Tide' : 'Next word',
          onTap: _busy || _input.text.trim().isEmpty
              ? null
              : () {
                  _sentences[wordId] = _input.text.trim();
                  if (isLast) {
                    _finishVocab();
                  } else {
                    setState(() {
                      _vocabIndex += 1;
                      _input.text = '';
                    });
                  }
                },
        ),
      ],
    );
  }

  Widget _doneView() => ListView(
        padding: const EdgeInsets.fromLTRB(20, 0, 20, 24),
        children: [
          _back(),
          Text('🏆 Morning Tide complete', textAlign: TextAlign.center, style: head(24, color: Sea.gold)),
          const SizedBox(height: 8),
          if (_score != null)
            Text('Comprehension: $_score%', textAlign: TextAlign.center, style: head(20)),
          if (_feedback != null && _feedback!.isNotEmpty) ...[
            const SizedBox(height: 6),
            Text(_feedback!, textAlign: TextAlign.center, style: const TextStyle(color: Sea.muted, fontWeight: FontWeight.w700)),
          ],
          const SizedBox(height: 16),
          for (final u in _usage)
            Padding(
              padding: const EdgeInsets.only(bottom: 10),
              child: GlassCard(
                padding: const EdgeInsets.fromLTRB(16, 12, 16, 14),
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Row(children: [
                    Text('${u['word']}', style: head(16, color: Sea.cyan)),
                    const SizedBox(width: 8),
                    Text(u['used'] == true ? '✓ used it' : 'keep practising', style: TextStyle(color: u['used'] == true ? Sea.aqua : Sea.muted, fontWeight: FontWeight.w800, fontSize: 13)),
                  ]),
                  const SizedBox(height: 6),
                  Text('“${u['sentence']}”', style: const TextStyle(color: Sea.foam, fontStyle: FontStyle.italic)),
                ]),
              ),
            ),
          const SizedBox(height: 8),
          _GoldButton(label: 'Back to my Voyage', onTap: () => Navigator.of(context).maybePop()),
        ],
      );

  InputDecoration _fieldDecoration(String hint) => InputDecoration(
        hintText: hint,
        hintStyle: const TextStyle(color: Sea.muted),
        filled: true,
        fillColor: const Color(0x22061830),
        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: Sea.cardBorder, width: 1.5)),
        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: Sea.cyan, width: 1.5)),
      );

  String _strip(String raw) => raw.replaceAll(RegExp(r'<[^>]+>'), '').replaceAll('&nbsp;', ' ').trim();
}

class _ChoiceCard extends StatelessWidget {
  const _ChoiceCard({required this.label, this.selected = false, this.onTap});
  final String label;
  final bool selected;
  final VoidCallback? onTap;
  @override
  Widget build(BuildContext context) {
    return Material(
      color: selected ? const Color(0x3322D3EE) : const Color(0x22061830),
      borderRadius: BorderRadius.circular(14),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(14),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: selected ? Sea.cyan : Sea.cardBorder, width: 1.5),
          ),
          child: Text(label, style: const TextStyle(color: Sea.foam, fontSize: 16, fontWeight: FontWeight.w600)),
        ),
      ),
    );
  }
}

class _GoldButton extends StatelessWidget {
  const _GoldButton({required this.label, this.onTap});
  final String label;
  final VoidCallback? onTap;
  @override
  Widget build(BuildContext context) {
    final enabled = onTap != null;
    return Opacity(
      opacity: enabled ? 1 : 0.5,
      child: Material(
        color: Sea.gold,
        borderRadius: BorderRadius.circular(15),
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(15),
          child: Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(vertical: 15),
            alignment: Alignment.center,
            child: Text(label, style: head(18, color: Sea.deep)),
          ),
        ),
      ),
    );
  }
}
