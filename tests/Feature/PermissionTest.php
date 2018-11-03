<?php

namespace Tests\Feature;

use Illuminate\Http\Response;

class PermissionTest extends FeatureTestCase
{
    public function testBillingTypesAreListedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $response = $this->get('/api/perms');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'data' => []
        ]);
        $response->assertJsonCount(7, 'data');
    }

}
