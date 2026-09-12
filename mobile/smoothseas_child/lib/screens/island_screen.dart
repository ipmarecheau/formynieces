import 'package:flutter/material.dart';

import '../api.dart';
import '../theme.dart';
import 'lesson_screen.dart';

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
    if (level['locked'] == true) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Master the earlier levels first, Captain.')));
      return;
    }
    // Level → lesson (teaching) → practice, mirroring the web's gated sequence.
    await Navigator.of(context).push(MaterialPageRoute(
      builder: (_) => LessonScreen(
        moduleId: level['id'] as int,
        missionId: level['mission_id'] as String,
        topic: level['topic'] as String,
      ),
    ));
    if (mounted) setState(() => _future = _load());
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
                  const SizedBox(height: 12),
                  _InteriorMap(slug: widget.slug, levels: levels, onPlay: _play),
                  const SizedBox(height: 16),
                  Text('Levels', style: head(18)),
                  const SizedBox(height: 8),
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

/// The island's painted interior with level stops placed by x/y% (mirrors web tier-2).
class _InteriorMap extends StatelessWidget {
  const _InteriorMap({required this.slug, required this.levels, required this.onPlay});
  final String slug;
  final List<dynamic> levels;
  final void Function(Map<String, dynamic>) onPlay;

  @override
  Widget build(BuildContext context) {
    return ClipRRect(
      borderRadius: BorderRadius.circular(20),
      child: Container(
        decoration: BoxDecoration(borderRadius: BorderRadius.circular(20), border: Border.all(color: Sea.cardBorder, width: 1.5)),
        child: InteractiveViewer(
          minScale: 1,
          maxScale: 4,
          boundaryMargin: const EdgeInsets.all(40),
          child: AspectRatio(
            aspectRatio: 2752 / 1536,
            child: LayoutBuilder(
              builder: (context, c) {
                final w = c.maxWidth, h = c.maxHeight;
                return Stack(children: [
                  Positioned.fill(
                    child: Image.asset(
                      'assets/images/voyage/interiors/$slug.webp',
                      fit: BoxFit.cover,
                      errorBuilder: (_, _, _) => Container(color: Sea.ocean),
                    ),
                  ),
                  for (var i = 0; i < levels.length; i++)
                    _stop(levels[i] as Map<String, dynamic>, i, w, h),
                ]);
              },
            ),
          ),
        ),
      ),
    );
  }

  Widget _stop(Map<String, dynamic> level, int i, double w, double h) {
    final xv = level['x'], yv = level['y'];
    if (xv == null || yv == null) return const SizedBox.shrink();
    final x = (xv as num).toDouble() / 100 * w;
    final y = (yv as num).toDouble() / 100 * h;
    final mastered = level['mastered'] == true;
    final locked = level['locked'] == true;
    final current = level['current'] == true;
    return Positioned(
      left: x - 16,
      top: y - 16,
      child: GestureDetector(
        onTap: () => onPlay(level),
        child: Container(
          width: 32,
          height: 32,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            color: locked ? const Color(0xCC0B2A4A) : (mastered ? const Color(0xCC15803D) : const Color(0xCC0E7490)),
            border: Border.all(color: current ? Sea.gold : (locked ? Sea.muted : Sea.aqua), width: current ? 2.5 : 1.5),
            boxShadow: current ? const [BoxShadow(color: Sea.gold, blurRadius: 9)] : null,
          ),
          alignment: Alignment.center,
          child: locked
              ? const Icon(Icons.lock, size: 15, color: Sea.foam)
              : (mastered ? const Icon(Icons.check, size: 16, color: Sea.foam) : Text('${i + 1}', style: head(13, color: Sea.foam))),
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
    final locked = level['locked'] == true;
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Opacity(
        opacity: locked ? 0.55 : 1,
        child: GlassCard(
          onTap: () => onPlay(level),
          padding: const EdgeInsets.all(14),
          child: Row(children: [
            Icon(
              locked ? Icons.lock : (mastered ? Icons.check_circle : (review ? Icons.refresh : Icons.play_circle_fill)),
              color: locked ? Sea.muted : (mastered ? const Color(0xFF6EE7B7) : (review ? Sea.gold : Sea.cyan)),
              size: 26,
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text('${level['topic']}', style: const TextStyle(color: Sea.foam, fontWeight: FontWeight.w700, fontSize: 15)),
                Text('${level['subject']}${mastered ? ' · mastered' : (review ? ' · review' : (locked ? ' · locked' : ''))}', style: const TextStyle(color: Sea.muted, fontSize: 12)),
              ]),
            ),
            Icon(locked ? Icons.lock : Icons.chevron_right, color: Sea.muted, size: locked ? 18 : 24),
          ]),
        ),
      ),
    );
  }
}
