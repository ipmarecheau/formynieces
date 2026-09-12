import 'package:flutter/material.dart';

import '../theme.dart';

/// MC-05 — result: accuracy, a simple celebration, streak, next step.
class ResultScreen extends StatelessWidget {
  const ResultScreen({super.key, required this.result});
  final Map<String, dynamic> result;

  @override
  Widget build(BuildContext context) {
    final accuracy = result['accuracy'] as int? ?? 0;
    final streak = result['streak'] as Map<String, dynamic>? ?? {};
    return Scaffold(
      body: SeaBackground(
        child: SafeArea(
          child: Padding(
            padding: const EdgeInsets.all(24),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                const Text('🎉', style: TextStyle(fontSize: 72), textAlign: TextAlign.center),
                const SizedBox(height: 12),
                Text('$accuracy%', textAlign: TextAlign.center, style: head(52, color: Sea.gold)),
                const SizedBox(height: 10),
                Text('${result['celebration']}', textAlign: TextAlign.center, style: head(20)),
                const SizedBox(height: 18),
                Center(
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(colors: [Sea.gold, Sea.goldDeep]),
                      borderRadius: BorderRadius.circular(999),
                    ),
                    child: Text('🔥 ${streak['label'] ?? ''}', style: head(15, color: Sea.deep)),
                  ),
                ),
                const SizedBox(height: 28),
                Text('${result['next_action'] ?? ''}', textAlign: TextAlign.center, style: const TextStyle(color: Sea.muted)),
                const SizedBox(height: 24),
                GoldButton(label: 'Back to my Voyage', onPressed: () => Navigator.of(context).pop()),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
