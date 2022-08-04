<?php namespace App\Controllers;
 
use CodeIgniter\RESTful\ResourceController;
 
class Brand extends ResourceController
{

    public function upload($filename)
    {
        $filepath = WRITEPATH . 'uploads/' . $filename;

        $mime = mime_content_type($filepath);
        header('Content-Length: ' . filesize($filepath));
        header("Content-Type: $mime");
        header('Content-Disposition: inline; filename="' . $filepath . '";');
        readfile($filepath);
        exit();
    }
 
}