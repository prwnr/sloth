<?php

namespace App\Http\Controllers\Api;

use App\Repositories\PermissionRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\ResourceCollection;

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
     * @return ResourceCollection
     */
    public function index(): ResourceCollection
    {
        return new ResourceCollection($this->permissionRepository->all());
    }
}
