import 'package:flutter/material.dart';

import '../api.dart';
import 'child_overview_screen.dart';
import 'login_screen.dart';

/// MP-01 — child cards with latest activity + attention status.
class ChildrenScreen extends StatefulWidget {
  const ChildrenScreen({super.key});

  @override
  State<ChildrenScreen> createState() => _ChildrenScreenState();
}

class _ChildrenScreenState extends State<ChildrenScreen> {
  late Future<List<dynamic>> _future;

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<List<dynamic>> _load() async {
    final data = await api.getJson('/children');
    return (data['children'] as List<dynamic>);
  }

  Future<void> _logout() async {
    await api.logout();
    if (!mounted) return;
    Navigator.of(context).pushReplacement(MaterialPageRoute(builder: (_) => const LoginScreen()));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('My children'),
        actions: [IconButton(onPressed: _logout, icon: const Icon(Icons.logout), tooltip: 'Log out')],
      ),
      body: RefreshIndicator(
        onRefresh: () async => setState(() => _future = _load()),
        child: FutureBuilder<List<dynamic>>(
          future: _future,
          builder: (context, snap) {
            if (snap.connectionState == ConnectionState.waiting) {
              return const Center(child: CircularProgressIndicator());
            }
            if (snap.hasError) {
              return _ErrorState(message: snap.error.toString(), onRetry: () => setState(() => _future = _load()));
            }
            final children = snap.data ?? [];
            if (children.isEmpty) {
              return const Center(child: Text('No children linked yet.'));
            }
            return ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: children.length,
              itemBuilder: (context, i) => _ChildCard(child: children[i] as Map<String, dynamic>),
            );
          },
        ),
      ),
    );
  }
}

class _ChildCard extends StatelessWidget {
  const _ChildCard({required this.child});
  final Map<String, dynamic> child;

  @override
  Widget build(BuildContext context) {
    final needsAttention = child['status'] == 'needs_attention';
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: ListTile(
        contentPadding: const EdgeInsets.all(16),
        title: Text('${child['name']}', style: const TextStyle(fontWeight: FontWeight.bold)),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 4),
            Text('${child['standard']} · ${child['latest_activity']}'),
            const SizedBox(height: 6),
            Row(children: [
              if (needsAttention)
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(color: const Color(0xFFFDE9C8), borderRadius: BorderRadius.circular(999)),
                  child: Text('${child['status_label']}', style: const TextStyle(color: Color(0xFF9A5A00), fontSize: 12)),
                )
              else
                Text('${child['status_label']}', style: const TextStyle(color: Color(0xFF15803D), fontSize: 12)),
              const Spacer(),
              Text('🔥 ${child['streak_days']}d', style: const TextStyle(fontSize: 12)),
            ]),
          ],
        ),
        trailing: const Icon(Icons.chevron_right),
        onTap: () => Navigator.of(context).push(
          MaterialPageRoute(builder: (_) => ChildOverviewScreen(childId: child['id'] as int, childName: '${child['name']}')),
        ),
      ),
    );
  }
}

class _ErrorState extends StatelessWidget {
  const _ErrorState({required this.message, required this.onRetry});
  final String message;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          Text(message, textAlign: TextAlign.center),
          const SizedBox(height: 12),
          FilledButton(onPressed: onRetry, child: const Text('Try again')),
        ]),
      ),
    );
  }
}
