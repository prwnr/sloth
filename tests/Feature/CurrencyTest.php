<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CurrencyTest extends FeatureTestCase
{
    public function testCurrenciesAreListedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $response = $this->json(Request::METHOD_GET, '/api/currencies');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => [
                [
                    'id', 'name', 'code', 'symbol', 'created_at', 'updated_at'
                ]
            ]
        ]);
        $response->assertJsonCount(5, 'data');
    }

    public function testCurrenciesAreNotListedForGuest(): void
    {
        $response = $this->json(Request::METHOD_GET, '/api/currencies');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJson([
            'message' => 'Unauthenticated.'
        ]);
    }
}
