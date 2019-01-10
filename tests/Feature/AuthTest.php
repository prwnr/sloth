<?php

namespace Tests\Feature;

use App\Models\Team\Creator;
use App\Models\Team\Member;
use App\Models\User;
use App\Notifications\PasswordResetNotification;
use Carbon\Carbon;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

class AuthTest extends FeatureTestCase
{

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('passport:install');
    }

    public function testCreatesAccount(): void
    {
        $response = $this->json(Request::METHOD_POST, '/api/auth/signup', $this->makeRegistrationData());

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJson([
            'message' => 'Account successfully created'
        ]);
    }

    public function testValidationWhenCreatingAccount(): void
    {
        $data = $this->makeRegistrationData();
        unset($data['password'], $data['password_confirmation']);

        $response = $this->json(Request::METHOD_POST, '/api/auth/signup', $data);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJson([
            'message' => 'The given data was invalid.',
            'errors' => [
                'password' => [
                    'The password field is required.'
                ]
            ]
        ]);
    }

    public function testFailsToCreateAccount(): void
    {
        $data = $this->makeRegistrationData();
        $mock = \Mockery::mock(Creator::class);
        $this->instance(Creator::class, $mock);
        $mock->shouldReceive('make')->with($data)->andThrow(\Exception::class);

        $response = $this->json(Request::METHOD_POST, '/api/auth/signup', $data);

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->assertJson([
            'message' => 'Cannot create account. Please contact website administrator',
        ]);
    }

    public function testUserLogsInAndReceivesToken(): void
    {
        $data = $this->makeLoginData();
        factory(User::class)->create(['email' => $data['email'], 'password' => $data['password']]);
        $response = $this->json(Request::METHOD_POST, '/api/auth/login', $data);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_at'
        ]);
    }

    public function testUserLogsInAndReceivesTokenWithLongerExpiration(): void
    {
        $data = $this->makeLoginData();
        $data['remember_me'] = true;
        factory(User::class)->create(['email' => $data['email'], 'password' => $data['password']]);
        $response = $this->json(Request::METHOD_POST, '/api/auth/login', $data);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_at'
        ]);
        $response->assertJsonFragment([
            'expires_at' => Carbon::now()->addWeek()->toDateTimeString()
        ]);
    }

    public function testUserFailsToLogInWithIncorrectCredentials(): void
    {
        $data = $this->makeLoginData();
        factory(User::class)->create(['email' => $data['email'], 'password' => 'foobar']);
        $response = $this->json(Request::METHOD_POST, '/api/auth/login', $data);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJson([
            'message' => 'Unauthorized'
        ]);
    }

    public function testUserLogsOut(): void
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $token = $user->createToken('Personal Access Token')->token;
        $token->expires_at = Carbon::now()->addDay();
        $token->save();
        $user->withAccessToken($token);
        $this->actingAs($user, 'api');

        $response = $this->json(Request::METHOD_GET, '/api/auth/logout');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(['message' => 'Successfully logged out']);
        $this->assertTrue($user->token()->revoked);
    }

    public function testEmailWithResetPasswordLinkIsSent(): void
    {
        $user = factory(User::class)->create();
        Notification::fake();

        $response = $this->json(Request::METHOD_POST, '/api/auth/password/forgot', ['email' => $user->email]);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        Notification::assertSentTo(
            $user,
            PasswordResetNotification::class,
            function ($notification, $channels) use ($user){
                $notificationData = $notification->toArray();
                $mailData = $notification->toMail($user)->toArray();
                $this->assertEquals('Reset Password Notification', $mailData['subject']);
                $this->assertContains('You are receiving this email because we received a password reset request for your account.', $mailData['introLines']);
                $this->assertEquals('Reset Password', $mailData['actionText']);
                $this->assertEquals(config('app.url') . '/password/reset?token=' . $notificationData['token'], $mailData['actionUrl']);
                $this->assertContains('If you did not request a password reset, no further action is required.', $mailData['outroLines']);
                return true;
            }
        );
    }

    public function testResetPasswordLinkIsNotSentIfEmailIsInvalid(): void
    {
        $user = factory(User::class)->create();
        Notification::fake();

        $response = $this->json(Request::METHOD_POST, '/api/auth/password/forgot', ['email' => 'foo@bar']);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJson([
            'message' => 'The given data was invalid.',
            'errors' => [
                'email' => [
                    'The email must be a valid email address.'
                ]
            ]
        ]);
        Notification::assertNotSentTo(
            $user,
            PasswordResetNotification::class
        );
    }

    public function testResetPasswordLinkIsNotSentIfUserNotExists(): void
    {
        $user = factory(User::class)->create();
        Notification::fake();

        $response = $this->json(Request::METHOD_POST, '/api/auth/password/forgot', ['email' => 'foo@bar.com']);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJson(['message' => 'We can\'t find a user with that e-mail address.']);
        Notification::assertNotSentTo(
            $user,
            PasswordResetNotification::class
        );
    }

    public function testUserResetPasswordWithResetLink(): void
    {
        $user = factory(User::class)->create();
        $token = Password::broker()->createToken($user);

        $this->expectsEvents(PasswordReset::class);
        $response = $this->json(Request::METHOD_POST, '/api/auth/password/reset?token=' . $token, [
            'email' => $user->email,
            'password' => 'secret',
            'password_confirmation' => 'secret'
        ]);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function testUserPasswordIsNotChangedWithResetLinkWhenTokenIsInvalid(): void
    {
        $user = factory(User::class)->create();
        Password::broker()->createToken($user);

        $this->doesntExpectEvents(PasswordReset::class);
        $response = $this->json(Request::METHOD_POST, '/api/auth/password/reset?token=foobar', [
            'email' => $user->email,
            'password' => 'secret',
            'password_confirmation' => 'secret'
        ]);

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->json(['message' => 'Password reset token is invalid. Re-check your email for valid token or contact administrator']);
    }

    public function testUserPasswordIsNotChangedWithResetLinkWhenEmailIsInvalid(): void
    {
        $user = factory(User::class)->create();
        $token = Password::broker()->createToken($user);

        $this->doesntExpectEvents(PasswordReset::class);
        $response = $this->json(Request::METHOD_POST, '/api/auth/password/reset?token=' . $token, [
            'email' => 'foo@bar.com',
            'password' => 'secret',
            'password_confirmation' => 'secret'
        ]);

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->json(['message' => 'We can\'t change your user password. Please contact administrator']);
    }

    public function testUserChangesPasswordWithAuthOnFirstLogin(): void
    {
        $user = factory(User::class)->create(['first_login' => true]);
        $this->actingAs($user, 'api');

        $response = $this->json(Request::METHOD_PUT, '/api/auth/password/change', ['password' => 'secret']);
        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function testUserCannotChangePasswordWithAuthOnConsequentLogin(): void
    {
        $user = factory(User::class)->create(['first_login' => false]);
        $this->actingAs($user, 'api');

        $response = $this->json(Request::METHOD_PUT, '/api/auth/password/change', ['password' => 'secret']);
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson(['message' => 'This is not your first login. You cannot change password this way.']);
    }

    public function testUserGetsErrorMessageWhenErrorOccursDuringPasswordChangeWithAuth(): void
    {
        $user = $this->mockAndReplaceInstance(User::class);
        $user->shouldReceive('getAuthIdentifier')->withAnyArgs()->andReturn(1);
        $user->shouldReceive('getAttribute')->with('first_login')->andReturn(true);
        $user->shouldReceive('update')->withAnyArgs()->andThrow(\Exception::class);
        $this->actingAs($user, 'api');

        $response = $this->json(Request::METHOD_PUT, '/api/auth/password/change', ['password' => 'secret']);
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->assertJson(['message' => 'Something went wrong when changing password.']);
    }

    public function testAuthUserReturnsLoggedInUser(): void
    {
        $user = factory(Member::class)->create()->user;
        $this->actingAs($user, 'api');

        $response = $this->json(Request::METHOD_GET, '/api/auth/user');
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => [
                'id', 'firstname', 'email', 'created_at', 'updated_at', 'lastname', 'team_id', 'owns_team', 'first_login',
                'fullname'
            ],
            'projects',
            'roles',
            'team'
        ]);
        $response->assertJsonFragment([
            'id' => $user->id,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'email' => $user->email,
        ]);
    }

    private function makeRegistrationData(): array
    {
        return [
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'team_name' => $this->faker->company,
            'email' => $this->faker->email,
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ];
    }

    private function makeLoginData(): array
    {
        return [
            'email' => $this->faker->email,
            'password' => 'secret',
            'remember_me' => false
        ];
    }
}
