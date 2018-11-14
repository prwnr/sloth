<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Team\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

abstract class FeatureTestCase extends TestCase
{
    use WithFaker, RefreshDatabase;

    /**
     * @var Member
     */
    protected $member;

    /**
     * @var User
     */
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->member = factory(Member::class)->create();
        $this->user = $this->member->user;
        $this->assertInstanceOf(Member::class, $this->member);
        $this->assertInstanceOf(User::class, $this->member->user);

        $this->setUpRolesAndPermissions();
    }

    protected function actAsRole(string $role): void
    {
        /** @var Role $role */
        $role = Role::findFromTeam($this->user->team)->where('name', $role)->first();
        $this->user->member()->attachRole($role);
        $this->user->member()->save();
    }

    private function setUpRolesAndPermissions(): void
    {
        $fileContent = Storage::disk('data')->get('roles.json');
        $roles = json_decode($fileContent, true);

        foreach ($roles as $roleData) {
            $role = $this->user->team->roles()->create([
                'name' => $roleData['name'],
                'display_name' => $roleData['display_name'],
                'description' => $roleData['description'],
            ]);

            if ($roleData['permissions'] === 'none') {
                $role->perms()->sync([]);
                continue;
            }

            if ($roleData['permissions'] === 'all') {
                $role->perms()->sync(Permission::all()->pluck('id'));
                continue;
            }

            if (\is_array($roleData['permissions'])) {
                $this->assignChosenPermissions($role, $roleData['permissions']);
            }
        }

        $this->assertCount(3, Role::findFromTeam($this->user->team)->get());
        $this->assertCount(7, Permission::all());
    }

    private function assignChosenPermissions(Role $role, array $permissions): void
    {
        $assignPermissions = [];
        foreach ($permissions as $name) {
            $permission = Permission::where('name', $name)->firstOrFail();
            $assignPermissions[] = $permission->id;
        }

        $role->perms()->sync($assignPermissions);
    }
}