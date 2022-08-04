<?php namespace App\Controllers;
 
use CodeIgniter\RESTful\ResourceController;
use App\Models\BrandModel;
use App\Models\User_model;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Files\File;
 
class Brand extends ResourceController
{
    private $_exec_time_start;
    private $validation;

    public function __construct()
    {
        $this->request = \Config\Services::request();
        
        $this->BrandModel  = new BrandModel();
        $this->User_model  = new User_model();

        helper(['custom', 'rsCode', 'form']);
        
        $this->_exec_time_start = microtime(true);
        setlocale(LC_MONETARY, 'en_GB');
        date_default_timezone_set('Asia/Jakarta');

        $this->validation = (object)[
            "datatable" => [
                'u' => ["label"=>"User", "rules"=>"required",],
                'token' => ["label"=>"Access Token", "rules"=>"required",],
                'limit' => ["label"=>"Pagination", "rules"=>"required",],
            ],
            "data" => [
                'u' => ["label"=>"User", "rules"=>"required",],
                'token' => ["label"=>"Access Token", "rules"=>"required",],
                'key' => ["label"=>"Key", "rules"=>"required",],
            ],
            "dropdown" => [
                'u' => ["label"=>"User", "rules"=>"required",],
                'token' => ["label"=>"Access Token", "rules"=>"required",],
            ],
            "store" => [
                'u' => ["label"=>"User", "rules"=>"required",],
                'token' => ["label"=>"Access Token", "rules"=>"required",],
                'category' => ["label"=>"Brand Category", "rules"=>"required",],
                'name' => ["label"=>"Brand Name", "rules"=>"required",],
                'image' => ['label'=>'Image', 'rules'=>'uploaded[image]|is_image[image]',],
            ],
            "amend" => [
                'key' => ["label"=>"Key", "rules"=>"required",],
                'u' => ["label"=>"User", "rules"=>"required",],
                'token' => ["label"=>"Access Token", "rules"=>"required",],
            ],
            "destroy" => [
                'key' => ["label"=>"Key", "rules"=>"required",],
                'u' => ["label"=>"User", "rules"=>"required",],
                'token' => ["label"=>"Access Token", "rules"=>"required",],
            ],
        ];
    }

    public function datatable()
    {
        if (!$this->validate($this->validation->datatable)) return $this->respond( tempResponse("00104") );

        $user = $this->User_model->get_user(['iduser'], ["filter" => ['related_id' => $this->request->getGet("u")]]);
        if ($user==null) return $this->respond( tempResponse("00102") );
        $user = $user[0];

        $access = $this->User_model->update_user_access_login_session(
            $this->request->getGet("u"),
            $this->request->getGet("token")
        );
        if ($access == 0) return $this->respond( tempResponse("00102") );

        $filters = [
            "limit" => $this->request->getGet("limit"),
            "order" => $this->request->getGet("order"),
            "search" => $this->request->getGet("search"),
            "user" => $user["iduser"],
            "searchable" => [ "brand.name", "brand_category.name", "brand.variant", ],
        ];
        $fields = [ "brand.idbrand", "brand.name", "brand_category.name as category", "brand.variant", ];
        $data = $this->BrandModel->datatable($fields, $filters);

        return $this->respond(
            tempResponse(
                '00000',
                [
                    'page' => $filters["limit"]["page"],
                    'per_page' => $filters["limit"]["n_item"],
                    'total' => $data->total,
                    'total_pages' => $data->total_pages,
                    'records' => $data->data,
                ]
            )
        );
    }

    public function data()
    {
        if (!$this->validate($this->validation->data)) return $this->respond( tempResponse("00104") );

        $user = $this->User_model->get_user(['iduser'], ["filter" => ['related_id' => $this->request->getGet("u")]]);
        if ($user==null) return $this->respond( tempResponse("00102") );
        $user = $user[0];

        $access = $this->User_model->update_user_access_login_session(
            $this->request->getGet("u"),
            $this->request->getGet("token")
        );
        if ($access == 0) return $this->respond( tempResponse("00102") );

        $fields = ["idbrand", "idcategorybrand", "name", "image", "variant", "mission", "idtonemanner", "targetmarket", "desc"];
        $filters = [ "filter" => ["idbrand" => $this->request->getGet("key")] ];
        $data = $this->BrandModel->get_brand($fields,$filters);

        $code = $data!=null && count($data)==1 ? '00000' : "00104";
        $data = $data!=null && count($data)==1 ? $data[0] : false;

        return $this->respond( tempResponse($code,$data) );
    }

    public function dropdown()
    {
        if (!$this->validate($this->validation->dropdown)) return $this->respond( tempResponse("00104") );

        $user = $this->User_model->get_user(['iduser'], ["filter" => ['related_id' => $this->request->getGet("u")]]);
        if ($user==null) return $this->respond( tempResponse("00102") );
        $user = $user[0];

        $access = $this->User_model->update_user_access_login_session(
            $this->request->getGet("u"),
            $this->request->getGet("token")
        );
        if ($access == 0) return $this->respond( tempResponse("00102") );

        $owner = $this->request->getGet("owner");
        if($owner=="true"){
            $owner = $user["iduser"];
        }else if($owner!=null){
            $owner = $this->User_model->get_user(['iduser'], ["filter" => ['related_id' => $owner]]);
            if ($owner!=null && count($owner)==1) $owner = $owner[0];
            else $owner = null;
        }

        $fields = ["idbrand as value","name as label"];
        $filters = $owner==null ? [] : [ "filter" => ["iduser" => $owner] ];
        $data = $this->BrandModel->get_brand($fields,$filters);

        return $this->respond( tempResponse('00000',$data) );
    }

    public function store()
    {
        if (!$this->validate($this->validation->store)) return $this->respond( tempResponse("00104",false,$this->validator->getErrors()) );

        $user = $this->User_model->get_user(['iduser'], ["filter" => ['related_id' => $this->request->getPost("u")]]);
        if ($user==null) return $this->respond( tempResponse("00102") );
        $user = $user[0];

        $access = $this->User_model->update_user_access_login_session(
            $this->request->getPost("u"),
            $this->request->getPost("token")
        );
        if ($access == 0) return $this->respond( tempResponse("00102") );

        $img = $this->request->getFile('image');
        if($img && !$img->hasMoved()) {
            $store = $img->store();
            $file = new File(WRITEPATH .'uploads/'. $store);
            $img = $store;
        }

        $data = $this->BrandModel->store([
            "idcategorybrand" => $this->request->getPost("category"),
            "idtonemanner" => $this->request->getPost("tonemanner"),
            "iduser" => $user["iduser"],
            "name" => $this->request->getPost("name"),
            "variant" => $this->request->getPost("variant"),
            "mission" => $this->request->getPost("mission"),
            "targetmarket" => $this->request->getPost("targetmarket"),
            "desc" => $this->request->getPost("desc"),
            "image" => $img,
        ]);
        
        $code = $data==false ? "00002" : "00000";

        return $this->respond( tempResponse($code, $data) );
    }

    public function amend()
    {
        if (!$this->validate($this->validation->amend)) return $this->respond( tempResponse("00104",false,$this->validator->getErrors()) );

        $user = $this->User_model->get_user(['iduser'], ["filter" => ['related_id' => $this->request->getPost("u")]]);
        if ($user==null) return $this->respond( tempResponse("00102") );
        $user = $user[0];

        $access = $this->User_model->update_user_access_login_session(
            $this->request->getPost("u"),
            $this->request->getPost("token")
        );
        if ($access == 0) return $this->respond( tempResponse("00102") );

        $img = $this->request->getFile('image');
        if($img && !$img->hasMoved()) {
            $store = $img->store();
            $file = new File(WRITEPATH .'uploads/'. $store);
            $img = $store;
        }

        $amend = [
            "idcategorybrand" => $this->request->getPost("category"),
            "idtonemanner" => $this->request->getPost("tonemanner"),
            "name" => $this->request->getPost("name"),
            "variant" => $this->request->getPost("variant"),
            "mission" => $this->request->getPost("mission"),
            "targetmarket" => $this->request->getPost("targetmarket"),
            "desc" => $this->request->getPost("desc"),
        ];
        if($img!=null) $amend["image"] = $img;

        $data = $this->BrandModel->amend($this->request->getPost("key"), [$amend]);
        
        $code = $data==false ? "00003" : "00000";

        return $this->respond( tempResponse($code, $data) );
    }

    public function destroy()
    {
        if (!$this->validate($this->validation->destroy)) return $this->respond( tempResponse("00104",false,$this->validator->getErrors()) );

        $user = $this->User_model->get_user(['iduser'], ["filter" => ['related_id' => $this->request->getPost("u")]]);
        if ($user==null) return $this->respond( tempResponse("00102") );
        $user = $user[0];

        $access = $this->User_model->update_user_access_login_session(
            $this->request->getPost("u"),
            $this->request->getPost("token")
        );
        if ($access == 0) return $this->respond( tempResponse("00102") );

        $data = $this->BrandModel->destroy($this->request->getPost("key"));
        
        $code = $data==false ? "00007" : "00000";

        return $this->respond( tempResponse($code, $data) );
    }
 
}