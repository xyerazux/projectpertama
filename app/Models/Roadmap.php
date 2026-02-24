<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Roadmap extends Model
{
    use SoftDeletes;
    /**
     * Attributes that are mass assignable
     * ⚠️ PENTING: Hanya field ini yang bisa diisi via create()/update()
     */
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
        'target_date',
    ];

    /**
     * Attributes that should be cast to native types
     */
    protected $casts = [
        'target_date' => 'date',
    ];

    /**
     * Relationship: Roadmap belongs to User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Roadmap has many Steps
     */
    public function steps(): HasMany
    {
        return $this->hasMany(RoadmapStep::class)->orderBy('created_at', 'asc');
    }

    /**
     * Helper: Get completion percentage
     */
    public function getCompletionPercentageAttribute(): float
    {
        $total = $this->steps->count();
        if ($total === 0)
            return 0;

        $completed = $this->steps->where('is_completed', true)->count();
        return round(($completed / $total) * 100);
    }
}