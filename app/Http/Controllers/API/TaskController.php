<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function index()
    {
        return Task::with(['project', 'assignee'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'priority' => 'required|in:Must be done,Important,Good to have',
            'status' => 'required|in:To Do,In Progress,Under Review,Completed',
            'assignee_id' => 'nullable|exists:users,id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240',
        ]);

        $task = Task::create([
            'project_id' => $validated['project_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_date' => $validated['due_date'],
            'priority' => $validated['priority'],
            'status' => $validated['status'],
            'assignee_id' => $validated['assignee_id'] ?? null,
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attachments', 'public');
                
                TaskAttachment::create([
                    'task_id' => $task->id,
                    'filename' => basename($path),
                    'original_filename' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        return response()->json($task->load('attachments'), 201);
    }

    public function show(Task $task)
    {
        return $task->load(['project', 'assignee', 'comments.user', 'attachments']);
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'project_id' => 'sometimes|required|exists:projects,id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'sometimes|required|date',
            'priority' => 'sometimes|required|in:Must be done,Important,Good to have',
            'status' => 'sometimes|required|in:To Do,In Progress,Under Review,Completed',
            'assignee_id' => 'nullable|exists:users,id',
        ]);

        $task->update($validated);

        return response()->json($task->load(['project', 'assignee']), 200);
    }

    public function destroy(Task $task)
    {
        // Delete associated attachments from storage
        foreach ($task->attachments as $attachment) {
            Storage::delete($attachment->file_path);
            $attachment->delete();
        }

        $task->delete();
        return response()->json(null, 204);
    }
    
    // Method to update task priority (for drag-and-drop functionality)
    public function updatePriority(Request $request, Task $task)
    {
        $validated = $request->validate([
            'priority' => 'required|in:Must be done,Important,Good to have',
        ]);
        
        $task->update([
            'priority' => $validated['priority']
        ]);
        
        return response()->json($task, 200);
    }
    
    // Method to add attachments to an existing task
    public function addAttachments(Request $request, Task $task)
    {
        $request->validate([
            'attachments' => 'required|array',
            'attachments.*' => 'file|max:10240',
        ]);
        
        $attachments = [];
        
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attachments', 'public');
                
                $attachment = TaskAttachment::create([
                    'task_id' => $task->id,
                    'filename' => basename($path),
                    'original_filename' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
                
                $attachments[] = $attachment;
            }
        }
        
        return response()->json($task->load(['project', 'assignee', 'comments.user', 'attachments']), 200);
    }
}