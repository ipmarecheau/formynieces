import 'package:flutter/material.dart';

import '../api.dart';
import 'welcome_back_screen.dart';

/// Child sign-in — mirrors the web /go (auth/student-login.blade.php): a teal
/// radial-gradient stage behind a white card, 🐢, and a gold "Set sail →" button.
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

  // Palette lifted verbatim from auth/student-login.blade.php.
  static const _ink = Color(0xFF0B2A31);
  static const _teal = Color(0xFF0D7D8C);
  static const _tealDeep = Color(0xFF0A5C68);
  static const _labelColor = Color(0xFF6E8890);
  static const _sub = Color(0xFF4B6670);
  static const _fieldBg = Color(0xFFF6FAF9);
  static const _fieldBorder = Color(0xFFD2E0DC);

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
      Navigator.of(context).pushReplacement(MaterialPageRoute(builder: (_) => const WelcomeBackScreen()));
    } on ApiException catch (e) {
      setState(() => _error = e.message);
    } catch (_) {
      setState(() => _error = "Hmm, that login didn't work. Check it with your parent and try again.");
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: RadialGradient(
            center: Alignment(0, -1.2),
            radius: 1.3,
            colors: [Color(0xFF1AA7C0), Color(0xFF0D7D8C), Color(0xFF0A5C68)],
            stops: [0.0, 0.45, 1.0],
          ),
        ),
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 380),
                child: Container(
                  padding: const EdgeInsets.fromLTRB(26, 30, 26, 26),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(26),
                    boxShadow: const [BoxShadow(color: Color(0x66000000), blurRadius: 70, offset: Offset(0, 30), spreadRadius: -30)],
                  ),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      // .turtle — web forces line-height:56px, so pin the box to 56.
                      const SizedBox(
                        height: 56,
                        child: Center(
                          child: Text('🐢', style: TextStyle(fontSize: 56, height: 1.0), textAlign: TextAlign.center),
                        ),
                      ),
                      const SizedBox(height: 6),
                      const Text('Sign in to your voyage',
                          textAlign: TextAlign.center,
                          style: TextStyle(fontFamily: 'Fredoka', fontWeight: FontWeight.w600, fontVariations: [FontVariation('wght', 600)], fontSize: 26, height: 1.19, color: _tealDeep)),
                      const SizedBox(height: 4),
                      const Text('Child sign-in — enter the login your parent gave you.',
                          textAlign: TextAlign.center,
                          style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, height: 1.36, color: _sub)),
                      const SizedBox(height: 22),
                      if (_error != null) ...[
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                          decoration: BoxDecoration(color: const Color(0xFFFDE8E6), borderRadius: BorderRadius.circular(11)),
                          child: Text(_error!,
                              style: const TextStyle(color: Color(0xFF9A2B1E), fontSize: 13, fontWeight: FontWeight.w700)),
                        ),
                        const SizedBox(height: 14),
                      ],
                      _fieldLabel('Your login'),
                      const SizedBox(height: 5),
                      _field(_email, hint: 'name@smoothseas.org', keyboard: TextInputType.emailAddress),
                      const SizedBox(height: 14),
                      _fieldLabel('Password'),
                      const SizedBox(height: 5),
                      _field(_password, hint: 'Your secret words', obscure: true),
                      const SizedBox(height: 14),
                      _setSailButton(),
                      const SizedBox(height: 20),
                      _parentFooter(),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
    );
  }

  Widget _fieldLabel(String text) => SizedBox(
        height: 16,
        child: Text(text.toUpperCase(),
            style: const TextStyle(fontSize: 12, height: 1.33, fontWeight: FontWeight.w800, color: _labelColor, letterSpacing: 0.6)),
      );

  Widget _field(TextEditingController c, {required String hint, bool obscure = false, TextInputType? keyboard}) {
    return SizedBox(
      height: 54,
      child: TextField(
        controller: c,
        obscureText: obscure,
        keyboardType: keyboard,
        textAlignVertical: TextAlignVertical.top,
        style: const TextStyle(color: _ink, fontWeight: FontWeight.w700, fontSize: 16),
        decoration: InputDecoration(
          hintText: hint,
          hintStyle: const TextStyle(color: Color(0xFF9DB2B0), fontWeight: FontWeight.w600),
          filled: true,
          fillColor: _fieldBg,
          isDense: true,
          contentPadding: const EdgeInsets.symmetric(horizontal: 15, vertical: 14),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(14),
            borderSide: const BorderSide(color: _fieldBorder, width: 1.5),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(14),
            borderSide: const BorderSide(color: _teal, width: 1.5),
          ),
        ),
      ),
    );
  }

  Widget _setSailButton() => Container(
        height: 55,
        decoration: BoxDecoration(
          gradient: const LinearGradient(begin: Alignment.topCenter, end: Alignment.bottomCenter, colors: [Color(0xFFF2A900), Color(0xFFD99400)]),
          borderRadius: BorderRadius.circular(15),
        ),
        child: Material(
          color: Colors.transparent,
          child: InkWell(
            borderRadius: BorderRadius.circular(15),
            onTap: _busy ? null : _submit,
            child: Center(
              child: _busy
                  ? const Center(child: SizedBox(height: 22, width: 22, child: CircularProgressIndicator(strokeWidth: 2.5, color: Color(0xFF3A2600))))
                  : const Text('Set sail →',
                      textAlign: TextAlign.center,
                      style: TextStyle(fontFamily: 'Fredoka', fontWeight: FontWeight.w600, fontVariations: [FontVariation('wght', 600)], fontSize: 19, color: Color(0xFF3A2600))),
            ),
          ),
        ),
      );

  Widget _parentFooter() => Text.rich(
        TextSpan(
          text: 'Are you a parent? ',
          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: _labelColor),
          children: const [
            TextSpan(text: 'Sign in here', style: TextStyle(color: _teal, fontWeight: FontWeight.w800)),
          ],
        ),
        textAlign: TextAlign.center,
      );
}
