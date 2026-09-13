import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:smoothseas_child/api.dart';
import 'package:smoothseas_child/screens/welcome_back_screen.dart';
import 'package:smoothseas_child/theme.dart';

void main() {
  setUpAll(() async {
    for (final family in ['Fredoka', 'Nunito']) {
      final loader = FontLoader(family)..addFont(rootBundle.load('assets/fonts/$family.ttf'));
      await loader.load();
    }
  });

  setUp(() {
    api = ApiClient(client: MockClient((req) async {
      if (req.url.path.endsWith('/child/welcome-back')) {
        return http.Response(
          jsonEncode({
            'child': {'id': 1, 'name': 'Ava'},
            'streaks': {'voyage': 5, 'practice': 3, 'login': 5, 'mastery': 1, 'pace_weeks': 2},
            'milestone': 5,
            'message': '🎉 A 5-day milestone — you\'re on fire!',
          }),
          200,
          headers: {'content-type': 'application/json'},
        );
      }
      return http.Response('{}', 404);
    }));
  });

  Widget host(Widget child) => MaterialApp(theme: buildSeaTheme(), debugShowCheckedModeBanner: false, home: child);

  testWidgets('welcome-back shows streaks and a set-sail button (flow)', (tester) async {
    await tester.pumpWidget(host(const WelcomeBackScreen()));
    await tester.pumpAndSettle();
    expect(find.textContaining('milestone'), findsWidgets);
    expect(find.text('Set sail! ⛵'), findsOneWidget);
  });

  testWidgets('welcome-back golden (visual)', (tester) async {
    await tester.pumpWidget(host(const WelcomeBackScreen()));
    await tester.pumpAndSettle();
    await expectLater(find.byType(WelcomeBackScreen), matchesGoldenFile('goldens/welcome_back.png'));
  });
}
