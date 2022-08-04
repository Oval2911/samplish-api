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
                'key' => ["label"=>"Key", "rules"=>"required",],
            ],
            "datatable" => [
                'u' => ["label"=>"User", "rules"=>"required",],
                'token' => ["label"=>"Access Token", "rules"=>"required",],
                'limit' => ["label"=>"Pagination", "rules"=>"required",],
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
        ];
    }

    public function data()
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

        $data = $this->BrandModel->get_brand(["*"],["filter" => ["idbrand" => $this->request->getGet("key")]]);

        return $this->respond( tempResponse('00000',$data) );
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
            "searchable" => [
                "brand.name",
                "brand_category.name",
                "brand.variant",
            ],
        ];
        $fields = [
            "brand.idbrand",
            "brand.name",
            "brand_category.name as category",
            "brand.variant",
        ];

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

        $amend = [];
        if(array_key_exists("idcategorybrand",$_POST)) $amend["idcategorybrand"] = $this->request->getPost("category");
        if(array_key_exists("name",$_POST)) $amend["name"] = $this->request->getPost("name");
        if(array_key_exists("variant",$_POST)) $amend["variant"] = $this->request->getPost("variant");
        if(array_key_exists("mission",$_POST)) $amend["mission"] = $this->request->getPost("mission");
        if(array_key_exists("targetmarket",$_POST)) $amend["targetmarket"] = $this->request->getPost("targetmarket");
        if(array_key_exists("desc",$_POST)) $amend["desc"] = $this->request->getPost("desc");
        if($img!=null) $amend["image"] = $img;

        $data = $this->BrandModel->amend(
            $this->request->getPost("key"),
            [
                "idcategorybrand" => $this->request->getPost("category"),
                "name" => $this->request->getPost("name"),
                "variant" => $this->request->getPost("variant"),
                "mission" => $this->request->getPost("mission"),
                "targetmarket" => $this->request->getPost("targetmarket"),
                "desc" => $this->request->getPost("desc"),
                "image" => $img,
            ]
        );
        
        $code = $data==false ? "00003" : "00000";

        return $this->respond( tempResponse($code, $data) );
    }
 
}