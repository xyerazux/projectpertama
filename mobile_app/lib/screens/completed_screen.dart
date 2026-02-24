import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../models/task.dart';
import '../services/task_service.dart';
import '../widgets/task_card.dart';
import 'task_form_screen.dart';

class CompletedScreen extends StatefulWidget {
  const CompletedScreen({super.key});

  @override
  State<CompletedScreen> createState() => CompletedScreenState();
}

class CompletedScreenState extends State<CompletedScreen>
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

  @override
  void dispose() {
    _tabCtrl.dispose();
    super.dispose();
  }

  Future<void> loadData() async {
    _loadTasks();
    _loadRoadmaps();
  }

  Future<void> _loadTasks() async {
    setState(() => _loadingTasks = true);
    try {
      _tasks = await TaskService.getCompletedTasks();
    } catch (_) {}
    if (mounted) setState(() => _loadingTasks = false);
  }

  Future<void> _loadRoadmaps() async {
    setState(() => _loadingRoadmaps = true);
    try {
      final all = await TaskService.getRoadmaps();
      _roadmaps = all.where((r) => r['status'] == 'completed').toList();
    } catch (_) {}
    if (mounted) setState(() => _loadingRoadmaps = false);
  }

  Future<void> _deleteTask(int taskId) async {
    setState(() => _tasks.removeWhere((t) => t.id == taskId));
    try {
      await TaskService.deleteTask(taskId);
    } catch (_) {}
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'Task moved to trash.',
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
              Tab(text: 'COMPLETED TASKS'),
              Tab(text: 'COMPLETED ROADMAPS'),
            ],
          ),
        ),
        Expanded(
          child: TabBarView(
            controller: _tabCtrl,
            children: [
              // ── Completed Tasks ──
              _loadingTasks
                  ? const Center(
                      child: CircularProgressIndicator(
                        color: Color(0xFF4F46E5),
                        strokeWidth: 2,
                      ),
                    )
                  : _tasks.isEmpty
                  ? _emptyState(
                      'No completed tasks',
                      Icons.check_circle_outline_rounded,
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
                            onTap: () async {
                              await Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => TaskFormScreen(task: task),
                                ),
                              );
                              loadData();
                            },
                            onDelete: () => _deleteTask(task.id),
                            onToggleSubtask: null,
                          );
                        },
                      ),
                    ),
              // ── Completed Roadmaps ──
              _loadingRoadmaps
                  ? const Center(
                      child: CircularProgressIndicator(
                        color: Color(0xFF4F46E5),
                        strokeWidth: 2,
                      ),
                    )
                  : _roadmaps.isEmpty
                  ? _emptyState('No completed roadmaps', Icons.map_rounded)
                  : RefreshIndicator(
                      color: const Color(0xFF4F46E5),
                      onRefresh: loadData,
                      child: ListView.builder(
                        padding: const EdgeInsets.all(16),
                        itemCount: _roadmaps.length,
                        itemBuilder: (ctx, i) {
                          final r = _roadmaps[i];
                          final title = r['title'] ?? 'Untitled';
                          final steps = r['steps'] as List? ?? [];

                          return Container(
                            margin: const EdgeInsets.only(bottom: 16),
                            child: Material(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(20),
                              child: InkWell(
                                onTap: null,
                                borderRadius: BorderRadius.circular(20),
                                highlightColor: Colors.grey[100],
                                child: Padding(
                                  padding: const EdgeInsets.all(18),
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      Row(
                                        children: [
                                          Container(
                                            padding: const EdgeInsets.symmetric(
                                              horizontal: 8,
                                              vertical: 3,
                                            ),
                                            decoration: BoxDecoration(
                                              color: Colors.green.withOpacity(
                                                0.1,
                                              ),
                                              borderRadius:
                                                  BorderRadius.circular(8),
                                            ),
                                            child: Text(
                                              'COMPLETED',
                                              style: GoogleFonts.inter(
                                                fontSize: 8,
                                                fontWeight: FontWeight.w900,
                                                letterSpacing: 1.5,
                                                color: Colors.green[700],
                                              ),
                                            ),
                                          ),
                                          const Spacer(),
                                          Text(
                                            '${steps.where((s) => s['is_completed'] == true).length}/${steps.length} milestones',
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
                                        title,
                                        style: GoogleFonts.inter(
                                          fontSize: 15,
                                          fontWeight: FontWeight.w800,
                                          color: Colors.grey[800],
                                        ),
                                      ),
                                      if (r['description'] != null &&
                                          r['description']
                                              .toString()
                                              .isNotEmpty) ...[
                                        const SizedBox(height: 3),
                                        Text(
                                          r['description'],
                                          maxLines: 2,
                                          overflow: TextOverflow.ellipsis,
                                          style: GoogleFonts.inter(
                                            fontSize: 11,
                                            color: Colors.grey[500],
                                          ),
                                        ),
                                      ],
                                      const SizedBox(height: 8),
                                      ClipRRect(
                                        borderRadius: BorderRadius.circular(6),
                                        child: LinearProgressIndicator(
                                          value: 1.0,
                                          backgroundColor: Colors.grey[100],
                                          valueColor:
                                              const AlwaysStoppedAnimation<
                                                Color
                                              >(Color(0xFF10B981)),
                                          minHeight: 4,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            ),
                          );
                        },
                      ),
                    ),
            ],
          ),
        ),
      ],
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
