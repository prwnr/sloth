<?php

namespace App\Http\Controllers\Api;

use App\Models\Permission;
use App\Http\Resources\Permission as PermissionResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * Class PermissionController
 * @package App\Http\Controllers\Api
 */
class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return PermissionResource
     */
    public function index()
    {
        return new PermissionResource(Permission::all());
    }
}
