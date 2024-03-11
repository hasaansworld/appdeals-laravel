<?php

namespace App\Http\Controllers;

class TestController extends Controller
{
    public function home()
    {
        return response('Status: Running');
    }
}