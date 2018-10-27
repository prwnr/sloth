<?php

namespace App\Http\Controllers\Api;

use App\Models\Currency;
use App\Http\Resources\Currency as CurrencyResource;
use App\Http\Controllers\Controller;

/**
 * Class CurrencyController
 * @package App\Http\Controllers\Api
 */
class CurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return CurrencyResource
     */
    public function index(): CurrencyResource
    {
        return new CurrencyResource(Currency::all());
    }
}
