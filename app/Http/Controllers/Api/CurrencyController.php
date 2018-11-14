<?php

namespace App\Http\Controllers\Api;

use App\Models\Currency;
use App\Http\Resources\Currency as CurrencyResource;
use App\Http\Controllers\Controller;
use App\Repositories\CurrencyRepository;

/**
 * Class CurrencyController
 * @package App\Http\Controllers\Api
 */
class CurrencyController extends Controller
{

    /**
     * @var CurrencyRepository
     */
    private $currencyRepository;

    /**
     * CurrencyController constructor.
     * @param CurrencyRepository $currencyRepository
     */
    public function __construct(CurrencyRepository $currencyRepository)
    {
        $this->currencyRepository = $currencyRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return CurrencyResource
     */
    public function index(): CurrencyResource
    {
        return new CurrencyResource($this->currencyRepository->all());
    }
}
