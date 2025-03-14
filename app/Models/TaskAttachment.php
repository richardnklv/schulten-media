<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskAttachment extends Model
{
    //
    protected $fillable = ['task_id', 'filename', 'original_filename', 'file_path', 'file_type', 'file_size'];

    public function task() {
        return $this->belongsTo(Task::class);
    }
}
