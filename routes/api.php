<?php

Route::group(['namespace' => 'Api', 'as' => 'api.'], function () {
    Route::group(['prefix' => 'auth'], function () {
        Route::post('login', 'AuthController@login');
        Route::post('signup', 'AuthController@signup');

        Route::group(['middleware' => 'auth:api'], function() {
            Route::get('logout', 'AuthController@logout');
            Route::get('user', 'AuthController@user');
        });
    });

    Route::group(['middleware' => ['auth:api']], function () {
        Route::prefix('billings')->group(function () {
            Route::get('/types', 'BillingController@types');
            Route::get('/data', 'BillingController@showBillingData');
        });

        Route::apiResource('currencies', 'CurrencyController')->only('index');
        Route::apiResource('perms', 'PermissionController')->only('index');

        //Roles routes
        Route::apiResource('roles', 'RoleController')->except('index')->middleware(['permission:manage_roles', 'team:role']);
        Route::apiResource('roles', 'RoleController')->only('index');

        //Members routes
        Route::get('members/{member}/projects', 'MemberController@showProjects')->middleware('team:member');
        Route::apiResource('members', 'MemberController')->except('index')->middleware(['permission:manage_team', 'team:member']);
        Route::apiResource('members', 'MemberController')->only('index');

        //Projects routes
        Route::get('projects/budget_periods', 'ProjectController@budgetPeriods');
        Route::get('projects/task-types', 'ProjectController@taskTypes')->middleware('team:project');
        Route::apiResource('projects', 'ProjectController')->except('index')->middleware(['permission:manage_projects', 'team:project']);
        Route::apiResource('projects', 'ProjectController')->only('index');

        //Clients routes
        Route::apiResource('clients', 'ClientController')->except('index')->middleware(['permission:manage_clients', 'team:client']);
        Route::apiResource('clients', 'ClientController')->only('index');

        //Tracker routes
        Route::put('time/{time}/duration', 'TrackerController@updateTime')->middleware('permission:track_time');
        Route::apiResource('time', 'TrackerController')->middleware('permission:track_time');

        //Todos routes
        Route::patch('todos/{todo}/status', 'TodoTaskController@changeStatus');
        Route::apiResource('todos', 'TodoTaskController')->except('show');

        //Report routes
        Route::prefix('reports')->group(function () {
            Route::post('', 'ReportController@index')->middleware('permission:view_reports');
            Route::post('/{member}', 'ReportController@show');
            Route::get('/{member}/hours/{period}', 'ReportController@userHours');
            Route::get('/{member}/projects/{period}', 'ReportController@userProjects');
            Route::get('/sales/{period}', 'ReportController@sales')->middleware('permission:view_reports');
        });

        //User routes
        Route::prefix('users')->group(function () {
            Route::get('/active', 'UserController@showActive')->name('users.active');
            Route::get('/{user}/logs', 'UserController@timeLogs');
            Route::put('/{user}/password', 'UserController@updatePassword')->middleware('permission:manage_team');
            Route::put('/{user}/switch', 'UserController@switchTeam');
        });
        Route::apiResource('users', 'UserController');

        Route::apiResource('teams', 'TeamController')->only(['update']);
    });
});

