<?php

namespace App\Controllers;
use App\Models\TestModel;

class Home extends BaseController
{
    private $_test;
    public function index()
    {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }
}
