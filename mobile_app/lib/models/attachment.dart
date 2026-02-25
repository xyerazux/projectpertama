class Attachment {
  final int id;
  final String attachableType;
  final int attachableId;
  final String filePath;
  final String fileName;
  final String fileMimeType;
  final int fileSize;
  final String? fileUrl;

  Attachment({
    required this.id,
    required this.attachableType,
    required this.attachableId,
    required this.filePath,
    required this.fileName,
    required this.fileMimeType,
    required this.fileSize,
    this.fileUrl,
  });

  /// Returns the fully-qualified download URL for this attachment.
  ///
  /// Priority order:
  /// 1. `fileUrl` from the server if it already includes the scheme (https://)
  /// 2. Built from `filePath`: https://productivityapp.up.railway.app/storage/{filePath}
  ///
  /// This prevents "No host specified" errors when the database only stores
  /// relative paths like `attachments/myfile.pdf`.
  String resolveUrl() {
    // Use fileUrl if the server already gave us a full URL
    if (fileUrl != null && fileUrl!.startsWith('http')) {
      return fileUrl!;
    }

    // Build from filePath. filePath is relative like "attachments/file.jpg"
    // so we strip any leading slash to avoid double slashes.
    const storageBase = 'https://productivityapp.up.railway.app/storage';
    final cleanPath = filePath.startsWith('/') ? filePath : '/$filePath';
    return '$storageBase$cleanPath';
  }

  factory Attachment.fromJson(Map<String, dynamic> json) {
    return Attachment(
      id: json['id'],
      attachableType: json['attachable_type'],
      attachableId: json['attachable_id'],
      filePath: json['file_path'],
      fileName: json['file_name'],
      fileMimeType: json['file_mime_type'],
      fileSize: json['file_size'],
      fileUrl: json['file_url'],
    );
  }
}
