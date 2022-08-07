<?php namespace App\Controllers;
 
use CodeIgniter\RESTful\ResourceController;
use App\Models\CampaignModel;
use App\Models\User_model;
use CodeIgniter\Files\File;
 
class Campaign extends ResourceController
{
    private $validation;

    public function __construct()
    {
        $this->request = \Config\Services::request();
        
        $this->CampaignModel  = new CampaignModel();
        $this->User_model  = new User_model();

        helper(['rsCode']);
        
        date_default_timezone_set('Asia/Jakarta');

        $this->validation = (object)[
            "datatable" => [
                'u' => ["label"=>"User", "rules"=>"required",],
                'token' => ["label"=>"Access Token", "rules"=>"required",],
                'limit' => ["label"=>"Pagination", "rules"=>"required",],
            ],
            "datatable_payment" => [
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
                'status' => ["label"=>"Campaign Status", "rules"=>"required",],
                'name' => ["label"=>"Campaign Name", "rules"=>"required",],
            ],
            "amend" => [
                'key' => ["label"=>"Key", "rules"=>"required",],
                'u' => ["label"=>"User", "rules"=>"required",],
                'token' => ["label"=>"Access Token", "rules"=>"required",],
                'status' => ["label"=>"Campaign Status", "rules"=>"required",],
                'name' => ["label"=>"Campaign Name", "rules"=>"required",],
            ],
            "amend_payment" => [
                'key' => ["label"=>"Key", "rules"=>"required",],
                'u' => ["label"=>"User", "rules"=>"required",],
                'token' => ["label"=>"Access Token", "rules"=>"required",],
            ],
            "payment" => [
                'key' => ["label"=>"Key", "rules"=>"required",],
                'u' => ["label"=>"User", "rules"=>"required",],
                'token' => ["label"=>"Access Token", "rules"=>"required",],
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
            "searchable" => [ "name", "status", "theme", "box_type", "start_date", "end_date", ],
        ];
        $fields = [ "idcampaign", "name", "status", "theme", "box_type", "start_date", "end_date", ];
        $data = $this->CampaignModel->datatable($fields, $filters);

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

    public function datatable_payment()
    {
        $user = $this->validate_session($this->validation->datatable_payment);

        $filters = [
            "limit" => $this->request->getGet("limit"),
            "order" => $this->request->getGet("order"),
            "search" => $this->request->getGet("search"),
            "user" => $user["iduser"],
            "statusNot" => "draft",
            "searchable" => [ "name", "box_type", "service", ],
        ];
        $fields = [ "idcampaign", "name", "box_type", "service", ];
        $data = $this->CampaignModel->datatable($fields, $filters);

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

        $id = $this->request->getGet("key");
        $fields = [
            "idcampaign",
            "idarea",
            "name",
            "status",
            "quantity",
            "theme",
            "box_type",
            "start_date",
            "end_date",
            "size",
            "desc",
            "objective",
            "key_message",
            "creative_direction",
            "adds_merchandise",
            "document_brief",
            "logo",
            "custom_box_design",
            "digital_campaign",
            "event",
            "feedback_due_date",
        ];
        $filters = [ "filter" => ["idcampaign" => $id] ];
        $campaign = $this->CampaignModel->get_campaign($fields,$filters);

        if( !($campaign!=null && count($campaign)==1) ) return $this->respond( tempResponse("00104") );
        
        $brands = $this->CampaignModel->get_campaign_brands(["*"],$filters);

        $questions = $this->CampaignModel->get_campaign_question(["*"],$filters);

        $merchandise = $this->CampaignModel->get_campaign_merchandise(["*"],$filters);

        return $this->respond(
            tempResponse(
                "00000",
                (object)[
                    "campaign" => $campaign[0],
                    "brands" => $brands,
                    "questions" => $questions,
                    "merchandise" => $merchandise,
                ],
            )
        );
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

        $fields = ["idbrand as value","name as label"];
        $filters = $owner==null ? [] : [ "filter" => ["iduser" => $owner] ];
        $data = $this->CampaignModel->get_brand($fields,$filters);

        $code = $data!=null && count($data)==1 ? '00000' : "00104";
        $data = $data!=null && count($data)==1 ? $data : false;

        return $this->respond( tempResponse($code,$data) );
    }

    public function store()
    {
        $user = $this->validate_session($this->validation->store);

        $document_brief = $this->request->getFile('document_brief');
        if($document_brief && !$document_brief->hasMoved()) {
            $store = $document_brief->store();
            $file = new File(WRITEPATH .'uploads/'. $store);
            $document_brief = $store;
        }

        $logo = $this->request->getFile('logo');
        if($logo && !$logo->hasMoved()) {
            $store = $logo->store();
            $file = new File(WRITEPATH .'uploads/'. $store);
            $logo = $store;
        }

        $campaign = $this->CampaignModel->store([
            "idarea" => $this->request->getPost("area"),
            "name" => $this->request->getPost("name"),
            "status" => $this->request->getPost("status"),
            "iduser" => $user["iduser"],
            "quantity" => $this->request->getPost("quantity"),
            "theme" => $this->request->getPost("theme"),
            "box_type" => $this->request->getPost("box_type"),
            "start_date" => $this->request->getPost("start_date"),
            "end_date" => $this->request->getPost("end_date"),
            "size" => $this->request->getPost("size"),
            "desc" => $this->request->getPost("desc"),
            "objective" => $this->request->getPost("objective"),
            "key_message" => $this->request->getPost("key_message"),
            "creative_direction" => $this->request->getPost("creative_direction"),
            "adds_merchandise" => $this->request->getPost("adds_merchandise"),
            "document_brief" => $document_brief,
            "logo" => $logo,
            "custom_box_design" => $this->request->getPost("custom_box_design"),
            "digital_campaign" => $this->request->getPost("digital_campaign"),
            "event" => $this->request->getPost("event"),
            "feedback_due_date" => $this->request->getPost("feedback_due_date"),
        ]);

        if($campaign==false) return $this->respond( tempResponse("00002") );

        $brands = $this->request->getPost("brands");
        $length = $this->request->getPost("length");
        $width = $this->request->getPost("width");
        $weight = $this->request->getPost("weight");
        $variant = $this->request->getPost("variant");
        if(is_array($brands) && is_array($length) && is_array($width) && is_array($weight) && is_array($variant)){
            foreach($brands as $k => $v){
                if($v=="") continue;
                $this->CampaignModel->store_brand([
                    "idcampaign" => $campaign,
                    "idbrand" => $v,
                    "variant" => $variant[$k],
                    "length" => $length[$k],
                    "width" => $width[$k],
                    "weight" => $weight[$k],
                ]);
            }
        }

        $questions = $this->request->getPost("feedback_question");
        if(is_array($questions)){
            foreach($questions as $k => $v){
                if($v=="") continue;
                $this->CampaignModel->store_question([
                    "idcampaign" => $campaign,
                    "text" => $v,
                ]);
            }
        }

        $text = $this->request->getPost("merchandise_text");
        $qty = $this->request->getPost("merchandise_qty");
        if(is_array($text) && is_array($qty)){
            foreach($text as $k => $v){
                $this->CampaignModel->store_merchandise([
                    "idcampaign" => $campaign,
                    "text" => $v,
                    "quantity" => $qty[$k],
                ]);
            }
        }

        return $this->respond( tempResponse("00000", $campaign) );
    }

    public function amend()
    {
        $user = $this->validate_session($this->validation->amend);
        $campaign = $this->request->getPost("key");

        $document_brief = $this->request->getFile('document_brief');
        if($document_brief && !$document_brief->hasMoved()) {
            $store = $document_brief->store();
            $file = new File(WRITEPATH .'uploads/'. $store);
            $document_brief = $store;
        }

        $logo = $this->request->getFile('logo');
        if($logo && !$logo->hasMoved()) {
            $store = $logo->store();
            $file = new File(WRITEPATH .'uploads/'. $store);
            $logo = $store;
        }

        $this->CampaignModel->amend(
            $campaign,
            [
                "idarea" => $this->request->getPost("area"),
                "name" => $this->request->getPost("name"),
                "status" => $this->request->getPost("status"),
                "iduser" => $user["iduser"],
                "quantity" => $this->request->getPost("quantity"),
                "theme" => $this->request->getPost("theme"),
                "box_type" => $this->request->getPost("box_type"),
                "start_date" => $this->request->getPost("start_date"),
                "end_date" => $this->request->getPost("end_date"),
                "size" => $this->request->getPost("size"),
                "desc" => $this->request->getPost("desc"),
                "objective" => $this->request->getPost("objective"),
                "key_message" => $this->request->getPost("key_message"),
                "creative_direction" => $this->request->getPost("creative_direction"),
                "adds_merchandise" => $this->request->getPost("adds_merchandise"),
                "document_brief" => $document_brief,
                "logo" => $logo,
                "custom_box_design" => $this->request->getPost("custom_box_design"),
                "digital_campaign" => $this->request->getPost("digital_campaign"),
                "event" => $this->request->getPost("event"),
                "feedback_due_date" => $this->request->getPost("feedback_due_date"),
                "updatedat" => date("Y-m-d H:i:s"),
            ]
        );

        $brands = $this->request->getPost("brands");
        $length = $this->request->getPost("length");
        $width = $this->request->getPost("width");
        $weight = $this->request->getPost("weight");
        $variant = $this->request->getPost("variant");
        $this->CampaignModel->destroy_brands($campaign);
        if(is_array($brands) && is_array($length) && is_array($width) && is_array($weight) && is_array($variant)){
            foreach($brands as $k => $v){
                if($v=="") continue;
                $this->CampaignModel->store_brand([
                    "idcampaign" => $campaign,
                    "idbrand" => $v,
                    "variant" => $variant[$k],
                    "length" => $length[$k],
                    "width" => $width[$k],
                    "weight" => $weight[$k],
                ]);
            }
        }

        $questions = $this->request->getPost("feedback_question");
        $this->CampaignModel->destroy_questions($campaign);
        if(is_array($questions)){
            foreach($questions as $k => $v){
                if($v=="") continue;
                $this->CampaignModel->store_question([
                    "idcampaign" => $campaign,
                    "text" => $v,
                ]);
            }
        }

        $text = $this->request->getPost("merchandise_text");
        $qty = $this->request->getPost("merchandise_qty");
        $this->CampaignModel->destroy_merchandises($campaign);
        if(is_array($text) && is_array($qty)){
            foreach($text as $k => $v){
                $this->CampaignModel->store_merchandise([
                    "idcampaign" => $campaign,
                    "text" => $v,
                    "quantity" => $qty[$k],
                ]);
            }
        }

        return $this->respond( tempResponse("00000", $campaign) );
    }

    public function amend_payment()
    {
        $this->validate_session($this->validation->amend_payment);
        $campaign = $this->request->getPost("key");

        $receipt_payment = $this->request->getFile('receipt_payment');
        if($receipt_payment && !$receipt_payment->hasMoved()) {
            $store = $receipt_payment->store();
            $file = new File(WRITEPATH .'uploads/'. $store);
            $receipt_payment = $store;
        }

        $this->CampaignModel->amend(
            $campaign,
            [
                "service" => $this->request->getPost("service"),
                "service_address" => $this->request->getPost("service_address"),
                "service_due_date" => $this->request->getPost("service_due_date"),
                "contact_name" => $this->request->getPost("contact_name"),
                "contact_number" => $this->request->getPost("contact_number"),
                "receipt_payment" => $receipt_payment,
                "updatedat" => date("Y-m-d H:i:s"),
            ]
        );

        return $this->respond( tempResponse("00000", $campaign) );
    }

    public function destroy()
    {
        $this->validate_session($this->validation->destroy);

        $data = $this->CampaignModel->destroy($this->request->getPost("key"));
        
        $code = $data==false ? "00007" : "00000";

        return $this->respond( tempResponse($code, $data) );
    }

    public function destroys()
    {
        $this->validate_session($this->validation->destroys);

        $keys = $this->request->getPost("keys");
        if(!is_array($keys)) return $this->respond( tempResponse("00104") );
        
        foreach($keys as $k => $v){
            $this->CampaignModel->destroy($v);
        }

        return $this->respond( tempResponse("00000", true) );
    }

    public function payment()
    {
        $this->validate_session($this->validation->payment);

        $campaign = $this->CampaignModel->amend($this->request->getPost("key"), [
            "status" => "wait_pay",
            "updatedat" => date("Y-m-d H:i:s"),
        ]);

        if($campaign==false) return $this->respond( tempResponse("00003") );

        return $this->respond( tempResponse("00000") );
    }

    public function draft()
    {
        $this->validate_session($this->validation->payment);

        $campaign = $this->CampaignModel->amend($this->request->getPost("key"), [
            "status" => "draft",
            "updatedat" => date("Y-m-d H:i:s"),
        ]);

        if($campaign==false) return $this->respond( tempResponse("00003") );

        return $this->respond( tempResponse("00000") );
    }
 
}