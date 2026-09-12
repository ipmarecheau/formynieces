class AppConfig {
  /// The mobile API base. Override at run time with:
  ///   flutter run --dart-define=API_BASE=http://HOST:PORT/api/mobile
  static const String apiBase = String.fromEnvironment(
    'API_BASE',
    defaultValue: 'http://172.233.163.6:8010/api/mobile',
  );
}
