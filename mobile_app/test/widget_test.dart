import 'package:flutter_test/flutter_test.dart';
import 'package:task_manager_app/main.dart';

void main() {
  testWidgets('App launches', (WidgetTester tester) async {
    await tester.pumpWidget(const TaskManagerApp());
  });
}
