<?php

namespace App\Models\Team;

use App\Models\{Billing, Project, TimeLog, User};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Zizaco\Entrust\Traits\EntrustUserTrait;

/**
 * Class Member
 * @package App\Models\Team
 */
class Member extends Model
{
    use Teamed, EntrustUserTrait;

    /**
     * @var array
     */
    protected $with = [
        'user'
    ];

    /**
     * @var array
     */
    protected $appends = [
        'editable', 'deletable'
    ];

    /**
     * @return bool
     */
    public function getEditableAttribute(): bool
    {
        return $this->isEditable();
    }

    /**
     * @return bool
     */
    public function getDeletableAttribute(): bool
    {
        return $this->isDeletable();
    }

    /**
     * @return bool
     */
    public function isEditable(): bool
    {
        if ($this->user->ownsTeam() && Auth::user()->id !== $this->user->id) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isDeletable(): bool
    {
        if (Auth::user()->id === $this->user->id || $this->isCurrentTeamOwner()) {
            return false;
        }

        return true;
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany
     */
    public function logs(): HasMany
    {
        return $this->hasMany(TimeLog::class);
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
     * @return bool
     */
    private function isCurrentTeamOwner(): bool
    {
        if ($this->user->team_id === Auth::user()->team_id && $this->user->ownsTeam()) {
            return true;
        }

        return false;
    }
}
