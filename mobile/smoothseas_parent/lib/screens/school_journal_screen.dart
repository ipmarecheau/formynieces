import 'package:flutter/material.dart';

import '../api.dart';

/// The school journal (SJ) — the term timeline of graded papers, each expandable into
/// its per-question breakdown. Reads the digitised journal from the mobile API.
///
/// NOTE: uploading a new paper needs a native file/image picker package (an approval-
/// gated dependency), so this screen presents the read timeline; the upload + confirm
/// endpoints exist and are covered by MobileSchoolJournalTest.
class SchoolJournalScreen extends StatefulWidget {
  const SchoolJournalScreen({super.key, required this.childId, required this.childName});
  final int childId;
  final String childName;

  @override
  State<SchoolJournalScreen> createState() => _SchoolJournalScreenState();
}

class _SchoolJournalScreenState extends State<SchoolJournalScreen> {
  late Future<Map<String, dynamic>> _future;

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<Map<String, dynamic>> _load() async =>
      await api.getJson('/children/${widget.childId}/journal') as Map<String, dynamic>;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('${widget.childName} — school journal')),
      body: RefreshIndicator(
        onRefresh: () async => setState(() => _future = _load()),
        child: FutureBuilder<Map<String, dynamic>>(
          future: _future,
          builder: (context, snap) {
            if (snap.connectionState == ConnectionState.waiting) {
              return const Center(child: CircularProgressIndicator());
            }
            if (snap.hasError) {
              return ListView(children: [Padding(padding: const EdgeInsets.all(20), child: Text(snap.error.toString(), textAlign: TextAlign.center))]);
            }
            final terms = ((snap.data?['terms'] as List<dynamic>?) ?? []).cast<Map<String, dynamic>>();
            if (terms.isEmpty) {
              return ListView(children: const [Padding(padding: EdgeInsets.all(28), child: Text('No graded papers filed yet. Upload one from the web dashboard to build the timeline.', textAlign: TextAlign.center, style: TextStyle(color: Colors.black54)))]);
            }
            return ListView(
              padding: const EdgeInsets.all(16),
              children: [
                for (final term in terms) ...[
                  Padding(
                    padding: const EdgeInsets.symmetric(vertical: 8),
                    child: Text('${term['term']}', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800)),
                  ),
                  for (final entry in (((term['entries'] as List<dynamic>?) ?? []).cast<Map<String, dynamic>>()))
                    _EntryCard(entry: entry),
                ],
              ],
            );
          },
        ),
      ),
    );
  }
}

class _EntryCard extends StatelessWidget {
  const _EntryCard({required this.entry});
  final Map<String, dynamic> entry;

  @override
  Widget build(BuildContext context) {
    final questions = ((entry['questions'] as List<dynamic>?) ?? []).cast<Map<String, dynamic>>();
    final subtitle = [entry['subject'], entry['assessment_type'], entry['date']].where((v) => v != null && '$v'.isNotEmpty).join(' · ');
    return Card(
      child: ExpansionTile(
        title: Text('${entry['subject'] ?? 'Paper'}${entry['score'] != null ? '  —  ${entry['score']}' : ''}', style: const TextStyle(fontWeight: FontWeight.w700)),
        subtitle: Text(subtitle),
        childrenPadding: const EdgeInsets.fromLTRB(16, 0, 16, 12),
        children: [
          if (entry['teacher_comment'] != null && '${entry['teacher_comment']}'.isNotEmpty)
            Padding(
              padding: const EdgeInsets.only(bottom: 8),
              child: Text('“${entry['teacher_comment']}”', style: const TextStyle(fontStyle: FontStyle.italic, color: Colors.black54)),
            ),
          if (questions.isEmpty)
            const Align(alignment: Alignment.centerLeft, child: Text('No per-question breakdown for this paper.', style: TextStyle(color: Colors.black45)))
          else
            for (final q in questions)
              ListTile(
                dense: true,
                contentPadding: EdgeInsets.zero,
                leading: Icon(
                  q['is_correct'] == true ? Icons.check_circle : (q['is_correct'] == false ? Icons.cancel : Icons.help_outline),
                  color: q['is_correct'] == true ? Colors.green : (q['is_correct'] == false ? Colors.orange : Colors.blueGrey),
                ),
                title: Text('${q['number'] != null ? '${q['number']}. ' : ''}${q['prompt'] ?? ''}'),
                subtitle: q['topic_label'] != null ? Text('${q['topic_label']}', style: const TextStyle(fontSize: 12)) : null,
              ),
        ],
      ),
    );
  }
}
