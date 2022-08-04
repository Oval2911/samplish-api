<?php namespace App\Controllers;
 
use CodeIgniter\RESTful\ResourceController;
 
class Resource extends ResourceController
{

    public function res($folder,$file)
    {
        $filepath = WRITEPATH . 'uploads/' . $folder . '/' . $file;

        $mime = mime_content_type($filepath);
        header('Content-Length: ' . filesize($filepath));
        header("Content-Type: $mime");
        header('Content-Disposition: inline; filename="' . $filepath . '";');
        readfile($filepath);
        exit();
    }
 
}