<?php

namespace App\Models;

use App\Models\Team\Member;
use App\Models\Team\TeamRelatedTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Project
 * @package App\Models
 */
class Project extends Model
{
    use TeamRelatedTrait;

    public const MAX_BILLING_RATE = 999999999.99;

    /**
     * Budget defined periods
     */
    public const BUDGET_PERIOD = [
        'month' => 'Month',
        'quarter' => 'Quarter',
        'year' => 'Year',
        'unlimited' => 'Unlimited'
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'name', 'code', 'budget', 'budget_period', 'budget_currency_id'
    ];

    /**
     * @return BelongsToMany
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class);
    }

    /**
     * @return BelongsTo
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return BelongsTo
     */
    public function billing(): BelongsTo
    {
        return $this->belongsTo(Billing::class);
    }

    /**
     * @return HasMany
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * @return HasMany
     */
    public function times(): HasMany
    {
        return $this->hasMany(TimeLog::class);
    }

    /**
     * @return BelongsTo
     */
    public function budgetCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
