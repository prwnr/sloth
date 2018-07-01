<?php


namespace App\Http\ViewComposers;

use App\Models\Permission;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Class ActiveUserComposer
 * @package App\Http\ViewComposers
 */
class ActiveUserComposer
{

    /**
     * Bind data to the view.
     *
     * @param  View $view
     * @return void
     */
    public function compose(View $view): void
    {
        $user = Auth::user();
        $user->loadMissing('member');
        $roles = $user->roles()->select(['id', 'name', 'display_name'])->get();
        $permissions = [];
        foreach (Permission::all() as $perm) {
            if ($user->can($perm->name)) {
                $permissions[] = $perm->name;
            }
        }

        $activeUser = [
            'data' => $user,
            'projects' => $user->member ? $user->member->projects : Project::findFromTeam($user->team)->get(),
            'permissions' => $permissions,
            'roles' => $roles,
            'team' => $user->team
        ];
        $view->with('activeUser', $activeUser);
    }
}