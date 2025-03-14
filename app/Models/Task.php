<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    //

    protected $fillable = ['project_id', 'title', 'description', 'due_date', 'priority', 'status', 'assignee_id'];

    public function project() {
        return $this->belongsTo(Project::class);
    }

    public function assignee() {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function comments() {
        return $this->hasMany(Comment::class);
    }

    public function attachments() {
        return $this->hasMany(TaskAttachment::class);
    }
}
