<?php namespace App\Controllers;
 
use CodeIgniter\RESTful\ResourceController;
 
class Resource extends ResourceController
{
    public function __construct()
    {
        $this->request = \Config\Services::request();

        helper(['rsCode']);
    }

    public function index(){
        $this->validate_session([ 'f' => ["label"=>"File", "rules"=>"required",], ]);

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