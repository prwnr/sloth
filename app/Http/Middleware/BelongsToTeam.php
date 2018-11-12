<?php

namespace App\Http\Middleware;

use App\Models\Client;
use App\Models\Project;
use App\Models\Role;
use App\Models\Team\Member;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Class BelongsToTeam
 * @package App\Http\Middleware
 */
class BelongsToTeam
{

    /**
     * Resources that are using teams
     * @var array
     */
    private $resourceClasses = [
        'role' => Role::class,
        'member' => Member::class,
        'project' => Project::class,
        'client' => Client::class
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
        $class = $this->resourceClasses[$resource] ?? null;
        if (!$class || !$request->$resource) {
            return $next($request);
        }

        $user = Auth::user();
        $model = $request->$resource;
        if (!$model instanceof Model) {
            $model = $class::find($model);
        }
        if ($model->team_id !== $user->team_id) {
            return abort(404);
        }

        return $next($request);
    }
}
