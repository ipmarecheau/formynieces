import 'package:flutter/material.dart';

import '../api.dart';
import 'login_screen.dart';
import 'practice_screen.dart';

/// MC-01/02 — Today: Smooth + the next mission (or a calm empty state).
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
      appBar: AppBar(
        title: const Text('Today'),
        actions: [IconButton(onPressed: _logout, icon: const Icon(Icons.logout))],
      ),
      body: RefreshIndicator(
        onRefresh: () async => setState(() => _future = _load()),
        child: FutureBuilder<Map<String, dynamic>>(
          future: _future,
          builder: (context, snap) {
            if (snap.connectionState == ConnectionState.waiting) {
              return const Center(child: CircularProgressIndicator());
            }
            if (snap.hasError) {
              return _retry(snap.error.toString());
            }
            final d = snap.data!;
            final mission = d['mission'] as Map<String, dynamic>?;
            final streak = d['streak'] as Map<String, dynamic>? ?? {};
            return ListView(
              padding: const EdgeInsets.all(20),
              children: [
                const SizedBox(height: 8),
                const Text('⛵', style: TextStyle(fontSize: 48), textAlign: TextAlign.center),
                const SizedBox(height: 12),
                Card(
                  color: const Color(0xFFE7F5F3),
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Text('${d['smooth_message']}', style: const TextStyle(fontSize: 16)),
                  ),
                ),
                const SizedBox(height: 20),
                if (mission == null)
                  const _EmptyMission()
                else
                  _MissionCard(mission: mission, onStart: () => _startMission(mission)),
                const SizedBox(height: 24),
                Center(child: Text('🔥 ${streak['label'] ?? '0-day streak'}')),
              ],
            );
          },
        ),
      ),
    );
  }

  Future<void> _startMission(Map<String, dynamic> mission) async {
    try {
      final session = await api.postJson('/child/practice/start', {'mission_id': mission['id']}) as Map<String, dynamic>;
      if (!mounted) return;
      await Navigator.of(context).push(MaterialPageRoute(builder: (_) => PracticeScreen(session: session)));
      if (mounted) setState(() => _future = _load()); // refresh streak/mission on return
    } on ApiException catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
      }
    }
  }

  Widget _retry(String message) => Center(
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          Padding(padding: const EdgeInsets.all(16), child: Text(message, textAlign: TextAlign.center)),
          FilledButton(onPressed: () => setState(() => _future = _load()), child: const Text('Retry')),
        ]),
      );
}

class _MissionCard extends StatelessWidget {
  const _MissionCard({required this.mission, required this.onStart});
  final Map<String, dynamic> mission;
  final VoidCallback onStart;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text('${mission['subject']}', style: const TextStyle(color: Color(0xFF475569))),
          const SizedBox(height: 4),
          Text('${mission['topic']}', style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
          const SizedBox(height: 6),
          Text('About ${mission['estimated_minutes']} minutes'),
          const SizedBox(height: 16),
          SizedBox(
            width: double.infinity,
            child: FilledButton(onPressed: onStart, child: Text('${mission['cta']}')),
          ),
        ]),
      ),
    );
  }
}

class _EmptyMission extends StatelessWidget {
  const _EmptyMission();
  @override
  Widget build(BuildContext context) => const Card(
        child: Padding(
          padding: EdgeInsets.all(20),
          child: Text('You’re all caught up! Check back soon for your next mission. 🌟'),
        ),
      );
}
