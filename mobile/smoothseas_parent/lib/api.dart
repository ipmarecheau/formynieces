import 'dart:convert';

import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

import 'config.dart';

/// Thin client for the SmoothSeas mobile API. One shared instance ([api]).
class ApiClient {
  String? _token;

  Future<void> loadToken() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString('token');
  }

  bool get isLoggedIn => _token != null;

  Future<void> _saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('token', token);
    _token = token;
  }

  Future<void> clearToken() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('token');
    _token = null;
  }

  Map<String, String> get _headers => {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        if (_token != null) 'Authorization': 'Bearer $_token',
      };

  Future<Map<String, dynamic>> login(String email, String password) async {
    final res = await http.post(
      Uri.parse('${AppConfig.apiBase}/login'),
      headers: _headers,
      body: jsonEncode({'email': email, 'password': password, 'device_name': 'flutter-parent'}),
    );
    if (res.statusCode != 200) {
      throw ApiException(_message(res));
    }
    final data = jsonDecode(res.body) as Map<String, dynamic>;
    await _saveToken(data['token'] as String);
    return data;
  }

  Future<void> logout() async {
    try {
      await http.post(Uri.parse('${AppConfig.apiBase}/logout'), headers: _headers);
    } catch (_) {
      // ignore network errors on logout
    }
    await clearToken();
  }

  Future<dynamic> getJson(String path) async {
    final res = await http.get(Uri.parse('${AppConfig.apiBase}$path'), headers: _headers);
    if (res.statusCode >= 400) {
      throw ApiException(_message(res), status: res.statusCode);
    }
    return jsonDecode(res.body);
  }

  String _message(http.Response res) {
    try {
      final body = jsonDecode(res.body) as Map<String, dynamic>;
      return (body['message'] ?? 'Something went wrong').toString();
    } catch (_) {
      return 'Error ${res.statusCode}';
    }
  }
}

class ApiException implements Exception {
  ApiException(this.message, {this.status});
  final String message;
  final int? status;
  @override
  String toString() => message;
}

final ApiClient api = ApiClient();
