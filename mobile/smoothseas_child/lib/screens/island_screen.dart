import 'package:flutter/material.dart';

import '../api.dart';
import '../theme.dart';
import 'explainer_screen.dart';

/// An island's mini-voyage — mirrors voyage/island.blade.php: nav brand + "Back to
/// the sea", a big title + progress line, the interior map with numbered stops, and
/// the "Stops on this island" legend.
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
    await Navigator.of(context).push(MaterialPageRoute(
      builder: (_) => ExplainerScreen(
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
            return Column(
              children: [
                _navBar(island),
                Expanded(
                  child: ListView(
                    padding: const EdgeInsets.fromLTRB(28, 14, 28, 24),
                    children: [
                      Text('${island['name']}', textAlign: TextAlign.center, style: head(30.4, height: 1.0)),
                      const SizedBox(height: 6),
                      Text('${island['conquered']} of ${island['total']} levels conquered — clear them in order to master this island.',
                          textAlign: TextAlign.center, style: const TextStyle(color: Sea.muted, fontSize: 15.2, height: 1.3)),
                      const SizedBox(height: 22),
                      _InteriorMap(slug: widget.slug, levels: levels, onPlay: _play),
                      const SizedBox(height: 16),
                      _legend(levels),
                    ],
                  ),
                ),
              ],
            );
          },
        ),
      ),
    );
  }

  Widget _navBar(Map<String, dynamic> island) => Container(
        height: 64,
        color: const Color(0x8C0C1432),
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            Flexible(child: Text('${island['icon']} ${island['name']}', style: head(20.8), overflow: TextOverflow.ellipsis)),
            GestureDetector(
              onTap: () => Navigator.of(context).pop(),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                decoration: BoxDecoration(color: const Color(0x14FFFFFF), border: Border.all(color: const Color(0x59FFFFFF), width: 1.5), borderRadius: BorderRadius.circular(999)),
                child: const Text('← Back to the sea', style: TextStyle(color: Sea.ink, fontWeight: FontWeight.w800, fontSize: 13)),
              ),
            ),
          ],
        ),
      );

  Widget _legend(List<dynamic> levels) {
    return Container(
      decoration: BoxDecoration(
        color: const Color(0x8C0C1432),
        border: Border.all(color: const Color(0x4793C5FD), width: 1.5),
        borderRadius: BorderRadius.circular(18),
      ),
      padding: const EdgeInsets.fromLTRB(16, 14, 16, 16),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text('Stops on this island', style: head(14, color: const Color(0xFFCFE6FB))),
        const SizedBox(height: 10),
        for (var i = 0; i < levels.length; i++) ...[
          if (i > 0) const SizedBox(height: 7),
          _legendRow(i + 1, levels[i] as Map<String, dynamic>),
        ],
      ]),
    );
  }

  Widget _legendRow(int number, Map<String, dynamic> level) {
    final mastered = level['mastered'] == true;
    final review = level['review'] == true;
    final locked = level['locked'] == true;
    final current = level['current'] == true;
    final (status, statusBg, statusFg) = mastered
        ? ('Conquered', const Color(0x3834D399), const Color(0xFF6EE7B7))
        : review
            ? ('Needs review', const Color(0x3DFDE68A), const Color(0xFFFDE68A))
            : current
                ? ('Current', const Color(0x3DFDE68A), const Color(0xFFFDE68A))
                : ('Locked', const Color(0x40949BB4), const Color(0xFFCBD5E1));
    return Opacity(
      opacity: locked ? 0.6 : 1,
      child: GestureDetector(
        onTap: () => _play(level),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
          decoration: BoxDecoration(color: const Color(0x0AFFFFFF), borderRadius: BorderRadius.circular(12)),
          child: Row(children: [
            Container(
              width: 26,
              padding: const EdgeInsets.symmetric(vertical: 2),
              alignment: Alignment.center,
              decoration: BoxDecoration(color: const Color(0xB3090E22), borderRadius: BorderRadius.circular(999)),
              child: Text('$number', style: head(13, color: Sea.ink)),
            ),
            const SizedBox(width: 9),
            Expanded(child: Text('${level['topic']}', style: const TextStyle(color: Sea.ink, fontSize: 13.1, fontWeight: FontWeight.w700, height: 1.2))),
            const SizedBox(width: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
              decoration: BoxDecoration(color: statusBg, borderRadius: BorderRadius.circular(999)),
              child: Text(status.toUpperCase(), style: TextStyle(color: statusFg, fontSize: 10.6, fontWeight: FontWeight.w800, letterSpacing: 0.4)),
            ),
          ]),
        ),
      ),
    );
  }
}

/// The island's painted interior with numbered stops (badge + number chip + boat).
class _InteriorMap extends StatelessWidget {
  const _InteriorMap({required this.slug, required this.levels, required this.onPlay});
  final String slug;
  final List<dynamic> levels;
  final void Function(Map<String, dynamic>) onPlay;

  @override
  Widget build(BuildContext context) {
    return DecoratedBox(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(20),
        boxShadow: const [BoxShadow(color: Color(0x40000000), blurRadius: 24, offset: Offset(0, 10))],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(20),
        child: InteractiveViewer(
          minScale: 1,
          maxScale: 4,
          boundaryMargin: const EdgeInsets.all(40),
          child: AspectRatio(
            aspectRatio: 2752 / 1536,
            child: LayoutBuilder(
              builder: (context, c) {
                final w = c.maxWidth, h = c.maxHeight;
                return Stack(clipBehavior: Clip.none, children: [
                  Positioned.fill(
                    child: Image.asset(
                      'assets/images/voyage/interiors/$slug.webp',
                      fit: BoxFit.cover,
                      errorBuilder: (_, _, _) => Container(color: Sea.ocean),
                    ),
                  ),
                  for (var i = 0; i < levels.length; i++) _stop(levels[i] as Map<String, dynamic>, i + 1, w, h),
                  for (final l in levels.cast<Map<String, dynamic>>())
                    if (l['current'] == true) _boat(l, w, h),
                ]);
              },
            ),
          ),
        ),
      ),
    );
  }

  Widget _stop(Map<String, dynamic> level, int number, double w, double h) {
    final xv = level['x'], yv = level['y'];
    if (xv == null || yv == null) return const SizedBox.shrink();
    final x = (xv as num).toDouble() / 100 * w;
    final y = (yv as num).toDouble() / 100 * h;
    final mastered = level['mastered'] == true;
    final review = level['review'] == true;
    final locked = level['locked'] == true;
    final current = level['current'] == true;
    final badge = (6.5 * w / 100).clamp(16.0, 52.0);
    final iconSz = (3.4 * w / 100).clamp(9.0, 26.0);
    final numSz = (2.6 * w / 100).clamp(7.0, 15.0);
    final glyph = locked ? '🔒' : (mastered || review ? '⭐' : (current ? '▶' : '🔒'));
    return Positioned(
      left: x - badge / 2 - 6,
      top: y - (badge + 16) / 2,
      width: badge + 12,
      child: GestureDetector(
        onTap: () => onPlay(level),
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          Container(
            width: badge, height: badge,
            alignment: Alignment.center,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: const Color(0xB8141E42),
              border: Border.all(
                color: locked
                    ? const Color(0x40FFFFFF)
                    : current
                        ? const Color(0xFFFDE68A)
                        : mastered
                            ? const Color(0xFF34D399)
                            : const Color(0x9993C5FD),
                width: 2.5,
              ),
              boxShadow: current
                  ? const [BoxShadow(color: Color(0xE6FDE68A), blurRadius: 12)]
                  : mastered
                      ? const [BoxShadow(color: Color(0xB334D399), blurRadius: 10)]
                      : const [BoxShadow(color: Color(0x66000000), blurRadius: 8)],
            ),
            child: Opacity(opacity: locked ? 0.7 : 1, child: Text(glyph, style: TextStyle(fontSize: iconSz, height: 1.0))),
          ),
          const SizedBox(height: 3),
          Container(
            padding: EdgeInsets.symmetric(horizontal: numSz * 0.55, vertical: numSz * 0.15),
            decoration: BoxDecoration(color: const Color(0xD1090E22), borderRadius: BorderRadius.circular(999)),
            child: Text('$number', style: TextStyle(fontFamily: 'FredokaOne', fontSize: numSz, color: const Color(0xFFF8FAFC), height: 1.0)),
          ),
        ]),
      ),
    );
  }

  Widget _boat(Map<String, dynamic> level, double w, double h) {
    final xv = level['x'], yv = level['y'];
    if (xv == null || yv == null) return const SizedBox.shrink();
    final x = (xv as num).toDouble() / 100 * w;
    final y = (yv as num).toDouble() / 100 * h;
    final sz = (5.0 * w / 100).clamp(14.0, 40.0);
    return Positioned(
      left: x - sz / 2,
      top: y - (6.5 * w / 100).clamp(16.0, 52.0) / 2 - sz,
      child: Text('⛵', style: TextStyle(fontSize: sz, height: 1.0)),
    );
  }
}
