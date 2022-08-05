<?php namespace App\Controllers;
 
use CodeIgniter\RESTful\ResourceController;
use App\Models\BrandCategoryModel;
use App\Models\BrandModel;
 
class Brand_Category extends ResourceController
{
    public function __construct()
    {
        $this->request = \Config\Services::request();
        
        $this->BrandCategoryModel  = new BrandCategoryModel();
        $this->BrandModel  = new BrandModel();

        helper(['rsCode']);
        
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
                'name' => ["label"=>"Category Name", "rules"=>"required",],
            ],
            "amend" => [
                'key' => ["label"=>"Key", "rules"=>"required",],
                'u' => ["label"=>"User", "rules"=>"required",],
                'token' => ["label"=>"Access Token", "rules"=>"required",],
                'name' => ["label"=>"Category Name", "rules"=>"required",],
            ],
            "destroy" => [
                'key' => ["label"=>"Key", "rules"=>"required",],
                'u' => ["label"=>"User", "rules"=>"required",],
                'token' => ["label"=>"Access Token", "rules"=>"required",],
            ],
            "destroys" => [
                'keys' => ["label"=>"Key", "rules"=>"required",],
                'u' => ["label"=>"User", "rules"=>"required",],
                'token' => ["label"=>"Access Token", "rules"=>"required",],
            ],
        ];
    }

    public function datatable()
    {
        $this->validate_session($this->validation->datatable);

        $filters = [
            "limit" => $this->request->getGet("limit"),
            "order" => $this->request->getGet("order"),
            "search" => $this->request->getGet("search"),
            "searchable" => [ "name", ],
        ];
        $fields = [ "idcategorybrand", "name", ];
        $data = $this->BrandCategoryModel->datatable($fields, $filters);

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
        $this->validate_session($this->validation->data);

        $fields = ["idcategorybrand", "name"];
        $filters = [ "filter" => ["idcategorybrand" => $this->request->getGet("key")] ];
        $data = $this->BrandCategoryModel->get_category($fields,$filters);

        $code = $data!=null && count($data)==1 ? '00000' : "00104";
        $data = $data!=null && count($data)==1 ? $data[0] : false;

        return $this->respond( tempResponse($code,$data) );
    }

    public function dropdown()
    {
        $this->validate_session($this->validation->dropdown);

        $data = $this->BrandCategoryModel->get_category(array("idcategorybrand as value","name as label"));

        return $this->respond( tempResponse('00000',$data) );
    }

    public function store()
    {
        $this->validate_session($this->validation->store);

        $data = $this->BrandCategoryModel->store([
            "name" => $this->request->getPost("name"),
        ]);
        
        $code = $data==false ? "00002" : "00000";

        return $this->respond( tempResponse($code, $data) );
    }

    public function amend()
    {
        $this->validate_session($this->validation->amend);

        $data = $this->BrandCategoryModel->amend($this->request->getPost("key"), ["name" => $this->request->getPost("name")]);
        
        $code = $data==false ? "00003" : "00000";

        return $this->respond( tempResponse($code, $data) );
    }

    public function destroy()
    {
        $this->validate_session($this->validation->destroy);

        $id = $this->request->getPost("key");

        $brand = $this->BrandModel->get_brand(["idbrand"],[ "filter" => ["idcategorybrand"=>$id] ]);
        if($brand!=null && count($brand)>=0) return $this->respond( tempResponse("00008") );

        $destroy = $this->BrandCategoryModel->destroy($id);
        $code = $destroy==false ? "00007" : "00000";

        return $this->respond( tempResponse($code, $destroy) );
    }

    public function destroys()
    {
        $this->validate_session($this->validation->destroys);

        $keys = $this->request->getPost("keys");
        if(!is_array($keys)) return $this->respond( tempResponse("00104") );
        
        $success = false;
        $fail = false;
        foreach($keys as $k => $v){
            $brand = $this->BrandModel->get_brand(["idbrand"],[ "filter" => ["idcategorybrand"=>$v] ]);
            if($brand!=null && count($brand)>=0) { $fail=true; continue; }

            $destroy = $this->BrandCategoryModel->destroy($v);
            if($destroy==false) $fail = true;
            else $success = true;
        }

        $code = $success==true ? "00000" : "00008";
        $msg = "";
        if($success==true && $fail==true) $msg = "some data was deleted successfully, but some cannot be deleted. Maybe the data is in use or error occurs";
        elseif($success==true && $fail==false) $msg = "OK";
        elseif($success==false && $fail==true) $msg = "Failed to delete data. Maybe the data is in use or error occurs";
        elseif($success==false && $fail==false) $msg = "Failed to delete data. Maybe the data is in use or error occurs";

        return $this->respond( tempResponse($code, true, $msg) );
    }
 
}