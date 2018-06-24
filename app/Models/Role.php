<?php

namespace App\Models;

use App\Models\Team\TeamRelatedTrait;
use Illuminate\Support\Collection;
use Zizaco\Entrust\EntrustRole;

/**
 * Class Role
 * @package App\Models
 */
class Role extends EntrustRole
{

    use TeamRelatedTrait;

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
}