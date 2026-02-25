import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:open_file_plus/open_file_plus.dart';
import 'package:path_provider/path_provider.dart';
import 'package:dio/dio.dart';
import '../models/attachment.dart';
import '../services/attachment_service.dart';

class AttachmentList extends StatelessWidget {
  final List<Attachment> attachments;
  final Function(int) onDelete;
  final bool isOwner;

  const AttachmentList({
    super.key,
    required this.attachments,
    required this.onDelete,
    this.isOwner = true,
  });

  Future<void> _handleTap(BuildContext context, Attachment attachment) async {
    // If the backend has provided fileUrl via eager loading and accessor, use it.
    // Otherwise fallback to the view endpoint.
    String? url = attachment.fileUrl;

    if (url == null) {
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (_) => const Center(child: CircularProgressIndicator()),
      );
      try {
        url = await AttachmentService.getViewUrl(attachment.id);
      } finally {
        if (context.mounted) Navigator.pop(context);
      }
    }

    if (url == null) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Could not load attachment')),
        );
      }
      return;
    }

    // Show downloading indicator
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => const Center(
        child: Card(
          child: Padding(
            padding: EdgeInsets.all(16.0),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                CircularProgressIndicator(),
                SizedBox(height: 16),
                Text('Downloading file...'),
              ],
            ),
          ),
        ),
      ),
    );

    try {
      final tempDir = await getTemporaryDirectory();
      // Use timestamp to prevent caching issues if same filename is downloaded again
      final timestamp = DateTime.now().millisecondsSinceEpoch;
      final savePath = '${tempDir.path}/${timestamp}_${attachment.fileName}';

      final dio = Dio();
      await dio.download(url, savePath);

      if (context.mounted) Navigator.pop(context); // close dialog

      final result = await OpenFile.open(savePath);
      if (result.type != ResultType.done &&
          result.type != ResultType.fileNotFound) {
        if (context.mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text('Could not open: ${result.message}')),
          );
        }
      }
    } catch (e) {
      if (context.mounted) {
        Navigator.pop(context); // close dialog
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text('Error downloading: $e')));
      }
    }
  }

  Widget _buildIcon(Attachment attachment) {
    final lowerName = attachment.fileName.toLowerCase();

    if (lowerName.endsWith('.jpg') ||
        lowerName.endsWith('.jpeg') ||
        lowerName.endsWith('.png')) {
      return const Icon(Icons.image, color: Colors.blue);
    } else if (lowerName.endsWith('.mp4') || lowerName.endsWith('.mov')) {
      return const Icon(Icons.videocam, color: Colors.red);
    } else if (lowerName.endsWith('.mp3') || lowerName.endsWith('.wav')) {
      return const Icon(Icons.audiotrack, color: Colors.purple);
    } else if (lowerName.endsWith('.pdf') ||
        lowerName.endsWith('.doc') ||
        lowerName.endsWith('.docx')) {
      return const Icon(Icons.description, color: Colors.redAccent);
    } else {
      return const Icon(Icons.insert_drive_file, color: Colors.grey);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (attachments.isEmpty) return const SizedBox.shrink();

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: ExpansionTile(
        title: Text(
          'Lampiran (${attachments.length})',
          style: GoogleFonts.inter(fontSize: 14, fontWeight: FontWeight.w600),
        ),
        initiallyExpanded: false,
        childrenPadding: const EdgeInsets.only(bottom: 8.0),
        children: [
          ListView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: attachments.length,
            itemBuilder: (context, index) {
              final attachment = attachments[index];
              return Padding(
                padding: const EdgeInsets.only(bottom: 8.0),
                child: Container(
                  decoration: BoxDecoration(
                    border: Border.all(color: Colors.grey[200]!),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: ListTile(
                    leading: _buildIcon(attachment),
                    title: Text(
                      attachment.fileName,
                      style: GoogleFonts.inter(fontSize: 14),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    subtitle: Text(
                      '${(attachment.fileSize / 1024).toStringAsFixed(1)} KB',
                      style: GoogleFonts.inter(
                        fontSize: 12,
                        color: Colors.grey,
                      ),
                    ),
                    onTap: () => _handleTap(context, attachment),
                    trailing: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        IconButton(
                          icon: const Icon(
                            Icons.open_in_new,
                            color: Colors.blue,
                            size: 20,
                          ),
                          tooltip: 'Buka File',
                          onPressed: () => _handleTap(context, attachment),
                        ),
                        if (isOwner)
                          IconButton(
                            icon: const Icon(
                              Icons.delete_outline,
                              color: Colors.red,
                              size: 20,
                            ),
                            onPressed: () => onDelete(attachment.id),
                          ),
                      ],
                    ),
                  ),
                ),
              );
            },
          ),
        ],
      ),
    );
  }
}
