import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:package_info_plus/package_info_plus.dart';
import 'package:ota_update/ota_update.dart';
import '../config/api_config.dart';
import 'dart:async';

class UpdateService {
  static Future<Map<String, dynamic>?> checkUpdate() async {
    try {
      final response = await http.get(
        Uri.parse('${ApiConstants.baseUrl}/check-update'),
      );

      if (response.statusCode == 200) {
        final body = jsonDecode(response.body);
        if (body['success'] == true) {
          return body['data'];
        }
      }
    } catch (e) {
      print('Error checking update: $e');
    }
    return null;
  }

  static Future<bool> isUpdateAvailable(String serverVersion) async {
    final packageInfo = await PackageInfo.fromPlatform();
    final localVersion = packageInfo.version;

    return _compareVersions(serverVersion, localVersion) > 0;
  }

  static int _compareVersions(String v1, String v2) {
    List<int> v1Parts = v1.split('.').map(int.parse).toList();
    List<int> v2Parts = v2.split('.').map(int.parse).toList();

    for (int i = 0; i < v1Parts.length; i++) {
      if (i >= v2Parts.length) return 1;
      if (v1Parts[i] > v2Parts[i]) return 1;
      if (v1Parts[i] < v2Parts[i]) return -1;
    }

    return v1Parts.length < v2Parts.length ? -1 : 0;
  }

  static Stream<OtaEvent> performUpdate(String downloadUrl) {
    try {
      return OtaUpdate().execute(
        downloadUrl,
        destinationFilename: 'app-update.apk',
      );
    } catch (e) {
      print('OTA Update Error: $e');
      rethrow;
    }
  }
}
