import 'dart:async';

import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';
import '../services/auth_service.dart';
import '../services/local_db.dart';

class ConnectivityService {
  static final Connectivity _connectivity = Connectivity();
  static bool _isOnline = true;
  static StreamSubscription? _sub;

  static bool get isOnline => _isOnline;

  static Future<void> init() async {
    final result = await _connectivity.checkConnectivity();
    _isOnline = !result.contains(ConnectivityResult.none);
    _sub = _connectivity.onConnectivityChanged.listen((result) {
      final wasOffline = !_isOnline;
      _isOnline = !result.contains(ConnectivityResult.none);
      if (_isOnline && wasOffline) {
        _syncPendingItems();
      }
    });
  }

  static void dispose() {
    _sub?.cancel();
  }

  static Future<void> _syncPendingItems() async {
    final queue = await LocalDb.getSyncQueue();
    if (queue.isEmpty) return;

    final token = await AuthService.getToken();
    final headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };

    for (final item in queue) {
      try {
        final url = Uri.parse('${ApiConstants.baseUrl}${item['endpoint']}');
        http.Response response;

        switch (item['method']) {
          case 'POST':
            response = await http.post(
              url,
              headers: headers,
              body: item['body'],
            );
            break;
          case 'PUT':
            response = await http.put(
              url,
              headers: headers,
              body: item['body'],
            );
            break;
          case 'PATCH':
            response = await http.patch(
              url,
              headers: headers,
              body: item['body'],
            );
            break;
          case 'DELETE':
            response = await http.delete(url, headers: headers);
            break;
          default:
            continue;
        }

        if (response.statusCode < 300) {
          await LocalDb.clearSyncItem(item['id'] as int);
        }
      } catch (_) {
        break; // Stop syncing if still offline
      }
    }
  }
}
