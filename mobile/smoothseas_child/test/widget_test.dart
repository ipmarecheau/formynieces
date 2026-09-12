import 'package:flutter_test/flutter_test.dart';
import 'package:smoothseas_child/main.dart';

void main() {
  testWidgets('Child app boots to the login screen', (WidgetTester tester) async {
    await tester.pumpWidget(const ChildApp());
    await tester.pump();
    expect(find.text('Let’s go!'), findsOneWidget);
  });
}
