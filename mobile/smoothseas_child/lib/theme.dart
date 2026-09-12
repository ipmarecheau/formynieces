import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

/// SmoothSeas "Voyage" palette — mirrors the web ss-* tokens (brand/head.blade.php).
class Sea {
  static const deep = Color(0xFF06182E);
  static const navy = Color(0xFF0B2A4A);
  static const ocean = Color(0xFF0E4D6E);
  static const teal = Color(0xFF0E7490);
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
  final textTheme = GoogleFonts.nunitoTextTheme(base.textTheme).apply(
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

/// A Fredoka display heading.
TextStyle head(double size, {Color color = Sea.foam, FontWeight weight = FontWeight.w600}) =>
    GoogleFonts.fredoka(fontSize: size, fontWeight: weight, color: color);

/// Deep-ocean gradient background wrapper.
class SeaBackground extends StatelessWidget {
  const SeaBackground({super.key, required this.child});
  final Widget child;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(gradient: Sea.gradient),
      child: child,
    );
  }
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
