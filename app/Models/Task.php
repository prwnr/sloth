<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Task
 * @package App\Models
 */
class Task extends Model
{
    public const PROGRAMMING = 'programming';
    public const CODE_REVIEW = 'code_review';
    public const QA = 'qa';
    public const PROJECT_MANAGEMENT = 'project_management';

    /**
     * @var array
     */
    protected $fillable = [
        'type', 'name', 'billable', 'currency_id', 'billing_rate', 'is_deleted'
    ];

    /**
     * @var array 
     */
    protected $appends = [
        'billable_text'
    ];

    /**
     * @return array
     */
    public static function getTypes(): array
    {
        return [
            self::PROGRAMMING => __('Programming'),
            self::CODE_REVIEW => __('Code review'),
            self::QA => __('Quality Assurance'),
            self::PROJECT_MANAGEMENT => __('Project management')
        ];
    }

    /**
     * @return string
     */
    public function getBillableTextAttribute(): string
    {
        return $this->billable ? __('Billable') : __('Non-billable');
    }

    /**
     * @return BelongsTo
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
    /**
     * @return HasMany
     */
    public function times(): HasMany
    {
        return $this->hasMany(TimeLog::class);
    }

}
