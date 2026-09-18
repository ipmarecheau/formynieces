import 'package:flutter/material.dart';

import '../theme.dart';
import '../widgets/loop_rail.dart';
import 'check_screen.dart';

/// The module entry — mirrors the web ModuleEntry "explainer" phase: how the level
/// works, then a button into the competency check. Loop-rail sits at the Check stage.
class ExplainerScreen extends StatelessWidget {
  const ExplainerScreen({super.key, required this.moduleId, required this.missionId, required this.topic});
  final int moduleId;
  final String missionId;
  final String topic;

  void _startCheck(BuildContext context) {
    Navigator.of(context).pushReplacement(MaterialPageRoute(
      builder: (_) => CheckScreen(moduleId: moduleId, missionId: missionId, topic: topic),
    ));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Stack(
        children: [
          SeaBackground(
            child: SafeArea(
              child: ListView(
                padding: const EdgeInsets.fromLTRB(20, 0, 20, 24),
                children: [
                  Align(
                    alignment: Alignment.centerLeft,
                    child: GestureDetector(
                      onTap: () => Navigator.of(context).pop(),
                      child: const Padding(
                        padding: EdgeInsets.symmetric(vertical: 14),
                        child: Text('⛵ Back to my Voyage', style: TextStyle(color: Sea.gold, fontWeight: FontWeight.w800, fontSize: 15)),
                      ),
                    ),
                  ),
                  Text(topic, textAlign: TextAlign.center, style: head(28, height: 1.1)),
                  const SizedBox(height: 18),
                  GlassCard(
                    padding: const EdgeInsets.fromLTRB(20, 18, 20, 20),
                    child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      Text('How this level works', style: head(20, color: Sea.cyan)),
                      const SizedBox(height: 12),
                      _p("First, a quick check — six questions, two easy, two medium, two tricky. Ace them all and you've already mastered it — no lesson needed!"),
                      _p("If not, that's totally fine. You pick how to learn it: a lesson, some worked examples, or jump into practice."),
                      _p("In practice you climb from easy to tricky. Every question gives you a second try, and nothing is ever “wrong” — just not yet."),
                      _p("Get three tricky ones right first try in a row and the level is yours. 🎉 (Miss both tries on a question and Smooth pauses to reteach it.)"),
                      const SizedBox(height: 4),
                      Text("It's not a test or a grade — the quick check just helps Smooth start you in the right place.",
                          style: const TextStyle(color: Sea.foam, fontSize: 15, height: 1.4, fontWeight: FontWeight.w700)),
                    ]),
                  ),
                  const SizedBox(height: 18),
                  GoldButton(label: 'Start the quick check →', onPressed: () => _startCheck(context)),
                ],
              ),
            ),
          ),
          const LoopRail(stage: 'check'),
        ],
      ),
    );
  }

  Widget _p(String text) => Padding(
        padding: const EdgeInsets.only(bottom: 12),
        child: Text(text, style: const TextStyle(color: Sea.ink, fontSize: 15, height: 1.45)),
      );
}
