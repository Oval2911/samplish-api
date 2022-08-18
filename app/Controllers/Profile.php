<?php namespace App\Controllers;
 
use CodeIgniter\RESTful\ResourceController;
use App\Models\ProfileModel;
use App\Models\User_model;
 
class Profile extends ResourceController
{
    public function __construct()
    {
        $this->request = \Config\Services::request();
        
        $this->Profile  = new ProfileModel();
        $this->User = new User_model();

        helper(['rsCode']);
        
        date_default_timezone_set('Asia/Jakarta');

        $this->validation = (object)[
            "data" => [
                'u' => ["label"=>"User", "rules"=>"required",],
                'token' => ["label"=>"Access Token", "rules"=>"required",],
            ],
            "amend_profile" => [
                'u' => ["label"=>"User", "rules"=>"required",],
                'token' => ["label"=>"Access Token", "rules"=>"required",],
                'name' => ["label"=>"Full Name", "rules"=>"required",],
                'nomor' => ["label"=>"Nomor KTP/SIM", "rules"=>"required",],
                'ktp' => ["label"=>"ID Image", "rules"=>"required",],
                'selfie_ktp' => ["label"=>"ID Selfie Image", "rules"=>"required",],
                'gender' => ["label"=>"Gender", "rules"=>"required",],
            ],
        ];
    }

    private function _data($key,$user){
        if($key!=null){
            $key = $this->User->get_user(['iduser'], ["filter" => ['related_id' => $key]]);
            if ($key==null) die(json_encode(tempResponse("00102")));
            $key = $key[0]["iduser"];
        }else{
            $key = $user["iduser"];
        }
        return $key;
    }

    public function data()
    {
        $user = $this->validate_session($this->validation->data);

        $key = $this->_data( $this->request->getPost("key"), $user );

        $fields = [ "fullname", ];
        $filters = [ "filter" => ["iduser" => $key] ];
        $user = $this->Profile->get_user($fields,$filters);

        if( !($user!=null && count($user)==1) ) $this->respond( tempResponse("00104") );
        
        $fields = [ "nomor", "ktp", "selfie_ktp", "gender", ];
        $profile = $this->Profile->get_profile($fields,$filters);

        return $this->respond(
            tempResponse("00000",(object)[
                "profile" => (object)[
                    "name" => $user->fulllname,
                    "nomor" => $profile->nomor,
                    "ktp" => $profile->ktp,
                    "selfie_ktp" => $profile->selfie_ktp,
                    "gender" => $profile->gender,
                ],
            ])
        );
    }
    
    public function amend_profile()
    {
        $user = $this->validate_session($this->validation->amend_profile);

        $key = $this->_data( $this->request->getPost("key"), $user );

        $data = $this->Profile->amend_profile($key,
            [ "fullname" => $this->request->getPost("name"), ],
            [
                "nomor" => $this->request->getPost("nomor"),
                "ktp" => $this->request->getPost("ktp"),
                "selfie_ktp" => $this->request->getPost("selfie_ktp"),
                "gender" => $this->request->getPost("gender"),
            ]
        );
        
        $code = $data==false ? "00003" : "00000";

        return $this->respond( tempResponse($code, $data) );
    }
 
}