import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import '../models/task.dart';
import '../models/category.dart';
import '../services/task_service.dart';
import '../services/auth_service.dart';
import 'dart:io';
import 'package:file_picker/file_picker.dart';
import '../models/attachment.dart';
import '../services/attachment_service.dart';
import '../widgets/attachment_list.dart';

class TaskFormScreen extends StatefulWidget {
  final Task? task; // null = create, non-null = edit

  const TaskFormScreen({super.key, this.task});

  @override
  State<TaskFormScreen> createState() => _TaskFormScreenState();
}

class _TaskFormScreenState extends State<TaskFormScreen> {
  final _titleController = TextEditingController();
  final _descController = TextEditingController();
  final _linkController = TextEditingController();
  DateTime? _deadline;
  int? _selectedCategoryId;
  String _priority = 'low';
  bool _isManualMode = false;
  List<Category> _categories = [];
  List<TextEditingController> _subtaskControllers = [];
  final Map<int, bool> _subtaskStatus = {};
  bool _loading = false;
  bool _saving = false;
  String? _error;

  bool _uploadingFile = false;
  double _uploadProgress = 0.0;
  List<Attachment> _attachments = [];

  bool get _isEditing => widget.task != null;

  @override
  void initState() {
    super.initState();
    _loadInitialData();
  }

  Future<void> _loadInitialData() async {
    setState(() => _loading = true);
    try {
      _categories = await TaskService.getCategories();
      final user = await AuthService.getUser();
      _isManualMode = user?['priority_mode'] == 'manual';

      if (_isEditing) {
        final t = widget.task!;
        _titleController.text = t.title;
        _descController.text = t.description ?? '';
        _linkController.text = t.linkAttachment ?? '';
        _deadline = t.deadline != null ? DateTime.tryParse(t.deadline!) : null;
        _selectedCategoryId = t.categoryId;
        _priority = t.priority.toLowerCase();

        for (int i = 0; i < t.subtasks.length; i++) {
          _subtaskControllers.add(
            TextEditingController(text: t.subtasks[i].title),
          );
          _subtaskStatus[i] = t.subtasks[i].isCompleted;
        }
        _attachments = List.from(t.attachments);
      } else {
        _subtaskControllers = [TextEditingController()];
        _subtaskStatus[0] = false;
      }
    } catch (_) {}
    if (mounted) setState(() => _loading = false);

    // ── Notify user to create a category first ──
    if (!_isEditing && _categories.isEmpty && mounted) {
      await showDialog(
        context: context,
        barrierDismissible: false,
        builder: (ctx) => AlertDialog(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
          ),
          title: Text(
            'Create a Category First',
            style: GoogleFonts.inter(fontWeight: FontWeight.w800, fontSize: 16),
          ),
          content: Text(
            'You need at least one category before creating a task. Please create a category first.',
            style: GoogleFonts.inter(fontSize: 13, color: Colors.grey[600]),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: Text(
                'OK',
                style: GoogleFonts.inter(
                  fontWeight: FontWeight.w700,
                  color: const Color(0xFF4F46E5),
                ),
              ),
            ),
          ],
        ),
      );
      if (mounted) {
        Navigator.pop(context); // Close task form
      }
    }
  }

  void _addSubtaskField() {
    setState(() {
      final newIndex = _subtaskControllers.length;
      _subtaskControllers.add(TextEditingController());
      _subtaskStatus[newIndex] = false;
    });
  }

  void _removeSubtaskField(int index) {
    setState(() {
      _subtaskControllers.removeAt(index);

      // Shift statuses down
      final newMap = <int, bool>{};
      for (int i = 0; i < _subtaskControllers.length; i++) {
        if (i < index) {
          newMap[i] = _subtaskStatus[i] ?? false;
        } else {
          newMap[i] = _subtaskStatus[i + 1] ?? false;
        }
      }
      _subtaskStatus.clear();
      _subtaskStatus.addAll(newMap);
    });
  }

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _deadline ?? DateTime.now(),
      firstDate: DateTime(2020),
      lastDate: DateTime(2030),
      builder: (ctx, child) => Theme(
        data: Theme.of(ctx).copyWith(
          colorScheme: const ColorScheme.light(primary: Color(0xFF4F46E5)),
        ),
        child: child!,
      ),
    );
    if (picked != null) setState(() => _deadline = picked);
  }

  Future<void> _pickAndUploadFiles() async {
    try {
      final result = await FilePicker.platform.pickFiles(allowMultiple: true);
      if (result == null || result.files.isEmpty) return;

      final files = result.paths
          .whereType<String>()
          .map((p) => File(p))
          .toList();
      if (files.isEmpty) return;

      setState(() {
        _uploadingFile = true;
        _uploadProgress = 0.0;
      });

      final uploaded = await AttachmentService.uploadAttachments(
        'tasks',
        widget.task!.id,
        files,
        (count, total) {
          setState(() {
            _uploadProgress = count / total;
          });
        },
      );

      setState(() {
        _attachments.addAll(uploaded);
        _uploadingFile = false;
      });
    } catch (e) {
      setState(() {
        _uploadingFile = false;
      });
      if (mounted) {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text('Upload failed: $e')));
      }
    }
  }

  Future<void> _deleteAttachment(int id) async {
    final success = await AttachmentService.deleteAttachment(id);
    if (success) {
      setState(() {
        _attachments.removeWhere((a) => a.id == id);
      });
    }
  }

  Future<void> _save() async {
    if (_titleController.text.trim().isEmpty) {
      setState(() => _error = 'Title is required.');
      return;
    }
    if (_selectedCategoryId == null) {
      setState(() => _error = 'Please select a category.');
      return;
    }
    if (_deadline == null) {
      setState(() => _error = 'Please select a deadline.');
      return;
    }

    setState(() {
      _saving = true;
      _error = null;
    });

    final data = <String, dynamic>{
      'title': _titleController.text.trim(),
      'description': _descController.text.trim().isEmpty
          ? null
          : _descController.text.trim(),
      'link_attachment': _linkController.text.trim().isEmpty
          ? null
          : _linkController.text.trim(),
      'category_id': _selectedCategoryId,
      'deadline': DateFormat('yyyy-MM-dd').format(_deadline!),
      'priority': _priority,
      'status': _isEditing ? (widget.task!.status) : 'pending',
    };

    if (_isEditing) {
      // Send existing subtasks with their IDs and toggled status
      final existingSubtasks = <String, String>{};
      final subtasksStatus = <String, bool>{};
      final task = widget.task!;

      for (
        int i = 0;
        i < task.subtasks.length && i < _subtaskControllers.length;
        i++
      ) {
        final title = _subtaskControllers[i].text.trim();
        if (title.isNotEmpty) {
          final subIdStr = task.subtasks[i].id.toString();
          existingSubtasks[subIdStr] = title;
          subtasksStatus[subIdStr] = _subtaskStatus[i] ?? false;
        }
      }
      data['existing_subtasks'] = existingSubtasks;
      data['subtasks_status'] = subtasksStatus;

      // Only truly new subtasks (controllers beyond the original count)
      final newSubtasks = <String>[];
      for (int i = task.subtasks.length; i < _subtaskControllers.length; i++) {
        final title = _subtaskControllers[i].text.trim();
        if (title.isNotEmpty) newSubtasks.add(title);
      }
      if (newSubtasks.isNotEmpty) data['subtasks'] = newSubtasks;
    } else {
      final subtaskTitles = _subtaskControllers
          .map((c) => c.text.trim())
          .where((s) => s.isNotEmpty)
          .toList();
      data['subtasks'] = subtaskTitles;
    }

    try {
      Map<String, dynamic> result;
      if (_isEditing) {
        result = await TaskService.updateTask(widget.task!.id, data);
      } else {
        result = await TaskService.createTask(data);
      }

      if (result['success'] == true) {
        if (mounted) Navigator.pop(context, true);
      } else {
        final errors = result['errors'];
        if (errors != null && errors is Map) {
          final firstError = (errors.values.first as List).first;
          setState(() => _error = firstError.toString());
        } else {
          setState(() => _error = result['message'] ?? 'Failed to save task.');
        }
      }
    } catch (e) {
      setState(() => _error = 'Connection error. Check your server.');
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        surfaceTintColor: Colors.white,
        leading: IconButton(
          icon: Icon(Icons.arrow_back_rounded, color: Colors.grey[800]),
          onPressed: () => Navigator.pop(context),
        ),
        title: Row(
          children: [
            Container(
              width: 5,
              height: 20,
              decoration: BoxDecoration(
                color: const Color(0xFF4F46E5),
                borderRadius: BorderRadius.circular(4),
              ),
            ),
            const SizedBox(width: 10),
            Text(
              _isEditing ? 'EDIT TASK' : 'CREATE NEW TASK',
              style: GoogleFonts.inter(
                fontSize: 13,
                fontWeight: FontWeight.w900,
                letterSpacing: 2,
                color: Colors.grey[800],
              ),
            ),
          ],
        ),
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(1),
          child: Container(height: 1, color: Colors.grey[100]),
        ),
      ),
      body: _loading
          ? const Center(
              child: CircularProgressIndicator(
                color: Color(0xFF4F46E5),
                strokeWidth: 2,
              ),
            )
          : SingleChildScrollView(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // ── Error ──
                  if (_error != null)
                    Container(
                      width: double.infinity,
                      margin: const EdgeInsets.only(bottom: 16),
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: Colors.red[50],
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: Colors.red[100]!),
                      ),
                      child: Text(
                        _error!,
                        style: GoogleFonts.inter(
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                          color: Colors.red[600],
                        ),
                      ),
                    ),

                  // ── Form card ──
                  Container(
                    padding: const EdgeInsets.all(24),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(24),
                      border: Border.all(color: Colors.grey[100]!),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Title
                        _label('TASK NAME'),
                        const SizedBox(height: 8),
                        _inputField(
                          _titleController,
                          'e.g. Finish Project Alpha',
                        ),
                        const SizedBox(height: 20),

                        // Description
                        _label('DESCRIPTION'),
                        const SizedBox(height: 8),
                        Container(
                          decoration: BoxDecoration(
                            color: Colors.grey[50],
                            borderRadius: BorderRadius.circular(16),
                          ),
                          child: TextField(
                            controller: _descController,
                            maxLines: 3,
                            style: GoogleFonts.inter(
                              fontWeight: FontWeight.w600,
                              fontSize: 13,
                            ),
                            decoration: InputDecoration(
                              hintText: 'Provide more details...',
                              hintStyle: GoogleFonts.inter(
                                color: Colors.grey[400],
                                fontWeight: FontWeight.w500,
                              ),
                              border: InputBorder.none,
                              contentPadding: const EdgeInsets.all(18),
                            ),
                          ),
                        ),
                        const SizedBox(height: 20),

                        // Subtasks
                        Divider(color: Colors.grey[100]),
                        const SizedBox(height: 12),
                        _label(
                          'TO-DO LIST / SUBTASKS',
                          color: const Color(0xFF4F46E5),
                        ),
                        const SizedBox(height: 10),
                        ...List.generate(_subtaskControllers.length, (i) {
                          return Padding(
                            padding: const EdgeInsets.only(bottom: 8),
                            child: Row(
                              children: [
                                if (_isEditing &&
                                    i < (widget.task?.subtasks.length ?? 0))
                                  GestureDetector(
                                    onTap: () {
                                      setState(() {
                                        _subtaskStatus[i] =
                                            !(_subtaskStatus[i] ?? false);
                                      });
                                    },
                                    child: Container(
                                      width: 20,
                                      height: 20,
                                      decoration: BoxDecoration(
                                        color: (_subtaskStatus[i] ?? false)
                                            ? const Color(0xFF4F46E5)
                                            : Colors.transparent,
                                        border: Border.all(
                                          color: (_subtaskStatus[i] ?? false)
                                              ? const Color(0xFF4F46E5)
                                              : Colors.grey[400]!,
                                          width: 2,
                                        ),
                                        borderRadius: BorderRadius.circular(6),
                                      ),
                                      child: (_subtaskStatus[i] ?? false)
                                          ? const Icon(
                                              Icons.check,
                                              size: 14,
                                              color: Colors.white,
                                            )
                                          : null,
                                    ),
                                  )
                                else
                                  Container(
                                    width: 6,
                                    height: 6,
                                    decoration: BoxDecoration(
                                      color: const Color(
                                        0xFF4F46E5,
                                      ).withOpacity(0.3),
                                      shape: BoxShape.circle,
                                    ),
                                  ),
                                const SizedBox(width: 10),
                                Expanded(
                                  child: Container(
                                    decoration: BoxDecoration(
                                      color: Colors.grey[50],
                                      borderRadius: BorderRadius.circular(12),
                                    ),
                                    child: TextField(
                                      controller: _subtaskControllers[i],
                                      style: GoogleFonts.inter(
                                        fontWeight: FontWeight.w700,
                                        fontSize: 12,
                                      ),
                                      decoration: InputDecoration(
                                        hintText: i == 0
                                            ? 'e.g. Read chapter 1'
                                            : 'Next step...',
                                        hintStyle: GoogleFonts.inter(
                                          color: Colors.grey[400],
                                          fontSize: 12,
                                        ),
                                        border: InputBorder.none,
                                        contentPadding:
                                            const EdgeInsets.symmetric(
                                              horizontal: 14,
                                              vertical: 12,
                                            ),
                                      ),
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 6),
                                if (i == 0)
                                  _circleButton(
                                    Icons.add,
                                    const Color(0xFF4F46E5),
                                    _addSubtaskField,
                                  )
                                else
                                  _circleButton(
                                    Icons.close,
                                    Colors.red[400]!,
                                    () => _removeSubtaskField(i),
                                  ),
                              ],
                            ),
                          );
                        }),
                        const SizedBox(height: 20),

                        // Link attachment
                        _label('ATTACHMENT LINK (URL)'),
                        const SizedBox(height: 8),
                        _inputField(
                          _linkController,
                          'https://example.com',
                          type: TextInputType.url,
                          textColor: const Color(0xFF4F46E5),
                        ),

                        if (_isEditing) ...[
                          const SizedBox(height: 20),
                          Divider(color: Colors.grey[100]),
                          const SizedBox(height: 12),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              _label(
                                'FILE ATTACHMENTS',
                                color: const Color(0xFF4F46E5),
                              ),
                              if (_uploadingFile)
                                SizedBox(
                                  width: 100,
                                  child: LinearProgressIndicator(
                                    value: _uploadProgress,
                                    backgroundColor: Colors.grey[200],
                                    color: const Color(0xFF4F46E5),
                                  ),
                                )
                              else
                                TextButton.icon(
                                  onPressed: _pickAndUploadFiles,
                                  icon: const Icon(Icons.upload_file, size: 16),
                                  label: Text(
                                    'UPLOAD',
                                    style: GoogleFonts.inter(
                                      fontSize: 10,
                                      fontWeight: FontWeight.w800,
                                    ),
                                  ),
                                ),
                            ],
                          ),
                          AttachmentList(
                            attachments: _attachments,
                            onDelete: _deleteAttachment,
                          ),
                        ],
                        const SizedBox(height: 20),

                        // Category + Deadline row
                        Row(
                          children: [
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  _label('CATEGORY'),
                                  const SizedBox(height: 8),
                                  Container(
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 14,
                                    ),
                                    decoration: BoxDecoration(
                                      color: Colors.grey[50],
                                      borderRadius: BorderRadius.circular(16),
                                    ),
                                    child: DropdownButtonHideUnderline(
                                      child: DropdownButton<int>(
                                        value: _selectedCategoryId,
                                        hint: Text(
                                          'Choose',
                                          style: GoogleFonts.inter(
                                            fontSize: 12,
                                            fontWeight: FontWeight.w600,
                                            color: Colors.grey[400],
                                          ),
                                        ),
                                        isExpanded: true,
                                        icon: Icon(
                                          Icons.expand_more,
                                          size: 18,
                                          color: Colors.grey[400],
                                        ),
                                        style: GoogleFonts.inter(
                                          fontSize: 12,
                                          fontWeight: FontWeight.w700,
                                          color: Colors.grey[700],
                                        ),
                                        items: _categories
                                            .map(
                                              (c) => DropdownMenuItem(
                                                value: c.id,
                                                child: Text(
                                                  c.name.toUpperCase(),
                                                ),
                                              ),
                                            )
                                            .toList(),
                                        onChanged: (v) => setState(
                                          () => _selectedCategoryId = v,
                                        ),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  _label('DUE DATE'),
                                  const SizedBox(height: 8),
                                  InkWell(
                                    onTap: _pickDate,
                                    borderRadius: BorderRadius.circular(16),
                                    child: Container(
                                      width: double.infinity,
                                      padding: const EdgeInsets.symmetric(
                                        horizontal: 14,
                                        vertical: 16,
                                      ),
                                      decoration: BoxDecoration(
                                        color: Colors.grey[50],
                                        borderRadius: BorderRadius.circular(16),
                                      ),
                                      child: Text(
                                        _deadline != null
                                            ? DateFormat(
                                                'dd MMM yyyy',
                                              ).format(_deadline!)
                                            : 'Select date',
                                        style: GoogleFonts.inter(
                                          fontSize: 12,
                                          fontWeight: FontWeight.w700,
                                          color: _deadline != null
                                              ? Colors.grey[700]
                                              : Colors.grey[400],
                                        ),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 20),

                        // Priority (manual mode only)
                        if (_isManualMode) ...[
                          _label('PRIORITY'),
                          const SizedBox(height: 10),
                          Row(
                            children: ['low', 'medium', 'high'].map((p) {
                              final selected = _priority == p;
                              return Expanded(
                                child: GestureDetector(
                                  onTap: () => setState(() => _priority = p),
                                  child: Container(
                                    margin: EdgeInsets.only(
                                      right: p != 'high' ? 8 : 0,
                                    ),
                                    padding: const EdgeInsets.symmetric(
                                      vertical: 14,
                                    ),
                                    decoration: BoxDecoration(
                                      color: selected
                                          ? const Color(
                                              0xFF4F46E5,
                                            ).withOpacity(0.08)
                                          : Colors.transparent,
                                      borderRadius: BorderRadius.circular(16),
                                      border: Border.all(
                                        color: selected
                                            ? const Color(0xFF4F46E5)
                                            : Colors.grey[200]!,
                                      ),
                                    ),
                                    child: Center(
                                      child: Text(
                                        p.toUpperCase(),
                                        style: GoogleFonts.inter(
                                          fontSize: 10,
                                          fontWeight: FontWeight.w900,
                                          letterSpacing: 2,
                                          color: selected
                                              ? const Color(0xFF4F46E5)
                                              : Colors.grey[400],
                                        ),
                                      ),
                                    ),
                                  ),
                                ),
                              );
                            }).toList(),
                          ),
                        ],
                      ],
                    ),
                  ),
                  const SizedBox(height: 24),

                  // ── Actions ──
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      TextButton(
                        onPressed: () => Navigator.pop(context),
                        child: Text(
                          '← CANCEL',
                          style: GoogleFonts.inter(
                            fontSize: 11,
                            fontWeight: FontWeight.w900,
                            letterSpacing: 2,
                            color: Colors.grey[400],
                          ),
                        ),
                      ),
                      SizedBox(
                        height: 52,
                        child: ElevatedButton(
                          onPressed: _saving ? null : _save,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF1F2937),
                            foregroundColor: Colors.white,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(16),
                            ),
                            padding: const EdgeInsets.symmetric(horizontal: 36),
                            elevation: 8,
                            shadowColor: Colors.grey[300],
                          ),
                          child: _saving
                              ? const SizedBox(
                                  width: 18,
                                  height: 18,
                                  child: CircularProgressIndicator(
                                    strokeWidth: 2,
                                    valueColor: AlwaysStoppedAnimation<Color>(
                                      Colors.white,
                                    ),
                                  ),
                                )
                              : Text(
                                  _isEditing ? 'UPDATE TASK' : 'SAVE TASK',
                                  style: GoogleFonts.inter(
                                    fontSize: 10,
                                    fontWeight: FontWeight.w900,
                                    letterSpacing: 3,
                                  ),
                                ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 40),
                ],
              ),
            ),
      // FAB for creating tasks from the active tab
      floatingActionButton: null,
    );
  }

  Widget _label(String text, {Color? color}) {
    return Text(
      text,
      style: GoogleFonts.inter(
        fontSize: 10,
        fontWeight: FontWeight.w900,
        letterSpacing: 2,
        color: color ?? Colors.grey[400],
      ),
    );
  }

  Widget _inputField(
    TextEditingController controller,
    String hint, {
    TextInputType type = TextInputType.text,
    Color? textColor,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.grey[50],
        borderRadius: BorderRadius.circular(16),
      ),
      child: TextField(
        controller: controller,
        keyboardType: type,
        style: GoogleFonts.inter(
          fontWeight: FontWeight.w700,
          fontSize: 14,
          color: textColor ?? Colors.grey[800],
        ),
        decoration: InputDecoration(
          hintText: hint,
          hintStyle: GoogleFonts.inter(
            color: Colors.grey[400],
            fontWeight: FontWeight.w500,
          ),
          border: InputBorder.none,
          contentPadding: const EdgeInsets.symmetric(
            horizontal: 18,
            vertical: 16,
          ),
        ),
      ),
    );
  }

  Widget _circleButton(IconData icon, Color color, VoidCallback onPressed) {
    return InkWell(
      onTap: onPressed,
      borderRadius: BorderRadius.circular(10),
      child: Container(
        width: 38,
        height: 38,
        decoration: BoxDecoration(
          color: color.withOpacity(0.1),
          borderRadius: BorderRadius.circular(10),
        ),
        child: Icon(icon, size: 18, color: color),
      ),
    );
  }

  @override
  void dispose() {
    _titleController.dispose();
    _descController.dispose();
    _linkController.dispose();
    for (final c in _subtaskControllers) {
      c.dispose();
    }
    super.dispose();
  }
}
