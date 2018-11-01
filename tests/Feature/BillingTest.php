<?php

namespace Tests\Feature;

use Illuminate\Http\Response;

class BillingTest extends FeatureTestCase
{

    public function testBillingTypesAreListedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $response = $this->get('/api/billings/types');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertExactJson([
            'data' => [
                'hourly' => 'Hourly rate',
                'fixed' => 'Fixed price'
            ]
        ]);
    }

    public function testBillingDataIsListedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $response = $this->get('/api/billings/data');

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
}
