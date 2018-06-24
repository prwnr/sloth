<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Billing
 * @package App\Models
 */
class Billing extends Model
{
    public const HOURLY_RATE = 'hourly';
    public const FIXED_RATE = 'fixed';

    /**
     * @var array
     */
    protected $fillable = [
        'rate', 'type', 'currency_id'
    ];

    /**
     * @return array
     */
    public static function getRateTypes(): array
    {
        return [
            self::HOURLY_RATE => __('Hourly rate'),
            self::FIXED_RATE => __('Fixed price')
        ];
    }

    /**
     * @return BelongsTo
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

}
