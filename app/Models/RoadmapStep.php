<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class RoadmapStep extends Model
{
    /**
     * Attributes that are mass assignable
     * ⚠️ Hanya field ini yang bisa diisi via create()/update()
     */
    protected $fillable = [
        'roadmap_id',
        'title',
        'is_completed',
        'priority',      // high, medium, low
        'due_date',      // deadline task
        'description',   // detail task
        'category',      // Design, Development, dll
        'progress',      // 0-100
    ];

    /**
     * Attributes that should be cast to native types
     */
    protected $casts = [
        'is_completed' => 'boolean',
        'due_date' => 'date',
        'progress' => 'integer',
    ];

    /**
     * Relationship: Step belongs to Roadmap
     */
    public function roadmap(): BelongsTo
    {
        return $this->belongsTo(Roadmap::class);
    }

    /**
     * Accessor: Get icon emoji based on category (case-insensitive)
     * 💡 Dipakai di Blade: {{ $step->category_icon }}
     */
    public function getCategoryIconAttribute(): string
    {
        $category = strtolower(trim($this->category ?? ''));

        return match ($category) {
            default => '',
        };
    }

    /**
     * Accessor: Get color class based on category for visual consistency
     * 💡 Dipakai di Blade: {{ $step->category_color }}
     */
    public function getCategoryColorAttribute(): string
    {
        $category = strtolower(trim($this->category ?? ''));

        return match ($category) {
            'design', 'ui', 'ux', 'graphics', 'animation', 'content' => 'text-pink-600 bg-pink-50 border-pink-200',
            'development', 'frontend', 'backend', 'mobile', 'deployment', 'devops' => 'text-indigo-600 bg-indigo-50 border-indigo-200',
            'testing', 'bug', 'debug', 'qa' => 'text-amber-600 bg-amber-50 border-amber-200',
            'marketing', 'research', 'planning', 'meeting', 'review', 'analytics' => 'text-emerald-600 bg-emerald-50 border-emerald-200',
            'security', 'performance', 'monitoring' => 'text-red-600 bg-red-50 border-red-200',
            'documentation', 'support', 'training' => 'text-sky-600 bg-sky-50 border-sky-200',
            'maintenance', 'update', 'backup' => 'text-slate-600 bg-slate-50 border-slate-200',
            'idea', 'feature', 'request' => 'text-violet-600 bg-violet-50 border-violet-200',
            'infrastructure', 'integration', 'localization' => 'text-cyan-600 bg-cyan-50 border-cyan-200',
            default => 'text-slate-600 bg-slate-50 border-slate-200',
        };
    }

    /**
     * Helper: Check if task is overdue
     * ✅ FIX: Proper null check and Carbon instance validation
     */
    public function getIsOverdueAttribute(): bool
    {
        if (!$this->due_date) {
            return false;
        }

        // Ensure due_date is a Carbon instance
        $dueDate = $this->due_date instanceof Carbon
            ? $this->due_date
            : Carbon::parse($this->due_date);

        return $dueDate->isPast() && !$this->is_completed;
    }
}