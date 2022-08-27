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
            "amend_address" => [
                'u' => ["label"=>"User", "rules"=>"required",],
                'token' => ["label"=>"Access Token", "rules"=>"required",],
                'address' => ["label"=>"Address", "rules"=>"required",],
            ],
            "amend_social" => [
                'u' => ["label"=>"User", "rules"=>"required",],
                'token' => ["label"=>"Access Token", "rules"=>"required",],
            ],
            "amend_family" => [
                'u' => ["label"=>"User", "rules"=>"required",],
                'token' => ["label"=>"Access Token", "rules"=>"required",],
            ],
            "amend_interest_community" => [
                'u' => ["label"=>"User", "rules"=>"required",],
                'token' => ["label"=>"Access Token", "rules"=>"required",],
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

        $key = $this->_data( $this->request->getGet("key"), $user );

        $fields = [ "fullname", ];
        $filters = [ "filter" => ["iduser" => $key] ];
        $user = $this->Profile->get_user($fields,$filters);

        if( !($user!=null && count($user)==1) ) $this->respond( tempResponse("00104") );
        $user = (object)$user[0];
        
        $fields = [ "*", ];
        $profile = $this->Profile->get_profile($fields,$filters);
        $profile = $profile!=null && count($profile)==1 ? (object)$profile[0] : false;
        
        $fields = [ "address", "rt", "rw", "city", "kec", "kel", "pos", ];
        $address = $this->Profile->get_address($fields,$filters);
        $address = $address!=null && count($address)==1 ? (object)$address[0] : false;
        
        $fields = [ "idcommunity", ];
        $community = $this->Profile->get_communities($fields,$filters);
        $community = $community!=null ? $community : [];
        
        $fields = [ "idinterest", ];
        $interest = $this->Profile->get_interests($fields,$filters);
        $interest = $interest!=null ? $interest : [];

        return $this->respond(
            tempResponse("00000",(object)[
                "profile" => (object)[
                    "company" => $user->fullname,
                    "name" => $profile ? $profile->name : null,
                    "phone" => $profile ? $profile->phone : null,
                    "nomor" => $profile ? $profile->nomor : null,
                    "photo" => $profile ? $profile->photo : null,
                    "ktp" => $profile ? $profile->ktp : null,
                    "selfie_ktp" => $profile ? $profile->selfie_ktp : null,
                    "gender" => $profile ? $profile->gender : null,
                    "birthdate" => $profile ? $profile->birthdate : null,
                ],
                "address" => (object)[
                    "address" => $address ? $address->address : null,
                    "rt" => $address ? $address->rt : null,
                    "rw" => $address ? $address->rw : null,
                    "city" => $address ? $address->city : null,
                    "kec" => $address ? $address->kec : null,
                    "kel" => $address ? $address->kel : null,
                    "pos" => $address ? $address->pos : null,
                ],
                "social" => (object)[
                    "ig" => $profile ? $profile->ig : null,
                    "fb" => $profile ? $profile->fb : null,
                    "tw" => $profile ? $profile->tw : null,
                    "in" => $profile ? $profile->in : null,
                    "tk" => $profile ? $profile->tk : null,
                    "wa" => $profile ? $profile->wa : null,
                ],
                "family" => (object)[
                    "status" => $profile ? $profile->status : null,
                    "child" => $profile ? $profile->child : null,
                    "is60th" => $profile ? $profile->is60th : null,
                    "amount60th" => $profile ? $profile->amount60th : null,
                    "age60th" => $profile ? $profile->age60th : null,
                    "isAnimal" => $profile ? $profile->isAnimal : null,
                    "animalType" => $profile ? $profile->animalType : null,
                    "income" => $profile ? $profile->income : null,
                ],
                "community" => $community,
                "interest" => $interest,
            ])
        );
    }
    
    public function amend_profile()
    {
        $user = $this->validate_session($this->validation->amend_profile);
        $role = $user["related_key"];
        $r = $this->request;

        $key = $this->_data( $r->getPost("key"), $user );

        $photo = $r->getFile('photo');
        if($photo && !$photo->hasMoved()) {
            $store = $photo->store();
            $file = new File(WRITEPATH .'uploads/'. $store);
            $photo = $store;
        }
        $ktp = $r->getFile('ktp');
        if($ktp && !$ktp->hasMoved()) {
            $store = $ktp->store();
            $file = new File(WRITEPATH .'uploads/'. $store);
            $ktp = $store;
        }
        $selfie_ktp = $r->getFile('selfie_ktp');
        if($selfie_ktp && !$selfie_ktp->hasMoved()) {
            $store = $selfie_ktp->store();
            $file = new File(WRITEPATH .'uploads/'. $store);
            $selfie_ktp = $store;
        }

        $user = [ "fullname" => $role=="sampler" ? $r->getPost("name") : $r->getPost("company"), ];
        $profile = [
            "iduser" => $key,
            "name" => $r->getPost("name"),
            "nomor" => $r->getPost("nomor"),
            "gender" => $r->getPost("gender"),
            "birthdate" => $r->getPost("birthdate"),
            "phone" => $r->getPost("phone"),
        ];
        if($ktp!=null) $profile["ktp"] = $ktp;
        if($selfie_ktp!=null) $profile["selfie_ktp"] = $selfie_ktp;
        if($photo!=null) $profile["photo"] = $photo;

        $_profile = $this->Profile->get_profile([ "iduser", ], [ "filter" => ["iduser" => $key] ]);
        if( !($_profile!=null && count($_profile)==1) ){
            $data = $this->Profile->store_profile($key,$user,$profile);
        }else{
            $data = $this->Profile->amend_profile($key,$user,$profile);
        }
        
        $code = $data==false ? "00003" : "00000";

        return $this->respond( tempResponse($code, true) );
    }
    
    public function amend_address()
    {
        $user = $this->validate_session($this->validation->amend_address);

        $key = $this->_data( $this->request->getPost("key"), $user );

        $this->Profile->destroys_address($key);
        $data = $this->Profile->store_address([
            "iduser" => $key,
            "address" => $this->request->getPost("address"),
            "city" => $this->request->getPost("city"),
            "rt" => $this->request->getPost("rt"),
            "rw" => $this->request->getPost("rw"),
            "kec" => $this->request->getPost("kec"),
            "kel" => $this->request->getPost("kel"),
            "pos" => $this->request->getPost("pos"),
        ]);
        
        $code = $data==false ? "00002" : "00000";

        return $this->respond( tempResponse($code, true) );
    }
    
    public function amend_social()
    {
        $user = $this->validate_session($this->validation->amend_social);

        $key = $this->_data( $this->request->getPost("key"), $user );
        $profile = [
            "ig" => $this->request->getPost("ig"),
            "tw" => $this->request->getPost("tw"),
            "fb" => $this->request->getPost("fb"),
            "in" => $this->request->getPost("in"),
            "tk" => $this->request->getPost("tk"),
            "wa" => $this->request->getPost("wa"),
        ];

        $_profile = $this->Profile->get_profile([ "iduser", ], [ "filter" => ["iduser" => $key] ]);
        if( !($_profile!=null && count($_profile)==1) ){
            $profile["iduser"] = $key;
            $data = $this->Profile->store($profile);
        }else{
            $data = $this->Profile->amend($key, $profile);
        }
        
        $code = $data==false ? "00003" : "00000";

        return $this->respond( tempResponse($code, true) );
    }
    
    public function amend_family()
    {
        $user = $this->validate_session($this->validation->amend_family);

        $key = $this->_data( $this->request->getPost("key"), $user );
        $profile = [
            "status" => $this->request->getPost("status"),
            "child" => $this->request->getPost("child"),
            "is60th" => $this->request->getPost("is60th"),
            "amount60th" => $this->request->getPost("amount60th"),
            "age60th" => $this->request->getPost("age60th"),
            "isAnimal" => $this->request->getPost("isAnimal"),
            "animalType" => $this->request->getPost("animalType"),
            "income" => $this->request->getPost("income"),
        ];

        $_profile = $this->Profile->get_profile([ "iduser", ], [ "filter" => ["iduser" => $key] ]);
        if( !($_profile!=null && count($_profile)==1) ){
            $profile["iduser"] = $key;
            $data = $this->Profile->store($profile);
        }else{
            $data = $this->Profile->amend($key, $profile);
        }
        
        $code = $data==false ? "00003" : "00000";

        return $this->respond( tempResponse($code, true) );
    }

    public function amend_interest_community()
    {
        $user = $this->validate_session($this->validation->amend_interest_community);
        $key = $this->_data( $this->request->getPost("key"), $user );
        
        $this->Profile->destroys_community($key);
        $communities = $this->request->getPost("community");
        if(is_array($communities)){
            foreach($communities as $k => $v){
                if($v=="") continue;
                $this->Profile->store_community([
                    "iduser" => $key,
                    "idcommunity" => $v,
                ]);
            }
        }
        
        $this->Profile->destroys_interest($key);
        $interest = $this->request->getPost("interest");
        if(is_array($interest)){
            foreach($interest as $k => $v){
                if($v=="") continue;
                $this->Profile->store_interest([
                    "iduser" => $key,
                    "idinterest" => $v,
                ]);
            }
        }

        return $this->respond( tempResponse("00000", true) );
    }
 
}