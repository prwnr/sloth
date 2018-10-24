<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\{
    Facades\Auth, Facades\DB, Facades\Hash, Facades\Session
};

class ChangePasswordController extends Controller
{
    /**
     * Display form to change password
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('auth.passwords.change');
    }

    /**
     * Change password for new member
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function changePassword(Request $request)
    {
        $this->validator($request->all())->validate();
        $user = Auth::user();

        try {
            DB::beginTransaction();
            $user->update([
                'first_login' => false,
                'password' => Hash::make($request->input('password'))
            ]);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();

            Session::flash('alert-danger', 'Failed to change password. Please try again');
            return redirect()->back();
        }

        Session::flash('alert-success', 'Password changed successfully');
        return redirect('/');
    }

    /**
     * Validate password
     *
     * @param array $data
     * @return Validator
     */
    private function validator(array $data): Validator
    {
        return \Illuminate\Support\Facades\Validator::make($data, [
            'password' => 'required|string|min:6|confirmed',
        ]);
    }
}
