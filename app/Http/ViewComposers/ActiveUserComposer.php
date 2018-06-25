<?php


namespace App\Http\ViewComposers;

use App\Models\Permission;
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
        $user->loadMissing('roles', 'member');
        $permissions = [];
        foreach (Permission::all() as $perm) {
            if ($user->can($perm->name)) {
                $permissions[] = $perm->name;
            }
        }

        $activeUser = [
            'user' => $user->toArray(),
            'permissions' => $permissions,
            'team' => $user->team->toArray()
        ];
        $view->with('activeUser', $activeUser);
    }
}