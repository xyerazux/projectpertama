import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../services/auth_service.dart';
import 'dashboard_screen.dart';
import 'task_list_screen.dart';
import 'completed_screen.dart';
import 'trash_screen.dart';
import 'roadmap_screen.dart';
import 'category_screen.dart';
import 'profile_screen.dart';
import 'task_form_screen.dart';
import 'login_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _currentIndex = 0;
  String _userName = '';
  String _userEmail = '';
  final _dashKey = GlobalKey<DashboardScreenState>();
  final _taskListKey = GlobalKey<TaskListScreenState>();
  final _completedKey = GlobalKey<CompletedScreenState>();
  final _trashKey = GlobalKey<TrashScreenState>();
  final _roadmapKey = GlobalKey<RoadmapScreenState>();

  static const _titles = [
    'DASHBOARD',
    'ACTIVE TASKS',
    'COMPLETED',
    'TRASH',
    'ROADMAP',
    'CATEGORIES',
    'PROFILE',
  ];

  late final List<Widget> _pages = [
    DashboardScreen(key: _dashKey),
    TaskListScreen(key: _taskListKey),
    CompletedScreen(key: _completedKey),
    TrashScreen(key: _trashKey),
    RoadmapScreen(key: _roadmapKey),
    const CategoryScreen(),
    const ProfileScreen(),
  ];

  void _refreshPage(int index) {
    switch (index) {
      case 0:
        _dashKey.currentState?.loadData();
        break;
      case 1:
        _taskListKey.currentState?.loadData();
        break;
      case 2:
        _completedKey.currentState?.loadData();
        break;
      case 3:
        _trashKey.currentState?.loadData();
        break;
      case 4:
        _roadmapKey.currentState?.loadData();
        break;
    }
  }

  @override
  void initState() {
    super.initState();
    _loadUser();
  }

  Future<void> _loadUser() async {
    final user = await AuthService.getUser();
    if (user != null && mounted) {
      setState(() {
        _userName = user['name'] ?? '';
        _userEmail = user['email'] ?? '';
      });
    }
  }

  Future<void> _logout() async {
    await AuthService.logout();
    if (mounted) {
      Navigator.pushAndRemoveUntil(
        context,
        MaterialPageRoute(builder: (_) => const LoginScreen()),
        (_) => false,
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final initials = _userName.isNotEmpty ? _userName[0].toUpperCase() : '?';

    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        surfaceTintColor: Colors.white,
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
            Flexible(
              child: Text(
                _titles[_currentIndex],
                style: GoogleFonts.inter(
                  fontSize: 13,
                  fontWeight: FontWeight.w900,
                  letterSpacing: 2,
                  color: Colors.grey[800],
                ),
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
        actions: [
          if (_currentIndex == 1) // show FAB-like add button on task list
            IconButton(
              icon: const Icon(
                Icons.add_rounded,
                color: Color(0xFF4F46E5),
                size: 26,
              ),
              onPressed: () async {
                await Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const TaskFormScreen()),
                );
                _taskListKey.currentState?.loadData();
              },
            ),
          const SizedBox(width: 4),
        ],
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(1),
          child: Container(height: 1, color: Colors.grey[100]),
        ),
      ),
      drawer: Drawer(
        backgroundColor: Colors.white,
        child: SafeArea(
          child: Column(
            children: [
              // ── Drawer header ──
              Container(
                width: double.infinity,
                padding: const EdgeInsets.fromLTRB(24, 32, 24, 24),
                decoration: const BoxDecoration(
                  gradient: LinearGradient(
                    colors: [Color(0xFF4F46E5), Color(0xFF7C3AED)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      width: 50,
                      height: 50,
                      decoration: BoxDecoration(
                        color: Colors.white24,
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: Center(
                        child: Text(
                          initials,
                          style: GoogleFonts.inter(
                            fontSize: 22,
                            fontWeight: FontWeight.w900,
                            color: Colors.white,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 12),
                    Text(
                      _userName,
                      style: GoogleFonts.inter(
                        fontSize: 15,
                        fontWeight: FontWeight.w800,
                        color: Colors.white,
                      ),
                    ),
                    Text(
                      _userEmail,
                      style: GoogleFonts.inter(
                        fontSize: 11,
                        color: Colors.white70,
                      ),
                    ),
                  ],
                ),
              ),

              // ── Menu items ──
              Expanded(
                child: ListView(
                  padding: const EdgeInsets.symmetric(vertical: 8),
                  children: [
                    _drawerItem(Icons.dashboard_rounded, 'Dashboard', 0),
                    _drawerDivider('TASKS'),
                    _drawerItem(Icons.task_alt_rounded, 'Active Tasks', 1),
                    _drawerItem(
                      Icons.check_circle_outline_rounded,
                      'Completed',
                      2,
                    ),
                    _drawerItem(Icons.delete_outline_rounded, 'Trash', 3),
                    _drawerDivider('FEATURES'),
                    _drawerItem(Icons.map_outlined, 'Roadmap', 4),
                    _drawerItem(Icons.folder_outlined, 'Categories', 5),
                    _drawerDivider('ACCOUNT'),
                    _drawerItem(Icons.person_outline_rounded, 'Profile', 6),
                  ],
                ),
              ),

              // ── Logout ──
              Container(
                padding: const EdgeInsets.all(16),
                child: SizedBox(
                  width: double.infinity,
                  height: 48,
                  child: OutlinedButton.icon(
                    onPressed: () {
                      Navigator.pop(context);
                      showDialog(
                        context: context,
                        builder: (ctx) => AlertDialog(
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(20),
                          ),
                          title: Text(
                            'Logout',
                            style: GoogleFonts.inter(
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                          content: Text(
                            'Are you sure?',
                            style: GoogleFonts.inter(fontSize: 14),
                          ),
                          actions: [
                            TextButton(
                              onPressed: () => Navigator.pop(ctx),
                              child: Text(
                                'Cancel',
                                style: GoogleFonts.inter(
                                  fontWeight: FontWeight.w700,
                                  color: Colors.grey[600],
                                ),
                              ),
                            ),
                            TextButton(
                              onPressed: () {
                                Navigator.pop(ctx);
                                _logout();
                              },
                              child: Text(
                                'Logout',
                                style: GoogleFonts.inter(
                                  fontWeight: FontWeight.w700,
                                  color: Colors.red[600],
                                ),
                              ),
                            ),
                          ],
                        ),
                      );
                    },
                    icon: Icon(
                      Icons.logout_rounded,
                      color: Colors.red[400],
                      size: 18,
                    ),
                    label: Text(
                      'LOGOUT',
                      style: GoogleFonts.inter(
                        fontSize: 10,
                        fontWeight: FontWeight.w900,
                        letterSpacing: 3,
                        color: Colors.red[400],
                      ),
                    ),
                    style: OutlinedButton.styleFrom(
                      side: BorderSide(color: Colors.red[200]!),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14),
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
      body: _pages[_currentIndex],
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          border: Border(top: BorderSide(color: Colors.grey[200]!, width: 1)),
        ),
        child: BottomNavigationBar(
          currentIndex: _currentIndex < 5 ? _currentIndex : 0,
          onTap: (i) {
            setState(() => _currentIndex = i);
            _refreshPage(i);
          },
          backgroundColor: Colors.white,
          elevation: 0,
          selectedItemColor: const Color(0xFF4F46E5),
          unselectedItemColor: Colors.grey[400],
          selectedLabelStyle: GoogleFonts.inter(
            fontSize: 8,
            fontWeight: FontWeight.w900,
            letterSpacing: 1.5,
          ),
          unselectedLabelStyle: GoogleFonts.inter(
            fontSize: 8,
            fontWeight: FontWeight.w700,
            letterSpacing: 1,
          ),
          type: BottomNavigationBarType.fixed,
          items: const [
            BottomNavigationBarItem(
              icon: Icon(Icons.dashboard_rounded),
              label: 'HOME',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.task_alt_rounded),
              label: 'TASKS',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.check_circle_outline),
              label: 'DONE',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.delete_outline_rounded),
              label: 'TRASH',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.map_outlined),
              label: 'ROADMAP',
            ),
          ],
        ),
      ),
    );
  }

  Widget _drawerItem(IconData icon, String label, int index) {
    final selected = _currentIndex == index;
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 12, vertical: 2),
      decoration: BoxDecoration(
        color: selected
            ? const Color(0xFF4F46E5).withOpacity(0.08)
            : Colors.transparent,
        borderRadius: BorderRadius.circular(14),
      ),
      child: ListTile(
        dense: true,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        leading: Icon(
          icon,
          size: 20,
          color: selected ? const Color(0xFF4F46E5) : Colors.grey[500],
        ),
        title: Text(
          label,
          style: GoogleFonts.inter(
            fontSize: 12,
            fontWeight: selected ? FontWeight.w800 : FontWeight.w600,
            color: selected ? const Color(0xFF4F46E5) : Colors.grey[700],
          ),
        ),
        onTap: () {
          setState(() => _currentIndex = index);
          _refreshPage(index);
          Navigator.pop(context);
        },
      ),
    );
  }

  Widget _drawerDivider(String label) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(24, 16, 24, 4),
      child: Text(
        label,
        style: GoogleFonts.inter(
          fontSize: 9,
          fontWeight: FontWeight.w900,
          letterSpacing: 2.5,
          color: Colors.grey[400],
        ),
      ),
    );
  }
}
