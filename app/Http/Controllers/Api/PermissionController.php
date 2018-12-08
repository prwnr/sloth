<?php

namespace App\Http\Controllers\Api;

use App\Models\Permission;
use App\Http\Resources\Permission as PermissionResource;
use App\Repositories\PermissionRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * Class PermissionController
 * @package App\Http\Controllers\Api
 */
class PermissionController extends Controller
{

    /**
     * @var PermissionRepository
     */
    private $permissionRepository;

    /**
     * PermissionController constructor.
     * @param PermissionRepository $permissionRepository
     */
    public function __construct(PermissionRepository $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return PermissionResource
     */
    public function index(): PermissionResource
    {
        return new PermissionResource($this->permissionRepository->all());
    }
}
