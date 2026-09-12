import 'package:flutter/material.dart';

import 'api.dart';
import 'screens/login_screen.dart';
import 'screens/voyage_screen.dart';
import 'theme.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await api.loadToken();
  runApp(const ChildApp());
}

class ChildApp extends StatelessWidget {
  const ChildApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'SmoothSeas',
      debugShowCheckedModeBanner: false,
      theme: buildSeaTheme(),
      home: api.isLoggedIn ? const VoyageScreen() : const LoginScreen(),
    );
  }
}
