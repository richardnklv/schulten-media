<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->project = Project::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_can_get_tasks()
    {
        Task::factory()->count(3)->create([
            'project_id' => $this->project->id,
            'creator_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/tasks?project_id=' . $this->project->id);

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_task()
    {
        $taskData = [
            'title' => 'New Test Task',
            'description' => 'Test description',
            'due_date' => now()->addDays(7)->toDateString(),
            'priority' => 'medium',
            'status' => 'todo',
            'project_id' => $this->project->id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/tasks', $taskData);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'New Test Task']);
    }

    public function test_can_update_task()
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'creator_id' => $this->user->id,
        ]);

        $updateData = [
            'title' => 'Updated Task Title',
            'status' => 'in_progress',
        ];

        $response = $this->actingAs($this->user)
            ->putJson('/api/tasks/' . $task->id, $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Updated Task Title']);
    }

    public function test_can_delete_task()
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'creator_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson('/api/tasks/' . $task->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/tasks', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'project_id']);
    }
}