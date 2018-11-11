<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BillingTest extends FeatureTestCase
{

    public function testBillingTypesAreListedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $response = $this->json(Request::METHOD_GET, '/api/billings/types');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertExactJson([
            'data' => [
                'hourly' => 'Hourly rate',
                'fixed' => 'Fixed price'
            ]
        ]);
    }

    public function testBillingTypesAreNotListedForGuest(): void
    {
        $response = $this->json(Request::METHOD_GET, '/api/billings/types');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJson([
            'message' => 'Unauthenticated.'
        ]);
    }

    public function testBillingDataIsListedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $response = $this->json(Request::METHOD_GET,'/api/billings/data');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'billing_types' => [
                'hourly', 'fixed'
            ],
            'currencies',
            'budget_periods' => [
                'month', 'quarter', 'year', 'unlimited'
            ]
        ]);
    }

    public function testBillingDataIsNotListedForGuest(): void
    {
        $response = $this->json(Request::METHOD_GET,'/api/billings/data');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJson([
            'message' => 'Unauthenticated.'
        ]);
    }

}
