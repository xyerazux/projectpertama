import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../services/task_service.dart';

class DailyReflectionBottomSheet extends StatefulWidget {
  const DailyReflectionBottomSheet({super.key});

  @override
  State<DailyReflectionBottomSheet> createState() =>
      _DailyReflectionBottomSheetState();
}

class _DailyReflectionBottomSheetState
    extends State<DailyReflectionBottomSheet> {
  String? _selectedMood;
  final _noteController = TextEditingController();
  bool _isLoading = false;

  void _submit() async {
    if (_selectedMood == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Please select a mood', style: GoogleFonts.inter()),
          backgroundColor: Colors.red[400],
        ),
      );
      return;
    }

    setState(() => _isLoading = true);

    try {
      await TaskService.submitReflection(
        mood: _selectedMood!,
        note: _noteController.text.trim(),
      );
      if (mounted) {
        Navigator.pop(context);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              'Reflection saved. Have a great day!',
              style: GoogleFonts.inter(),
            ),
            backgroundColor: const Color(0xFF10B981),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              'Failed to save reflection.',
              style: GoogleFonts.inter(),
            ),
            backgroundColor: Colors.red[400],
          ),
        );
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  void dispose() {
    _noteController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.only(
        bottom: MediaQuery.of(context).viewInsets.bottom + 24,
        top: 24,
        left: 24,
        right: 24,
      ),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(
            'Daily Reflection',
            style: GoogleFonts.inter(fontSize: 20, fontWeight: FontWeight.w800),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 8),
          Text(
            'How was your day?',
            style: GoogleFonts.inter(fontSize: 14, color: Colors.grey[600]),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 24),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceEvenly,
            children: [
              _MoodIcon(
                icon: Icons.sentiment_very_dissatisfied_rounded,
                label: 'Bad',
                color: Colors.red[400]!,
                isSelected: _selectedMood == 'bad',
                onTap: () => setState(() => _selectedMood = 'bad'),
              ),
              _MoodIcon(
                icon: Icons.sentiment_neutral_rounded,
                label: 'Okay',
                color: Colors.orange[400]!,
                isSelected: _selectedMood == 'okay',
                onTap: () => setState(() => _selectedMood = 'okay'),
              ),
              _MoodIcon(
                icon: Icons.sentiment_very_satisfied_rounded,
                label: 'Great',
                color: Colors.green[400]!,
                isSelected: _selectedMood == 'great',
                onTap: () => setState(() => _selectedMood = 'great'),
              ),
            ],
          ),
          const SizedBox(height: 24),
          TextField(
            controller: _noteController,
            maxLines: 3,
            decoration: InputDecoration(
              hintText: 'Any notes about today?',
              hintStyle: GoogleFonts.inter(
                fontSize: 14,
                color: Colors.grey[400],
              ),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: BorderSide(color: Colors.grey[200]!),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: BorderSide(color: Colors.grey[200]!),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: Color(0xFF4F46E5)),
              ),
            ),
          ),
          const SizedBox(height: 24),
          ElevatedButton(
            onPressed: _isLoading ? null : _submit,
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF4F46E5),
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(vertical: 16),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
            ),
            child: _isLoading
                ? const SizedBox(
                    width: 20,
                    height: 20,
                    child: CircularProgressIndicator(
                      strokeWidth: 2,
                      color: Colors.white,
                    ),
                  )
                : Text(
                    'Save Reflection',
                    style: GoogleFonts.inter(fontWeight: FontWeight.w700),
                  ),
          ),
        ],
      ),
    );
  }
}

class _MoodIcon extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;
  final bool isSelected;
  final VoidCallback onTap;

  const _MoodIcon({
    required this.icon,
    required this.label,
    required this.color,
    required this.isSelected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Column(
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: isSelected ? color.withOpacity(0.1) : Colors.transparent,
              border: Border.all(
                color: isSelected ? color : Colors.transparent,
                width: 2,
              ),
              shape: BoxShape.circle,
            ),
            child: Icon(
              icon,
              size: 40,
              color: isSelected ? color : Colors.grey[400],
            ),
          ),
          const SizedBox(height: 8),
          Text(
            label,
            style: GoogleFonts.inter(
              fontSize: 12,
              fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
              color: isSelected ? color : Colors.grey[500],
            ),
          ),
        ],
      ),
    );
  }
}
