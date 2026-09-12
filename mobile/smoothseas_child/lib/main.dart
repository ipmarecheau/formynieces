import 'package:flutter/material.dart';

import 'api.dart';
import 'screens/login_screen.dart';
import 'screens/today_screen.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await api.loadToken();
  runApp(const ChildApp());
}

const seaTeal = Color(0xFF0D9488);

class ChildApp extends StatelessWidget {
  const ChildApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'SmoothSeas',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: seaTeal),
        scaffoldBackgroundColor: const Color(0xFFFBF8F2),
        useMaterial3: true,
      ),
      home: api.isLoggedIn ? const TodayScreen() : const LoginScreen(),
    );
  }
}
