import 'package:flutter/material.dart';

import '../api.dart';
import '../theme.dart';
import 'voyage_screen.dart';

/// The return splash — streaks + any milestone, flowing into the Voyage (SH-06/CE-04).
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
        child: SafeArea(
          child: FutureBuilder<Map<String, dynamic>>(
            future: _future,
            builder: (context, snap) {
              // On any hiccup, don't trap the child on the splash — sail on.
              if (snap.hasError) {
                WidgetsBinding.instance.addPostFrameCallback((_) => _sail());
                return const SizedBox.shrink();
              }
              if (snap.connectionState == ConnectionState.waiting) {
                return const Center(child: CircularProgressIndicator(color: Sea.gold));
              }
              final d = snap.data!;
              final streaks = d['streaks'] as Map<String, dynamic>? ?? {};
              final milestone = d['milestone'];
              return Padding(
                padding: const EdgeInsets.all(24),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    ClipOval(child: Image.asset('assets/images/voyage/smooth-cheer.webp', width: 92, height: 92, fit: BoxFit.cover)),
                    const SizedBox(height: 16),
                    Text('${d['message']}', textAlign: TextAlign.center, style: head(24)),
                    if (milestone != null) ...[
                      const SizedBox(height: 12),
                      Center(
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                          decoration: BoxDecoration(gradient: const LinearGradient(colors: [Sea.gold, Sea.goldDeep]), borderRadius: BorderRadius.circular(999)),
                          child: Text('🎉 $milestone-day milestone!', style: head(15, color: Sea.deep)),
                        ),
                      ),
                    ],
                    const SizedBox(height: 24),
                    Wrap(
                      alignment: WrapAlignment.center,
                      spacing: 10,
                      runSpacing: 10,
                      children: [
                        _chip('🔥 Voyage', streaks['voyage']),
                        _chip('🎯 Practice', streaks['practice']),
                        _chip('📅 Login', streaks['login']),
                        _chip('🏆 Mastery', streaks['mastery']),
                      ],
                    ),
                    const SizedBox(height: 30),
                    GoldButton(label: 'Set sail! ⛵', onPressed: _sail),
                  ],
                ),
              );
            },
          ),
        ),
      ),
    );
  }

  Widget _chip(String label, dynamic count) => Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        decoration: BoxDecoration(color: Sea.cardFill, borderRadius: BorderRadius.circular(12), border: Border.all(color: Sea.cardBorder)),
        child: Text('$label  ${count ?? 0}', style: const TextStyle(color: Sea.foam, fontWeight: FontWeight.w700)),
      );
}
