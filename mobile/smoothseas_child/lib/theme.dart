import 'package:flutter/material.dart';

/// SmoothSeas "Voyage" palette — mirrors the web ss-* tokens (brand/head.blade.php).
class Sea {
  static const deep = Color(0xFF06182E);
  static const navy = Color(0xFF0B2A4A);
  static const ocean = Color(0xFF0E4D6E);
  static const teal = Color(0xFF0E7490);
  static const sea = Color(0xFF0D9488);
  static const cyan = Color(0xFF22D3EE);
  static const aqua = Color(0xFF67E8F9);
  static const foam = Color(0xFFECFEFF);
  static const gold = Color(0xFFF6B71E);
  static const goldDeep = Color(0xFFD97706);
  static const ink = Color(0xFFE6F2FB);
  static const muted = Color(0xFF93B2CC);

  /// The deep-ocean vertical gradient behind every child screen.
  static const gradient = LinearGradient(
    begin: Alignment.topCenter,
    end: Alignment.bottomCenter,
    colors: [deep, navy, ocean, teal],
    stops: [0.0, 0.38, 0.72, 1.0],
  );

  static const cardFill = Color(0x9E091E36); // rgba(9,30,54,0.62)
  static const cardBorder = Color(0x4767E8F9); // rgba(103,232,249,0.28)
}

/// App-wide theme: Fredoka display + Nunito body, on the sea palette.
ThemeData buildSeaTheme() {
  final base = ThemeData(brightness: Brightness.dark, useMaterial3: true);
  final textTheme = base.textTheme.apply(
    fontFamily: 'Nunito',
    bodyColor: Sea.ink,
    displayColor: Sea.foam,
  );
  return base.copyWith(
    scaffoldBackgroundColor: Sea.navy,
    colorScheme: base.colorScheme.copyWith(primary: Sea.cyan, secondary: Sea.gold, surface: Sea.navy),
    textTheme: textTheme,
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: const Color(0x33061830),
      hintStyle: const TextStyle(color: Sea.muted),
      labelStyle: const TextStyle(color: Sea.muted),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: Sea.cardBorder),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: Sea.cyan, width: 1.5),
      ),
    ),
  );
}

/// A Fredoka One display heading — the web brand head font (--ss-font-head).
TextStyle head(double size, {Color color = Sea.foam, FontWeight weight = FontWeight.w400, double? height}) =>
    TextStyle(fontFamily: 'FredokaOne', fontSize: size, fontWeight: weight, color: color, height: height);

/// Deep-ocean background — mirrors the web `.ss-body`: the sea gradient, a warm
/// gold horizon glow near the top, and a faint cyan nautical chart grid (64px).
class SeaBackground extends StatelessWidget {
  const SeaBackground({super.key, required this.child});
  final Widget child;

  @override
  Widget build(BuildContext context) {
    return Stack(
      children: [
        // Base sea gradient + glow + chart grid + waves (the .ss-body + .ss-sea scene).
        const Positioned.fill(child: DecoratedBox(decoration: BoxDecoration(gradient: Sea.gradient))),
        Positioned.fill(child: CustomPaint(painter: _SeaScenePainter())),
        // Floating atmosphere (initial, unanimated positions — matches prefers-reduced-motion).
        const _Float(emoji: '⛵', size: 30, left: 0.15, top: 0.21),
        const _Float(emoji: '🏝️', size: 30, right: 0.12, top: 0.27),
        const _Float(emoji: '🌴', size: 26, left: 0.09, bottom: 0.30),
        child,
      ],
    );
  }
}

/// A drop-shadowed floating decoration positioned by viewport fraction.
class _Float extends StatelessWidget {
  const _Float({required this.emoji, required this.size, this.left, this.right, this.top, this.bottom});
  final String emoji;
  final double size;
  final double? left, right, top, bottom;

  @override
  Widget build(BuildContext context) {
    final m = MediaQuery.sizeOf(context);
    return Positioned(
      left: left == null ? null : left! * m.width,
      right: right == null ? null : right! * m.width,
      top: top == null ? null : top! * m.height,
      bottom: bottom == null ? null : bottom! * m.height,
      child: Text(emoji, style: TextStyle(fontSize: size, shadows: const [Shadow(color: Color(0x73000000), blurRadius: 12, offset: Offset(0, 8))])),
    );
  }
}

class _SeaScenePainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final w = size.width, h = size.height;

    // .ss-body faint horizon glow: radial(70% 45% at 50% 8%, gold 0.14 -> transparent 62%).
    canvas.drawRect(
      Offset.zero & size,
      Paint()
        ..shader = RadialGradient(colors: const [Color(0x14F6B71E), Color(0x00F6B71E)], stops: const [0.0, 0.62])
            .createShader(Rect.fromCenter(center: Offset(w * 0.5, h * 0.08), width: w * 1.4, height: h * 0.9)),
    );

    // Nautical chart grid: 1px cyan lines every 64px, rgba(103,232,249,0.045).
    final line = Paint()..color = const Color(0x0C67E8F9)..strokeWidth = 1;
    for (double x = 0; x <= w; x += 64) {
      canvas.drawLine(Offset(x, 0), Offset(x, h), line);
    }
    for (double y = 0; y <= h; y += 64) {
      canvas.drawLine(Offset(0, y), Offset(w, y), line);
    }

    // .ss-sun: 180px circle at top:9%, radial gold 0.5 -> 0 at 70%.
    final sun = Offset(w * 0.5, h * 0.09);
    canvas.drawCircle(
      sun,
      90,
      Paint()
        ..shader = RadialGradient(colors: const [Color(0x4DF6B71E), Color(0x00F6B71E)], stops: const [0.0, 0.7])
            .createShader(Rect.fromCircle(center: sun, radius: 90)),
    );

    // .ss-waves: three layered foam waves along the bottom (~30vh), initial (undrifted) frame.
    final waveH = h * 0.30;
    final top = h - waveH;
    double y(double vb) => top + vb / 120 * waveH;
    void wave(double s, double c1, double c2, double e, Color color) {
      final p = Path()
        ..moveTo(0, y(s))
        ..cubicTo(w / 3, y(c1), 2 * w / 3, y(c2), w, y(e))
        ..lineTo(w, h)
        ..lineTo(0, h)
        ..close();
      canvas.drawPath(p, Paint()..color = color);
    }

    wave(50, 110, 10, 50, const Color(0x8C0E7490)); // w1 teal .55
    wave(60, 20, 100, 60, const Color(0x660D9488)); // w2 sea .40
    wave(70, 110, 40, 70, const Color(0x4D22D3EE)); // w3 cyan .30
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

/// A translucent "glass" card, like the web's .ss-card.
class GlassCard extends StatelessWidget {
  const GlassCard({super.key, required this.child, this.padding = const EdgeInsets.all(18), this.onTap});
  final Widget child;
  final EdgeInsets padding;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Sea.cardFill,
      borderRadius: BorderRadius.circular(20),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(20),
        child: Container(
          padding: padding,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: Sea.cardBorder, width: 1.5),
          ),
          child: child,
        ),
      ),
    );
  }
}

/// The gold "treasure" primary button (Fredoka, dark text) — the web's .ss-btn.
class GoldButton extends StatelessWidget {
  const GoldButton({super.key, required this.label, this.onPressed, this.expand = true});
  final String label;
  final VoidCallback? onPressed;
  final bool expand;

  @override
  Widget build(BuildContext context) {
    final btn = DecoratedBox(
      decoration: BoxDecoration(
        gradient: onPressed == null
            ? null
            : const LinearGradient(colors: [Color(0xFFFCD34D), Color(0xFFF59E0B)]),
        color: onPressed == null ? const Color(0x55F6B71E) : null,
        borderRadius: BorderRadius.circular(999),
      ),
      child: TextButton(
        onPressed: onPressed,
        style: TextButton.styleFrom(
          padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 26),
          foregroundColor: Sea.deep,
        ),
        child: Text(label, style: head(17, color: Sea.deep)),
      ),
    );
    return expand ? SizedBox(width: double.infinity, child: btn) : btn;
  }
}
