<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\Request;

class CommentController extends Controller
{
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