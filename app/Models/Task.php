<?php

namespace App\Models;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'deadline',
        'priority',
        'status',
        
    ];
    public function user() {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
    return $this->belongsTo(Category::class);
    }
    
  

}
