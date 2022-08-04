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
            "store" => [
                'u' => ["label"=>"User", "rules"=>"required",],
                'token' => ["label"=>"Access Token", "rules"=>"required",],
                'category' => ["label"=>"Brand Category", "rules"=>"required",],
                'name' => ["label"=>"Brand Name", "rules"=>"required",],
                'image' => ['label'=>'Image', 'rules'=>'uploaded[image]|is_image[image]',],
            ],
        ];
    }

    public function datatable()
    {
        if (!$this->validate($this->validation->datatable)) return $this->respond( tempResponse("00104") );

        $user = $this->User_model->get_user(array('iduser'), array("filter" => array('related_id' => $this->request->getGet("u"))));
        if ($user==null) return $this->respond( tempResponse("00102") );
        $user = $user[0];

        $access = $this->User_model->update_user_access_login_session(
            $this->request->getGet("u"),
            $this->request->getGet("token")
        );
        if ($access == 0) return $this->respond( tempResponse("00102") );

        $filters = array(
            "limit" => $this->request->getGet("limit"),
            "order" => $this->request->getGet("order"),
            "search" => $this->request->getGet("search"),
            "user" => $user["iduser"],
            "searchable" => array(
                "brand.name",
                "brand_category.name",
                "brand.variant",
            ),
        );
        $fields = array(
            "brand.idbrand",
            "brand.name",
            "brand_category.name as category",
            "brand.variant",
        );

        $data = $this->BrandModel->datatable($fields, $filters);

        return $this->respond(
            tempResponse(
                '00000',
                array(
                    'page' => $filters["limit"]["page"],
                    'per_page' => $filters["limit"]["n_item"],
                    'total' => $data->total,
                    'total_pages' => $data->total_pages,
                    'records' => $data->data,
                )
            )
        );
    }

    public function store()
    {
        if (!$this->validate($this->validation->store)) return $this->respond( tempResponse("00104",false,$this->validator->getErrors()) );

        $user = $this->User_model->get_user(array('iduser'), array("filter" => array('related_id' => $this->request->getPost("u"))));
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

        $data = $this->BrandModel->store(array(
            "idcategorybrand" => $this->request->getPost("category"),
            "iduser" => $user["iduser"],
            "name" => $this->request->getPost("name"),
            "variant" => $this->request->getPost("variant"),
            "mission" => $this->request->getPost("mission"),
            "targetmarket" => $this->request->getPost("targetmarket"),
            "desc" => $this->request->getPost("desc"),
            "image" => $img,
        ));
        
        $code = $data==false ? "00002" : "00000";

        return $this->respond( tempResponse($code, $data) );
    }
 
}