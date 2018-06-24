<?php

namespace App\Models\Team;

use App\Models\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait TeamRelatedTrait
 * @package App\Models\Team
 */
trait TeamRelatedTrait
{

    /**
     * Get query builder for records from given team
     * @param Team $team
     * @return Builder
     */
    public static function findFromTeam(Team $team): Builder
    {
        return self::where('team_id', $team->id);
    }

    /**
     * @return BelongsTo
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}