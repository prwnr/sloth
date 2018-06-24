<?php

namespace App\Models;

use App\Models\Team\TeamRelatedTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Client
 * @package App\Models
 */
class Client extends Model
{
    use TeamRelatedTrait;

    /**
     * @var string
     */
    protected $table = 'clients';

    /**
     * @var array
     */
    protected $fillable = [
        'company_name', 'city', 'zip', 'country', 'street', 'vat', 'fullname', 'email'
    ];

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
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
