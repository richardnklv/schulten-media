<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Add this import

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // Add HasApiTokens here
    
    // Rest of your model remains unchanged
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function projects() {
        return $this->hasMany(Project::class);
    }

    public function assignedTasks() {
        return $this->hasMany(Task::class, 'assignee_id');
    }
}