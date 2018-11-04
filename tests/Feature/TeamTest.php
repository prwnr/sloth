<?php

namespace Tests\Feature;

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
        $respone = $this->json(Request::METHOD_PUT, "/api/teams/{$this->user->team_id}", $data);

        $respone->assertStatus(Response::HTTP_ACCEPTED);
        $respone->assertJson([
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

        $respone = $this->json(Request::METHOD_PUT, "/api/teams/{$this->user->team_id}", [
            'name' => $this->faker->word
        ]);

        $respone->assertStatus(Response::HTTP_FORBIDDEN);
        $respone->assertJson([
            'message' => 'You are not allowed to edit this team'
        ]);
    }
}
