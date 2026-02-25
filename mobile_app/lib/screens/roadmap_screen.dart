import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import 'package:confetti/confetti.dart';
import '../services/task_service.dart';
import '../services/local_db.dart';
import '../services/connectivity_service.dart';
import 'dart:io';
import 'package:file_picker/file_picker.dart';
import '../models/attachment.dart';
import '../services/attachment_service.dart';
import '../widgets/attachment_list.dart';

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// ROADMAP SCREEN — Vertical Timeline + Filters + Stats
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
class RoadmapScreen extends StatefulWidget {
  const RoadmapScreen({super.key});

  @override
  State<RoadmapScreen> createState() => RoadmapScreenState();
}

class RoadmapScreenState extends State<RoadmapScreen> {
  List<dynamic> _roadmaps = [];
  bool _loading = true;
  String? _statusFilter;
  String _searchQuery = '';
  final Set<int> _expanded = {};
  late ConfettiController _confettiCtrl;

  @override
  void initState() {
    super.initState();
    _confettiCtrl = ConfettiController(duration: const Duration(seconds: 2));
    loadData();
  }

  @override
  void dispose() {
    _confettiCtrl.dispose();
    super.dispose();
  }

  Future<void> loadData() async {
    setState(() => _loading = true);
    try {
      if (ConnectivityService.isOnline) {
        final data = await TaskService.getRoadmaps();
        await LocalDb.cacheRoadmaps(
          List<Map<String, dynamic>>.from(
            data.map((e) => Map<String, dynamic>.from(e)),
          ),
        );
        if (mounted) {
          setState(() {
            _roadmaps = data;
            _loading = false;
          });
        }
      } else {
        final cached = await LocalDb.getCachedRoadmaps();
        if (mounted) {
          setState(() {
            _roadmaps = cached;
            _loading = false;
          });
        }
      }
    } catch (_) {
      if (mounted) setState(() => _loading = false);
    }
  }

  List<dynamic> get _filtered {
    var list = _roadmaps;
    if (_statusFilter != null) {
      list = list.where((r) => r['status'] == _statusFilter).toList();
    }
    if (_searchQuery.isNotEmpty) {
      list = list
          .where(
            (r) => (r['title'] ?? '').toString().toLowerCase().contains(
              _searchQuery.toLowerCase(),
            ),
          )
          .toList();
    }
    return list;
  }

  int get _totalGoals => _roadmaps.length;
  int get _doneGoals =>
      _roadmaps.where((r) => r['status'] == 'completed').length;
  int get _pendingGoals => _totalGoals - _doneGoals;

  Color _statusColor(String? s) {
    switch (s) {
      case 'in_progress':
        return const Color(0xFF4F46E5);
      case 'completed':
        return const Color(0xFF10B981);
      case 'on_hold':
        return const Color(0xFFF59E0B);
      default:
        return const Color(0xFF94A3B8);
    }
  }

  String _statusLabel(String? s) {
    switch (s) {
      case 'in_progress':
        return 'IN PROGRESS';
      case 'completed':
        return 'COMPLETED';
      case 'on_hold':
        return 'ON HOLD';
      default:
        return 'PLANNED';
    }
  }

  IconData _statusIcon(String? s) {
    switch (s) {
      case 'in_progress':
        return Icons.play_circle_outline_rounded;
      case 'completed':
        return Icons.check_circle_outline_rounded;
      case 'on_hold':
        return Icons.pause_circle_outline_rounded;
      default:
        return Icons.schedule_rounded;
    }
  }

  // ── Create Roadmap (Modal) ──
  void _showCreateModal() {
    final titleC = TextEditingController();
    final descC = TextEditingController();
    String status = 'planned';
    DateTime? targetDate;
    String? titleError;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setBS) => Container(
          padding: EdgeInsets.only(
            left: 24,
            right: 24,
            top: 20,
            bottom: MediaQuery.of(ctx).viewInsets.bottom + 20,
          ),
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
          ),
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Center(
                  child: Container(
                    width: 40,
                    height: 4,
                    decoration: BoxDecoration(
                      color: Colors.grey[300],
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                Text(
                  'NEW ROADMAP',
                  style: GoogleFonts.inter(
                    fontSize: 12,
                    fontWeight: FontWeight.w900,
                    letterSpacing: 2,
                    color: Colors.grey[800],
                  ),
                ),
                const SizedBox(height: 14),
                _inputBox(titleC, 'Roadmap title (min 3 chars)'),
                if (titleError != null)
                  Padding(
                    padding: const EdgeInsets.only(top: 4, left: 4),
                    child: Text(
                      titleError!,
                      style: GoogleFonts.inter(
                        fontSize: 11,
                        color: Colors.red[600],
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                const SizedBox(height: 8),
                _inputBox(descC, 'Description (optional)', maxLines: 2),
                const SizedBox(height: 8),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14),
                  decoration: BoxDecoration(
                    color: Colors.grey[50],
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: DropdownButtonHideUnderline(
                    child: DropdownButton<String>(
                      value: status,
                      isExpanded: true,
                      style: GoogleFonts.inter(
                        fontSize: 13,
                        fontWeight: FontWeight.w700,
                        color: Colors.grey[700],
                      ),
                      items: ['planned', 'in_progress', 'on_hold', 'completed']
                          .map(
                            (s) => DropdownMenuItem(
                              value: s,
                              child: Text(_statusLabel(s)),
                            ),
                          )
                          .toList(),
                      onChanged: (v) => setBS(() => status = v!),
                    ),
                  ),
                ),
                const SizedBox(height: 8),
                InkWell(
                  onTap: () async {
                    final picked = await showDatePicker(
                      context: ctx,
                      initialDate: DateTime.now(),
                      firstDate: DateTime(2020),
                      lastDate: DateTime(2030),
                      builder: (c, child) => Theme(
                        data: Theme.of(c).copyWith(
                          colorScheme: const ColorScheme.light(
                            primary: Color(0xFF4F46E5),
                          ),
                        ),
                        child: child!,
                      ),
                    );
                    if (picked != null) setBS(() => targetDate = picked);
                  },
                  child: Container(
                    width: double.infinity,
                    padding: const EdgeInsets.symmetric(
                      horizontal: 18,
                      vertical: 16,
                    ),
                    decoration: BoxDecoration(
                      color: Colors.grey[50],
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Row(
                      children: [
                        Icon(
                          Icons.calendar_today_rounded,
                          size: 16,
                          color: Colors.grey[400],
                        ),
                        const SizedBox(width: 10),
                        Text(
                          targetDate != null
                              ? DateFormat('dd MMM yyyy').format(targetDate!)
                              : 'Target date (optional)',
                          style: GoogleFonts.inter(
                            fontSize: 13,
                            fontWeight: FontWeight.w700,
                            color: targetDate != null
                                ? Colors.grey[700]
                                : Colors.grey[400],
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 14),
                SizedBox(
                  width: double.infinity,
                  height: 50,
                  child: ElevatedButton(
                    onPressed: () async {
                      if (titleC.text.trim().length < 3) {
                        setBS(
                          () => titleError =
                              'Title must be at least 3 characters',
                        );
                        return;
                      }
                      Navigator.pop(ctx);
                      if (ConnectivityService.isOnline) {
                        await TaskService.createRoadmap({
                          'title': titleC.text.trim(),
                          'description': descC.text.trim().isEmpty
                              ? null
                              : descC.text.trim(),
                          'status': status,
                          'target_date': targetDate != null
                              ? DateFormat('yyyy-MM-dd').format(targetDate!)
                              : null,
                        });
                      } else {
                        await LocalDb.addToSyncQueue(
                          'create_roadmap',
                          '/roadmaps',
                          'POST',
                          jsonEncode({
                            'title': titleC.text.trim(),
                            'description': descC.text.trim().isEmpty
                                ? null
                                : descC.text.trim(),
                            'status': status,
                            'target_date': targetDate != null
                                ? DateFormat('yyyy-MM-dd').format(targetDate!)
                                : null,
                          }),
                        );
                      }
                      _confettiCtrl.play();
                      loadData();
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF1F2937),
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(16),
                      ),
                    ),
                    child: Text(
                      'CREATE ROADMAP',
                      style: GoogleFonts.inter(
                        fontSize: 10,
                        fontWeight: FontWeight.w900,
                        letterSpacing: 2,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  // ── Safety Delete (Double Confirmation) ──
  Future<void> _showDeleteConfirm(dynamic roadmap) async {
    final steps = (roadmap['steps'] as List?) ?? [];
    final ok1 = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Row(
          children: [
            const Icon(
              Icons.warning_amber_rounded,
              color: Color(0xFFEF4444),
              size: 26,
            ),
            const SizedBox(width: 8),
            Flexible(
              child: Text(
                'Delete Roadmap',
                style: GoogleFonts.inter(fontWeight: FontWeight.w800),
              ),
            ),
          ],
        ),
        content: RichText(
          text: TextSpan(
            style: GoogleFonts.inter(fontSize: 14, color: Colors.grey[700]),
            children: [
              const TextSpan(text: 'This will permanently delete '),
              TextSpan(
                text: '"${roadmap['title']}"',
                style: const TextStyle(fontWeight: FontWeight.w800),
              ),
              TextSpan(
                text:
                    ' and ${steps.length} milestone${steps.length != 1 ? 's' : ''}.',
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: Text(
              'Cancel',
              style: GoogleFonts.inter(
                fontWeight: FontWeight.w700,
                color: Colors.grey[600],
              ),
            ),
          ),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: Text(
              'Proceed',
              style: GoogleFonts.inter(
                fontWeight: FontWeight.w700,
                color: Colors.red[600],
              ),
            ),
          ),
        ],
      ),
    );
    if (ok1 != true) return;

    final ok2 = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Text(
          'Are you absolutely sure?',
          style: GoogleFonts.inter(fontWeight: FontWeight.w800, fontSize: 16),
        ),
        content: Text(
          'This action cannot be undone. All data will be lost forever.',
          style: GoogleFonts.inter(fontSize: 13),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: Text(
              'No',
              style: GoogleFonts.inter(
                fontWeight: FontWeight.w700,
                color: Colors.grey[600],
              ),
            ),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFEF4444),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
            ),
            child: Text(
              'DELETE FOREVER',
              style: GoogleFonts.inter(
                fontWeight: FontWeight.w900,
                fontSize: 10,
                letterSpacing: 2,
              ),
            ),
          ),
        ],
      ),
    );
    if (ok2 == true) {
      await TaskService.deleteRoadmap(roadmap['id']);
      loadData();
    }
  }

  Future<void> _pickAndUploadRoadmapFiles(int index) async {
    final roadmap = _roadmaps[index];
    try {
      final result = await FilePicker.platform.pickFiles(allowMultiple: true);
      if (result == null || result.files.isEmpty) return;

      final files = result.paths
          .whereType<String>()
          .map((p) => File(p))
          .toList();
      if (files.isEmpty) return;

      setState(() {
        roadmap['_uploading'] = true;
        roadmap['_uploadProgress'] = 0.0;
      });

      final uploaded = await AttachmentService.uploadAttachments(
        'roadmaps',
        roadmap['id'],
        files,
        (count, total) {
          setState(() {
            roadmap['_uploadProgress'] = count / total;
          });
        },
      );

      setState(() {
        roadmap['attachments'] = (roadmap['attachments'] as List? ?? [])
          ..addAll(
            uploaded
                .map(
                  (a) => {
                    'id': a.id,
                    'attachable_type': a.attachableType,
                    'attachable_id': a.attachableId,
                    'file_path': a.filePath,
                    'file_name': a.fileName,
                    'file_mime_type': a.fileMimeType,
                    'file_size': a.fileSize,
                  },
                )
                .toList(),
          );
        roadmap['_uploading'] = false;
      });
    } catch (e) {
      setState(() {
        roadmap['_uploading'] = false;
      });
      if (mounted) {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text('Upload failed: $e')));
      }
    }
  }

  Future<void> _deleteRoadmapAttachment(
    int roadmapIndex,
    int attachmentId,
  ) async {
    final success = await AttachmentService.deleteAttachment(attachmentId);
    if (success) {
      setState(() {
        final roadmap = _roadmaps[roadmapIndex];
        final atts = roadmap['attachments'] as List?;
        if (atts != null) {
          atts.removeWhere((a) => a['id'] == attachmentId);
        }
      });
    }
  }

  Widget _inputBox(TextEditingController c, String hint, {int maxLines = 1}) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.grey[50],
        borderRadius: BorderRadius.circular(16),
      ),
      child: TextField(
        controller: c,
        maxLines: maxLines,
        style: GoogleFonts.inter(fontWeight: FontWeight.w700, fontSize: 13),
        decoration: InputDecoration(
          hintText: hint,
          hintStyle: GoogleFonts.inter(color: Colors.grey[400]),
          border: InputBorder.none,
          contentPadding: const EdgeInsets.symmetric(
            horizontal: 18,
            vertical: 14,
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_loading)
      return const Center(
        child: CircularProgressIndicator(
          color: Color(0xFF4F46E5),
          strokeWidth: 2,
        ),
      );

    final filtered = _filtered;
    return Stack(
      children: [
        Column(
          children: [
            // ── Status Filters ──
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 4),
              child: Row(
                children: [
                  _filterChip(null, 'ALL'),
                  _filterChip('planned', 'PLANNED'),
                  _filterChip('in_progress', 'ACTIVE'),
                  _filterChip('on_hold', 'ON HOLD'),
                  _filterChip('completed', 'DONE'),
                ],
              ),
            ),

            // ── Search bar ──
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 14),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: Colors.grey[200]!),
                ),
                child: TextField(
                  onChanged: (v) => setState(() => _searchQuery = v),
                  style: GoogleFonts.inter(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                  ),
                  decoration: InputDecoration(
                    hintText: 'Search roadmaps...',
                    hintStyle: GoogleFonts.inter(
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                      color: Colors.grey[400],
                    ),
                    icon: Icon(
                      Icons.search_rounded,
                      size: 18,
                      color: Colors.grey[400],
                    ),
                    border: InputBorder.none,
                    contentPadding: const EdgeInsets.symmetric(vertical: 12),
                  ),
                ),
              ),
            ),

            // ── Offline indicator ──
            if (!ConnectivityService.isOnline)
              Container(
                width: double.infinity,
                margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                padding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 8,
                ),
                decoration: BoxDecoration(
                  color: Colors.orange[50],
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.orange[200]!),
                ),
                child: Row(
                  children: [
                    Icon(
                      Icons.cloud_off_rounded,
                      size: 16,
                      color: Colors.orange[700],
                    ),
                    const SizedBox(width: 8),
                    Flexible(
                      child: Text(
                        'Offline mode — changes will sync when connected',
                        style: GoogleFonts.inter(
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                          color: Colors.orange[700],
                        ),
                      ),
                    ),
                  ],
                ),
              ),

            // ── Roadmap List (Timeline) ──
            Expanded(
              child: filtered.isEmpty
                  ? Center(
                      child: SingleChildScrollView(
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              Icons.route_rounded,
                              size: 56,
                              color: Colors.grey[300],
                            ),
                            const SizedBox(height: 12),
                            Text(
                              'NO ROADMAPS YET',
                              style: GoogleFonts.inter(
                                fontSize: 12,
                                fontWeight: FontWeight.w900,
                                letterSpacing: 2,
                                color: Colors.grey[400],
                              ),
                            ),
                            const SizedBox(height: 6),
                            Text(
                              'Tap the + button to create your first goal',
                              style: GoogleFonts.inter(
                                fontSize: 12,
                                color: Colors.grey[400],
                              ),
                            ),
                            const SizedBox(height: 16),
                            ElevatedButton.icon(
                              onPressed: _showCreateModal,
                              icon: const Icon(Icons.add_rounded, size: 18),
                              label: Text(
                                'CREATE ROADMAP',
                                style: GoogleFonts.inter(
                                  fontSize: 10,
                                  fontWeight: FontWeight.w900,
                                  letterSpacing: 2,
                                ),
                              ),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: const Color(0xFF4F46E5),
                                foregroundColor: Colors.white,
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 20,
                                  vertical: 12,
                                ),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(14),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    )
                  : RefreshIndicator(
                      color: const Color(0xFF4F46E5),
                      onRefresh: loadData,
                      child: ListView.builder(
                        padding: const EdgeInsets.fromLTRB(12, 4, 16, 100),
                        itemCount: filtered.length,
                        itemBuilder: (ctx, i) => _buildTimelineItem(
                          filtered[i],
                          i,
                          i == filtered.length - 1,
                        ),
                      ),
                    ),
            ),
          ],
        ),

        // ── Bottom Stats Bar ──
        if (_roadmaps.isNotEmpty)
          Positioned(
            left: 0,
            right: 0,
            bottom: 0,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
              decoration: BoxDecoration(
                color: Colors.white,
                border: Border(top: BorderSide(color: Colors.grey[200]!)),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.04),
                    blurRadius: 8,
                    offset: const Offset(0, -2),
                  ),
                ],
              ),
              child: SafeArea(
                top: false,
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                  children: [
                    _statBadge('TOTAL', _totalGoals, const Color(0xFF4F46E5)),
                    _statBadge('DONE', _doneGoals, const Color(0xFF10B981)),
                    _statBadge(
                      'PENDING',
                      _pendingGoals,
                      const Color(0xFFF59E0B),
                    ),
                  ],
                ),
              ),
            ),
          ),

        // ── FAB ──
        Positioned(
          bottom: 70,
          right: 16,
          child: FloatingActionButton(
            onPressed: _showCreateModal,
            backgroundColor: const Color(0xFF4F46E5),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(16),
            ),
            child: const Icon(Icons.add_rounded, color: Colors.white),
          ),
        ),

        // ── Confetti ──
        Align(
          alignment: Alignment.topCenter,
          child: ConfettiWidget(
            confettiController: _confettiCtrl,
            blastDirectionality: BlastDirectionality.explosive,
            shouldLoop: false,
            numberOfParticles: 20,
            emissionFrequency: 0.05,
            colors: const [
              Color(0xFF4F46E5),
              Color(0xFF7C3AED),
              Color(0xFF10B981),
              Color(0xFFF59E0B),
              Color(0xFFEF4444),
            ],
          ),
        ),
      ],
    );
  }

  // ── Timeline Item ──
  Widget _buildTimelineItem(dynamic roadmap, int index, bool isLast) {
    final status = roadmap['status'] ?? 'planned';
    final color = _statusColor(status);
    final steps = (roadmap['steps'] as List?) ?? [];
    final totalSteps = steps.length;
    final doneSteps = steps.where((s) => s['is_completed'] == true).length;
    final pct = totalSteps > 0 ? (doneSteps / totalSteps * 100).round() : 0;
    final isExp = _expanded.contains(index);
    final List<Attachment> attachments =
        (roadmap['attachments'] as List?)
            ?.map((a) => Attachment.fromJson(Map<String, dynamic>.from(a)))
            .toList() ??
        [];
    bool uploading = roadmap['_uploading'] == true;
    double uploadProgress = roadmap['_uploadProgress'] ?? 0.0;

    return IntrinsicHeight(
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // ── Timeline rail ──
          SizedBox(
            width: 40,
            child: Column(
              children: [
                Container(
                  width: 18,
                  height: 18,
                  decoration: BoxDecoration(
                    color: color,
                    shape: BoxShape.circle,
                    boxShadow: [
                      BoxShadow(
                        color: color.withOpacity(0.3),
                        blurRadius: 6,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Icon(
                    _statusIcon(status),
                    size: 11,
                    color: Colors.white,
                  ),
                ),
                if (!isLast)
                  Expanded(
                    child: Container(width: 2, color: color.withOpacity(0.2)),
                  ),
              ],
            ),
          ),

          // ── Card ──
          Expanded(
            child: Container(
              margin: const EdgeInsets.only(bottom: 12),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: Colors.grey[100]!),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Header
                  InkWell(
                    borderRadius: BorderRadius.circular(20),
                    onTap: () => setState(
                      () => isExp
                          ? _expanded.remove(index)
                          : _expanded.add(index),
                    ),
                    child: Padding(
                      padding: const EdgeInsets.fromLTRB(16, 14, 12, 14),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 8,
                                  vertical: 3,
                                ),
                                decoration: BoxDecoration(
                                  color: color.withOpacity(0.1),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Text(
                                  _statusLabel(status),
                                  style: GoogleFonts.inter(
                                    fontSize: 8,
                                    fontWeight: FontWeight.w900,
                                    letterSpacing: 1.5,
                                    color: color,
                                  ),
                                ),
                              ),
                              const Spacer(),
                              if (roadmap['target_date'] != null)
                                Flexible(
                                  child: Text(
                                    DateFormat('dd MMM yyyy').format(
                                      DateTime.parse(roadmap['target_date']),
                                    ),
                                    style: GoogleFonts.inter(
                                      fontSize: 10,
                                      fontWeight: FontWeight.w600,
                                      color: Colors.grey[400],
                                    ),
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                              const SizedBox(width: 4),
                              Icon(
                                isExp
                                    ? Icons.expand_less_rounded
                                    : Icons.expand_more_rounded,
                                color: Colors.grey[400],
                                size: 20,
                              ),
                            ],
                          ),
                          const SizedBox(height: 8),
                          Text(
                            roadmap['title'] ?? '',
                            style: GoogleFonts.inter(
                              fontSize: 15,
                              fontWeight: FontWeight.w800,
                              color: Colors.grey[800],
                            ),
                          ),
                          if (roadmap['description'] != null &&
                              roadmap['description'].toString().isNotEmpty) ...[
                            const SizedBox(height: 3),
                            Text(
                              roadmap['description'],
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                              style: GoogleFonts.inter(
                                fontSize: 11,
                                color: Colors.grey[500],
                              ),
                            ),
                          ],
                          const SizedBox(height: 10),
                          // Global Progress Bar
                          Row(
                            children: [
                              Expanded(
                                child: ClipRRect(
                                  borderRadius: BorderRadius.circular(6),
                                  child: LinearProgressIndicator(
                                    value: pct / 100,
                                    backgroundColor: Colors.grey[100],
                                    valueColor: AlwaysStoppedAnimation<Color>(
                                      color,
                                    ),
                                    minHeight: 5,
                                  ),
                                ),
                              ),
                              const SizedBox(width: 8),
                              Text(
                                '$pct%',
                                style: GoogleFonts.inter(
                                  fontSize: 10,
                                  fontWeight: FontWeight.w900,
                                  color: color,
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ),

                  // Expanded — Milestones
                  if (isExp) ...[
                    Divider(height: 1, color: Colors.grey[100]),
                    Padding(
                      padding: const EdgeInsets.fromLTRB(16, 10, 16, 4),
                      child: Row(
                        children: [
                          Text(
                            'MILESTONES',
                            style: GoogleFonts.inter(
                              fontSize: 9,
                              fontWeight: FontWeight.w900,
                              letterSpacing: 2,
                              color: Colors.grey[400],
                            ),
                          ),
                          const Spacer(),
                          InkWell(
                            onTap: () => _showAddStep(roadmap['id']),
                            child: Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 10,
                                vertical: 4,
                              ),
                              decoration: BoxDecoration(
                                color: const Color(
                                  0xFF4F46E5,
                                ).withOpacity(0.08),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  const Icon(
                                    Icons.add_rounded,
                                    size: 14,
                                    color: Color(0xFF4F46E5),
                                  ),
                                  const SizedBox(width: 3),
                                  Text(
                                    'ADD',
                                    style: GoogleFonts.inter(
                                      fontSize: 9,
                                      fontWeight: FontWeight.w900,
                                      letterSpacing: 1,
                                      color: const Color(0xFF4F46E5),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    if (steps.isEmpty)
                      Padding(
                        padding: const EdgeInsets.all(16),
                        child: Text(
                          'No milestones yet',
                          style: GoogleFonts.inter(
                            fontSize: 12,
                            color: Colors.grey[400],
                          ),
                        ),
                      ),
                    ...steps.map<Widget>(
                      (step) => _StepTile(
                        step: step,
                        onToggle: () async {
                          await TaskService.toggleRoadmapStep(step['id']);
                          loadData();
                        },
                        onDelete: () async {
                          await TaskService.deleteRoadmapStep(step['id']);
                          loadData();
                        },
                        onProgressChanged: (v) async {
                          await TaskService.toggleRoadmapStep(
                            step['id'],
                          ); // toggle functionality via API
                          loadData();
                        },
                        onUpdate: (data) async {
                          // We use the updateStep endpoint via patch
                          await _updateStepInline(step['id'], data);
                        },
                      ),
                    ),
                    Divider(height: 1, color: Colors.grey[100]),
                    Padding(
                      padding: const EdgeInsets.fromLTRB(16, 12, 16, 0),
                      child: ExpansionTile(
                        title: Text(
                          'Lampiran (${attachments.length})',
                          style: GoogleFonts.inter(
                            fontSize: 10,
                            fontWeight: FontWeight.w900,
                            letterSpacing: 2,
                            color: const Color(0xFF4F46E5),
                          ),
                        ),
                        initiallyExpanded: false,
                        tilePadding: EdgeInsets.zero,
                        childrenPadding: const EdgeInsets.only(bottom: 8),
                        trailing: uploading
                            ? SizedBox(
                                width: 80,
                                height: 4,
                                child: LinearProgressIndicator(
                                  value: uploadProgress,
                                  backgroundColor: Colors.grey[200],
                                  color: const Color(0xFF4F46E5),
                                ),
                              )
                            : InkWell(
                                onTap: () => _pickAndUploadRoadmapFiles(index),
                                child: Container(
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 10,
                                    vertical: 4,
                                  ),
                                  decoration: BoxDecoration(
                                    color: const Color(
                                      0xFF4F46E5,
                                    ).withOpacity(0.08),
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      const Icon(
                                        Icons.upload_file_rounded,
                                        size: 14,
                                        color: Color(0xFF4F46E5),
                                      ),
                                      const SizedBox(width: 3),
                                      Text(
                                        'UPLOAD',
                                        style: GoogleFonts.inter(
                                          fontSize: 9,
                                          fontWeight: FontWeight.w900,
                                          letterSpacing: 1,
                                          color: const Color(0xFF4F46E5),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                        children: [
                          if (attachments.isNotEmpty)
                            AttachmentList(
                              attachments: attachments,
                              onDelete: (attId) =>
                                  _deleteRoadmapAttachment(index, attId),
                            ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 12),
                    // Delete roadmap button
                    Padding(
                      padding: const EdgeInsets.fromLTRB(16, 4, 16, 14),
                      child: InkWell(
                        onTap: () => _showDeleteConfirm(roadmap),
                        child: Container(
                          width: double.infinity,
                          padding: const EdgeInsets.symmetric(vertical: 10),
                          decoration: BoxDecoration(
                            color: Colors.red[50],
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Center(
                            child: Text(
                              'DELETE ROADMAP',
                              style: GoogleFonts.inter(
                                fontSize: 9,
                                fontWeight: FontWeight.w900,
                                letterSpacing: 2,
                                color: Colors.red[600],
                              ),
                            ),
                          ),
                        ),
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _updateStepInline(int stepId, Map<String, dynamic> data) async {
    try {
      await TaskService.updateRoadmapStep(stepId, data);
      loadData();
    } catch (_) {}
  }

  void _showAddStep(int roadmapId) {
    final titleC = TextEditingController();
    final catC = TextEditingController();
    String priority = 'medium';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setBS) => Container(
          padding: EdgeInsets.only(
            left: 24,
            right: 24,
            top: 20,
            bottom: MediaQuery.of(ctx).viewInsets.bottom + 20,
          ),
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
          ),
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Center(
                  child: Container(
                    width: 40,
                    height: 4,
                    decoration: BoxDecoration(
                      color: Colors.grey[300],
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                Text(
                  'ADD MILESTONE',
                  style: GoogleFonts.inter(
                    fontSize: 11,
                    fontWeight: FontWeight.w900,
                    letterSpacing: 2,
                    color: Colors.grey[800],
                  ),
                ),
                const SizedBox(height: 14),
                _inputBox(titleC, 'Milestone title'),
                const SizedBox(height: 8),
                _inputBox(catC, 'Category (e.g. Design, Dev)'),
                const SizedBox(height: 8),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14),
                  decoration: BoxDecoration(
                    color: Colors.grey[50],
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: DropdownButtonHideUnderline(
                    child: DropdownButton<String>(
                      value: priority,
                      isExpanded: true,
                      style: GoogleFonts.inter(
                        fontSize: 13,
                        fontWeight: FontWeight.w700,
                        color: Colors.grey[700],
                      ),
                      items: [
                        DropdownMenuItem(
                          value: 'high',
                          child: Text('HIGH PRIORITY'),
                        ),
                        DropdownMenuItem(
                          value: 'medium',
                          child: Text('MEDIUM PRIORITY'),
                        ),
                        DropdownMenuItem(
                          value: 'low',
                          child: Text('LOW PRIORITY'),
                        ),
                      ],
                      onChanged: (v) => setBS(() => priority = v!),
                    ),
                  ),
                ),
                const SizedBox(height: 14),
                SizedBox(
                  width: double.infinity,
                  height: 50,
                  child: ElevatedButton(
                    onPressed: () async {
                      if (titleC.text.trim().isEmpty) return;
                      Navigator.pop(ctx);
                      await TaskService.createRoadmapStep(roadmapId, {
                        'title': titleC.text.trim(),
                        'category': catC.text.trim().isEmpty
                            ? null
                            : catC.text.trim(),
                        'priority': priority,
                      });
                      loadData();
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF1F2937),
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(16),
                      ),
                    ),
                    child: Text(
                      'ADD MILESTONE',
                      style: GoogleFonts.inter(
                        fontSize: 10,
                        fontWeight: FontWeight.w900,
                        letterSpacing: 2,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _filterChip(String? value, String label) {
    final active = _statusFilter == value;
    return GestureDetector(
      onTap: () => setState(() => _statusFilter = value),
      child: Container(
        margin: const EdgeInsets.only(right: 8),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
        decoration: BoxDecoration(
          color: active ? const Color(0xFF4F46E5) : Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: active ? const Color(0xFF4F46E5) : Colors.grey[200]!,
          ),
        ),
        child: Text(
          label,
          style: GoogleFonts.inter(
            fontSize: 9,
            fontWeight: FontWeight.w900,
            letterSpacing: 1.5,
            color: active ? Colors.white : Colors.grey[500],
          ),
        ),
      ),
    );
  }

  Widget _statBadge(String label, int val, Color c) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Text(
          '$val',
          style: GoogleFonts.inter(
            fontSize: 18,
            fontWeight: FontWeight.w900,
            color: c,
          ),
        ),
        Text(
          label,
          style: GoogleFonts.inter(
            fontSize: 8,
            fontWeight: FontWeight.w800,
            letterSpacing: 2,
            color: Colors.grey[400],
          ),
        ),
      ],
    );
  }
}

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// STEP TILE — Checkbox, Priority, Emoji, Slider, Inline Edit
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
class _StepTile extends StatefulWidget {
  final Map<String, dynamic> step;
  final VoidCallback onToggle;
  final VoidCallback onDelete;
  final ValueChanged<double> onProgressChanged;
  final ValueChanged<Map<String, dynamic>> onUpdate;

  const _StepTile({
    required this.step,
    required this.onToggle,
    required this.onDelete,
    required this.onProgressChanged,
    required this.onUpdate,
  });

  @override
  State<_StepTile> createState() => _StepTileState();
}

class _StepTileState extends State<_StepTile> {
  bool _editing = false;
  late TextEditingController _titleC;
  late TextEditingController _catC;
  late TextEditingController _descC;
  late double _progress;
  late String _editPriority;

  @override
  void initState() {
    super.initState();
    _titleC = TextEditingController(text: widget.step['title'] ?? '');
    _catC = TextEditingController(text: widget.step['category'] ?? '');
    _descC = TextEditingController(text: widget.step['description'] ?? '');
    _progress = (widget.step['progress'] ?? 0).toDouble();
    _editPriority = widget.step['priority'] ?? 'medium';
  }

  @override
  void didUpdateWidget(covariant _StepTile oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.step != widget.step) {
      _titleC.text = widget.step['title'] ?? '';
      _catC.text = widget.step['category'] ?? '';
      _descC.text = widget.step['description'] ?? '';
      _progress = (widget.step['progress'] ?? 0).toDouble();
      _editPriority = widget.step['priority'] ?? 'medium';
    }
  }

  @override
  void dispose() {
    _titleC.dispose();
    _catC.dispose();
    _descC.dispose();
    super.dispose();
  }

  Color get _priorityColor {
    switch (widget.step['priority']) {
      case 'high':
        return const Color(0xFFEF4444);
      case 'medium':
        return const Color(0xFFF59E0B);
      default:
        return const Color(0xFF10B981);
    }
  }

  bool get _isOverdue {
    final dd = widget.step['due_date'];
    if (dd == null || widget.step['is_completed'] == true) return false;
    try {
      return DateTime.parse(dd.toString()).isBefore(DateTime.now());
    } catch (_) {
      return false;
    }
  }

  @override
  Widget build(BuildContext context) {
    final done = widget.step['is_completed'] == true;
    final priority = widget.step['priority'] ?? 'medium';

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 3),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      decoration: BoxDecoration(
        color: done ? Colors.green.withOpacity(0.03) : Colors.grey[50],
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: done ? Colors.green.withOpacity(0.15) : Colors.grey[100]!,
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Checkbox
              GestureDetector(
                onTap: widget.onToggle,
                child: Padding(
                  padding: const EdgeInsets.only(top: 2, right: 8),
                  child: Icon(
                    done ? Icons.check_circle_rounded : Icons.circle_outlined,
                    color: done ? const Color(0xFF10B981) : _priorityColor,
                    size: 22,
                  ),
                ),
              ),
              // Content
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    if (_editing) ...[
                      TextField(
                        controller: _titleC,
                        style: GoogleFonts.inter(
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                        ),
                        decoration: InputDecoration(
                          isDense: true,
                          contentPadding: const EdgeInsets.symmetric(
                            vertical: 4,
                          ),
                          border: UnderlineInputBorder(
                            borderSide: BorderSide(color: Colors.grey[300]!),
                          ),
                        ),
                      ),
                      const SizedBox(height: 4),
                      TextField(
                        controller: _catC,
                        style: GoogleFonts.inter(
                          fontSize: 11,
                          color: Colors.grey[600],
                        ),
                        decoration: InputDecoration(
                          isDense: true,
                          hintText: 'Category',
                          hintStyle: GoogleFonts.inter(color: Colors.grey[400]),
                          contentPadding: const EdgeInsets.symmetric(
                            vertical: 4,
                          ),
                          border: UnderlineInputBorder(
                            borderSide: BorderSide(color: Colors.grey[200]!),
                          ),
                        ),
                      ),
                      const SizedBox(height: 4),
                      TextField(
                        controller: _descC,
                        maxLines: 2,
                        style: GoogleFonts.inter(
                          fontSize: 11,
                          color: Colors.grey[600],
                        ),
                        decoration: InputDecoration(
                          isDense: true,
                          hintText: 'Description',
                          hintStyle: GoogleFonts.inter(color: Colors.grey[400]),
                          contentPadding: const EdgeInsets.symmetric(
                            vertical: 4,
                          ),
                          border: UnderlineInputBorder(
                            borderSide: BorderSide(color: Colors.grey[200]!),
                          ),
                        ),
                      ),
                      const SizedBox(height: 4),
                      // Priority dropdown
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10),
                        decoration: BoxDecoration(
                          color: Colors.grey[100],
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: DropdownButtonHideUnderline(
                          child: DropdownButton<String>(
                            value: _editPriority,
                            isExpanded: true,
                            isDense: true,
                            style: GoogleFonts.inter(
                              fontSize: 11,
                              fontWeight: FontWeight.w700,
                              color: Colors.grey[700],
                            ),
                            items: ['high', 'medium', 'low']
                                .map(
                                  (p) => DropdownMenuItem(
                                    value: p,
                                    child: Text(p.toUpperCase()),
                                  ),
                                )
                                .toList(),
                            onChanged: (v) =>
                                setState(() => _editPriority = v!),
                          ),
                        ),
                      ),
                      const SizedBox(height: 6),
                      Row(
                        children: [
                          ElevatedButton(
                            onPressed: () {
                              widget.onUpdate({
                                'title': _titleC.text.trim(),
                                'category': _catC.text.trim(),
                                'description': _descC.text.trim(),
                                'priority': _editPriority,
                              });
                              setState(() => _editing = false);
                            },
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFF4F46E5),
                              foregroundColor: Colors.white,
                              padding: const EdgeInsets.symmetric(
                                horizontal: 12,
                                vertical: 6,
                              ),
                              minimumSize: Size.zero,
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(8),
                              ),
                            ),
                            child: Text(
                              'SAVE',
                              style: GoogleFonts.inter(
                                fontSize: 9,
                                fontWeight: FontWeight.w900,
                                letterSpacing: 1,
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          GestureDetector(
                            onTap: () => setState(() => _editing = false),
                            child: Text(
                              'Cancel',
                              style: GoogleFonts.inter(
                                fontSize: 11,
                                fontWeight: FontWeight.w700,
                                color: Colors.grey[500],
                              ),
                            ),
                          ),
                        ],
                      ),
                    ] else ...[
                      // Title
                      Text(
                        widget.step['title'] ?? '',
                        style: GoogleFonts.inter(
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                          color: done ? Colors.grey[400] : Colors.grey[800],
                          decoration: done
                              ? TextDecoration.lineThrough
                              : TextDecoration.none,
                        ),
                      ),
                      const SizedBox(height: 3),
                      // Meta row
                      Wrap(
                        spacing: 6,
                        runSpacing: 4,
                        children: [
                          // Priority
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 6,
                              vertical: 2,
                            ),
                            decoration: BoxDecoration(
                              color: _priorityColor.withOpacity(0.1),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              priority.toString().toUpperCase(),
                              style: GoogleFonts.inter(
                                fontSize: 8,
                                fontWeight: FontWeight.w900,
                                letterSpacing: 1,
                                color: _priorityColor,
                              ),
                            ),
                          ),
                          // Category
                          if (widget.step['category'] != null)
                            Text(
                              '${widget.step['category']}',
                              style: GoogleFonts.inter(
                                fontSize: 10,
                                fontWeight: FontWeight.w600,
                                color: Colors.grey[500],
                              ),
                            ),
                          // Due date
                          if (widget.step['due_date'] != null)
                            Text(
                              DateFormat('dd MMM').format(
                                DateTime.parse(
                                  widget.step['due_date'].toString(),
                                ),
                              ),
                              style: GoogleFonts.inter(
                                fontSize: 10,
                                fontWeight: FontWeight.w700,
                                color: _isOverdue
                                    ? Colors.red[600]
                                    : Colors.grey[400],
                              ),
                            ),
                        ],
                      ),
                    ],
                  ],
                ),
              ),
              // Actions
              if (!_editing)
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    InkWell(
                      onTap: () => setState(() => _editing = true),
                      child: Padding(
                        padding: const EdgeInsets.all(4),
                        child: Icon(
                          Icons.edit_outlined,
                          size: 15,
                          color: Colors.grey[400],
                        ),
                      ),
                    ),
                    InkWell(
                      onTap: widget.onDelete,
                      child: Padding(
                        padding: const EdgeInsets.all(4),
                        child: Icon(
                          Icons.close_rounded,
                          size: 15,
                          color: Colors.red[400],
                        ),
                      ),
                    ),
                  ],
                ),
            ],
          ),
          // Progress slider
          if (!done) ...[
            const SizedBox(height: 6),
            Row(
              children: [
                Text(
                  '${_progress.toInt()}%',
                  style: GoogleFonts.inter(
                    fontSize: 9,
                    fontWeight: FontWeight.w900,
                    color: Colors.grey[400],
                  ),
                ),
                Expanded(
                  child: SliderTheme(
                    data: SliderThemeData(
                      trackHeight: 3,
                      thumbShape: const RoundSliderThumbShape(
                        enabledThumbRadius: 6,
                      ),
                      activeTrackColor: const Color(0xFF4F46E5),
                      inactiveTrackColor: Colors.grey[200],
                      thumbColor: const Color(0xFF4F46E5),
                      overlayShape: const RoundSliderOverlayShape(
                        overlayRadius: 14,
                      ),
                    ),
                    child: Slider(
                      value: _progress.clamp(0, 100),
                      min: 0,
                      max: 100,
                      divisions: 20,
                      onChanged: (v) => setState(() => _progress = v),
                      onChangeEnd: (v) {
                        widget.onUpdate({
                          'title': widget.step['title'],
                          'progress': v.toInt(),
                        });
                      },
                    ),
                  ),
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }
}
