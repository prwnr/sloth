<?php

namespace App\Http\Controllers;

/**
 * Class DefaultController
 * @package App\Http\Controllers
 */
class DefaultController extends Controller
{

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('app');
    }
}
