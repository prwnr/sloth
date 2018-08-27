<?php

namespace App\Models\Team;

use App\Models\{
    Billing, Project, User
};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Member
 * @package App\Models\Team
 */
class Member extends Model
{
    use Teamed;

    /**
     * @var array
     */
    protected $fillable = [
        'first_login',
    ];

    /**
     * @var array
     */
    protected $appends = [
        'active'
    ];

    /**
     * @var array
     */
    protected $with = [
        'user'
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function billing(): BelongsTo
    {
        return $this->belongsTo(Billing::class);
    }

    /**
     * @return BelongsToMany
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class);
    }

    /**
     * Get is_active attribute as Yes/No string
     * @return string
     */
    public function getActiveAttribute(): string
    {
        if ($this->is_active) {
            return __('Yes');
        }

        return __('No');
    }
}
