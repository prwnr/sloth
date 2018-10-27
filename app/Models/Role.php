<?php

namespace App\Models;

use App\Models\Team\Member;
use App\Models\Team\Teamed;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Zend\Diactoros\Exception\DeprecatedMethodException;
use Zizaco\Entrust\EntrustRole;

/**
 * Class Role
 * @package App\Models
 */
class Role extends EntrustRole
{

    use Teamed;

    public const ADMIN = 'admin';
    public const MANAGER = 'manager';
    public const PROGRAMMER = 'programmer';

    /**
     * Roles that cannot be edit by anyone
     * @var array
     */
    protected $nonEditable = [
        self::ADMIN
    ];

    /**
     * Roles that cannot be deleted by anyone
     * @var array
     */
    protected $nonDeletable = [
        self::ADMIN, self::MANAGER
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'name', 'display_name', 'description'
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
        if (\in_array($this->name, $this->nonEditable, true)) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isDeletable(): bool
    {
        if (\in_array($this->name, $this->nonDeletable, true)) {
            return false;
        }

        return true;
    }

    /**
     * @return BelongsToMany
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, Config::get('entrust.role_user_table'), Config::get('entrust.role_foreign_key'), Config::get('entrust.user_foreign_key'));
    }

    /**
     * @deprecated Roles are assigned to members. Use members method to access them
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->members();
    }
}