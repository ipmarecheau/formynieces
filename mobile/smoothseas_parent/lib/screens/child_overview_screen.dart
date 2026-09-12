import 'package:flutter/material.dart';

import '../api.dart';

/// MP-02..06 — the child overview with weak topics, writing, and readiness.
class ChildOverviewScreen extends StatelessWidget {
  const ChildOverviewScreen({super.key, required this.childId, required this.childName});
  final int childId;
  final String childName;

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 4,
      child: Scaffold(
        appBar: AppBar(
          title: Text(childName),
          bottom: const TabBar(isScrollable: true, tabs: [
            Tab(text: 'Overview'),
            Tab(text: 'Weak topics'),
            Tab(text: 'Writing'),
            Tab(text: 'Readiness'),
          ]),
        ),
        body: TabBarView(children: [
          _Section(path: '/children/$childId/overview', builder: _overview),
          _Section(path: '/children/$childId/weak-topics', builder: _weakTopics),
          _Section(path: '/children/$childId/writing', builder: _writing),
          _Section(path: '/children/$childId/readiness', builder: _readiness),
        ]),
      ),
    );
  }

  Widget _overview(Map<String, dynamic> d) {
    final next = d['next_action'] as Map<String, dynamic>;
    final wk = d['weekly_summary'] as Map<String, dynamic>;
    final cards = d['cards'] as Map<String, dynamic>;
    return ListView(padding: const EdgeInsets.all(16), children: [
      Card(
        color: const Color(0xFFE7F5F3),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text('${next['title']}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
            const SizedBox(height: 6),
            Text('${next['why']}'),
            const SizedBox(height: 12),
            FilledButton(onPressed: () {}, child: Text('${next['cta']}')),
          ]),
        ),
      ),
      const SizedBox(height: 16),
      _tile('This week', '${wk['sessions_completed']} sessions · 🔥 ${wk['streak_days']}-day streak'),
      _tile('Weak topics', '${cards['weak_topics']}'),
      _tile('Writing', '${cards['writing_status']}'),
      _tile('Readiness', '${cards['readiness_status']}'),
    ]);
  }

  Widget _weakTopics(Map<String, dynamic> d) {
    final topics = (d['topics'] as List<dynamic>);
    if (topics.isEmpty) return const _Empty('No weak topics right now — nice!');
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: topics.length,
      itemBuilder: (context, i) {
        final t = topics[i] as Map<String, dynamic>;
        return Card(
          child: ListTile(
            title: Text('${t['title']}', style: const TextStyle(fontWeight: FontWeight.bold)),
            subtitle: Text('${t['subject']} · ${t['reason']}\n${t['suggested_action']}'),
            isThreeLine: true,
            leading: const Icon(Icons.flag, color: Color(0xFFB45309)),
          ),
        );
      },
    );
  }

  Widget _writing(Map<String, dynamic> d) {
    if (d['status'] == 'none' || d['latest'] == null) {
      return const _Empty('No writing submitted yet.');
    }
    final l = d['latest'] as Map<String, dynamic>;
    return ListView(padding: const EdgeInsets.all(16), children: [
      Text('${l['prompt'] ?? 'Writing'}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
      const SizedBox(height: 8),
      Text('Status: ${d['status']}'),
      const SizedBox(height: 12),
      if (l['summary'] != null) Text('${l['summary']}'),
      const SizedBox(height: 12),
      Wrap(spacing: 8, children: [
        for (final s in (l['skills'] as List<dynamic>? ?? [])) Chip(label: Text('$s')),
      ]),
    ]);
  }

  Widget _readiness(Map<String, dynamic> d) {
    return ListView(padding: const EdgeInsets.all(16), children: [
      Text('${d['headline']}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
      const SizedBox(height: 12),
      for (final e in (d['evidence'] as List<dynamic>? ?? []))
        Padding(padding: const EdgeInsets.only(bottom: 6), child: Text('• $e')),
      const SizedBox(height: 12),
      Card(
        color: const Color(0xFFE7F5F3),
        child: Padding(padding: const EdgeInsets.all(14), child: Text('Next: ${d['next_action']}')),
      ),
    ]);
  }

  Widget _tile(String title, String value) => Card(
        child: ListTile(title: Text(title), trailing: Text(value, style: const TextStyle(fontWeight: FontWeight.bold))),
      );
}

class _Section extends StatefulWidget {
  const _Section({required this.path, required this.builder});
  final String path;
  final Widget Function(Map<String, dynamic>) builder;

  @override
  State<_Section> createState() => _SectionState();
}

class _SectionState extends State<_Section> {
  late Future<Map<String, dynamic>> _future;

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<Map<String, dynamic>> _load() async => (await api.getJson(widget.path)) as Map<String, dynamic>;

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<Map<String, dynamic>>(
      future: _future,
      builder: (context, snap) {
        if (snap.connectionState == ConnectionState.waiting) {
          return const Center(child: CircularProgressIndicator());
        }
        if (snap.hasError) {
          return Center(
            child: Column(mainAxisSize: MainAxisSize.min, children: [
              Padding(padding: const EdgeInsets.all(16), child: Text(snap.error.toString(), textAlign: TextAlign.center)),
              FilledButton(onPressed: () => setState(() => _future = _load()), child: const Text('Retry')),
            ]),
          );
        }
        return widget.builder(snap.data!);
      },
    );
  }
}

class _Empty extends StatelessWidget {
  const _Empty(this.message);
  final String message;
  @override
  Widget build(BuildContext context) => Center(child: Text(message));
}
