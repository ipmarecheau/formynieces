import 'package:flutter/material.dart';

/// LL-08 — the learning-loop route map (web <x-loop-rail>): a fixed board down the
/// left of every module screen showing Check → Lesson → Practice → Mastered, with
/// the travelled nodes/edges lit and a turtle riding beside the current stage.
class LoopRail extends StatelessWidget {
  const LoopRail({super.key, this.stage = 'lesson'});
  final String stage; // check | lesson | practice | mastered

  static const _order = ['check', 'lesson', 'practice', 'mastered'];
  static const _labels = ['Check', 'Lesson', 'Practice', 'Mastered'];
  static const _ys = [0.12, 0.42, 0.66, 0.90];

  @override
  Widget build(BuildContext context) {
    final h = MediaQuery.sizeOf(context).height;
    final railH = (h * 0.42).clamp(344.0, 460.0);
    const railW = 94.0;
    final cur = _order.indexOf(stage).clamp(0, 3);
    final mastered = stage == 'mastered';

    return Positioned(
      left: 6,
      top: (h - railH) / 2,
      child: SizedBox(
        width: railW,
        height: railH,
        child: Stack(
          clipBehavior: Clip.none,
          children: [
            // Board.
            Positioned.fill(
              child: DecoratedBox(
                decoration: BoxDecoration(
                  gradient: const LinearGradient(begin: Alignment.topCenter, end: Alignment.bottomCenter, colors: [Color(0xD9081828), Color(0xD906121E)]),
                  border: Border.all(color: const Color(0x2E78B4DC)),
                  borderRadius: BorderRadius.circular(18),
                  boxShadow: const [BoxShadow(color: Color(0x6B000000), blurRadius: 36, offset: Offset(0, 14))],
                ),
              ),
            ),
            // Edges behind the nodes.
            Positioned.fill(child: CustomPaint(painter: _RailEdges(cur: cur, railW: railW, railH: railH))),
            // Nodes.
            for (var i = 0; i < 4; i++) _node(i, cur, railW, railH),
            // Turtle token beside the current node.
            Positioned(
              left: railW * 0.5 + 8,
              top: _ys[cur] * railH - 14,
              child: Text(mastered ? '🏁' : '🐢', style: const TextStyle(fontSize: 22, shadows: [Shadow(color: Color(0x80000000), blurRadius: 4, offset: Offset(0, 2))])),
            ),
          ],
        ),
      ),
    );
  }

  Widget _node(int i, int cur, double railW, double railH) {
    final state = i < cur ? 'done' : (i == cur ? 'now' : 'dim');
    final done = state == 'done';
    final now = state == 'now';
    final (bg, border, fg) = done
        ? (const Color(0x2957D6A0), const Color(0xFF57D6A0), const Color(0xFF57D6A0))
        : now
            ? (const Color(0x29F6B71E), const Color(0xFFF6B71E), const Color(0xFFF6B71E))
            : (const Color(0xE60A1A2A), const Color(0xFF3A6A86), const Color(0xFF6F93AD));
    final num = done ? '✓' : (i == 3 ? '⭐' : '${i + 1}');
    const nodeW = 54.0, nodeH = 44.0;
    return Positioned(
      left: (railW - nodeW) / 2,
      top: _ys[i] * railH - nodeH / 2,
      child: Container(
        width: nodeW,
        constraints: const BoxConstraints(minHeight: nodeH),
        padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 6),
        decoration: BoxDecoration(
          color: bg,
          border: Border.all(color: border, width: 1.5),
          borderRadius: BorderRadius.circular(13),
          boxShadow: now ? const [BoxShadow(color: Color(0x29F6B71E), blurRadius: 0, spreadRadius: 5)] : null,
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(num, style: TextStyle(fontFamily: 'FredokaOne', fontSize: 17, height: 1.0, color: fg)),
            const SizedBox(height: 2),
            Text(_labels[i], textAlign: TextAlign.center, style: TextStyle(fontSize: 9, fontWeight: FontWeight.w800, color: fg)),
          ],
        ),
      ),
    );
  }
}

class _RailEdges extends CustomPainter {
  _RailEdges({required this.cur, required this.railW, required this.railH});
  final int cur;
  final double railW, railH;
  static const _ys = LoopRail._ys;

  @override
  void paint(Canvas canvas, Size size) {
    final cx = railW / 2;
    double y(int i) => _ys[i] * railH;
    final faint = Paint()
      ..color = const Color(0xFF33546C)
      ..strokeWidth = 2.4
      ..style = PaintingStyle.stroke;
    // Base main segments (faint dashed look approximated as thin solid).
    for (var i = 0; i < 3; i++) {
      canvas.drawLine(Offset(cx, y(i)), Offset(cx, y(i + 1)), faint);
    }
    // Faint direct bow (1->4) and loopback (3->2).
    canvas.drawPath(Path()..moveTo(cx, y(0))..cubicTo(cx + 30, y(0) + 22 * railH / 100, cx + 30, y(3) - 22 * railH / 100, cx, y(3)), faint);
    canvas.drawPath(Path()..moveTo(cx, y(2))..cubicTo(cx - 28, y(2) - 6 * railH / 100, cx - 28, y(1) + 6 * railH / 100, cx, y(1)), faint);
    // Lit travelled main segments up to current.
    final lit = Paint()
      ..color = const Color(0xFF57D6A0)
      ..strokeWidth = 3.4
      ..style = PaintingStyle.stroke;
    for (var i = 0; i < cur; i++) {
      canvas.drawLine(Offset(cx, y(i)), Offset(cx, y(i + 1)), lit);
    }
  }

  @override
  bool shouldRepaint(covariant _RailEdges oldDelegate) => oldDelegate.cur != cur;
}
