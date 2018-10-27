<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\Currency as CurrencyResource;
use App\Models\Billing;
use App\Http\Resources\Billing as BillingResource;
use App\Models\Currency;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * Class BillingController
 * @package App\Http\Controllers\Api
 */
class BillingController extends Controller
{
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
            'currencies' => (new CurrencyResource(Currency::all()))->toArray($request),
            'budget_periods' => Project::BUDGET_PERIOD
        ];
    }
}
