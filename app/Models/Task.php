<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

     protected $fillable = [
        'user_id',
        'title',
        'description',
        'category_id',
        'deadline',
        'priority',
        'priority_color',
        'status',
        'link_attachment',
        'completed_at',
    ];

     protected $casts = [
        'deadline' => 'date',
        'completed_at' => 'datetime',
    ];

    // relationships...
    public function user() {
        return $this->belongsTo(User::class);
    }

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function subtasks() {
        return $this->hasMany(Subtask::class);
    }
}
