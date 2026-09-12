import 'package:flutter/material.dart';

import '../api.dart';
import '../theme.dart';
import 'login_screen.dart';
import 'practice_screen.dart';

/// MC-01/02 — the Voyage home: Smooth + today's mission, streak. Mirrors the web /voyage look.
class TodayScreen extends StatefulWidget {
  const TodayScreen({super.key});

  @override
  State<TodayScreen> createState() => _TodayScreenState();
}

class _TodayScreenState extends State<TodayScreen> {
  late Future<Map<String, dynamic>> _future;

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<Map<String, dynamic>> _load() async => (await api.getJson('/child/today')) as Map<String, dynamic>;

  Future<void> _logout() async {
    await api.logout();
    if (!mounted) return;
    Navigator.of(context).pushReplacement(MaterialPageRoute(builder: (_) => const LoginScreen()));
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
                return _retry(snap.error.toString());
              }
              final d = snap.data!;
              final mission = d['mission'] as Map<String, dynamic>?;
              final streak = d['streak'] as Map<String, dynamic>? ?? {};
              final childName = (d['child'] as Map<String, dynamic>?)?['name'] ?? 'explorer';
              return RefreshIndicator(
                color: Sea.gold,
                backgroundColor: Sea.navy,
                onRefresh: () async => setState(() => _future = _load()),
                child: ListView(
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
                  children: [
                    _topBar(streak),
                    const SizedBox(height: 16),
                    _smoothCard(childName, streak),
                    const SizedBox(height: 18),
                    Text('Your mission', style: head(20)),
                    const SizedBox(height: 10),
                    if (mission == null) _empty() else _missionCard(mission),
                  ],
                ),
              );
            },
          ),
        ),
      ),
    );
  }

  Widget _topBar(Map<String, dynamic> streak) {
    return Row(
      children: [
        const Text('⛵', style: TextStyle(fontSize: 26)),
        const SizedBox(width: 8),
        Text('Your Voyage', style: head(22)),
        const Spacer(),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
          decoration: BoxDecoration(
            gradient: const LinearGradient(colors: [Sea.gold, Sea.goldDeep]),
            borderRadius: BorderRadius.circular(999),
          ),
          child: Text('🔥 ${streak['days'] ?? 0}', style: head(14, color: Sea.deep)),
        ),
        IconButton(onPressed: _logout, icon: const Icon(Icons.logout, color: Sea.muted)),
      ],
    );
  }

  Widget _smoothCard(String name, Map<String, dynamic> streak) {
    return GlassCard(
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('🐢', style: TextStyle(fontSize: 44)),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Welcome back, $name!', style: head(20)),
                const SizedBox(height: 6),
                Text('🔥 ${streak['label'] ?? '0-day streak'} — keep it going!',
                    style: const TextStyle(color: Sea.gold, fontWeight: FontWeight.w700)),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _missionCard(Map<String, dynamic> mission) {
    return GlassCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(children: [
            const Text('🏝️', style: TextStyle(fontSize: 22)),
            const SizedBox(width: 8),
            Text('${mission['subject']}', style: const TextStyle(color: Sea.aqua, fontWeight: FontWeight.w800)),
          ]),
          const SizedBox(height: 8),
          Text('${mission['topic']}', style: head(19)),
          const SizedBox(height: 6),
          Text('About ${mission['estimated_minutes']} minutes', style: const TextStyle(color: Sea.muted)),
          const SizedBox(height: 16),
          GoldButton(label: '${mission['cta']}', onPressed: () => _startMission(mission)),
        ],
      ),
    );
  }

  Widget _empty() => GlassCard(
        child: Row(children: [
          const Text('🌟', style: TextStyle(fontSize: 32)),
          const SizedBox(width: 12),
          Expanded(child: Text('You’re all caught up! Check back soon for your next mission.', style: const TextStyle(color: Sea.ink))),
        ]),
      );

  Future<void> _startMission(Map<String, dynamic> mission) async {
    try {
      final session = await api.postJson('/child/practice/start', {'mission_id': mission['id']}) as Map<String, dynamic>;
      if (!mounted) return;
      await Navigator.of(context).push(MaterialPageRoute(builder: (_) => PracticeScreen(session: session)));
      if (mounted) setState(() => _future = _load());
    } on ApiException catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    }
  }

  Widget _retry(String message) => Center(
        child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
          Padding(padding: const EdgeInsets.all(20), child: Text(message, textAlign: TextAlign.center, style: const TextStyle(color: Sea.ink))),
          GoldButton(label: 'Retry', expand: false, onPressed: () => setState(() => _future = _load())),
        ]),
      );
}
