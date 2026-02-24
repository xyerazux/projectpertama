import 'subtask.dart';

class Task {
  final int id;
  final String title;
  final String? description;
  final String? linkAttachment;
  final String priority;
  final String status;
  final String? deadline;
  final int? categoryId;
  final String? categoryName;
  final DateTime? completedAt;
  final List<Subtask> subtasks;

  Task({
    required this.id,
    required this.title,
    this.description,
    this.linkAttachment,
    required this.priority,
    required this.status,
    this.deadline,
    this.categoryId,
    this.categoryName,
    this.completedAt,
    this.subtasks = const [],
  });

  factory Task.fromJson(Map<String, dynamic> json) {
    List<Subtask> subtaskList = [];
    if (json['subtasks'] != null) {
      subtaskList = (json['subtasks'] as List)
          .map((s) => Subtask.fromJson(s))
          .toList();
    }

    return Task(
      id: json['id'],
      title: json['title'] ?? '',
      description: json['description'],
      linkAttachment: json['link_attachment'],
      priority: json['priority'] ?? 'low',
      status: json['status'] ?? 'pending',
      deadline: json['deadline'],
      categoryId: json['category_id'],
      categoryName: json['category'] != null ? json['category']['name'] : null,
      completedAt: json['completed_at'] != null
          ? DateTime.tryParse(json['completed_at'])
          : null,
      subtasks: subtaskList,
    );
  }

  int get completedSubtaskCount => subtasks.where((s) => s.isCompleted).length;
  int get totalSubtaskCount => subtasks.length;
  double get progressPercent =>
      totalSubtaskCount > 0 ? completedSubtaskCount / totalSubtaskCount : 0.0;
}
