import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../models/task.dart';
import '../services/task_service.dart';
import '../widgets/task_card.dart';

class TrashScreen extends StatefulWidget {
  const TrashScreen({super.key});

  @override
  State<TrashScreen> createState() => TrashScreenState();
}

class TrashScreenState extends State<TrashScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabCtrl;
  List<Task> _tasks = [];
  List<dynamic> _roadmaps = [];
  bool _loadingTasks = true;
  bool _loadingRoadmaps = true;

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(length: 2, vsync: this);
    loadData();
  }

  Future<void> loadData() async {
    _loadTasks();
    _loadRoadmaps();
  }

  @override
  void dispose() {
    _tabCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadTasks() async {
    setState(() => _loadingTasks = true);
    try {
      _tasks = await TaskService.getTrashedTasks();
    } catch (_) {}
    if (mounted) setState(() => _loadingTasks = false);
  }

  Future<void> _loadRoadmaps() async {
    setState(() => _loadingRoadmaps = true);
    try {
      _roadmaps = await TaskService.getTrashedRoadmaps();
    } catch (_) {}
    if (mounted) setState(() => _loadingRoadmaps = false);
  }

  Future<void> _restoreTask(int taskId) async {
    setState(() => _tasks.removeWhere((t) => t.id == taskId));
    try {
      await TaskService.restoreTask(taskId);
    } catch (_) {}
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'Task restored.',
            style: GoogleFonts.inter(fontSize: 11, fontWeight: FontWeight.w700),
          ),
          backgroundColor: const Color(0xFF1F2937),
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),
      );
    }
  }

  Future<void> _forceDelete(int taskId) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Text(
          'Permanently Delete',
          style: GoogleFonts.inter(fontWeight: FontWeight.w800),
        ),
        content: Text(
          'This cannot be undone.',
          style: GoogleFonts.inter(fontSize: 13),
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
              'Delete',
              style: GoogleFonts.inter(
                fontWeight: FontWeight.w700,
                color: Colors.red[600],
              ),
            ),
          ),
        ],
      ),
    );
    if (ok == true) {
      setState(() => _tasks.removeWhere((t) => t.id == taskId));
      try {
        await TaskService.forceDeleteTask(taskId);
      } catch (_) {}
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              'Task permanently deleted.',
              style: GoogleFonts.inter(
                fontSize: 11,
                fontWeight: FontWeight.w700,
              ),
            ),
            backgroundColor: const Color(0xFF1F2937),
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
          ),
        );
      }
    }
  }

  Future<void> _restoreRoadmap(int id) async {
    setState(() => _roadmaps.removeWhere((r) => r['id'] == id));
    try {
      await TaskService.restoreRoadmap(id);
    } catch (_) {}
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'Roadmap restored.',
            style: GoogleFonts.inter(fontSize: 11, fontWeight: FontWeight.w700),
          ),
          backgroundColor: const Color(0xFF1F2937),
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),
      );
    }
  }

  Future<void> _forceDeleteRoadmap(int id) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Text(
          'Permanently Delete',
          style: GoogleFonts.inter(fontWeight: FontWeight.w800),
        ),
        content: Text(
          'This roadmap and all its milestones will be completely deleted.',
          style: GoogleFonts.inter(fontSize: 13),
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
              'Delete',
              style: GoogleFonts.inter(
                fontWeight: FontWeight.w700,
                color: Colors.red[600],
              ),
            ),
          ),
        ],
      ),
    );
    if (ok == true) {
      setState(() => _roadmaps.removeWhere((r) => r['id'] == id));
      try {
        await TaskService.forceDeleteRoadmap(id);
      } catch (_) {}
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              'Roadmap permanently deleted.',
              style: GoogleFonts.inter(
                fontSize: 11,
                fontWeight: FontWeight.w700,
              ),
            ),
            backgroundColor: const Color(0xFF1F2937),
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Container(
          color: Colors.white,
          child: TabBar(
            controller: _tabCtrl,
            indicatorColor: const Color(0xFF4F46E5),
            labelColor: const Color(0xFF4F46E5),
            unselectedLabelColor: Colors.grey[400],
            labelStyle: GoogleFonts.inter(
              fontSize: 10,
              fontWeight: FontWeight.w900,
              letterSpacing: 1.5,
            ),
            unselectedLabelStyle: GoogleFonts.inter(
              fontSize: 10,
              fontWeight: FontWeight.w700,
              letterSpacing: 1,
            ),
            tabs: const [
              Tab(text: 'DELETED TASKS'),
              Tab(text: 'DELETED ROADMAPS'),
            ],
          ),
        ),
        Expanded(
          child: TabBarView(
            controller: _tabCtrl,
            children: [
              // ── Deleted Tasks ──
              _loadingTasks
                  ? const Center(
                      child: CircularProgressIndicator(
                        color: Color(0xFF4F46E5),
                        strokeWidth: 2,
                      ),
                    )
                  : _tasks.isEmpty
                  ? _emptyState(
                      'No deleted tasks',
                      Icons.delete_outline_rounded,
                    )
                  : RefreshIndicator(
                      color: const Color(0xFF4F46E5),
                      onRefresh: loadData,
                      child: ListView.builder(
                        padding: const EdgeInsets.only(bottom: 20),
                        itemCount: _tasks.length,
                        itemBuilder: (ctx, i) {
                          final task = _tasks[i];
                          return TaskCard(
                            task: task,
                            showCompleteAction: false,
                            showTrashActions: true,
                            onRestore: () => _restoreTask(task.id),
                            onForceDelete: () => _forceDelete(task.id),
                            onToggleSubtask: null,
                          );
                        },
                      ),
                    ),
              // ── Deleted Roadmaps ──
              _loadingRoadmaps
                  ? const Center(
                      child: CircularProgressIndicator(
                        color: Color(0xFF4F46E5),
                        strokeWidth: 2,
                      ),
                    )
                  : _roadmaps.isEmpty
                  ? _emptyState('No deleted roadmaps', Icons.map_outlined)
                  : RefreshIndicator(
                      color: const Color(0xFF4F46E5),
                      onRefresh: loadData,
                      child: ListView.builder(
                        padding: const EdgeInsets.all(16),
                        itemCount: _roadmaps.length,
                        itemBuilder: (ctx, i) =>
                            _deletedRoadmapCard(_roadmaps[i]),
                      ),
                    ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _deletedRoadmapCard(dynamic roadmap) {
    final steps = (roadmap['steps'] as List?) ?? [];
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: Colors.red.withOpacity(0.15)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: Colors.red.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  'DELETED',
                  style: GoogleFonts.inter(
                    fontSize: 8,
                    fontWeight: FontWeight.w900,
                    letterSpacing: 1.5,
                    color: Colors.red[700],
                  ),
                ),
              ),
              const Spacer(),
              Text(
                '${steps.length} milestones',
                style: GoogleFonts.inter(
                  fontSize: 10,
                  fontWeight: FontWeight.w700,
                  color: Colors.grey[400],
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Text(
            roadmap['title'] ?? '',
            style: GoogleFonts.inter(
              fontSize: 15,
              fontWeight: FontWeight.w800,
              color: Colors.grey[800],
            ),
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: () => _restoreRoadmap(roadmap['id']),
                  icon: Icon(
                    Icons.restore_rounded,
                    size: 16,
                    color: Colors.green[600],
                  ),
                  label: Text(
                    'RESTORE',
                    style: GoogleFonts.inter(
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                      color: Colors.green[600],
                    ),
                  ),
                  style: OutlinedButton.styleFrom(
                    side: BorderSide(color: Colors.green[200]!),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: () => _forceDeleteRoadmap(roadmap['id']),
                  icon: Icon(
                    Icons.delete_forever_rounded,
                    size: 16,
                    color: Colors.red[600],
                  ),
                  label: Text(
                    'DELETE',
                    style: GoogleFonts.inter(
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                      color: Colors.red[600],
                    ),
                  ),
                  style: OutlinedButton.styleFrom(
                    side: BorderSide(color: Colors.red[200]!),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _emptyState(String label, IconData icon) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 48, color: Colors.grey[300]),
          const SizedBox(height: 12),
          Text(
            label.toUpperCase(),
            style: GoogleFonts.inter(
              fontSize: 11,
              fontWeight: FontWeight.w900,
              letterSpacing: 2,
              color: Colors.grey[400],
            ),
          ),
        ],
      ),
    );
  }
}
