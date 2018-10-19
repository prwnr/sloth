<?php

namespace App\Models\Team;

use App\Models\{Currency, Permission, Role, Team, User};
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\{Facades\DB, Facades\Hash, Facades\Storage};

/**
 * Class Creator
 * @package App\Models\Team
 */
class Creator
{
    private const PERMISSIONS_ALL = 'all';
    private const PERMISSIONS_NONE = 'none';

    /**
     * @var array
     */
    private $files = [
        'roles' => [],
        'permissions' => []
    ];

    /**
     * @var array
     */
    private $data;

    /**
     * @var Member
     */
    private $member;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Team
     */
    private $team;

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Creator constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the console command.
     * @throws TeamCreatorException
     */
    public function make(): void
    {
        $this->loadDataFiles();

        try {
            DB::beginTransaction();
            $this->createTeam();
            $this->createUser();
            $this->createRoles();
            $this->assignPermissions();
            $this->assignRole();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new TeamCreatorException('Could not create User and his team');
        }
    }

    /**
     * Create team with name as user company name
     */
    private function createTeam(): void
    {
        $this->team = Team::create([
            'name' => $this->data['team_name']
        ]);
    }

    /**
     * Create admin user
     */
    private function createUser(): void
    {
        /** @var User $user */
        $this->user = $this->team->users()->create([
            'firstname' => $this->data['firstname'],
            'lastname' => $this->data['lastname'],
            'email' => $this->data['email'],
            'password' => Hash::make($this->data['password']),
            'owns_team' => $this->team->id
        ]);

        $this->member = new Member();
        $this->member->user()->associate($this->user);
        $this->member->team()->associate($this->team);
        $billing = $this->member->billing()->create([
            'rate' => 0,
            'type' => '',
            'currency_id' => Currency::first()->id
        ]);
        $this->member->billing()->associate($billing);
        $this->member->save();
    }

    /**
     * Create/Updates roles defined in storage/data/roles.json file
     * @throws TeamCreatorException
     */
    private function createRoles(): void
    {
        foreach ((array) $this->files['roles'] as $values) {
            try {
                $this->team->roles()->create($values);
            } catch (\Exception $ex) {
                throw new TeamCreatorException("Failed installation of role named '{$values['name']}'. Reason: " . $ex->getMessage());
            }
        }
    }

    /**
     * Assign permissions to roles based on storage/data/roles.json
     * @throws TeamCreatorException
     */
    private function assignPermissions(): void
    {
        foreach ((array) $this->files['roles'] as $role) {
            try {
                $roleModel = Role::where([
                    ['name', '=', $role['name']],
                    ['team_id', '=', $this->team->id]
                ])->firstOrFail();
                if ($role['permissions'] === self::PERMISSIONS_NONE) {
                    $roleModel->perms()->sync([]);
                    continue;
                }

                if ($role['permissions'] === self::PERMISSIONS_ALL) {
                    $this->assignAllPermissions($roleModel);
                    continue;
                }

                if (\is_array($role['permissions'])) {
                    $this->assignChosenPermissions($roleModel, $role['permissions']);
                }
            } catch (\Exception $ex) {
                throw new TeamCreatorException('Could not assign permission to role. ' . $ex->getMessage());
            }
        }
    }

    /**
     * @param Role $role
     */
    private function assignAllPermissions(Role $role): void
    {
        $assignPermissions = [];
        foreach ((array) $this->files['permissions'] as $permissionRaw) {
            $permission = Permission::where('name', $permissionRaw['name'])->firstOrFail();
            $assignPermissions[] = $permission->id;
        }

        $role->perms()->sync($assignPermissions);
    }

    /**
     * @param Role $role
     * @param array $permissions
     */
    private function assignChosenPermissions(Role $role, array $permissions): void
    {
        $assignPermissions = [];
        foreach ($permissions as $name) {
            $permission = Permission::where('name', $name)->firstOrFail();
            $assignPermissions[] = $permission->id;
        }

        $role->perms()->sync($assignPermissions);
    }

    /**
     * Attach admin role to user
     * @throws TeamCreatorException
     */
    private function assignRole(): void
    {
        try {
            $role = Role::where([
                ['name', '=', Role::ADMIN],
                ['team_id', '=', $this->team->id]
            ])->firstOrFail();

            $this->member->attachRole($role);
        } catch (ModelNotFoundException $ex) {
            throw new TeamCreatorException('Couldn\t assign admin role to user. ' . $ex->getMessage());
        }
    }

    /**
     * Load data files before installation
     * @throws TeamCreatorException
     */
    private function loadDataFiles(): void
    {
        try {
            foreach ($this->files as $fileName => $value) {
                $fileContent = Storage::disk('data')->get("$fileName.json");
                $data = json_decode($fileContent, true);
                $this->files[$fileName] = $data;
            }
        } catch (FileNotFoundException $ex) {
            throw new TeamCreatorException('File not found: ' . $ex->getMessage());
        }
    }
}