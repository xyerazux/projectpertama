<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'priority_mode',
        'profile_photo', // Tambahkan jika kamu memakai fitur foto profil
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function roadmaps(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Roadmap::class);
    }
}