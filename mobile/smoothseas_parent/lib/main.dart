import 'package:flutter/material.dart';

import 'api.dart';
import 'screens/children_screen.dart';
import 'screens/login_screen.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await api.loadToken();
  runApp(const ParentApp());
}

const seaTeal = Color(0xFF0D9488);

class ParentApp extends StatelessWidget {
  const ParentApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'SmoothSeas Parent',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: seaTeal),
        scaffoldBackgroundColor: const Color(0xFFFBF8F2),
        useMaterial3: true,
      ),
      home: api.isLoggedIn ? const ChildrenScreen() : const LoginScreen(),
    );
  }
}
