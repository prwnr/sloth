<?php

namespace Tests\Feature;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TeamTest extends FeatureTestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testTeamIsUpdatedCorrectlyByOwner(): void
    {
        $this->actingAs($this->user, 'api');
        $this->user->update([
            'owns_team' => $this->user->team_id
        ]);

        $data = [
            'name' => $this->faker->word
        ];
        $response = $this->json(Request::METHOD_PUT, "/api/teams/{$this->user->team_id}", $data);

        $response->assertStatus(Response::HTTP_ACCEPTED);
        $response->assertJson([
            'data' => [
                'id' => $this->user->team->id,
                'name' => $data['name'],
            ]
        ]);
    }

    public function testTeamsIsNotUpdatedByNotOwner(): void
    {
        $this->actingAs($this->user, 'api');
        $this->user->update([
            'owns_team' => null
        ]);

        $response = $this->json(Request::METHOD_PUT, "/api/teams/{$this->user->team_id}", [
            'name' => $this->faker->word
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $response->assertJson([
            'message' => 'You are not allowed to edit this team'
        ]);
    }

    public function testErrorMessageIsReturnedWhenExceptionIsThrownOnTeamUpdate(): void
    {
        $this->actingAs($this->user, 'api');
        $this->user->update([
            'owns_team' => $this->user->team_id
        ]);

        $data = [
            'name' => $this->faker->word
        ];

        $mock = $this->mockAndReplaceInstance(Team::class);
        $mock->shouldReceive('resolveRouteBinding')->with($this->user->team_id)->andReturn($mock);
        $mock->shouldReceive('getAttribute')->with('id')->andReturn($this->user->team_id);
        $mock->shouldReceive('update')->with($data)->andThrow(\Exception::class);

        $response = $this->json(Request::METHOD_PUT, "/api/teams/{$this->user->team_id}", $data);

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->assertJson([
            'message' => 'Something went wrong when trying to update your team. Please try again'
        ]);
    }
}
