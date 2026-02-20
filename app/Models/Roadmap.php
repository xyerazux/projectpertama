<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Roadmap extends Model
{
    protected $fillable = ['user_id', 'title', 'description', 'status', 'target_date'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function steps(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(RoadmapStep::class);
}

}