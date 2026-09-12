import 'package:flutter/material.dart';

import '../theme.dart';

/// The painted overworld map with pan/zoom and island markers — mirrors the web /voyage map.
class VoyageMap extends StatelessWidget {
  const VoyageMap({super.key, required this.islands, required this.onOpen});
  final List<dynamic> islands;
  final void Function(Map<String, dynamic>) onOpen;

  @override
  Widget build(BuildContext context) {
    return ClipRRect(
      borderRadius: BorderRadius.circular(20),
      child: Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: Sea.cardBorder, width: 1.5),
        ),
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
                      children: [
                        Positioned.fill(
                          child: Image.asset('assets/images/voyage/overworld.webp', fit: BoxFit.cover),
                        ),
                        for (var i = 0; i < islands.length; i++)
                          _marker(islands[i] as Map<String, dynamic>, i + 1, w, h),
                      ],
                    );
                  },
                ),
              ),
            ),
            const Positioned(
              left: 10,
              top: 10,
              child: _Hint(),
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

    return Positioned(
      left: x - 17,
      top: y - 17,
      child: GestureDetector(
        onTap: () => onOpen(island),
        child: Container(
          width: 34,
          height: 34,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            color: locked ? const Color(0xCC0B2A4A) : (mastered ? const Color(0xCC15803D) : const Color(0xCC0E7490)),
            border: Border.all(color: current ? Sea.gold : Sea.aqua, width: current ? 2.5 : 1.5),
            boxShadow: current ? const [BoxShadow(color: Sea.gold, blurRadius: 10)] : null,
          ),
          alignment: Alignment.center,
          child: locked
              ? const Icon(Icons.lock, size: 15, color: Sea.foam)
              : (mastered
                  ? const Icon(Icons.check, size: 17, color: Sea.foam)
                  : Text('$number', style: head(14, color: Sea.foam))),
        ),
      ),
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
