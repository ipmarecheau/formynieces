import 'package:flutter/material.dart';

import '../api.dart';
import '../theme.dart';
import '../widgets/voyage_map.dart';
import 'captains_orders_panel.dart';
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
    final h = MediaQuery.sizeOf(context).height;
    final panelHeight = h * 0.448; // web co-frame = 378 of 844
    return Scaffold(
      body: SeaBackground(
        child: Stack(
          children: [
            FutureBuilder<Map<String, dynamic>>(
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
                return Column(
                  children: [
                    _navBar(streak), // fixed 98px, matches web .vy-nav
                    Expanded(
                      child: RefreshIndicator(
                        color: Sea.gold,
                        backgroundColor: Sea.navy,
                        onRefresh: () async => setState(() => _future = _load()),
                        child: ListView(
                          padding: EdgeInsets.fromLTRB(28, 14, 28, panelHeight),
                          children: [
                            VoyageMap(islands: islands, onOpen: _openIsland), // top 112, w334
                            const SizedBox(height: 16),
                            _smoothCard(name, streak), // companion top 315
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
                      ),
                    ),
                  ],
                );
              },
            ),
            // Captain's Orders — open parchment bottom sheet by default (CO-12).
            Positioned(
              left: 0, right: 0, bottom: 0,
              child: SizedBox(height: panelHeight, child: const CaptainsOrdersPanel()),
            ),
          ],
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

  /// Sticky nav — mirrors web .vy-nav (height 98, translucent navy backdrop).
  Widget _navBar(Map<String, dynamic> streak) => Container(
        height: 98,
        color: const Color(0x8C0C1432),
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            Row(mainAxisSize: MainAxisSize.min, children: [
              const Text('⛵', style: TextStyle(fontSize: 24)),
              const SizedBox(width: 8),
              SizedBox(width: 92, child: Text('Your Voyage', style: head(20.8, height: 1.15))),
            ]),
            Row(mainAxisSize: MainAxisSize.min, children: [
              // Take the tour — gold-outline pill (TR-04).
              Container(
                width: 66,
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
                decoration: BoxDecoration(
                  color: const Color(0x1FF6B71E),
                  border: Border.all(color: const Color(0x8CF6B71E), width: 1.5),
                  borderRadius: BorderRadius.circular(16),
                ),
                child: const Text('🧭 Take the tour', textAlign: TextAlign.center, style: TextStyle(color: Color(0xFFFDE68A), fontWeight: FontWeight.w800, fontSize: 11, height: 1.15)),
              ),
              const SizedBox(width: 8),
              // Streak — orange circle.
              Container(
                width: 66, height: 66,
                alignment: Alignment.center,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  gradient: const LinearGradient(begin: Alignment.topLeft, end: Alignment.bottomRight, colors: [Color(0xD9F97316), Color(0xD9F6B71E)]),
                  boxShadow: const [BoxShadow(color: Color(0x59F6B71E), blurRadius: 12)],
                ),
                child: Text('🔥 ${streak['days'] ?? 0}\nday\nstreak', textAlign: TextAlign.center, style: const TextStyle(color: Color(0xFFFFF7ED), fontWeight: FontWeight.w800, fontSize: 11, height: 1.1)),
              ),
              const SizedBox(width: 8),
              // Log out pill.
              GestureDetector(
                onTap: _logout,
                child: Container(
                  width: 48,
                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 8),
                  decoration: BoxDecoration(color: const Color(0x14FFFFFF), border: Border.all(color: const Color(0x59FFFFFF), width: 1.5), borderRadius: BorderRadius.circular(16)),
                  child: const Text('Log out', textAlign: TextAlign.center, style: TextStyle(color: Sea.ink, fontWeight: FontWeight.w800, fontSize: 11, height: 1.15)),
                ),
              ),
            ]),
          ],
        ),
      );

  Widget _smoothCard(String name, Map<String, dynamic> streak) {
    final days = (streak['days'] as num?)?.toInt() ?? 0;
    return GlassCard(
      padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 20),
      child: Row(crossAxisAlignment: CrossAxisAlignment.center, children: [
        Image.asset('assets/images/voyage/smooth.webp', height: 78, fit: BoxFit.contain),
        const SizedBox(width: 14),
        Expanded(
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, mainAxisSize: MainAxisSize.min, children: [
            Text('Welcome back, $name!', style: head(17.6, height: 1.1)),
            const SizedBox(height: 4),
            Text('🔥 $days ${days == 1 ? 'day' : 'days'} in a row — keep it going!',
                style: const TextStyle(color: Sea.gold, fontWeight: FontWeight.w700, fontSize: 14.7, height: 1.35)),
          ]),
        ),
      ]),
    );
  }

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
