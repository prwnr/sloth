<?php

namespace App\Http\Middleware;

use App\Models\Project;
use App\Models\Role;
use App\Models\Team\Member;
use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Class TeamOwner
 * @package App\Http\Middleware
 */
class TeamOwner
{

    /**
     * Resources that are using teams
     * @var array
     */
    private $resourceClasses = [
        Role::class,
        Member::class,
        Project::class
    ];

    /**
     * Handle an incoming request.
     * Prevents viewing/editing/deleting resources that do not belong to your team
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param string $resource name of resource (model) that is attempted to be used
     * @return mixed
     */
    public function handle($request, Closure $next, string $resource)
    {
        $user = Auth::user();
        $model = $request->$resource;
        foreach ($this->resourceClasses as $class) {
            if ($model instanceof $class && $model->team_id !== $user->team_id) {
                return abort(404);
            }
        }

        return $next($request);
    }
}
