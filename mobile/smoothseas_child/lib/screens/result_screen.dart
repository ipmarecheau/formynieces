import 'package:flutter/material.dart';

/// MC-05 — result: accuracy, a simple celebration, streak, next step.
class ResultScreen extends StatelessWidget {
  const ResultScreen({super.key, required this.result});
  final Map<String, dynamic> result;

  @override
  Widget build(BuildContext context) {
    final accuracy = result['accuracy'] as int? ?? 0;
    final streak = result['streak'] as Map<String, dynamic>? ?? {};
    return Scaffold(
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Text('🎉', style: TextStyle(fontSize: 64), textAlign: TextAlign.center),
              const SizedBox(height: 12),
              Text('$accuracy%',
                  textAlign: TextAlign.center,
                  style: const TextStyle(fontSize: 44, fontWeight: FontWeight.bold, color: Color(0xFF0D9488))),
              const SizedBox(height: 8),
              Text('${result['celebration']}', textAlign: TextAlign.center, style: const TextStyle(fontSize: 18)),
              const SizedBox(height: 20),
              Center(child: Text('🔥 ${streak['label'] ?? ''}')),
              const SizedBox(height: 28),
              Text('${result['next_action'] ?? ''}', textAlign: TextAlign.center, style: const TextStyle(color: Color(0xFF475569))),
              const SizedBox(height: 24),
              FilledButton(
                onPressed: () => Navigator.of(context).pop(), // back to Today (MC-07)
                child: const Text('Back to Today'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
