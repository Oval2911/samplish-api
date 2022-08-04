<?php namespace App\Controllers;
 
use CodeIgniter\RESTful\ResourceController;
use App\Models\ToneMannerModel;
use App\Models\User_model;
use CodeIgniter\HTTP\ResponseInterface;
 
class Tone_Manner extends ResourceController
{
    private $_exec_time_start;

    public function __construct()
    {
        $this->request = \Config\Services::request();
        
        $this->ToneMannerModel  = new ToneMannerModel();
        $this->User_model  = new User_model();

        helper(['custom', 'rsCode']);
        
        $this->_exec_time_start = microtime(true);
        setlocale(LC_MONETARY, 'en_GB');
        date_default_timezone_set('Asia/Jakarta');
    }

    public function dropdown()
    {
        if(!isExist("get","u")) return $this->respond( tempResponse("00104") );
        if(!isExist("get","token")) return $this->respond( tempResponse("00104") );

        $user = $this->User_model->get_user(array('iduser'), array("filter" => array('related_id' => $this->request->getGet("u"))));
        if ($user==null) return $this->respond( tempResponse("00102") );
        $user = $user[0];

        $access = $this->User_model->update_user_access_login_session(
            $this->request->getGet("u"),
            $this->request->getGet("token")
        );
        if ($access == 0) return $this->respond( tempResponse("00102") );

        $data = $this->ToneMannerModel->get_tone_manner(array("idtonemanner as value","name as label"));

        return $this->respond( tempResponse('00000',$data) );
    }
 
}