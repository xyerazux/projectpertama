<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyReflection extends Model
{
    protected $fillable = ['user_id', 'mood', 'note'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
