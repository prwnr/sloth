<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\Billing as BillingResource;
use App\Models\Billing;
use App\Models\Project;
use App\Repositories\CurrencyRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class BillingController
 * @package App\Http\Controllers\Api
 */
class BillingController extends Controller
{
    /**
     * @var CurrencyRepository
     */
    private $currencyRepository;

    /**
     * BillingController constructor.
     * @param CurrencyRepository $currencyRepository
     */
    public function __construct(CurrencyRepository $currencyRepository)
    {
        $this->currencyRepository = $currencyRepository;
    }

    /**
     * Display types of the resource.
     *
     * @return BillingResource
     */
    public function types(): BillingResource
    {
        return new BillingResource(Billing::getRateTypes());
    }

    /**
     * @param Request $request
     * @return array
     */
    public function showBillingData(Request $request): array
    {
        return [
            'billing_types' => (new BillingResource(Billing::getRateTypes()))->toArray($request),
            'currencies' => (new ResourceCollection($this->currencyRepository->all()))->toArray($request),
            'budget_periods' => Project::BUDGET_PERIOD
        ];
    }
}
