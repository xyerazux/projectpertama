import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart';

class LocalDb {
  static Database? _db;

  static Future<Database> get database async {
    if (_db != null) return _db!;
    _db = await _init();
    return _db!;
  }

  static Future<Database> _init() async {
    final path = join(await getDatabasesPath(), 'productivityapp.db');
    return openDatabase(
      path,
      version: 2,
      onCreate: (db, version) async {
        await db.execute('''
          CREATE TABLE roadmaps (
            id INTEGER PRIMARY KEY,
            server_id INTEGER,
            title TEXT NOT NULL,
            description TEXT,
            status TEXT DEFAULT 'planned',
            target_date TEXT,
            completion_percentage REAL DEFAULT 0,
            synced INTEGER DEFAULT 1,
            created_at TEXT,
            updated_at TEXT
          )
        ''');
        await db.execute('''
          CREATE TABLE roadmap_steps (
            id INTEGER PRIMARY KEY,
            server_id INTEGER,
            roadmap_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            description TEXT,
            category TEXT,
            priority TEXT DEFAULT 'medium',
            due_date TEXT,
            progress INTEGER DEFAULT 0,
            is_completed INTEGER DEFAULT 0,
            synced INTEGER DEFAULT 1,
            FOREIGN KEY (roadmap_id) REFERENCES roadmaps (id) ON DELETE CASCADE
          )
        ''');
        await db.execute('''
          CREATE TABLE sync_queue (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            action TEXT NOT NULL,
            endpoint TEXT NOT NULL,
            method TEXT NOT NULL,
            body TEXT,
            created_at TEXT
          )
        ''');
      },
      onUpgrade: (db, oldV, newV) async {
        if (oldV < 2) {
          await db.execute('DROP TABLE IF EXISTS sync_queue');
          await db.execute('DROP TABLE IF EXISTS roadmap_steps');
          await db.execute('DROP TABLE IF EXISTS roadmaps');
          await db.execute('''
            CREATE TABLE roadmaps (
              id INTEGER PRIMARY KEY,
              server_id INTEGER,
              title TEXT NOT NULL,
              description TEXT,
              status TEXT DEFAULT 'planned',
              target_date TEXT,
              completion_percentage REAL DEFAULT 0,
              synced INTEGER DEFAULT 1,
              created_at TEXT,
              updated_at TEXT
            )
          ''');
          await db.execute('''
            CREATE TABLE roadmap_steps (
              id INTEGER PRIMARY KEY,
              server_id INTEGER,
              roadmap_id INTEGER NOT NULL,
              title TEXT NOT NULL,
              description TEXT,
              category TEXT,
              priority TEXT DEFAULT 'medium',
              due_date TEXT,
              progress INTEGER DEFAULT 0,
              is_completed INTEGER DEFAULT 0,
              synced INTEGER DEFAULT 1,
              FOREIGN KEY (roadmap_id) REFERENCES roadmaps (id) ON DELETE CASCADE
            )
          ''');
          await db.execute('''
            CREATE TABLE sync_queue (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              action TEXT NOT NULL,
              endpoint TEXT NOT NULL,
              method TEXT NOT NULL,
              body TEXT,
              created_at TEXT
            )
          ''');
        }
      },
    );
  }

  // ── Roadmaps ──
  static Future<void> cacheRoadmaps(List<Map<String, dynamic>> roadmaps) async {
    final db = await database;
    await db.delete('roadmap_steps');
    await db.delete('roadmaps');
    for (final r in roadmaps) {
      final roadmapId = r['id'];
      await db.insert('roadmaps', {
        'id': roadmapId,
        'server_id': roadmapId,
        'title': r['title'],
        'description': r['description'],
        'status': r['status'],
        'target_date': r['target_date'],
        'completion_percentage': (r['completion_percentage'] ?? 0).toDouble(),
        'synced': 1,
        'created_at': r['created_at'],
        'updated_at': r['updated_at'],
      });
      final steps = r['steps'] as List? ?? [];
      for (final s in steps) {
        await db.insert('roadmap_steps', {
          'id': s['id'],
          'server_id': s['id'],
          'roadmap_id': roadmapId,
          'title': s['title'],
          'description': s['description'],
          'category': s['category'],
          'priority': s['priority'],
          'due_date': s['due_date'],
          'progress': s['progress'] ?? 0,
          'is_completed': (s['is_completed'] == true || s['is_completed'] == 1)
              ? 1
              : 0,
          'synced': 1,
        });
      }
    }
  }

  static Future<List<Map<String, dynamic>>> getCachedRoadmaps() async {
    final db = await database;
    final roadmaps = await db.query('roadmaps', orderBy: 'target_date ASC');
    final result = <Map<String, dynamic>>[];
    for (final r in roadmaps) {
      final rm = Map<String, dynamic>.from(r);
      final steps = await db.query(
        'roadmap_steps',
        where: 'roadmap_id = ?',
        whereArgs: [r['id']],
      );
      rm['steps'] = steps.map((s) {
        final m = Map<String, dynamic>.from(s);
        m['is_completed'] = m['is_completed'] == 1;
        return m;
      }).toList();
      result.add(rm);
    }
    return result;
  }

  // ── Sync Queue ──
  static Future<void> addToSyncQueue(
    String action,
    String endpoint,
    String method,
    String? body,
  ) async {
    final db = await database;
    await db.insert('sync_queue', {
      'action': action,
      'endpoint': endpoint,
      'method': method,
      'body': body,
      'created_at': DateTime.now().toIso8601String(),
    });
  }

  static Future<List<Map<String, dynamic>>> getSyncQueue() async {
    final db = await database;
    return db.query('sync_queue', orderBy: 'created_at ASC');
  }

  static Future<void> clearSyncItem(int id) async {
    final db = await database;
    await db.delete('sync_queue', where: 'id = ?', whereArgs: [id]);
  }
}
