<?php

namespace Tests\Feature;

use Illuminate\Http\Response;

class CurrencyTest extends FeatureTestCase
{
    public function testCurrenciesAreListedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $response = $this->get('/api/currencies');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => []
        ]);
        $response->assertJsonCount(5, 'data');
    }
}
