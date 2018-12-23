<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Team;
use App\Models\Team\Member;
use App\Models\TimeLog;
use App\Models\User;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserTest extends FeatureTestCase
{

    public function testUsersAreListedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        factory(User::class, 5)->create(['team_id' => $this->user->team_id]);

        $response = $this->json(Request::METHOD_GET, '/api/users');
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => [
                [
                    'id', 'firstname', 'email', 'created_at', 'updated_at', 'lastname', 'team_id', 'owns_team', 'first_login', 'fullname',
                ]
            ]
        ]);
        $response->assertJsonCount(6, 'data');
    }

    public function testUsersAreNotListedForGuest(): void
    {
        $response = $this->json(Request::METHOD_GET, '/api/users');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJson([
            'message' => 'Unauthenticated.'
        ]);
    }

    public function testUserTimeLogsAreListedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        factory(TimeLog::class, 5)->create(['member_id' => $this->user->member()->id]);

        $response = $this->json(Request::METHOD_GET, "/api/users/{$this->user->id}/logs");
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => [
                [
                    'id', 'project_id', 'task_id', 'member_id', 'description', 'start', 'duration', 'created_at', 'updated_at', 'project', 'task'
                ]
            ]
        ]);
        $response->assertJsonCount(5, 'data');
    }

    public function testErrorMessageIsReturnedWhenExceptionIsThrownOnTimeLogsListing(): void
    {
        $this->actingAs($this->user, 'api');
        factory(TimeLog::class, 5)->create(['member_id' => $this->user->member()->id]);

        $mock = $this->mockAndReplaceInstance(UserRepository::class);
        $mock->shouldReceive('find')->with($this->user->id)->andThrow(\Exception::class);

        $response = $this->json(Request::METHOD_GET, "/api/users/{$this->user->id}/logs");

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertExactJson([
            'message' => 'Something when wrong when listing user time logs. Please ry again'
        ]);
    }

    public function testUserTimeLogsAreNotListedForGuest(): void
    {
        factory(TimeLog::class, 5)->create(['member_id' => $this->user->member()->id]);

        $response = $this->json(Request::METHOD_GET, "/api/users/{$this->user->id}/logs");
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJson([
            'message' => 'Unauthenticated.'
        ]);
    }

    public function testUserActiveTimeLogsAreListedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');

        for ($i = 0; $i < 5; $i++) {
            $data = [];
            $data['member_id'] = $this->user->member()->id;
            if ($i % 2 === 0) {
                $data['start'] = Carbon::now();
            }
            factory(TimeLog::class)->create($data);
        }

        $response = $this->json(Request::METHOD_GET, "/api/users/{$this->user->id}/logs", ['active' => true]);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => [
                [
                    'id', 'project_id', 'task_id', 'member_id', 'description', 'start', 'duration', 'created_at', 'updated_at', 'project', 'task'
                ]
            ]
        ]);
        $response->assertJsonCount(3, 'data');
    }

    public function testUserTimeLogsFromGivenDateAreListedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        factory(TimeLog::class, 5)->create(['member_id' => $this->user->member()->id]);

        $date = $this->faker->date();
        factory(TimeLog::class)->create([
            'member_id' => $this->user->member()->id,
            'created_at' => $date
        ]);

        $response = $this->json(Request::METHOD_GET, "/api/users/{$this->user->id}/logs", ['date' => $date]);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => [
                [
                    'id', 'project_id', 'task_id', 'member_id', 'description', 'start', 'duration', 'created_at', 'updated_at', 'project', 'task'
                ]
            ]
        ]);
        $response->assertJsonCount(1, 'data');
    }

    public function testUsersAreShowedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $user = factory(User::class)->create(['team_id' => $this->user->team_id]);
        factory(Member::class)->create(['team_id' => $user->team_id, 'user_id' => $user->id]);

        $response = $this->json(Request::METHOD_GET, "/api/users/{$user->id}");
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => [
                'id', 'firstname', 'email', 'created_at', 'updated_at', 'lastname', 'team_id', 'owns_team', 'first_login', 'fullname',
                'members' => [
                    [
                        'id', 'user_id', 'team_id', 'created_at', 'updated_at', 'billing_id', 'editable', 'deletable', 'user', 'team'
                    ]
                ],
                'team' => [
                    'id', 'name', 'created_at', 'updated_at'
                ],
            ]
        ]);
        $response->assertJson([
            'data' => [
                'id' => $user->id,
                'firstname' => $user->firstname,
                'email' => $user->email,
                'lastname' => $user->lastname,
                'team_id' => $user->team_id,
                'owns_team' => $user->owns_team,
                'first_login' => $user->first_login,
                'fullname' => $user->fullname,
            ]
        ]);
    }

    public function testActiveUserIsShowedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');

        $response = $this->json(Request::METHOD_GET, '/api/users/active');
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => [
                'id', 'firstname', 'email', 'created_at', 'updated_at', 'lastname', 'team_id', 'owns_team', 'first_login', 'fullname',
                'team' => [
                    'id', 'name', 'created_at', 'updated_at'
                ],
            ]
        ]);
        $response->assertJson([
            'data' => [
                'id' => $this->user->id,
                'firstname' => $this->user->firstname,
                'email' => $this->user->email,
                'lastname' => $this->user->lastname,
                'team_id' => $this->user->team_id,
                'owns_team' => $this->user->owns_team,
                'first_login' => $this->user->first_login,
                'fullname' => $this->user->fullname,
            ]
        ]);
    }

    public function testUsersCanUpdateHisDataCorrectly(): void
    {
        $this->actingAs($this->user, 'api');

        $password = $this->faker->password;
        $data = $this->makeUserData($password);
        $response = $this->json(Request::METHOD_PUT, "/api/users/{$this->user->id}", $data);
        $response->assertStatus(Response::HTTP_ACCEPTED);
        $response->assertJsonStructure([
            'data' => [
                'id', 'firstname', 'email', 'created_at', 'updated_at', 'lastname', 'team_id', 'owns_team', 'first_login', 'fullname',
                'team' => [
                    'id', 'name', 'created_at', 'updated_at'
                ],
            ]
        ]);
        $response->assertJson([
            'data' => [
                'id' => $this->user->id,
                'firstname' => $data['firstname'],
                'email' => $data['email'],
                'lastname' => $data['lastname']
            ]
        ]);
    }

    public function testErrorMessageIsReturnedWhenExceptionIsThrownOnUserDataUpdate(): void
    {
        $this->actingAs($this->user, 'api');

        $password = $this->faker->password;
        $data = $this->makeUserData($password);
        $repository = $this->mockAndReplaceInstance(UserRepository::class);
        $repository->shouldReceive('update')->with($this->user->id, $data)->andThrow(\Exception::class);

        $response = $this->json(Request::METHOD_PUT, "/api/users/{$this->user->id}", $data);
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->assertExactJson([
            'message' => 'Something when wrong when updating user data. Please ry again'
        ]);
    }

    public function testUsersPasswordsAreUpdatedCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $user = factory(User::class)->create(['team_id' => $this->user->team_id]);
        factory(Member::class)->create(['team_id' => $user->team_id, 'user_id' => $user->id]);

        $password = $this->faker->password;
        $data = [
            'password' => $password,
            'password_confirmation' => $password
        ];
        $response = $this->json(Request::METHOD_PUT, "/api/users/{$user->id}/password", $data);
        $response->assertStatus(Response::HTTP_ACCEPTED);
        $response->assertJsonStructure([
            'data' => [
                'id', 'firstname', 'email', 'created_at', 'updated_at', 'lastname', 'team_id', 'owns_team', 'first_login', 'fullname',
                'team' => [
                    'id', 'name', 'created_at', 'updated_at'
                ],
            ]
        ]);
        $response->assertJson([
            'data' => [
                'id' => $user->id,
            ]
        ]);
    }

    public function testErrorMessageIsReturnedWhenExceptionIsThrownOnUserPasswordUpdate(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);

        $user = factory(User::class)->create(['team_id' => $this->user->team_id]);
        $password = $this->faker->password;
        $data = [
            'password' => $password,
            'password_confirmation' => $password
        ];
        $repository = $this->mockAndReplaceInstance(UserRepository::class);
        $repository->shouldReceive('update')->with($this->user->id, $data)->andThrow(\Exception::class);

        $response = $this->json(Request::METHOD_PUT, "/api/users/{$user->id}/password", $data);
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->assertExactJson([
            'message' => 'Something when wrong when updating user password. Please ry again'
        ]);
    }

    public function testUsersPasswordsAreUpdatedByUserWithoutPermissions(): void
    {
        $this->actingAs($this->user, 'api');
        $role = factory(Role::class)->create();
        $this->actAsRole($role->name);

        $user = factory(User::class)->create(['team_id' => $this->user->team_id]);
        factory(Member::class)->create(['team_id' => $user->team_id, 'user_id' => $user->id]);

        $password = $this->faker->password;
        $data = [
            'password' => $password,
            'password_confirmation' => $password
        ];
        $response = $this->json(Request::METHOD_PUT, "/api/users/{$user->id}/password", $data);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testUserCanSwitchTeamsCorrectly(): void
    {
        $this->actingAs($this->user, 'api');
        $member = factory(Member::class)->create(['user_id' => $this->user->id]);

        $response = $this->json(Request::METHOD_PUT, "/api/users/{$this->user->id}/switch", ['team' => $member->team_id]);
        $response->assertStatus(Response::HTTP_ACCEPTED);
        $response->assertJson([
            'data' => [
                'team_id' => $member->team_id
            ]
        ]);
    }

    public function testErrorMessageIsReturnedWhenExceptionIsThrownOnUserTeamChange(): void
    {
        $this->actingAs($this->user, 'api');
        $this->actAsRole(Role::ADMIN);
        $member = factory(Member::class)->create(['user_id' => $this->user->id]);

        $mock = $this->mockAndReplaceInstance(User::class);
        $mock->shouldReceive('resolveRouteBinding')->with($this->user->id)->andReturn($mock);
        $mock->shouldReceive('getAttribute')->with('id')->andReturn($this->user);
        $mock->shouldReceive('team->associate')->withNoArgs()->with($member->team)->andThrow(\Exception::class);

        $response = $this->json(Request::METHOD_PUT, "/api/users/{$this->user->id}/switch", ['team' => $member->team_id]);
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->assertExactJson([
            'message' => 'Something when wrong when switching active team. Please ry again'
        ]);
    }

    /**
     * @param string $password
     * @return array
     */
    private function makeUserData(string $password): array
    {
        return [
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'email' => $this->faker->email,
            'password' => $password,
            'password_confirmation' => $password
        ];
    }
}
