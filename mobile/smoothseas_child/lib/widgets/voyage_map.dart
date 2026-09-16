import 'package:flutter/material.dart';

import '../theme.dart';

/// The painted overworld map with pan/zoom and island markers — mirrors the web /voyage map.
class VoyageMap extends StatelessWidget {
  const VoyageMap({super.key, required this.islands, required this.onOpen});
  final List<dynamic> islands;
  final void Function(Map<String, dynamic>) onOpen;

  @override
  Widget build(BuildContext context) {
    // No border — web .vy-map is border-radius + shadow only; a border would inset
    // and mis-scale the illustration, ghosting every coastline against the web map.
    return DecoratedBox(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(20),
        boxShadow: const [BoxShadow(color: Color(0x40000000), blurRadius: 24, offset: Offset(0, 10))],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(20),
        child: Stack(
          children: [
            InteractiveViewer(
              minScale: 1,
              maxScale: 4,
              boundaryMargin: const EdgeInsets.all(40),
              child: AspectRatio(
                aspectRatio: 2752 / 1536,
                child: LayoutBuilder(
                  builder: (context, c) {
                    final w = c.maxWidth, h = c.maxHeight;
                    return Stack(
                      clipBehavior: Clip.none,
                      children: [
                        Positioned.fill(
                          child: Image.asset('assets/images/voyage/overworld.webp', fit: BoxFit.cover),
                        ),
                        for (var i = 0; i < islands.length; i++)
                          _marker(islands[i] as Map<String, dynamic>, i + 1, w, h),
                        for (final isl in islands.cast<Map<String, dynamic>>())
                          if (isl['current'] == true) _boat(isl, w, h),
                      ],
                    );
                  },
                ),
              ),
            ),
            const Positioned(left: 10, top: 10, child: _Hint()),
            // Zoom + locate controls (web .mv-zoom / Find me).
            const Positioned(right: 10, top: 55, child: _ZoomBtn(icon: '+')),
            const Positioned(right: 10, top: 105, child: _ZoomBtn(icon: '–')),
            Positioned(
              right: 10, bottom: 10,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                decoration: BoxDecoration(color: const Color(0xB30B2A4A), borderRadius: BorderRadius.circular(999)),
                child: const Row(mainAxisSize: MainAxisSize.min, children: [
                  Text('🐢', style: TextStyle(fontSize: 14)),
                  SizedBox(width: 6),
                  Text('Find me', style: TextStyle(color: Sea.foam, fontSize: 13, fontWeight: FontWeight.w700)),
                ]),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _marker(Map<String, dynamic> island, int number, double w, double h) {
    final x = (island['x'] as num).toDouble() / 100 * w;
    final y = (island['y'] as num).toDouble() / 100 * h;
    final state = island['state'] as String;
    final locked = state == 'locked';
    final mastered = state == 'mastered';
    final current = island['current'] == true;
    // Web .vy-badge = min(6.5cqw, 52); icon min(3.6cqw, 28); number min(2.6cqw, 15).
    final badge = (6.5 * w / 100).clamp(16.0, 52.0);
    final iconSz = (3.6 * w / 100).clamp(9.0, 28.0);
    final numSz = (2.6 * w / 100).clamp(7.0, 15.0);

    // Web centres the whole badge+number group at (x,y) (translate -50%,-50%).
    return Positioned(
      left: x - badge / 2 - 6,
      top: y - (badge + 16) / 2,
      width: badge + 12,
      child: GestureDetector(
        onTap: () => onOpen(island),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: badge,
              height: badge,
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
              alignment: Alignment.center,
              child: Opacity(
                opacity: locked ? 0.7 : 1,
                child: Text(locked ? '🔒' : '${island['icon']}', style: TextStyle(fontSize: iconSz, height: 1.0)),
              ),
            ),
            const SizedBox(height: 3),
            Container(
              padding: EdgeInsets.symmetric(horizontal: numSz * 0.55, vertical: numSz * 0.15),
              decoration: BoxDecoration(color: const Color(0xD1090E22), borderRadius: BorderRadius.circular(999)),
              child: Text('$number', style: TextStyle(fontFamily: 'FredokaOne', fontSize: numSz, color: const Color(0xFFF8FAFC), height: 1.0)),
            ),
          ],
        ),
      ),
    );
  }

  /// The ⛵ that rides above the current island (web .vy-boat, translate -140%).
  Widget _boat(Map<String, dynamic> island, double w, double h) {
    final x = (island['x'] as num).toDouble() / 100 * w;
    final y = (island['y'] as num).toDouble() / 100 * h;
    final sz = (5.0 * w / 100).clamp(14.0, 40.0);
    return Positioned(
      left: x - sz / 2,
      top: y - (6.5 * w / 100).clamp(16.0, 52.0) / 2 - sz,
      child: Text('⛵', style: TextStyle(fontSize: sz, height: 1.0)),
    );
  }
}

class _ZoomBtn extends StatelessWidget {
  const _ZoomBtn({required this.icon});
  final String icon;
  @override
  Widget build(BuildContext context) {
    return Container(
      width: 44, height: 44,
      alignment: Alignment.center,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        color: const Color(0xCC0B2A4A),
        border: Border.all(color: const Color(0x4067E8F9), width: 1.5),
      ),
      child: Text(icon, style: const TextStyle(color: Sea.foam, fontSize: 24, fontWeight: FontWeight.w600, height: 1.0)),
    );
  }
}

class _Hint extends StatelessWidget {
  const _Hint();
  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(color: const Color(0xB30B2A4A), borderRadius: BorderRadius.circular(999)),
      child: const Row(mainAxisSize: MainAxisSize.min, children: [
        Icon(Icons.open_with, size: 13, color: Sea.foam),
        SizedBox(width: 5),
        Text('Drag to explore', style: TextStyle(color: Sea.foam, fontSize: 11, fontWeight: FontWeight.w700)),
      ]),
    );
  }
}
