<?php

namespace App\Models;

use App\Models\Team\Member;
use App\Models\Team\TeamRelatedTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;
use Zizaco\Entrust\Traits\EntrustUserTrait;

/**
 * Class User
 * @package App\Models
 */
class User extends Authenticatable
{
    use Notifiable, EntrustUserTrait, TeamRelatedTrait, HasApiTokens;

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
        'firstname', 'lastname', 'company_name', 'email', 'password',
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
    public function setPasswordAttribute($input)
    {
        if ($input) {
            $this->attributes['password'] = app('hash')->needsRehash($input) ? Hash::make($input) : $input;
        }
    }

    /**
     * @return HasOne
     */
    public function member(): HasOne
    {
        return $this->hasOne(Member::class);
    }

    /**
     * @return HasMany
     */
    public function logs(): HasMany
    {
        return $this->hasMany(TimeLog::class);
    }

    /**
     * Return all important info about user as array (for json response)
     * @return array
     */
    public function getAllInfoData(): array
    {
        $this->loadMissing('member');
        $roles = $this->roles()->select(['id', 'name', 'display_name'])->get();
        $permissions = [];
        foreach (Permission::all() as $perm) {
            if ($this->can($perm->name)) {
                $permissions[] = $perm->name;
            }
        }

        return [
            'data' => $this,
            'projects' => $this->member ? $this->member->projects : Project::findFromTeam($this->team)->get(),
            'permissions' => $permissions,
            'roles' => $roles,
            'team' => $this->team
        ];
    }
}
