import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:package_info_plus/package_info_plus.dart';
import 'package:ota_update/ota_update.dart';
import '../config/api_config.dart';
import 'dart:async';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../main.dart'; // To access navigatorKey

class UpdateService {
  static Future<Map<String, dynamic>?> checkUpdate() async {
    try {
      final response = await http.get(
        Uri.parse('${ApiConstants.baseUrl}/check-version'),
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
    final localVersion = packageInfo.version.split('+')[0];
    final serverVerClean = serverVersion.split('+')[0];

    print('=========== UPDATE CHECK ===========');
    print('Local Version: $localVersion');
    print('Server Version: $serverVerClean');
    print('====================================');

    return localVersion != serverVerClean;
  }

  static Future<void> checkAndShowUpdate([BuildContext? context]) async {
    final config = await checkUpdate();
    if (config != null) {
      final serverVersion = config['latest_version'];
      final downloadUrl = config['download_url'];

      final isAvailable = await isUpdateAvailable(serverVersion);
      if (isAvailable) {
        final ctx = context ?? navigatorKey.currentContext;
        if (ctx != null && ctx.mounted) {
          showUpdateDialog(ctx, serverVersion, downloadUrl);
        }
      }
    }
  }

  static void showUpdateDialog(
    BuildContext context,
    String version,
    String url,
  ) {
    bool isDownloading = false;
    double downloadProgress = 0;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => StatefulBuilder(
        builder: (context, setDialogState) {
          return AlertDialog(
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(20),
            ),
            title: Text(
              'New Update Available',
              style: GoogleFonts.inter(fontWeight: FontWeight.w800),
            ),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'New version $version is available. Update now to get the latest features.',
                  style: GoogleFonts.inter(fontSize: 14),
                ),
                if (isDownloading) ...[
                  const SizedBox(height: 20),
                  ClipRRect(
                    borderRadius: BorderRadius.circular(8),
                    child: LinearProgressIndicator(
                      value: downloadProgress / 100,
                      backgroundColor: Colors.grey[200],
                      valueColor: const AlwaysStoppedAnimation<Color>(
                        Color(0xFF4F46E5),
                      ),
                      minHeight: 10,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Downloading: ${downloadProgress.toInt()}%',
                    style: GoogleFonts.inter(
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ],
              ],
            ),
            actions: [
              if (!isDownloading)
                TextButton(
                  onPressed: () => Navigator.pop(context),
                  child: Text(
                    'Later',
                    style: GoogleFonts.inter(color: Colors.grey),
                  ),
                ),
              if (!isDownloading)
                ElevatedButton(
                  onPressed: () {
                    setDialogState(() => isDownloading = true);
                    performUpdate(url).listen((OtaEvent event) {
                      setDialogState(() {
                        downloadProgress =
                            double.tryParse(event.value ?? '0') ?? 0;
                        if (event.status == OtaStatus.INSTALLING ||
                            event.status == OtaStatus.ALREADY_RUNNING_ERROR ||
                            event.status ==
                                OtaStatus.PERMISSION_NOT_GRANTED_ERROR ||
                            event.status == OtaStatus.INTERNAL_ERROR ||
                            event.status == OtaStatus.DOWNLOAD_ERROR ||
                            event.status == OtaStatus.CHECKSUM_ERROR) {
                          isDownloading = false;
                        }
                      });
                    });
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF4F46E5),
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                  child: Text(
                    'Update Now',
                    style: GoogleFonts.inter(fontWeight: FontWeight.w700),
                  ),
                ),
            ],
          );
        },
      ),
    );
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
