<?php

namespace App\Models;

use App\Models\Team\Member;
use App\Models\Team\Teamed;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;

/**
 * Class User
 * @package App\Models
 */
class User extends Authenticatable
{
    use Notifiable, Teamed, HasApiTokens;

    /**
     * Light user skin
     */
    public CONST THEME_LIGHT = 'light';

    /**
     * Dark user skin
     */
    public CONST THEME_DARK = 'dark';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname', 'lastname', 'email', 'password', 'owns_team'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * @var array
     */
    protected $appends = [
        'fullname'
    ];

    /**
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return $this->attributes['firstname'] . ' ' . $this->attributes['lastname'];
    }

    /**
     * Hash password
     * @param $input
     */
    public function setPasswordAttribute($input): void
    {
        if ($input) {
            $this->attributes['password'] = app('hash')->needsRehash($input) ? Hash::make($input) : $input;
        }
    }

    /**
     * @return bool
     */
    public function ownsTeam(): bool
    {
        if ($this->team_id === $this->owns_team) {
            return true;
        }

        return false;
    }

    /**
     * @return Member
     */
    public function member(): Member
    {
        return Member::findFromTeam($this->team)->firstOrFail();
    }

    /**
     * @return HasMany
     */
    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    /**
     * Check if users current active member has a permission by its name.
     *
     * @param string|array $permission Permission string or array of permissions.
     * @param bool $requireAll All permissions in the array are required.
     *
     * @return bool
     */
    public function can($permission, $requireAll = false): bool
    {
        if (\is_array($permission)) {
            foreach ($permission as $permName) {
                $hasPerm = $this->member()->can($permName);

                if ($hasPerm && !$requireAll) {
                    return true;
                }

                if (!$hasPerm && $requireAll) {
                    return false;
                }
            }

            return $requireAll;
        }

        foreach ($this->member()->cachedRoles() as $role) {
            // Validate against the Permission table
            foreach ($role->member()->cachedPermissions() as $perm) {
                if (str_is($permission, $perm->name)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Return all important info about user as array (for json response)
     * @return array
     */
    public function getAllInfoData(): array
    {
        $roles = $this->member()->roles()->select(['id', 'name', 'display_name'])->get();
        $permissions = [];
        foreach (Permission::all() as $perm) {
            if ($this->member()->can($perm->name)) {
                $permissions[] = $perm->name;
            }
        }

        return [
            'data' => $this,
            'projects' => $this->member() ? $this->member()->projects : Project::findFromTeam($this->team)->get(),
            'permissions' => $permissions,
            'roles' => $roles,
            'team' => $this->team,
            'member' => $this->member()
        ];
    }
}
