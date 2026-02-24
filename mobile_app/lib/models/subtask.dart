class Subtask {
  final int id;
  final String title;
  bool isCompleted;

  Subtask({required this.id, required this.title, required this.isCompleted});

  factory Subtask.fromJson(Map<String, dynamic> json) {
    return Subtask(
      id: json['id'],
      title: json['title'] ?? '',
      isCompleted: json['is_completed'] == true || json['is_completed'] == 1,
    );
  }
}
