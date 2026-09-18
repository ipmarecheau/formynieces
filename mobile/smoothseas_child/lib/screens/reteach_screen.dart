import 'package:flutter/material.dart';

import '../theme.dart';
import '../widgets/loop_rail.dart';
import 'lesson_screen.dart';

/// The reteach loopback — reached when a practice question is missed on both tries.
/// Smooth pauses, then routes back to the lesson to re-learn the idea (LL-14 / loop 3↺2).
class ReteachScreen extends StatelessWidget {
  const ReteachScreen({super.key, required this.moduleId, required this.missionId, required this.topic});
  final int moduleId;
  final String missionId;
  final String topic;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Stack(
        children: [
          SeaBackground(
            child: SafeArea(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(20, 0, 20, 24),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Image.asset('assets/images/voyage/smooth.webp', height: 120, fit: BoxFit.contain),
                    const SizedBox(height: 12),
                    Text("Let's revisit this one", textAlign: TextAlign.center, style: head(28, height: 1.1)),
                    const SizedBox(height: 10),
                    Text('No worries, Captain — that one was tricky. Let’s look at $topic together again, then sail back into practice.',
                        textAlign: TextAlign.center, style: const TextStyle(color: Sea.foam, fontSize: 16, height: 1.4, fontWeight: FontWeight.w600)),
                    const SizedBox(height: 28),
                    GoldButton(
                      label: 'Back to the lesson 📘',
                      onPressed: () => Navigator.of(context).pushReplacement(MaterialPageRoute(
                        builder: (_) => LessonScreen(moduleId: moduleId, missionId: missionId, topic: topic),
                      )),
                    ),
                  ],
                ),
              ),
            ),
          ),
          const LoopRail(stage: 'lesson'),
        ],
      ),
    );
  }
}
