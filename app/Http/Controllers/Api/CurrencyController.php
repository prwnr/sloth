<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\CurrencyRepository;
use Illuminate\Http\Resources\Json\ResourceCollection;

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
     * @return ResourceCollection
     */
    public function index(): ResourceCollection
    {
        return new ResourceCollection($this->currencyRepository->all());
    }
}
