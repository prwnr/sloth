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
        $view->with('activeUser', Auth::user()->getAllInfoData());
    }
}