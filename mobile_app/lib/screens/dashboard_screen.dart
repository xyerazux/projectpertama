import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../services/task_service.dart';
import '../models/task.dart';
import '../widgets/task_card.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => DashboardScreenState();
}

class DashboardScreenState extends State<DashboardScreen> {
  Map<String, dynamic> _data = {};
  bool _loading = true;
  @override
  void initState() {
    super.initState();
    loadData();
  }

  Future<void> loadData() async {
    setState(() => _loading = true);
    try {
      final data = await TaskService.getDashboard();
      if (mounted)
        setState(() {
          _data = data;
          _loading = false;
        });
    } catch (e) {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return const Center(
        child: CircularProgressIndicator(
          color: Color(0xFF4F46E5),
          strokeWidth: 2,
        ),
      );
    }

    final total = _data['total_tasks'] ?? 0;
    final done = _data['done_tasks'] ?? 0;
    final pending = _data['pending_tasks'] ?? 0;
    final trashed = _data['trashed_tasks'] ?? 0;
    final progress = (_data['progress'] ?? 0).toDouble();
    final recentTasks = (_data['recent_tasks'] as List? ?? [])
        .map((j) => Task.fromJson(j))
        .toList();
    final taskPerCat = _data['task_per_category'] as List? ?? [];

    return RefreshIndicator(
      color: const Color(0xFF4F46E5),
      onRefresh: loadData,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // ── Progress card ──
          Container(
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF4F46E5), Color(0xFF7C3AED)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(24),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF4F46E5).withOpacity(0.3),
                  blurRadius: 20,
                  offset: const Offset(0, 8),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Overall Progress',
                  style: GoogleFonts.inter(
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                    color: Colors.white70,
                    letterSpacing: 1,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  '${progress.toInt()}%',
                  style: GoogleFonts.inter(
                    fontSize: 40,
                    fontWeight: FontWeight.w900,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(height: 12),
                ClipRRect(
                  borderRadius: BorderRadius.circular(8),
                  child: LinearProgressIndicator(
                    value: progress / 100,
                    backgroundColor: Colors.white24,
                    valueColor: const AlwaysStoppedAnimation<Color>(
                      Colors.white,
                    ),
                    minHeight: 8,
                  ),
                ),
                const SizedBox(height: 16),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    _statChip('TOTAL', total, Colors.white),
                    _statChip('DONE', done, const Color(0xFF34D399)),
                    _statChip('ACTIVE', pending, const Color(0xFFFBBF24)),
                    _statChip('TRASH', trashed, const Color(0xFFF87171)),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),

          // ── Tasks by Category ──
          if (taskPerCat.isNotEmpty) ...[
            _sectionTitle('TASKS PER CATEGORY'),
            const SizedBox(height: 10),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: taskPerCat.map<Widget>((cat) {
                final name = cat['name'] ?? 'Unknown';
                final count = cat['tasks_count'] ?? 0;
                return Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 12,
                  ),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: Colors.grey[100]!),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Container(
                        width: 8,
                        height: 8,
                        decoration: const BoxDecoration(
                          color: Color(0xFF4F46E5),
                          shape: BoxShape.circle,
                        ),
                      ),
                      const SizedBox(width: 8),
                      Text(
                        name.toString().toUpperCase(),
                        style: GoogleFonts.inter(
                          fontSize: 10,
                          fontWeight: FontWeight.w800,
                          letterSpacing: 1.5,
                          color: Colors.grey[600],
                        ),
                      ),
                      const SizedBox(width: 8),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 2,
                        ),
                        decoration: BoxDecoration(
                          color: const Color(0xFF4F46E5).withOpacity(0.1),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          '$count',
                          style: GoogleFonts.inter(
                            fontSize: 11,
                            fontWeight: FontWeight.w900,
                            color: const Color(0xFF4F46E5),
                          ),
                        ),
                      ),
                    ],
                  ),
                );
              }).toList(),
            ),
            const SizedBox(height: 20),
          ],

          // ── Active Roadmaps ──
          _sectionTitle('ACTIVE ROADMAPS'),
          const SizedBox(height: 10),
          if ((_data['roadmaps_summary'] as List? ?? []).isEmpty)
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: Colors.grey[100]!),
              ),
              child: Column(
                children: [
                  Icon(Icons.map_rounded, size: 32, color: Colors.grey[300]),
                  const SizedBox(height: 12),
                  Text(
                    'NO ACTIVE ROADMAPS',
                    style: GoogleFonts.inter(
                      fontSize: 10,
                      fontWeight: FontWeight.w800,
                      letterSpacing: 2,
                      color: Colors.grey[400],
                    ),
                  ),
                ],
              ),
            )
          else
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              clipBehavior: Clip.none,
              child: Row(
                children: (_data['roadmaps_summary'] as List).map<Widget>((rm) {
                  final title = rm['title'] ?? 'Untitled';
                  final progressPct = (rm['progress_percent'] ?? 0).toDouble();
                  return Container(
                    width: 200,
                    margin: const EdgeInsets.only(right: 12),
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(color: Colors.grey[100]!),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.grey[200]!.withOpacity(0.5),
                          blurRadius: 10,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          title,
                          style: GoogleFonts.inter(
                            fontSize: 13,
                            fontWeight: FontWeight.w800,
                            color: Colors.grey[800],
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 8),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              'Progress',
                              style: GoogleFonts.inter(
                                fontSize: 9,
                                fontWeight: FontWeight.w600,
                                color: Colors.grey[400],
                              ),
                            ),
                            Text(
                              '${progressPct.toInt()}%',
                              style: GoogleFonts.inter(
                                fontSize: 10,
                                fontWeight: FontWeight.w800,
                                color: const Color(0xFF4F46E5),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 6),
                        ClipRRect(
                          borderRadius: BorderRadius.circular(4),
                          child: LinearProgressIndicator(
                            value: progressPct / 100,
                            backgroundColor: Colors.grey[100],
                            valueColor: const AlwaysStoppedAnimation<Color>(
                              Color(0xFF4F46E5),
                            ),
                            minHeight: 4,
                          ),
                        ),
                      ],
                    ),
                  );
                }).toList(),
              ),
            ),
          const SizedBox(height: 24),

          // ── Recent Tasks ──
          _sectionTitle('RECENT ACTIVE TASKS'),
          const SizedBox(height: 10),
          if (recentTasks.isEmpty)
            Container(
              padding: const EdgeInsets.all(32),
              alignment: Alignment.center,
              child: Text(
                'No active tasks yet',
                style: GoogleFonts.inter(color: Colors.grey[400], fontSize: 13),
              ),
            )
          else
            for (final task in recentTasks)
              TaskCard(task: task, showCompleteAction: false),
        ],
      ),
    );
  }

  Widget _statChip(String label, int value, Color color) {
    return Column(
      children: [
        Text(
          '$value',
          style: GoogleFonts.inter(
            fontSize: 20,
            fontWeight: FontWeight.w900,
            color: color,
          ),
        ),
        const SizedBox(height: 2),
        Text(
          label,
          style: GoogleFonts.inter(
            fontSize: 8,
            fontWeight: FontWeight.w800,
            letterSpacing: 2,
            color: Colors.white60,
          ),
        ),
      ],
    );
  }

  Widget _sectionTitle(String title) {
    return Text(
      title,
      style: GoogleFonts.inter(
        fontSize: 10,
        fontWeight: FontWeight.w900,
        letterSpacing: 2.5,
        color: Colors.grey[400],
      ),
    );
  }
}
