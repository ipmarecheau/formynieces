import 'package:flutter/material.dart';

import '../api.dart';
import '../theme.dart';
import 'voyage_screen.dart';

/// The return splash — mirrors the web student-splash.blade.php: an emoji banner,
/// a teal hero card greeting, her live streak pills, and the voyage CTA (SH-06).
class WelcomeBackScreen extends StatefulWidget {
  const WelcomeBackScreen({super.key});

  @override
  State<WelcomeBackScreen> createState() => _WelcomeBackScreenState();
}

class _WelcomeBackScreenState extends State<WelcomeBackScreen> {
  late Future<Map<String, dynamic>> _future;

  @override
  void initState() {
    super.initState();
    _future = api.getJson('/child/welcome-back').then((d) => d as Map<String, dynamic>);
  }

  void _sail() => Navigator.of(context).pushReplacement(MaterialPageRoute(builder: (_) => const VoyageScreen()));

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SeaBackground(
        scene: true,
        child: FutureBuilder<Map<String, dynamic>>(
            future: _future,
            builder: (context, snap) {
              if (snap.hasError) {
                WidgetsBinding.instance.addPostFrameCallback((_) => _sail());
                return const SizedBox.shrink();
              }
              if (snap.connectionState == ConnectionState.waiting) {
                return const Center(child: CircularProgressIndicator(color: Sea.gold));
              }
              final d = snap.data!;
              final streaks = d['streaks'] as Map<String, dynamic>? ?? {};
              final name = ((d['child'] as Map<String, dynamic>?)?['name'] ?? 'explorer').toString().split(' ').first;
              int s(String k) => (streaks[k] as num?)?.toInt() ?? 0;

              return Center(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.all(16),
                  child: ConstrainedBox(
                    constraints: const BoxConstraints(maxWidth: 560),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        // splash-flags
                        const Text('🎉 ⛵ 🌊 ⛵ 🎉',
                            textAlign: TextAlign.center,
                            style: TextStyle(fontSize: 28.8, letterSpacing: 8, height: 1.0)),
                        const SizedBox(height: 9.6),
                        _hero(name),
                        const SizedBox(height: 24),
                        _streaks(s),
                        const SizedBox(height: 32),
                        Center(child: _voyageButton()),
                        const SizedBox(height: 14.4),
                        const Text('Keep the streak alive today! 🌟',
                            textAlign: TextAlign.center,
                            style: TextStyle(fontSize: 12.5, color: Sea.muted, fontWeight: FontWeight.w700)),
                      ],
                    ),
                  ),
                ),
              );
            },
          ),
        ),
    );
  }

  Widget _hero(String name) => ClipRRect(
        borderRadius: BorderRadius.circular(26),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 32, horizontal: 28),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [Sea.teal, Sea.sea, Sea.ocean],
              stops: [0.0, 0.55, 1.0],
            ),
            borderRadius: BorderRadius.circular(26),
            border: Border.all(color: Sea.cardBorder, width: 1.5),
            boxShadow: const [BoxShadow(color: Color(0x66000000), blurRadius: 44, offset: Offset(0, 18))],
          ),
          child: Stack(
            children: [
              // Watermarks (⚓ top-left, 🧭 bottom-right, opacity .16).
              const Positioned(left: 0, top: -6, child: Opacity(opacity: 0.16, child: Text('⚓', style: TextStyle(fontSize: 38)))),
              const Positioned(right: 0, bottom: -6, child: Opacity(opacity: 0.16, child: Text('🧭', style: TextStyle(fontSize: 51)))),
              Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text.rich(
                    TextSpan(
                      style: head(32, color: Sea.foam, height: 1.15),
                      children: [
                        const TextSpan(text: 'Welcome back aboard, '),
                        TextSpan(text: name, style: head(32, color: Sea.gold, height: 1.15)),
                        const TextSpan(text: '! ⛵'),
                      ],
                    ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 6.4),
                  const Text('Fair winds and a following sea — your streaks are on fire! 🔥',
                      textAlign: TextAlign.center,
                      style: TextStyle(fontSize: 15.7, color: Sea.foam, height: 1.35)),
                ],
              ),
            ],
          ),
        ),
      );

  Widget _streaks(int Function(String) s) {
    final rows = <Widget>[];
    if (s('practice') > 0) rows.add(_streakPill('🔥', '${s('practice')} day practice streak', Sea.gold));
    if (s('login') > 0) rows.add(_streakPill('🧭', '${s('login')} day login streak', Sea.cyan));
    if (s('mastery') > 0) rows.add(_streakPill('🏆', '${s('mastery')} day mastery streak', Sea.gold));
    if (s('pace_weeks') > 0) rows.add(_streakPill('🗺️', '${s('pace_weeks')} week on-pace streak', const Color(0xFF6EE7B7)));
    return Column(
      children: [
        for (var i = 0; i < rows.length; i++) ...[
          if (i > 0) const SizedBox(height: 12),
          rows[i],
        ],
      ],
    );
  }

  Widget _streakPill(String emoji, String label, Color color) => Container(
        padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 22),
        decoration: BoxDecoration(
          color: Sea.cardFill,
          borderRadius: BorderRadius.circular(999),
          border: Border.all(color: Sea.cardBorder, width: 1.5),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(emoji, style: const TextStyle(fontSize: 20.8)),
            const SizedBox(width: 10),
            Text(label, style: TextStyle(color: color, fontWeight: FontWeight.w800, fontSize: 16)),
          ],
        ),
      );

  Widget _voyageButton() => DecoratedBox(
        decoration: BoxDecoration(
          gradient: const LinearGradient(begin: Alignment.topLeft, end: Alignment.bottomRight, colors: [Color(0xFFFCD34D), Color(0xFFF59E0B)]),
          borderRadius: BorderRadius.circular(999),
          boxShadow: const [BoxShadow(color: Color(0x59D97706), blurRadius: 22, offset: Offset(0, 8))],
        ),
        child: Material(
          color: Colors.transparent,
          child: InkWell(
            borderRadius: BorderRadius.circular(999),
            onTap: _sail,
            child: Padding(
              padding: const EdgeInsets.symmetric(vertical: 13, horizontal: 30),
              child: Text('Continue to my voyage ⛵', style: head(17, color: Sea.deep)),
            ),
          ),
        ),
      );
}
