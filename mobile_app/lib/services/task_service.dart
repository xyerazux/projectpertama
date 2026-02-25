import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';
import '../models/task.dart';
import '../models/category.dart';
import '../services/auth_service.dart';

class TaskService {
  // ── Auth headers ────────────────────────────────
  static Future<Map<String, String>> _headers() async {
    final token = await AuthService.getToken();
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  // ── GET Dashboard ───────────────────────────────
  static Future<Map<String, dynamic>> getDashboard() async {
    final response = await http.get(
      Uri.parse('${ApiConstants.baseUrl}/dashboard'),
      headers: await _headers(),
    );
    final body = jsonDecode(response.body);
    if (body['success'] == true) return body['data'];
    return {};
  }

  // ── GET pending tasks ───────────────────────────
  static Future<List<Task>> getTasks({
    String? priority,
    int? categoryId,
  }) async {
    String url = '${ApiConstants.baseUrl}/tasks';
    final params = <String, String>{};
    if (priority != null) params['priority'] = priority;
    if (categoryId != null) params['category_id'] = categoryId.toString();
    if (params.isNotEmpty) {
      url += '?${Uri(queryParameters: params).query}';
    }

    final response = await http.get(Uri.parse(url), headers: await _headers());
    final body = jsonDecode(response.body);

    if (body['success'] == true) {
      final data = body['data']['data'] as List;
      return data.map((j) => Task.fromJson(j)).toList();
    }
    return [];
  }

  // ── GET completed tasks ─────────────────────────
  static Future<List<Task>> getCompletedTasks() async {
    final response = await http.get(
      Uri.parse('${ApiConstants.baseUrl}/tasks/completed'),
      headers: await _headers(),
    );
    final body = jsonDecode(response.body);
    if (body['success'] == true) {
      final data = body['data'] as List;
      return data.map((j) => Task.fromJson(j)).toList();
    }
    return [];
  }

  // ── GET trashed tasks ───────────────────────────
  static Future<List<Task>> getTrashedTasks() async {
    final response = await http.get(
      Uri.parse('${ApiConstants.baseUrl}/tasks/trash'),
      headers: await _headers(),
    );
    final body = jsonDecode(response.body);
    if (body['success'] == true) {
      final data = body['data']['data'] as List;
      return data.map((j) => Task.fromJson(j)).toList();
    }
    return [];
  }

  // ── GET categories ──────────────────────────────
  static Future<List<Category>> getCategories() async {
    final response = await http.get(
      Uri.parse('${ApiConstants.baseUrl}/categories'),
      headers: await _headers(),
    );
    final body = jsonDecode(response.body);
    if (body['success'] == true) {
      final data = body['data'] as List;
      return data.map((j) => Category.fromJson(j)).toList();
    }
    return [];
  }

  // ── Categories CRUD ─────────────────────────────
  static Future<Map<String, dynamic>> createCategory(String name) async {
    final response = await http.post(
      Uri.parse('${ApiConstants.baseUrl}/categories'),
      headers: await _headers(),
      body: jsonEncode({'name': name}),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> updateCategory(
    int id,
    String name,
  ) async {
    final response = await http.put(
      Uri.parse('${ApiConstants.baseUrl}/categories/$id'),
      headers: await _headers(),
      body: jsonEncode({'name': name}),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> deleteCategory(int id) async {
    final response = await http.delete(
      Uri.parse('${ApiConstants.baseUrl}/categories/$id'),
      headers: await _headers(),
    );
    return jsonDecode(response.body);
  }

  // ── POST create task ────────────────────────────
  static Future<Map<String, dynamic>> createTask(
    Map<String, dynamic> data,
  ) async {
    final response = await http.post(
      Uri.parse('${ApiConstants.baseUrl}/tasks'),
      headers: await _headers(),
      body: jsonEncode(data),
    );
    return jsonDecode(response.body);
  }

  // ── PUT update task ─────────────────────────────
  static Future<Map<String, dynamic>> updateTask(
    int taskId,
    Map<String, dynamic> data,
  ) async {
    final response = await http.put(
      Uri.parse('${ApiConstants.baseUrl}/tasks/$taskId'),
      headers: await _headers(),
      body: jsonEncode(data),
    );
    return jsonDecode(response.body);
  }

  // ── POST complete task ──────────────────────────
  static Future<Map<String, dynamic>> completeTask(int taskId) async {
    final response = await http.post(
      Uri.parse('${ApiConstants.baseUrl}/tasks/$taskId/complete'),
      headers: await _headers(),
    );
    return jsonDecode(response.body);
  }

  // ── POST motivation for task ───────────────────────
  static Future<String?> getMotivation(String taskTitle) async {
    try {
      final response = await http.post(
        Uri.parse('${ApiConstants.baseUrl}/tasks/motivate'),
        headers: await _headers(),
        body: jsonEncode({'task_title': taskTitle}),
      );
      final body = jsonDecode(response.body);
      return body['motivation'];
    } catch (_) {
      return null;
    }
  }

  // ── POST submit reflection ────────────────────────
  static Future<Map<String, dynamic>> submitReflection({
    required String mood,
    String? note,
  }) async {
    final response = await http.post(
      Uri.parse('${ApiConstants.baseUrl}/reflections'),
      headers: await _headers(),
      body: jsonEncode({'mood': mood, 'note': note}),
    );
    return jsonDecode(response.body);
  }

  // ── DELETE soft delete ──────────────────────────
  static Future<Map<String, dynamic>> deleteTask(int taskId) async {
    final response = await http.delete(
      Uri.parse('${ApiConstants.baseUrl}/tasks/$taskId'),
      headers: await _headers(),
    );
    return jsonDecode(response.body);
  }

  // ── PATCH restore from trash ────────────────────
  static Future<Map<String, dynamic>> restoreTask(int taskId) async {
    final response = await http.patch(
      Uri.parse('${ApiConstants.baseUrl}/tasks/$taskId/restore'),
      headers: await _headers(),
    );
    return jsonDecode(response.body);
  }

  // ── DELETE force delete ─────────────────────────
  static Future<Map<String, dynamic>> forceDeleteTask(int taskId) async {
    final response = await http.delete(
      Uri.parse('${ApiConstants.baseUrl}/tasks/$taskId/force'),
      headers: await _headers(),
    );
    return jsonDecode(response.body);
  }

  // ── PATCH toggle subtask ────────────────────────
  static Future<Map<String, dynamic>> toggleSubtask(int subtaskId) async {
    final response = await http.patch(
      Uri.parse('${ApiConstants.baseUrl}/subtasks/$subtaskId/toggle'),
      headers: await _headers(),
    );
    return jsonDecode(response.body);
  }

  // ── Roadmaps ────────────────────────────────────
  static Future<List<dynamic>> getRoadmaps() async {
    final response = await http.get(
      Uri.parse('${ApiConstants.baseUrl}/roadmaps'),
      headers: await _headers(),
    );
    final body = jsonDecode(response.body);
    if (body['success'] == true) return body['data'] as List;
    return [];
  }

  static Future<Map<String, dynamic>> getRoadmap(int id) async {
    final response = await http.get(
      Uri.parse('${ApiConstants.baseUrl}/roadmaps/$id'),
      headers: await _headers(),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> createRoadmap(
    Map<String, dynamic> data,
  ) async {
    final response = await http.post(
      Uri.parse('${ApiConstants.baseUrl}/roadmaps'),
      headers: await _headers(),
      body: jsonEncode(data),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> deleteRoadmap(int id) async {
    final response = await http.delete(
      Uri.parse('${ApiConstants.baseUrl}/roadmaps/$id'),
      headers: await _headers(),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> createRoadmapStep(
    int roadmapId,
    Map<String, dynamic> data,
  ) async {
    final response = await http.post(
      Uri.parse('${ApiConstants.baseUrl}/roadmaps/$roadmapId/steps'),
      headers: await _headers(),
      body: jsonEncode(data),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> toggleRoadmapStep(int stepId) async {
    final response = await http.patch(
      Uri.parse('${ApiConstants.baseUrl}/roadmap-steps/$stepId/toggle'),
      headers: await _headers(),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> deleteRoadmapStep(int stepId) async {
    final response = await http.delete(
      Uri.parse('${ApiConstants.baseUrl}/roadmap-steps/$stepId'),
      headers: await _headers(),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> updateRoadmapStep(
    int stepId,
    Map<String, dynamic> data,
  ) async {
    final response = await http.patch(
      Uri.parse('${ApiConstants.baseUrl}/roadmap-steps/$stepId'),
      headers: await _headers(),
      body: jsonEncode(data),
    );
    return jsonDecode(response.body);
  }

  // ── Roadmap trash ───────────────────────────────
  static Future<List<dynamic>> getTrashedRoadmaps() async {
    final response = await http.get(
      Uri.parse('${ApiConstants.baseUrl}/roadmaps/trash'),
      headers: await _headers(),
    );
    final body = jsonDecode(response.body);
    if (body['success'] == true) return body['data'] as List;
    return [];
  }

  static Future<Map<String, dynamic>> restoreRoadmap(int id) async {
    final response = await http.patch(
      Uri.parse('${ApiConstants.baseUrl}/roadmaps/$id/restore'),
      headers: await _headers(),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> forceDeleteRoadmap(int id) async {
    final response = await http.delete(
      Uri.parse('${ApiConstants.baseUrl}/roadmaps/$id/force'),
      headers: await _headers(),
    );
    return jsonDecode(response.body);
  }

  // ── Profile ─────────────────────────────────────
  static Future<Map<String, dynamic>> getProfile() async {
    final response = await http.get(
      Uri.parse('${ApiConstants.baseUrl}/profile'),
      headers: await _headers(),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> updateProfile(
    Map<String, dynamic> data,
  ) async {
    final response = await http.put(
      Uri.parse('${ApiConstants.baseUrl}/profile'),
      headers: await _headers(),
      body: jsonEncode(data),
    );
    return jsonDecode(response.body);
  }

  static Future<Map<String, dynamic>> updatePriorityMode(String mode) async {
    final response = await http.patch(
      Uri.parse('${ApiConstants.baseUrl}/profile/priority'),
      headers: await _headers(),
      body: jsonEncode({'priority_mode': mode}),
    );
    return jsonDecode(response.body);
  }
}
