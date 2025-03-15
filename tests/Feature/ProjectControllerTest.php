<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_get_all_projects()
    {
        Project::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/projects');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_project()
    {
        $projectData = [
            'title' => 'New Project',
            'description' => 'Project Description'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/projects', $projectData);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'New Project']);
    }

    public function test_can_update_project()
    {
        $project = Project::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'title' => 'Updated Project Title',
            'description' => 'Updated description'
        ];

        $response = $this->actingAs($this->user)
            ->putJson('/api/projects/' . $project->id, $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Updated Project Title']);
    }

    public function test_can_delete_project()
    {
        $project = Project::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->deleteJson('/api/projects/' . $project->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_user_can_only_access_own_projects()
    {
        $otherUser = User::factory()->create();
        $otherProject = Project::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/projects/' . $otherProject->id);

        $response->assertStatus(403);
    }
}