import 'package:flutter/material.dart';

import '../api.dart';
import '../theme.dart';

/// The Writer's Log (WR): this week's prompt, her draft, and a warm four-criterion
/// rubric — two strengths and one thing to try next, never a grade. Drives the same
/// WritingScorer as the web through the mobile API; scoring queues on an AI outage.
class WritingScreen extends StatefulWidget {
  const WritingScreen({super.key});

  @override
  State<WritingScreen> createState() => _WritingScreenState();
}

class _WritingScreenState extends State<WritingScreen> {
  late Future<Map<String, dynamic>> _future;
  final TextEditingController _draft = TextEditingController();
  bool _busy = false;
  bool _hasPrompt = false;
  String _emptyMessage = '';
  Map<String, dynamic>? _prompt;
  Map<String, dynamic>? _submission;
  bool _queued = false;

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  @override
  void dispose() {
    _draft.dispose();
    super.dispose();
  }

  Future<Map<String, dynamic>> _load() async {
    final d = await api.getJson('/child/writing') as Map<String, dynamic>;
    _hasPrompt = d['has_prompt'] == true;
    _emptyMessage = d['message'] as String? ?? '';
    _prompt = d['prompt'] as Map<String, dynamic>?;
    _submission = d['submission'] as Map<String, dynamic>?;
    _queued = d['queued'] == true;
    return d;
  }

  Future<void> _submit() async {
    if (_draft.text.trim().length < 20) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Try writing a bit more before you send it in.')));
      return;
    }
    setState(() => _busy = true);
    try {
      final res = await api.postJson('/child/writing', {'body': _draft.text.trim()}) as Map<String, dynamic>;
      setState(() {
        _queued = res['queued'] == true;
        _submission = res['submission'] as Map<String, dynamic>?;
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
      body: SeaBackground(
        child: SafeArea(
          child: FutureBuilder<Map<String, dynamic>>(
            future: _future,
            builder: (context, snap) {
              if (snap.connectionState == ConnectionState.waiting) {
                return const Center(child: CircularProgressIndicator(color: Sea.gold));
              }
              if (snap.hasError) {
                return _centered(snap.error.toString());
              }
              if (!_hasPrompt) {
                return _centered(_emptyMessage.isEmpty ? 'No writing prompt this week — check back soon! ✍️' : _emptyMessage);
              }
              final scored = _submission != null && _submission!['scored'] == true;
              return ListView(
                padding: const EdgeInsets.fromLTRB(20, 0, 20, 24),
                children: [
                  _back(),
                  Text('✍️ Writer’s Log', textAlign: TextAlign.center, style: head(26, color: Sea.gold)),
                  const SizedBox(height: 4),
                  Text('${_prompt?['title'] ?? ''}', textAlign: TextAlign.center, style: head(22, height: 1.15)),
                  const SizedBox(height: 12),
                  GlassCard(
                    padding: const EdgeInsets.fromLTRB(18, 16, 18, 18),
                    child: Text('${_prompt?['prompt'] ?? ''}', style: const TextStyle(color: Sea.foam, fontSize: 16, height: 1.4, fontWeight: FontWeight.w600)),
                  ),
                  const SizedBox(height: 16),
                  if (scored) _rubricView() else if (_queued) _queuedView() else _draftView(),
                ],
              );
            },
          ),
        ),
      ),
    );
  }

  Widget _draftView() => Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          GlassCard(
            padding: const EdgeInsets.fromLTRB(14, 12, 14, 14),
            child: TextField(
              controller: _draft,
              minLines: 6,
              maxLines: 14,
              style: const TextStyle(color: Sea.foam, height: 1.4),
              decoration: const InputDecoration(
                border: InputBorder.none,
                hintText: 'Start your story here…',
                hintStyle: TextStyle(color: Sea.muted),
              ),
            ),
          ),
          const SizedBox(height: 16),
          _GoldButton(label: _busy ? 'Sending…' : 'Send my writing in', onTap: _busy ? null : _submit),
        ],
      );

  Widget _queuedView() => GlassCard(
        padding: const EdgeInsets.fromLTRB(18, 16, 18, 18),
        child: Column(children: [
          Text('Your writing is in! 🌊', style: head(18, color: Sea.cyan)),
          const SizedBox(height: 8),
          const Text('Your feedback is on its way — check back a little later to see your rubric.', textAlign: TextAlign.center, style: TextStyle(color: Sea.foam, height: 1.4)),
        ]),
      );

  Widget _rubricView() {
    final rubric = (_submission!['rubric'] as Map<String, dynamic>?) ?? {};
    final didWell = ((_submission!['did_well'] as List<dynamic>?) ?? []).cast<dynamic>();
    final tryNext = _submission!['try_next'] as String?;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Text('Your rubric', style: head(20, color: Sea.gold)),
        const SizedBox(height: 8),
        GlassCard(
          padding: const EdgeInsets.fromLTRB(18, 14, 18, 16),
          child: Column(children: [
            for (final entry in rubric.entries)
              Padding(
                padding: const EdgeInsets.only(bottom: 8),
                child: Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
                  Text(entry.key, style: const TextStyle(color: Sea.foam, fontWeight: FontWeight.w700, fontSize: 15)),
                  Text('${entry.value}/10', style: const TextStyle(color: Sea.aqua, fontWeight: FontWeight.w900, fontSize: 15)),
                ]),
              ),
          ]),
        ),
        const SizedBox(height: 12),
        if (didWell.isNotEmpty)
          GlassCard(
            padding: const EdgeInsets.fromLTRB(18, 14, 18, 16),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text('What you did well 🌟', style: head(16, color: Sea.cyan)),
              const SizedBox(height: 6),
              for (final d in didWell)
                Padding(
                  padding: const EdgeInsets.only(bottom: 4),
                  child: Text('• $d', style: const TextStyle(color: Sea.foam, height: 1.3)),
                ),
            ]),
          ),
        if (tryNext != null && tryNext.isNotEmpty) ...[
          const SizedBox(height: 12),
          GlassCard(
            padding: const EdgeInsets.fromLTRB(18, 14, 18, 16),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text('One thing to try next 🧭', style: head(16, color: Sea.gold)),
              const SizedBox(height: 6),
              Text(tryNext, style: const TextStyle(color: Sea.foam, height: 1.3)),
            ]),
          ),
        ],
        const SizedBox(height: 16),
        _GoldButton(label: 'Back to my Voyage', onTap: () => Navigator.of(context).maybePop()),
      ],
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
