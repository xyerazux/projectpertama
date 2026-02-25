import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';
import '../models/task.dart';
import '../services/task_service.dart';

class TaskCard extends StatelessWidget {
  final Task task;
  final VoidCallback? onTap;
  final VoidCallback? onComplete;
  final VoidCallback? onDelete;
  final VoidCallback? onRestore;
  final VoidCallback? onForceDelete;
  final Function(int subtaskId)? onToggleSubtask;
  final bool showCompleteAction;
  final bool showTrashActions;

  const TaskCard({
    super.key,
    required this.task,
    this.onTap,
    this.onComplete,
    this.onDelete,
    this.onRestore,
    this.onForceDelete,
    this.onToggleSubtask,
    this.showCompleteAction = true,
    this.showTrashActions = false,
  });

  Color get _priorityColor {
    switch (task.priority.toLowerCase()) {
      case 'high':
        return const Color(0xFFEF4444);
      case 'medium':
        return const Color(0xFFF59E0B);
      default:
        return const Color(0xFF6366F1);
    }
  }

  @override
  Widget build(BuildContext context) {
    final bool isPastDeadline =
        task.deadline != null &&
        DateTime.tryParse(task.deadline!) != null &&
        DateTime.parse(task.deadline!).isBefore(DateTime.now());

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: Colors.grey[100]!),
        boxShadow: [
          BoxShadow(
            color: Colors.grey.withOpacity(0.06),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // ── Tappable content area (navigates to edit) ──
            Material(
              color: Colors.transparent,
              child: InkWell(
                onTap: onTap,
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(18, 18, 18, 0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // ── Top row: priority badge + category ──
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 10,
                              vertical: 5,
                            ),
                            decoration: BoxDecoration(
                              color: _priorityColor.withOpacity(0.1),
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: Text(
                              task.priority.toUpperCase(),
                              style: GoogleFonts.inter(
                                fontSize: 9,
                                fontWeight: FontWeight.w900,
                                letterSpacing: 2,
                                color: _priorityColor,
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          if (task.categoryName != null)
                            Text(
                              task.categoryName!.toUpperCase(),
                              style: GoogleFonts.inter(
                                fontSize: 9,
                                fontWeight: FontWeight.w800,
                                letterSpacing: 1.5,
                                color: Colors.grey[400],
                              ),
                            ),
                          const Spacer(),
                          if (isPastDeadline && task.status == 'pending')
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 8,
                                vertical: 3,
                              ),
                              decoration: BoxDecoration(
                                color: Colors.red[50],
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Text(
                                'OVERDUE',
                                style: GoogleFonts.inter(
                                  fontSize: 8,
                                  fontWeight: FontWeight.w900,
                                  letterSpacing: 1.5,
                                  color: Colors.red[400],
                                ),
                              ),
                            ),
                        ],
                      ),
                      const SizedBox(height: 12),

                      // ── Title ──
                      Text(
                        task.title,
                        style: GoogleFonts.inter(
                          fontSize: 16,
                          fontWeight: FontWeight.w800,
                          color: Colors.grey[800],
                        ),
                      ),
                      if (task.description != null &&
                          task.description!.isNotEmpty) ...[
                        const SizedBox(height: 4),
                        Text(
                          task.description!,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: GoogleFonts.inter(
                            fontSize: 12,
                            color: Colors.grey[500],
                            height: 1.4,
                          ),
                        ),
                      ],

                      // ── Attachment Link ──
                      if (task.linkAttachment != null &&
                          task.linkAttachment!.isNotEmpty) ...[
                        const SizedBox(height: 12),
                        GestureDetector(
                          onTap: () async {
                            final uri = Uri.parse(task.linkAttachment!);
                            if (await canLaunchUrl(uri)) {
                              await launchUrl(
                                uri,
                                mode: LaunchMode.externalApplication,
                              );
                            }
                          },
                          child: Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 10,
                              vertical: 6,
                            ),
                            decoration: BoxDecoration(
                              color: Colors.blue.withOpacity(0.08),
                              border: Border.all(
                                color: Colors.blue.withOpacity(0.2),
                              ),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(
                                  Icons.link_rounded,
                                  size: 14,
                                  color: Colors.blue[600],
                                ),
                                const SizedBox(width: 6),
                                Text(
                                  'View Attachment',
                                  style: GoogleFonts.inter(
                                    fontSize: 10,
                                    fontWeight: FontWeight.w700,
                                    color: Colors.blue[600],
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ],

                      // ── Subtask progress ──
                      if (task.totalSubtaskCount > 0) ...[
                        const SizedBox(height: 12),
                        Row(
                          children: [
                            Expanded(
                              child: ClipRRect(
                                borderRadius: BorderRadius.circular(6),
                                child: LinearProgressIndicator(
                                  value: task.progressPercent,
                                  backgroundColor: Colors.grey[100],
                                  valueColor: AlwaysStoppedAnimation<Color>(
                                    _priorityColor,
                                  ),
                                  minHeight: 5,
                                ),
                              ),
                            ),
                            const SizedBox(width: 10),
                            Text(
                              '${task.completedSubtaskCount}/${task.totalSubtaskCount}',
                              style: GoogleFonts.inter(
                                fontSize: 10,
                                fontWeight: FontWeight.w800,
                                color: Colors.grey[400],
                              ),
                            ),
                          ],
                        ),
                        // ── Subtask list ──
                        const SizedBox(height: 8),
                        ...task.subtasks.map(
                          (sub) => Padding(
                            padding: const EdgeInsets.symmetric(vertical: 2),
                            child: Row(
                              children: [
                                Icon(
                                  sub.isCompleted
                                      ? Icons.check_circle_rounded
                                      : Icons.circle_outlined,
                                  size: 16,
                                  color: sub.isCompleted
                                      ? _priorityColor
                                      : Colors.grey[300],
                                ),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Text(
                                    sub.title,
                                    style: GoogleFonts.inter(
                                      fontSize: 11,
                                      fontWeight: FontWeight.w600,
                                      color: sub.isCompleted
                                          ? Colors.grey[400]
                                          : Colors.grey[700],
                                      decoration: sub.isCompleted
                                          ? TextDecoration.lineThrough
                                          : TextDecoration.none,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ],
                      const SizedBox(height: 12),
                    ],
                  ),
                ),
              ),
            ),

            // ── Bottom row: deadline + actions (OUTSIDE navigation InkWell) ──
            Padding(
              padding: const EdgeInsets.fromLTRB(18, 6, 18, 18),
              child: Row(
                children: [
                  // Lightbulb Motivation Icon
                  _actionButton(
                    Icons.lightbulb_outline_rounded,
                    const Color(0xFFF59E0B),
                    () async {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text(
                            'Asking Gemini...',
                            style: GoogleFonts.inter(fontSize: 12),
                          ),
                          duration: const Duration(seconds: 1),
                          backgroundColor: Colors.grey[800],
                        ),
                      );

                      final motivation = await TaskService.getMotivation(
                        task.title,
                      );

                      if (context.mounted && motivation != null) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(
                              motivation,
                              style: GoogleFonts.inter(
                                fontSize: 13,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            behavior: SnackBarBehavior.floating,
                            backgroundColor: const Color(0xFF10B981),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(10),
                            ),
                            duration: const Duration(seconds: 4),
                          ),
                        );
                      }
                    },
                  ),
                  const SizedBox(width: 8),

                  Icon(
                    Icons.calendar_today_rounded,
                    size: 13,
                    color: Colors.grey[400],
                  ),
                  const SizedBox(width: 5),
                  Text(
                    task.deadline != null &&
                            DateTime.tryParse(task.deadline!) != null
                        ? DateFormat(
                            'dd MMM yyyy',
                          ).format(DateTime.parse(task.deadline!))
                        : '—',
                    style: GoogleFonts.inter(
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                      color: isPastDeadline
                          ? Colors.red[400]
                          : Colors.grey[500],
                    ),
                  ),
                  const Spacer(),
                  if (showTrashActions) ...[
                    _actionButton(
                      Icons.restore_rounded,
                      Colors.green[600]!,
                      onRestore,
                    ),
                    const SizedBox(width: 6),
                    _actionButton(
                      Icons.delete_forever_rounded,
                      Colors.red[600]!,
                      onForceDelete,
                    ),
                  ] else ...[
                    if (showCompleteAction)
                      _actionButton(
                        Icons.check_rounded,
                        const Color(0xFF4F46E5),
                        onComplete,
                      ),
                    const SizedBox(width: 6),
                    _actionButton(
                      Icons.delete_outline_rounded,
                      Colors.red[400]!,
                      onDelete,
                    ),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _actionButton(IconData icon, Color color, VoidCallback? onPressed) {
    return SizedBox(
      width: 42,
      height: 42,
      child: Container(
        decoration: BoxDecoration(
          color: color.withOpacity(0.08),
          borderRadius: BorderRadius.circular(10),
        ),
        child: IconButton(
          onPressed: onPressed,
          icon: Icon(icon, size: 20, color: color),
          padding: EdgeInsets.zero,
          splashRadius: 22,
        ),
      ),
    );
  }
}
