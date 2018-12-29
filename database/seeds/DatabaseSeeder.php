<?php

use App\Models\Currency;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Project\Task;
use App\Models\Role;
use App\Models\Team\Member;
use App\Models\TodoTask;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{

    private const MEMBERS_NUM = 5;
    private const PROJECTS_NUM = 10;
    private const LOGS_NUM = 15;

    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Member
     */
    private $member;

    /**
     * @var array
     */
    private $projects = [];

    /**
     * @var array
     */
    private $members = [];

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        $this->faker = \Faker\Factory::create();
        $this->user = factory(User::class)->create([
            'email' => 'test@test.com'
        ]);

        $this->member = factory(Member::class)->create([
            'team_id' => $this->user->team_id,
            'user_id' => $this->user->id
        ]);

        $this->seedRolesAndPermissions();
        $this->seedProjects();
        $this->seedMembers();

        $this->member->projects()->attach($this->projects);
        $this->member->save();

        $this->seedLogs();
        $this->seedTodoTasks();
    }

    private function seedProjects(): void
    {
        for ($i = 0; $i < self::PROJECTS_NUM; $i++) {
            $client = factory(\App\Models\Client::class)->create([
                'team_id' => $this->user->team_id
            ]);
            $project = factory(Project::class)->create([
                'team_id' => $this->user->team_id,
                'client_id' => $client->id
            ]);

            if ($i % 2 === 0) {
                $this->seedProjectTasks($project);
            }

            $this->projects[] = $project->id;
        }
    }

    /**
     * @param Project $project
     */
    private function seedProjectTasks(Project $project): void
    {
        foreach (Task::getTypes() as $type => $name) {
            $project->tasks()->create([
                'name' => $name,
                'type' => $type,
                'billable' => $this->faker->boolean,
                'billing_rate' => $this->faker->numberBetween(0, 100),
                'currency_id' => Currency::all()->random()->id,
                'is_deleted' => false
            ]);
        }
    }

    private function seedMembers(): void
    {
        for ($i = 0; $i < self::MEMBERS_NUM; $i++) {
            $user = factory(User::class)->create([
                'team_id' => $this->user->team_id,
                'owns_team' => null
            ]);
            $member = factory(Member::class)->create([
                'team_id' => $this->user->team_id,
                'user_id' => $user->id
            ]);
            $roles = Role::findFromTeam($this->user->team)->where('name', '!=', Role::ADMIN)->pluck('id');
            $member->attachRoles($this->faker->randomElements($roles, $this->faker->numberBetween(1, 2)));
            $member->projects()->attach($this->faker->randomElements($this->projects, $this->faker->numberBetween(1, 4)));
            $member->save();

            $this->members[] = $member;
        }
    }

    private function seedLogs(): void
    {
        array_push($this->members, $this->member);
        /** @var Member $member */
        foreach ($this->members as $member) {
            /** @var Project $project */
            for ($i = 0; $i < self::LOGS_NUM; $i++) {
                $project = $member->projects()->get()->random();
                $task = null;
                if ($project->tasks->count() > 0) {
                    $task = $project->tasks()->get()->random();
                }
                $data = [
                    'project_id' => $project->id,
                    'task_id' => $task ? $task->id : null,
                    'description' => $this->faker->sentence,
                    'start' => null,
                    'duration' => $this->faker->numberBetween(1, 1440),
                    'created_at' => $this->faker->dateTimeBetween('-3 months')
                ];
                $member->logs()->create($data);
            }
        }
    }

    private function seedTodoTasks(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $project = $this->member->projects()->get()->random();
            $task = null;
            if ($project->tasks->count() > 0) {
                $task = $project->tasks()->get()->random();
            }
            factory(TodoTask::class, 2)->create([
                'project_id' => $project->id,
                'task_id' => $task ? $task->id : null,
                'member_id' => $this->member->id
            ]);
        }
    }

    private function seedRolesAndPermissions(): void
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

        $role = Role::findFromTeam($this->user->team)->where('name', Role::ADMIN)->first();
        $this->user->member()->attachRole($role);
        $this->user->member()->save();
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
