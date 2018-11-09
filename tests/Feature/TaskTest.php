<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TaskTest extends FeatureTestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testTasksAreListedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $response = $this->json(Request::METHOD_GET, '/api/tasks');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            [
                'type' => 'programming',
                'name' => 'Programming',
                'billable' => true
            ],
            [
                'type' => 'code_review',
                'name' => 'Code review',
                'billable' => true
            ],
            [
                'type' => 'qa',
                'name' => 'Quality Assurance',
                'billable' => true
            ],
            [
                'type' => 'project_management',
                'name' => 'Project management',
                'billable' => true
            ],
        ]);
    }

    public function testTasksAreNotListedForGUest(): void
    {
        $response = $this->json(Request::METHOD_GET, '/api/tasks');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJson([
            'message' => 'Unauthenticated.'
        ]);
    }
}
