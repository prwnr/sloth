<?php

use App\Models\Billing;
use App\Models\Project;
use App\Models\Currency;
use App\Models\Permission;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(['middleware' => ['auth:api'], 'namespace' => 'Api', 'as' => 'api.'], function () {
    Route::apiResource('currencies', 'CurrencyController')->only('index');
    Route::apiResource('perms', 'PermissionController')->only('index');
    Route::get('/billings/types', 'BillingController@types');
    Route::get('/billings/data', 'BillingController@showBillingData');

    //Roles routes
    Route::apiResource('roles', 'RoleController')->except('index')->middleware('permission:manage_roles', 'team:role');
    Route::apiResource('roles', 'RoleController')->only('index')->middleware('team:role');   

    //Members routes
    Route::get('members/{member}/projects', 'MemberController@showProjects')->middleware('team:member');
    Route::apiResource('members', 'MemberController')->except('index')->middleware('permission:manage_team', 'team:member');
    Route::apiResource('members', 'MemberController')->only('index')->middleware('team:member');

    //Projects routes
    Route::get('projects/budget_periods', 'ProjectController@budgetPeriods');
    Route::apiResource('projects', 'ProjectController')->except('index')->middleware('permission:manage_projects', 'team:project');
    Route::apiResource('projects', 'ProjectController')->only('index')->middleware('team:project');

    //Clients routes
    Route::apiResource('clients', 'ClientController')->except('index')->middleware('permission:manage_clients', 'team:client');
    Route::apiResource('clients', 'ClientController')->only('index')->middleware('team:client');    

    //Tracker routes
    Route::put('time/{time}/duration', 'TrackerController@updateTime')->middleware('permission:track_time');
    Route::apiResource('time', 'TrackerController')->middleware('permission:track_time');

    //Report routes
    Route::post('reports', 'ReportController@index')->middleware('permission:view_reports');
    Route::post('reports/{user}', 'ReportController@show');
    Route::get('reports/{user}/hours/{period}', 'ReportController@userHours');
    Route::get('reports/{user}/projects/{period}', 'ReportController@userProjects');

    Route::apiResource('tasks', 'TaskController')->only('index')->middleware('team:project');

    Route::get('/users/active', 'UserController@showActive')->name('users.active');
    Route::post('/users/logout', 'UserController@logout');
    Route::get('/users/{user}/logs', 'UserController@timeLogs');
    Route::put('/users/{user}/password', 'UserController@updatePassword')->middleware('permission:manage_team');
    Route::apiResource('users', 'UserController');
    Route::apiResource('teams', 'TeamController')->only(['show', 'update']);
});

