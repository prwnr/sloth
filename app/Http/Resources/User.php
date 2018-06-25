<?php

namespace App\Http\Resources;

use App\Models\Permission;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class User
 * @package App\Http\Resources
 */
class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function with($request)
    {
        $this->loadMissing('roles', 'member');
        $roles = $this->roles->pluck('name')->toArray();
        $permissions = [];
        foreach (Permission::all() as $perm) {
            if ($this->can($perm->name)) {
                $permissions[] = $perm->name;
            }
        }

        return [
            'roles' => $roles,
            'permissions' => $permissions,
            'team' => $this->team->toArray()
        ];
    }
}
