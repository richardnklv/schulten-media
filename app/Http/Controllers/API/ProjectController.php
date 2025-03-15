<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    protected $notificationService;
    
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Project::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $project = Project::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'user_id' => Auth::id(), // Add creator ID
        ]);
        
        // Force create a notification for the current user
        $currentUser = Auth::user();
        if ($currentUser) {
            \Log::info("Creating project notification for user ID: " . $currentUser->id);
            
            // Create a notification directly
            $notification = new \App\Models\Notification();
            $notification->user_id = $currentUser->id;
            $notification->title = 'New Project Created';
            $notification->content = "You created a new project: \"{$project->title}\"";
            $notification->type = 'project_created';
            $notification->read = false;
            $notification->save();
            
            \Log::info("Project notification created with ID: " . $notification->id);
            
            // Notify a few team members as an example
            $teamMembers = User::where('id', '!=', Auth::id())
                ->limit(2) // Just notify 2 users as an example
                ->get();
                
            foreach ($teamMembers as $member) {
                $notification = new \App\Models\Notification();
                $notification->user_id = $member->id;
                $notification->title = 'New Project Created';
                $notification->content = "A new project \"{$project->title}\" has been created";
                $notification->type = 'project_created';
                $notification->read = false;
                $notification->save();
                
                \Log::info("Project notification for team member {$member->id} created");
            }
        }

        return $project;
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        return $project->load('tasks');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $project->update($validated);
        
        // Notify team members involved with this project (simplified example)
        $teamMembers = User::whereHas('tasks', function($query) use ($project) {
                $query->where('project_id', $project->id);
            })
            ->where('id', '!=', Auth::id())
            ->get();
            
        foreach ($teamMembers as $member) {
            $this->notificationService->create(
                $member,
                'Project Updated',
                "The project \"{$project->title}\" has been updated",
                'project_updated',
                $project
            );
        }
        
        return $project;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $project->delete();
        return response()->json(null, 204);
    }
}