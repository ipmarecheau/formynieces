import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:smoothseas_child/screens/login_screen.dart';
import 'package:smoothseas_child/screens/result_screen.dart';
import 'package:smoothseas_child/theme.dart';

/// Golden (visual-regression) tests. Baselines live in test/goldens/.
/// Regenerate after an intended UI change: flutter test --update-goldens
void main() {
  setUpAll(() async {
    // Load the bundled fonts so goldens render with Fredoka/Nunito deterministically.
    for (final family in ['Fredoka', 'Nunito']) {
      final loader = FontLoader(family)..addFont(rootBundle.load('assets/fonts/$family.ttf'));
      await loader.load();
    }
  });

  Widget host(Widget child) => MaterialApp(theme: buildSeaTheme(), debugShowCheckedModeBanner: false, home: child);

  testWidgets('login screen golden', (tester) async {
    await tester.pumpWidget(host(const LoginScreen()));
    await tester.pump(const Duration(milliseconds: 100));
    await expectLater(find.byType(LoginScreen), matchesGoldenFile('goldens/login.png'));
  });

  testWidgets('result screen golden', (tester) async {
    await tester.pumpWidget(host(const ResultScreen(result: {
      'accuracy': 80,
      'celebration': 'Great work — you strengthened Fractions!',
      'streak': {'days': 5, 'label': '5-day streak'},
      'next_action': 'Return tomorrow for your next mission',
    })));
    await tester.pump(const Duration(milliseconds: 100));
    await expectLater(find.byType(ResultScreen), matchesGoldenFile('goldens/result.png'));
  });
}
