import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../api.dart';

/// The child login card — the always-findable "your child's login" screen. Shows the
/// login ID and reveals the recoverable password on tap (never by default), with a
/// reset that generates a fresh ocean-word password. Guardian-only, own child.
class ChildLoginCardScreen extends StatefulWidget {
  const ChildLoginCardScreen({super.key, required this.childId, required this.childName});
  final int childId;
  final String childName;

  @override
  State<ChildLoginCardScreen> createState() => _ChildLoginCardScreenState();
}

class _ChildLoginCardScreenState extends State<ChildLoginCardScreen> {
  late Future<Map<String, dynamic>> _future;
  bool _revealed = false;
  bool _busy = false;
  String? _loginId;
  String? _password;

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<Map<String, dynamic>> _load() async {
    final d = await api.getJson('/children/${widget.childId}/login') as Map<String, dynamic>;
    _loginId = d['login_id'] as String?;
    _password = d['password'] as String?;
    return d;
  }

  Future<void> _reset() async {
    setState(() => _busy = true);
    try {
      final d = await api.postJson('/children/${widget.childId}/login/reset') as Map<String, dynamic>;
      setState(() {
        _loginId = d['login_id'] as String?;
        _password = d['password'] as String?;
        _revealed = true;
        _busy = false;
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('New password generated.')));
      }
    } on ApiException catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
        setState(() => _busy = false);
      }
    }
  }

  void _copy(String value, String label) {
    Clipboard.setData(ClipboardData(text: value));
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('$label copied')));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('${widget.childName}’s login')),
      body: FutureBuilder<Map<String, dynamic>>(
        future: _future,
        builder: (context, snap) {
          if (snap.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snap.hasError) {
            return Center(child: Padding(padding: const EdgeInsets.all(20), child: Text(snap.error.toString(), textAlign: TextAlign.center)));
          }
          return ListView(
            padding: const EdgeInsets.all(20),
            children: [
              Text('Help ${widget.childName} sign in on their device.', style: const TextStyle(fontSize: 15, color: Colors.black54)),
              const SizedBox(height: 20),
              _field('Login ID', _loginId ?? '—', onCopy: _loginId == null ? null : () => _copy(_loginId!, 'Login ID')),
              const SizedBox(height: 16),
              _passwordField(),
              const SizedBox(height: 28),
              OutlinedButton.icon(
                onPressed: _busy ? null : _reset,
                icon: const Icon(Icons.refresh),
                label: Text(_busy ? 'Resetting…' : 'Reset password'),
              ),
              const SizedBox(height: 8),
              const Text('Resetting creates a new password and signs them out of any device using the old one.', style: TextStyle(fontSize: 12.5, color: Colors.black45)),
            ],
          );
        },
      ),
    );
  }

  Widget _field(String label, String value, {VoidCallback? onCopy}) => Card(
        child: ListTile(
          title: Text(label, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: Colors.black54)),
          subtitle: Text(value, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w700, color: Colors.black87)),
          trailing: onCopy == null ? null : IconButton(icon: const Icon(Icons.copy), onPressed: onCopy, tooltip: 'Copy'),
        ),
      );

  Widget _passwordField() {
    if (_password == null) {
      return const Card(
        child: ListTile(
          title: Text('Password', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: Colors.black54)),
          subtitle: Text('No recoverable password stored. Use “Reset password” to generate one.', style: TextStyle(color: Colors.black54)),
        ),
      );
    }
    return Card(
      child: ListTile(
        title: const Text('Password', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: Colors.black54)),
        subtitle: Text(_revealed ? _password! : '••••••••', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w700, color: Colors.black87, letterSpacing: 1.5)),
        trailing: Row(mainAxisSize: MainAxisSize.min, children: [
          IconButton(
            icon: Icon(_revealed ? Icons.visibility_off : Icons.visibility),
            tooltip: _revealed ? 'Hide' : 'Reveal',
            onPressed: () => setState(() => _revealed = !_revealed),
          ),
          if (_revealed) IconButton(icon: const Icon(Icons.copy), tooltip: 'Copy', onPressed: () => _copy(_password!, 'Password')),
        ]),
      ),
    );
  }
}
