<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function index()
    {
        return Task::with(['project', 'assignee'])->get();
    }

    protected $notificationService;
    
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
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
            'creator_id' => Auth::id(),
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
        
        // Force create notifications
        $currentUser = Auth::user();
        if ($currentUser) {
            \Log::info("Creating task creation notification for user ID: " . $currentUser->id);
            
            // Create a notification for the current user
            $notification = new \App\Models\Notification();
            $notification->user_id = $currentUser->id;
            $notification->title = 'New Task Created';
            $notification->content = "You created task \"{$task->title}\"";
            $notification->type = 'task_created';
            $notification->read = false;
            $notification->save();
            
            \Log::info("Task creation notification created with ID: " . $notification->id);
            
            // Create notification for the assignee if it's not the creator
            if ($task->assignee_id && $task->assignee_id !== $currentUser->id) {
                $notification = new \App\Models\Notification();
                $notification->user_id = $task->assignee_id;
                $notification->title = 'New Task Assigned';
                $notification->content = "You have been assigned to the task \"{$task->title}\"";
                $notification->type = 'task_assigned';
                $notification->read = false;
                $notification->save();
                
                \Log::info("Task assignment notification created for user ID: " . $task->assignee_id);
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
        
        // Store the old values before update
        $oldValues = $task->getAttributes();
        
        $task->update($validated);
        
        // Check if assignee changed
        if (isset($validated['assignee_id']) && $oldValues['assignee_id'] != $validated['assignee_id'] && $validated['assignee_id'] != null) {
            // Get the new assignee
            $newAssignee = \App\Models\User::find($validated['assignee_id']);
            
            if ($newAssignee && $newAssignee->id !== Auth::id()) {
                $this->notificationService->create(
                    $newAssignee,
                    'Task Assigned to You',
                    "You have been assigned to the task \"{$task->title}\"",
                    'task_assigned',
                    $task
                );
            }
        }
        
        // Check if status changed
        if (isset($validated['status']) && $oldValues['status'] != $validated['status']) {
            // Notify task creator if they're not the one who updated it
            if ($task->creator_id && $task->creator_id !== Auth::id()) {
                $creator = \App\Models\User::find($task->creator_id);
                if ($creator) {
                    $this->notificationService->create(
                        $creator,
                        'Task Status Updated',
                        "The status of \"{$task->title}\" has been changed to \"{$validated['status']}\"",
                        'task_updated',
                        $task
                    );
                }
            }
        }

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
        
        // Store old priority
        $oldPriority = $task->priority;
        
        $task->update([
            'priority' => $validated['priority']
        ]);
        
        // Force create a test notification for the authenticated user
        $currentUser = Auth::user();
        if ($currentUser) {
            \Log::info("Creating test notification for user ID: " . $currentUser->id);
            
            // Create a notification for the current user directly
            $notification = new \App\Models\Notification();
            $notification->user_id = $currentUser->id;
            $notification->title = 'Task Priority Changed';
            $notification->content = "Priority changed to: " . $validated['priority'];
            $notification->type = 'task_updated';
            $notification->read = false;
            $notification->save();
            
            \Log::info("Test notification created with ID: " . $notification->id);
        } else {
            \Log::error("No authenticated user found for notification");
        }
        
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
            
            // Notify assignee about new attachments if they're not the uploader
            if ($task->assignee_id && $task->assignee_id !== Auth::id()) {
                $this->notificationService->create(
                    $task->assignee,
                    'New Attachments Added',
                    "New files have been attached to task \"{$task->title}\"",
                    'task_attachment',
                    $task
                );
            }
            
            // Notify task creator about new attachments if they're not the uploader and not the assignee
            if ($task->creator_id && $task->creator_id !== Auth::id() && $task->creator_id !== $task->assignee_id) {
                $creator = \App\Models\User::find($task->creator_id);
                if ($creator) {
                    $this->notificationService->create(
                        $creator,
                        'New Attachments Added',
                        "New files have been attached to task \"{$task->title}\"",
                        'task_attachment',
                        $task
                    );
                }
            }
        }
        
        return response()->json($task->load(['project', 'assignee', 'comments.user', 'attachments']), 200);
    }
}