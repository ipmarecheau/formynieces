import 'package:flutter_test/flutter_test.dart';
import 'package:smoothseas_parent/main.dart';

void main() {
  testWidgets('Parent app boots to the login screen', (WidgetTester tester) async {
    await tester.pumpWidget(const ParentApp());
    await tester.pump();
    expect(find.text('Sign in'), findsOneWidget);
  });
}
