class Attachment {
  final int id;
  final String attachableType;
  final int attachableId;
  final String filePath;
  final String fileName;
  final String fileMimeType;
  final int fileSize;

  Attachment({
    required this.id,
    required this.attachableType,
    required this.attachableId,
    required this.filePath,
    required this.fileName,
    required this.fileMimeType,
    required this.fileSize,
  });

  factory Attachment.fromJson(Map<String, dynamic> json) {
    return Attachment(
      id: json['id'],
      attachableType: json['attachable_type'],
      attachableId: json['attachable_id'],
      filePath: json['file_path'],
      fileName: json['file_name'],
      fileMimeType: json['file_mime_type'],
      fileSize: json['file_size'],
    );
  }
}
