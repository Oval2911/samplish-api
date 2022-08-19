<?php namespace App\Controllers;
 
use CodeIgniter\RESTful\ResourceController;
use App\Models\ProfileModel;
use App\Models\User_model;
use CodeIgniter\Files\File;
 
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

    private function _profile($user,$profile){
        if($profile!=null && count($profile)==1){
            $profile = (object)$profile;
            return (object)[
                "name" => $user->fullname,
                "nomor" => $profile->nomor,
                "ktp" => $profile->ktp,
                "selfie_ktp" => $profile->selfie_ktp,
                "gender" => $profile->gender,
            ];
        }
        
        return (object)[
            "name" => $user->fullname,
            "nomor" => null,
            "ktp" => null,
            "selfie_ktp" => null,
            "gender" => null,
        ];
    }

    public function data()
    {
        $user = $this->validate_session($this->validation->data);

        $key = $this->_data( $this->request->getPost("key"), $user );

        $fields = [ "fullname", ];
        $filters = [ "filter" => ["iduser" => $key] ];
        $user = $this->Profile->get_user($fields,$filters);

        if( !($user!=null && count($user)==1) ) $this->respond( tempResponse("00104") );
        $user = (object)$user[0];
        
        $fields = [ "nomor", "ktp", "selfie_ktp", "gender", ];
        $profile = $this->Profile->get_profile($fields,$filters);

        return $this->respond(
            tempResponse("00000",(object)[
                "profile" => $this->_profile($user,$profile),
            ])
        );
    }
    
    public function amend_profile()
    {
        $user = $this->validate_session($this->validation->amend_profile);

        $key = $this->_data( $this->request->getPost("key"), $user );

        $ktp = $this->request->getFile('ktp');
        if($ktp && !$ktp->hasMoved()) {
            $store = $ktp->store();
            $file = new File(WRITEPATH .'uploads/'. $store);
            $ktp = $store;
        }
        $selfie_ktp = $this->request->getFile('selfie_ktp');
        if($selfie_ktp && !$selfie_ktp->hasMoved()) {
            $store = $selfie_ktp->store();
            $file = new File(WRITEPATH .'uploads/'. $store);
            $selfie_ktp = $store;
        }

        $user = [ "fullname" => $this->request->getPost("name"), ];
        $profile = [
            "iduser" => $key,
            "nomor" => $this->request->getPost("nomor"),
            "gender" => $this->request->getPost("gender"),
        ];
        if($ktp!=null) $profile["ktp"] = $ktp;
        if($selfie_ktp!=null) $profile["selfie_ktp"] = $selfie_ktp;

        $_profile = $this->Profile->get_profile([ "iduser", ], [ "filter" => ["iduser" => $key] ]);
        if( !($_profile!=null && count($_profile)==1) ){
            $data = $this->Profile->store_profile($key,$user,$profile);
        }else{
            $data = $this->Profile->amend_profile($key,$user,$profile);
        }
        
        $code = $data==false ? "00003" : "00000";

        return $this->respond( tempResponse($code, $data) );
    }
 
}