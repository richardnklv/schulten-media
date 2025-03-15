<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Task;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    protected $notificationService;
    
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    
    public function index(Task $task)
    {
        return $task->comments()->with('user')->get();
    }

    public function store(Request $request, Task $task)
    {
        $validated = $request->validate([
            'content' => 'required|string'
        ]);

        $comment = $task->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $validated['content']
        ]);
        
        // Force create a notification for the current user
        $currentUser = Auth::user();
        if ($currentUser) {
            \Log::info("Creating comment notification for user ID: " . $currentUser->id);
            
            // Create a notification directly
            $notification = new \App\Models\Notification();
            $notification->user_id = $currentUser->id;
            $notification->title = 'New Comment Added';
            $notification->content = "You added a comment to task \"{$task->title}\"";
            $notification->type = 'comment_created';
            $notification->read = false;
            $notification->save();
            
            \Log::info("Comment notification created with ID: " . $notification->id);
            
            // Also notify task assignee if not the current user
            if ($task->assignee_id && $task->assignee_id !== $currentUser->id) {
                $notification = new \App\Models\Notification();
                $notification->user_id = $task->assignee_id;
                $notification->title = 'New Comment on Task';
                $notification->content = "A new comment has been added to task \"{$task->title}\"";
                $notification->type = 'comment_created';
                $notification->read = false;
                $notification->save();
                
                \Log::info("Comment notification for assignee created with ID: " . $notification->id);
            }
        }

        return response()->json($comment->load('user'), 201);
    }

    public function update(Request $request, Comment $comment)
    {
        $validated = $request->validate([
            'content' => 'required|string'
        ]);

        $comment->update($validated);
        return response()->json($comment);
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();
        return response()->json(null, 204);
    }
}