<?php namespace App\Controllers;
 
use CodeIgniter\RESTful\ResourceController;
use App\Models\BrandModel;
use App\Models\User_model;
use App\Models\CampaignModel;
use CodeIgniter\Files\File;
 
class Brand extends ResourceController
{
    private $validation;

    public function __construct()
    {
        $this->request = \Config\Services::request();
        
        $this->BrandModel  = new BrandModel();
        $this->User_model  = new User_model();
        $this->CampaignModel  = new CampaignModel();

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
                'category' => ["label"=>"Brand Category", "rules"=>"required",],
                'name' => ["label"=>"Brand Name", "rules"=>"required",],
                'image' => ['label'=>'Image', 'rules'=>'uploaded[image]|is_image[image]',],
            ],
            "amend" => [
                'key' => ["label"=>"Key", "rules"=>"required",],
                'u' => ["label"=>"User", "rules"=>"required",],
                'token' => ["label"=>"Access Token", "rules"=>"required",],
                'name' => ["label"=>"Brand Name", "rules"=>"required",],
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
        $user = $this->validate_session($this->validation->datatable);

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

    public function datatable_all()
    {
        $this->validate_session($this->validation->datatable);

        $filters = [
            "limit" => $this->request->getGet("limit"),
            "order" => $this->request->getGet("order"),
            "search" => $this->request->getGet("search"),
            "searchable" => [ "brand.name", "brand_category.name", "brand.variant", ],
        ];
        $fields = [ "brand.idbrand", "brand.image", "brand.name", "brand_category.name as category", "brand.variant", ];
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
        $this->validate_session($this->validation->data);

        $fields = ["idbrand", "idcategorybrand", "name", "image", "variant", "mission", "idtonemanner", "targetmarket", "desc"];
        $filters = [ "filter" => ["idbrand" => $this->request->getGet("key")] ];
        $data = $this->BrandModel->get_brand($fields,$filters);

        $code = $data!=null && count($data)==1 ? '00000' : "00104";
        $data = $data!=null && count($data)==1 ? $data[0] : false;

        return $this->respond( tempResponse($code,$data) );
    }

    public function dropdown()
    {
        $user = $this->validate_session($this->validation->dropdown);

        $owner = $this->request->getGet("owner");
        if($owner=="true"){
            $owner = $user["iduser"];
        }else if($owner!=null){
            $owner = $this->User_model->get_user(['iduser'], ["filter" => ['related_id' => $owner]]);
            if ($owner!=null && count($owner)==1) $owner = $owner[0];
            else $owner = null;
        }

        $fields = ["idbrand as value","name as label","variant"];
        $filters = $owner==null ? [] : [ "filter" => ["iduser" => $owner] ];
        $data = $this->BrandModel->get_brand($fields,$filters);
        
        $data = $data!=null ? $data : [];

        return $this->respond( tempResponse("00000",$data) );
    }

    public function store()
    {
        $user = $this->validate_session($this->validation->store);

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
        $this->validate_session($this->validation->amend);

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
            "updatedat" => date("Y-m-d H:i:s"),
        ];
        if($img!=null) $amend["image"] = $img;

        $data = $this->BrandModel->amend($this->request->getPost("key"), $amend);
        
        $code = $data==false ? "00003" : "00000";

        return $this->respond( tempResponse($code, $data) );
    }

    public function destroy()
    {
        $this->validate_session($this->validation->destroy);

        $id = $this->request->getPost("key");

        $campaign = $this->CampaignModel->get_campaign_brands(["idbrand"],[ "filter" => ["idbrand"=>$id] ]);
        if($campaign!=null && count($campaign)>=0) return $this->respond( tempResponse("00008") );

        $destroy = $this->BrandModel->destroy($id);
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
            $campaign = $this->CampaignModel->get_campaign_brands(["idbrand"],[ "filter" => ["idbrand"=>$v] ]);
            if($campaign!=null && count($campaign)>=0) { $fail=true; continue; }

            $destroy = $this->BrandModel->destroy($v);
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