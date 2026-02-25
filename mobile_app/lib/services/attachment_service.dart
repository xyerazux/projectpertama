import 'dart:io';
import 'package:dio/dio.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../config/api_config.dart';
import '../models/attachment.dart';

class AttachmentService {
  static const _storage = FlutterSecureStorage();
  static final Dio _dio = Dio(
    BaseOptions(
      baseUrl: ApiConstants.baseUrl,
      connectTimeout: ApiConstants.timeout,
      receiveTimeout: ApiConstants.timeout,
    ),
  );

  static Future<String?> _getToken() async {
    return await _storage.read(key: 'auth_token');
  }

  /// The array key in post request for multiple files is `files[]`.
  static Future<List<Attachment>> uploadAttachments(
    String type,
    int id,
    List<File> files,
    void Function(int count, int total) onProgress,
  ) async {
    final token = await _getToken();
    if (token == null) throw Exception('Not authenticated');

    final formData = FormData();
    for (int i = 0; i < files.length; i++) {
      formData.files.add(
        MapEntry(
          'files[]',
          await MultipartFile.fromFile(
            files[i].path,
            filename: files[i].path.split('/').last,
          ),
        ),
      );
    }

    try {
      final response = await _dio.post(
        '/$type/$id/attachments',
        data: formData,
        options: Options(
          headers: {
            'Authorization': 'Bearer $token',
            'Accept': 'application/json',
          },
        ),
        onSendProgress: onProgress,
      );

      if (response.statusCode == 201) {
        final List data = response.data['data'];
        return data.map((json) => Attachment.fromJson(json)).toList();
      } else {
        throw Exception(response.data['message'] ?? 'Failed to upload files');
      }
    } on DioException catch (e) {
      throw Exception(e.response?.data['message'] ?? e.message);
    }
  }

  static Future<bool> deleteAttachment(int attachmentId) async {
    final token = await _getToken();
    if (token == null) return false;

    try {
      final response = await _dio.delete(
        '/attachments/$attachmentId',
        options: Options(
          headers: {
            'Authorization': 'Bearer $token',
            'Accept': 'application/json',
          },
        ),
      );
      return response.statusCode == 200;
    } catch (_) {
      return false;
    }
  }

  static Future<String?> getViewUrl(int attachmentId) async {
    final token = await _getToken();
    if (token == null) return null;

    try {
      final response = await _dio.get(
        '/attachments/$attachmentId/view',
        options: Options(
          headers: {
            'Authorization': 'Bearer $token',
            'Accept': 'application/json',
          },
        ),
      );
      if (response.statusCode == 200 && response.data['success']) {
        return response.data['data']['url'];
      }
      return null;
    } catch (_) {
      return null;
    }
  }
}
