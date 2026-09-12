import 'package:flutter/material.dart';

import '../api.dart';
import '../theme.dart';
import 'practice_screen.dart';

/// An island's levels (its mini-voyage). Tap a level to practise it.
class IslandScreen extends StatefulWidget {
  const IslandScreen({super.key, required this.slug, required this.name});
  final String slug;
  final String name;

  @override
  State<IslandScreen> createState() => _IslandScreenState();
}

class _IslandScreenState extends State<IslandScreen> {
  late Future<Map<String, dynamic>> _future;

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<Map<String, dynamic>> _load() async => (await api.getJson('/child/island/${widget.slug}')) as Map<String, dynamic>;

  Future<void> _play(Map<String, dynamic> level) async {
    try {
      final session = await api.postJson('/child/practice/start', {'mission_id': level['mission_id']}) as Map<String, dynamic>;
      if (!mounted) return;
      await Navigator.of(context).push(MaterialPageRoute(builder: (_) => PracticeScreen(session: session)));
      if (mounted) setState(() => _future = _load());
    } on ApiException catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
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
              final island = d['island'] as Map<String, dynamic>;
              final levels = (d['levels'] as List<dynamic>);
              return ListView(
                padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
                children: [
                  Row(children: [
                    IconButton(icon: const Icon(Icons.arrow_back, color: Sea.foam), onPressed: () => Navigator.of(context).pop()),
                    Text('${island['icon']} ${island['name']}', style: head(20)),
                  ]),
                  const SizedBox(height: 4),
                  Padding(
                    padding: const EdgeInsets.only(left: 12),
                    child: Text('${island['conquered']} / ${island['total']} conquered', style: const TextStyle(color: Sea.muted)),
                  ),
                  const SizedBox(height: 14),
                  for (final l in levels) _LevelCard(level: l as Map<String, dynamic>, onPlay: _play),
                ],
              );
            },
          ),
        ),
      ),
    );
  }
}

class _LevelCard extends StatelessWidget {
  const _LevelCard({required this.level, required this.onPlay});
  final Map<String, dynamic> level;
  final void Function(Map<String, dynamic>) onPlay;

  @override
  Widget build(BuildContext context) {
    final mastered = level['mastered'] == true;
    final review = level['review'] == true;
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: GlassCard(
        onTap: () => onPlay(level),
        padding: const EdgeInsets.all(14),
        child: Row(children: [
          Icon(mastered ? Icons.check_circle : (review ? Icons.refresh : Icons.play_circle_fill),
              color: mastered ? const Color(0xFF6EE7B7) : (review ? Sea.gold : Sea.cyan), size: 26),
          const SizedBox(width: 12),
          Expanded(
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text('${level['topic']}', style: const TextStyle(color: Sea.foam, fontWeight: FontWeight.w700, fontSize: 15)),
              Text('${level['subject']}${mastered ? ' · mastered' : (review ? ' · review' : '')}', style: const TextStyle(color: Sea.muted, fontSize: 12)),
            ]),
          ),
          const Icon(Icons.chevron_right, color: Sea.muted),
        ]),
      ),
    );
  }
}
