import 'package:flutter/material.dart';

import '../api.dart';
import '../theme.dart';
import 'island_screen.dart';
import 'login_screen.dart';

/// The Voyage overworld — Smooth + streak + the islands list. Mirrors web /voyage.
class VoyageScreen extends StatefulWidget {
  const VoyageScreen({super.key});

  @override
  State<VoyageScreen> createState() => _VoyageScreenState();
}

class _VoyageScreenState extends State<VoyageScreen> {
  late Future<Map<String, dynamic>> _future;

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<Map<String, dynamic>> _load() async => (await api.getJson('/child/voyage')) as Map<String, dynamic>;

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
              final streak = d['streak'] as Map<String, dynamic>? ?? {};
              final name = (d['child'] as Map<String, dynamic>?)?['name'] ?? 'explorer';
              final islands = (d['islands'] as List<dynamic>);
              return RefreshIndicator(
                color: Sea.gold,
                backgroundColor: Sea.navy,
                onRefresh: () async => setState(() => _future = _load()),
                child: ListView(
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
                  children: [
                    _topBar(streak),
                    const SizedBox(height: 16),
                    _smoothCard(name, streak),
                    const SizedBox(height: 18),
                    Text('Islands', style: head(20)),
                    const SizedBox(height: 10),
                    for (var i = 0; i < islands.length; i++)
                      Padding(
                        padding: const EdgeInsets.only(bottom: 10),
                        child: _IslandCard(number: i + 1, island: islands[i] as Map<String, dynamic>, onOpen: _openIsland),
                      ),
                  ],
                ),
              );
            },
          ),
        ),
      ),
    );
  }

  Future<void> _openIsland(Map<String, dynamic> island) async {
    if (island['state'] == 'locked') {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('That island is still locked — conquer the ones before it first.')));
      return;
    }
    await Navigator.of(context).push(MaterialPageRoute(
      builder: (_) => IslandScreen(slug: island['slug'] as String, name: island['name'] as String),
    ));
    if (mounted) setState(() => _future = _load());
  }

  Widget _topBar(Map<String, dynamic> streak) => Row(children: [
        const Text('⛵', style: TextStyle(fontSize: 26)),
        const SizedBox(width: 8),
        Text('Your Voyage', style: head(22)),
        const Spacer(),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
          decoration: BoxDecoration(gradient: const LinearGradient(colors: [Sea.gold, Sea.goldDeep]), borderRadius: BorderRadius.circular(999)),
          child: Text('🔥 ${streak['days'] ?? 0}', style: head(14, color: Sea.deep)),
        ),
        IconButton(onPressed: _logout, icon: const Icon(Icons.logout, color: Sea.muted)),
      ]);

  Widget _smoothCard(String name, Map<String, dynamic> streak) => GlassCard(
        child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
          const Text('🐢', style: TextStyle(fontSize: 44)),
          const SizedBox(width: 14),
          Expanded(
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text('Welcome back, $name!', style: head(20)),
              const SizedBox(height: 6),
              Text('🔥 ${streak['label'] ?? '0-day streak'} — keep it going!', style: const TextStyle(color: Sea.gold, fontWeight: FontWeight.w700)),
            ]),
          ),
        ]),
      );

  Widget _retry(String message) => Center(
        child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
          Padding(padding: const EdgeInsets.all(20), child: Text(message, textAlign: TextAlign.center, style: const TextStyle(color: Sea.ink))),
          GoldButton(label: 'Retry', expand: false, onPressed: () => setState(() => _future = _load())),
        ]),
      );
}

class _IslandCard extends StatelessWidget {
  const _IslandCard({required this.number, required this.island, required this.onOpen});
  final int number;
  final Map<String, dynamic> island;
  final void Function(Map<String, dynamic>) onOpen;

  @override
  Widget build(BuildContext context) {
    final state = island['state'] as String;
    final locked = state == 'locked';
    final mastered = state == 'mastered';
    return GlassCard(
      onTap: () => onOpen(island),
      padding: const EdgeInsets.all(14),
      child: Row(children: [
        CircleAvatar(
          radius: 18,
          backgroundColor: const Color(0x2267E8F9),
          child: Text('$number', style: head(15)),
        ),
        const SizedBox(width: 12),
        Text('${island['icon']}', style: const TextStyle(fontSize: 22)),
        const SizedBox(width: 10),
        Expanded(
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text('${island['name']}', style: TextStyle(color: locked ? Sea.muted : Sea.foam, fontWeight: FontWeight.w800, fontSize: 16)),
            Text('${island['conquered']} / ${island['total']} conquered', style: const TextStyle(color: Sea.muted, fontSize: 12)),
          ]),
        ),
        _badge(state, locked, mastered),
      ]),
    );
  }

  Widget _badge(String state, bool locked, bool mastered) {
    if (locked) {
      return const Row(children: [Icon(Icons.lock, size: 14, color: Sea.muted), SizedBox(width: 4), Text('LOCKED', style: TextStyle(color: Sea.muted, fontSize: 11, fontWeight: FontWeight.w800))]);
    }
    if (mastered) {
      return const Text('✓ DONE', style: TextStyle(color: Color(0xFF6EE7B7), fontSize: 11, fontWeight: FontWeight.w800));
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(color: const Color(0x33F6B71E), borderRadius: BorderRadius.circular(999)),
      child: Text(island['current'] == true ? 'SAILING HERE' : 'PLAY', style: const TextStyle(color: Sea.gold, fontSize: 11, fontWeight: FontWeight.w800)),
    );
  }
}
