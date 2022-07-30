<?php

namespace App\Controllers;
use App\Models\TestModel;

class Home extends BaseController
{
    private $_test;
    public function index()
    {
        
        $this->_test = new TestModel();
        print_r(jsoN_encode($this->_test->stock(array('*'), array())));
        return view('welcome_message');
    }
}
