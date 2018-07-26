<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Role as RoleResource;
use App\Models\{Role, User};
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\{RedirectResponse, Request, Response};
use Illuminate\Support\{Facades\Auth, Facades\DB, Facades\Session};

/**
 * Class RoleController
 * @package App\Http\Controllers
 */
class RoleController extends Controller
{

    /**
     * Show list of all roles
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $roles = Role::findFromTeam(Auth::user()->team)->get();
        return new RoleResource($roles);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $data['name'] = str_slug($data['display_name'] ?? '', '_');
        $this->validator($data)->validate();

        /** @var User $user */
        $user = Auth::user();
        if (Role::where(['name' => $data['name'], 'team_id' => $user->team_id])->first()) {
            return response()->json(['message' => __('Role with this name already exists')], Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();
            $role = $user->team->roles()->create([
                'name' => $data['name'],
                'display_name' => $data['display_name'],
                'description' => $data['description']
            ]);

            $role->users()->sync($data['users'] ?? []);
            $role->perms()->sync($data['permissions'] ?? []);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            report($ex);
            return response()->json(['message' => __('Something went wrong when creating new role. Please try again')], Response::HTTP_BAD_REQUEST);
        }

        return (new RoleResource($role))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param Role $role
     * @return \Illuminate\Http\Response
     */
    public function show(Role $role)
    {
        $role->loadMissing(['users', 'perms', 'users.member']);
        return new RoleResource($role);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param Role $role
     * @return RedirectResponse
     */
    public function update(Request $request, Role $role)
    {
        if (!$role->isEditable()) {
            Session::flash('alert-danger', 'You can\'t edit this role');
            return redirect()->back();
        }

        $data = $request->all();
        $data['name'] = str_slug($data['display_name'] ?? '', '_');
        $this->validator($data)->validate();

        $exists = Role::where([
            ['name', '=', $data['name']],
            ['team_id', '=', $role->team_id],
            ['id', '<>', $role->id]
        ])->first();
        if ($exists) {
            return response()->json(['message' => __('Role with this name already exists')], Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();
            $role->update([
                'name' => $data['name'],
                'display_name' => $data['display_name'],
                'description' => $data['description']
            ]);

            $role->users()->sync($data['users'] ?? []);
            $role->perms()->sync($data['permissions'] ?? []);
            $role->touch();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            report($ex);
            return response()->json(['message' => __('Failed to update role. Please try again')], Response::HTTP_BAD_REQUEST);
        }

        return (new RoleResource($role))->response()->setStatusCode(Response::HTTP_ACCEPTED);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Role $role
     * @return RedirectResponse
     */
    public function destroy(Role $role)
    {
        if (!$role->isDeletable()) {
            return response()->json(['message' => __('You can\'t delete this role')], Response::HTTP_FORBIDDEN);
        }

        DB::beginTransaction();
        try {
            if ($role->delete()) {
                DB::commit();
                return response()->json(null, Response::HTTP_NO_CONTENT);   
            }
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => $ex->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => __('Something went wrong and role could not be deleted. It may not exists, please try again')
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param array $data
     * @return mixed
     */
    private function validator(array $data): Validator
    {
        return \Illuminate\Support\Facades\Validator::make($data, [
            'name' => 'required',
            'display_name' => 'required|string|max:255',
            'description' => 'required|string|max:500',
        ]);
    }
}
