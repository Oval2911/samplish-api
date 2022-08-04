<?php namespace App\Controllers;
 
use CodeIgniter\RESTful\ResourceController;
use App\Models\BrandCategoryModel;
 
class Brand_Category extends ResourceController
{
    public function __construct()
    {
        $this->request = \Config\Services::request();
        
        $this->BrandCategoryModel  = new BrandCategoryModel();

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
        $data = $this->BrandCategoryModel->get_brand($fields,$filters);

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

        $data = $this->BrandCategoryModel->destroy($this->request->getPost("key"));
        
        $code = $data==false ? "00007" : "00000";

        return $this->respond( tempResponse($code, $data) );
    }
 
}