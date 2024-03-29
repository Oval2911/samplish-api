<?php namespace App\Controllers;
 
use CodeIgniter\RESTful\ResourceController;
 
class Resource extends ResourceController
{
    public function __construct()
    {
        $this->request = \Config\Services::request();
    }

    public function index(){
        $file = $this->request->getGet("f");
        $filepath = WRITEPATH . 'uploads/' .$file;

        $mime = mime_content_type($filepath);
        header('Content-Length: ' . filesize($filepath));
        header("Content-Type: $mime");
        header('Content-Disposition: inline; filename="' . $filepath . '";');
        readfile($filepath);
        exit();
    }
 
}