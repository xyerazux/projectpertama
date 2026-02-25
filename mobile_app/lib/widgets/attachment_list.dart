import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:video_player/video_player.dart';
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
    // Show loading indicator
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => const Center(child: CircularProgressIndicator()),
    );

    try {
      final url = await AttachmentService.getViewUrl(attachment.id);
      if (context.mounted) Navigator.pop(context); // Close loading

      if (url == null) {
        if (context.mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Could not load attachment')),
          );
        }
        return;
      }

      if (attachment.fileMimeType.startsWith('image/')) {
        if (context.mounted) {
          _showImageFullScreen(context, url);
        }
      } else if (attachment.fileMimeType.startsWith('video/')) {
        if (context.mounted) {
          _showVideoFullScreen(context, url);
        }
      } else {
        // Document or other
        if (await canLaunchUrl(Uri.parse(url))) {
          await launchUrl(Uri.parse(url), mode: LaunchMode.externalApplication);
        } else {
          if (context.mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(content: Text('Could not open file')),
            );
          }
        }
      }
    } catch (e) {
      if (context.mounted) {
        Navigator.pop(context); // Close loading if open
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text('Error: $e')));
      }
    }
  }

  void _showImageFullScreen(BuildContext context, String url) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => Scaffold(
          backgroundColor: Colors.black,
          appBar: AppBar(
            backgroundColor: Colors.black,
            iconTheme: const IconThemeData(color: Colors.white),
          ),
          body: Center(
            child: InteractiveViewer(
              child: CachedNetworkImage(
                imageUrl: url,
                placeholder: (context, url) =>
                    const CircularProgressIndicator(),
                errorWidget: (context, url, error) =>
                    const Icon(Icons.error, color: Colors.white),
              ),
            ),
          ),
        ),
      ),
    );
  }

  void _showVideoFullScreen(BuildContext context, String url) {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (context) => VideoPlayerScreen(url: url)),
    );
  }

  Widget _buildIcon(Attachment attachment) {
    final mime = attachment.fileMimeType;
    if (mime.startsWith('image/')) {
      return const Icon(Icons.image, color: Colors.blue);
    } else if (mime.startsWith('video/')) {
      return const Icon(Icons.video_library, color: Colors.red);
    } else if (mime.startsWith('audio/')) {
      return const Icon(Icons.audiotrack, color: Colors.purple);
    } else if (mime.contains('pdf')) {
      return const Icon(Icons.picture_as_pdf, color: Colors.redAccent);
    } else {
      return const Icon(Icons.insert_drive_file, color: Colors.grey);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (attachments.isEmpty) return const SizedBox.shrink();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const SizedBox(height: 16),
        Text(
          'Attachments',
          style: GoogleFonts.inter(fontSize: 14, fontWeight: FontWeight.w600),
        ),
        const SizedBox(height: 8),
        ListView.separated(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          itemCount: attachments.length,
          separatorBuilder: (_, __) => const SizedBox(height: 8),
          itemBuilder: (context, index) {
            final attachment = attachments[index];
            return Container(
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
                  style: GoogleFonts.inter(fontSize: 12, color: Colors.grey),
                ),
                onTap: () => _handleTap(context, attachment),
                trailing: isOwner
                    ? IconButton(
                        icon: const Icon(
                          Icons.delete_outline,
                          color: Colors.red,
                          size: 20,
                        ),
                        onPressed: () => onDelete(attachment.id),
                      )
                    : null,
              ),
            );
          },
        ),
      ],
    );
  }
}

class VideoPlayerScreen extends StatefulWidget {
  final String url;

  const VideoPlayerScreen({super.key, required this.url});

  @override
  State<VideoPlayerScreen> createState() => _VideoPlayerScreenState();
}

class _VideoPlayerScreenState extends State<VideoPlayerScreen> {
  late VideoPlayerController _controller;

  @override
  void initState() {
    super.initState();
    _controller = VideoPlayerController.networkUrl(Uri.parse(widget.url))
      ..initialize().then((_) {
        setState(() {});
        _controller.play();
      });
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        backgroundColor: Colors.black,
        iconTheme: const IconThemeData(color: Colors.white),
      ),
      body: Center(
        child: _controller.value.isInitialized
            ? AspectRatio(
                aspectRatio: _controller.value.aspectRatio,
                child: Stack(
                  alignment: Alignment.bottomCenter,
                  children: [
                    VideoPlayer(_controller),
                    VideoProgressIndicator(_controller, allowScrubbing: true),
                    Center(
                      child: IconButton(
                        icon: Icon(
                          _controller.value.isPlaying
                              ? Icons.pause
                              : Icons.play_arrow,
                          color: Colors.white,
                          size: 50,
                        ),
                        onPressed: () {
                          setState(() {
                            _controller.value.isPlaying
                                ? _controller.pause()
                                : _controller.play();
                          });
                        },
                      ),
                    ),
                  ],
                ),
              )
            : const CircularProgressIndicator(),
      ),
    );
  }
}
