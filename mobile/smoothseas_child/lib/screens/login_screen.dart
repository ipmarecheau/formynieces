import 'package:flutter/material.dart';

import '../api.dart';
import '../theme.dart';
import 'voyage_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _email = TextEditingController();
  final _password = TextEditingController();
  bool _busy = false;
  String? _error;

  Future<void> _submit() async {
    setState(() {
      _busy = true;
      _error = null;
    });
    try {
      final data = await api.login(_email.text.trim(), _password.text);
      if (data['default_experience'] != 'child') {
        await api.clearToken();
        throw ApiException("That's a parent login — please use your child login.");
      }
      if (!mounted) return;
      Navigator.of(context).pushReplacement(MaterialPageRoute(builder: (_) => const VoyageScreen()));
    } on ApiException catch (e) {
      setState(() => _error = e.message);
    } catch (_) {
      setState(() => _error = 'Could not reach the server.');
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SeaBackground(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: GlassCard(
              padding: const EdgeInsets.all(28),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const Text('⛵', style: TextStyle(fontSize: 52), textAlign: TextAlign.center),
                  const SizedBox(height: 8),
                  Text('SmoothSeas', textAlign: TextAlign.center, style: head(30)),
                  const SizedBox(height: 4),
                  Text('Sign in to sail', textAlign: TextAlign.center, style: const TextStyle(color: Sea.muted)),
                  const SizedBox(height: 24),
                  TextField(
                    controller: _email,
                    keyboardType: TextInputType.emailAddress,
                    style: const TextStyle(color: Sea.ink),
                    decoration: const InputDecoration(labelText: 'Your login'),
                  ),
                  const SizedBox(height: 14),
                  TextField(
                    controller: _password,
                    obscureText: true,
                    style: const TextStyle(color: Sea.ink),
                    decoration: const InputDecoration(labelText: 'Password'),
                  ),
                  if (_error != null) ...[
                    const SizedBox(height: 12),
                    Text(_error!, style: const TextStyle(color: Color(0xFFFCA5A5))),
                  ],
                  const SizedBox(height: 22),
                  _busy
                      ? const Center(child: CircularProgressIndicator(color: Sea.gold))
                      : GoldButton(label: "Let’s go!", onPressed: _submit),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
