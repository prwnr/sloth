<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PermissionTest extends FeatureTestCase
{
    public function testPermissionsAreListedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $response = $this->json(Request::METHOD_GET, '/api/perms');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'data' => []
        ]);
        $response->assertJsonCount(7, 'data');
    }

    public function testPermissionsAreNotListedForGuest(): void
    {
        $response = $this->json(Request::METHOD_GET, '/api/perms');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJson([
            'message' => 'Unauthenticated.'
        ]);
    }
}
