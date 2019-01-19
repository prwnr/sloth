<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\SignupRequest;
use App\Http\Resources\User as UserResource;
use App\Models\Team\Creator;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Http\{JsonResponse, Request, Response};
use Illuminate\Support\{Facades\Auth, Facades\Hash, Facades\Password, Str};

/**
 * Class AuthController
 * @package App\Http\Controllers\Api
 */
class AuthController extends Controller
{

    /**
     * @var Creator
     */
    private $teamCreator;

    /**
     * AuthController constructor.
     * @param Creator $creator
     */
    public function __construct(Creator $creator)
    {
        $this->teamCreator = $creator;
    }

    /**
     * @param SignupRequest $request
     * @return JsonResponse
     */
    public function signup(SignupRequest $request): JsonResponse
    {
        try {
            $this->teamCreator->make($request->all());
        } catch (\Exception $ex) {
            return response()->json(['message' => 'Cannot create account. Please contact website administrator'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json(['message' => 'Account successfully created'], Response::HTTP_CREATED);
    }

    /**
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = request(['email', 'password']);
        if(!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        /** @var User $user */
        $user = $request->user();

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;

        $token->expires_at = Carbon::now()->addDay();
        if ($request->remember_me) {
            $token->expires_at = Carbon::now()->addWeek();
        }
        $token->save();

        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString()
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->token()->revoke();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * @param ForgotPasswordRequest $request
     * @return mixed
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $response = $this->broker()->sendResetLink(
            $request->only('email')
        );

        if ($response === PasswordBroker::INVALID_USER) {
            return response()->json(['message' => 'We can\'t find a user with that e-mail address.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([], Response::HTTP_NO_CONTENT);
    }

    /**
     * @param ResetPasswordRequest $request
     * @return JsonResponse
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $response = $this->broker()->reset(
            $request->all(), function ($user, $password) {
                $user->password = Hash::make($password);
                $user->setRememberToken(Str::random(60));
                $user->save();

                event(new PasswordReset($user));
        });

        if ($response !== Password::PASSWORD_RESET) {
            $message = 'We can\'t change your user password. Please contact administrator';
            if ($response === Password::INVALID_TOKEN) {
                $message = 'Password reset token is invalid. Re-check your email for valid token or contact administrator';
            }

            return response()->json(['message' => $message], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([], Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function changePassword(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        if (!$user->first_login) {
            return response()->json(['message' => 'This is not your first login. You cannot change password this way.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $user->update([
                'first_login' => false,
                'password' => Hash::make($request->input('password'))
            ]);
        } catch (\Exception $ex) {
            return response()->json(['message' => 'Something went wrong when changing password.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([], Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Request $request
     * @return UserResource
     */
    public function user(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    /**
     * Get the broker to be used during password reset.
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    private function broker(): PasswordBroker
    {
        return Password::broker();
    }
}