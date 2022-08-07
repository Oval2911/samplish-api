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
            "searchable" => [ "name", "box_type", "service", "quantity", "payment_due_date", "payment_status", ],
        ];
        $fields = [ "idcampaign", "name", "box_type", "service", "quantity", "payment_due_date", "payment_status", ];
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

    private function _data($id)
    {
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

        if( !($campaign!=null && count($campaign)==1) ) return false;
        
        $brands = $this->CampaignModel->get_campaign_brands(["*"],$filters);

        $questions = $this->CampaignModel->get_campaign_question(["*"],$filters);

        $merchandise = $this->CampaignModel->get_campaign_merchandise(["*"],$filters);

        return (object)[
            "campaign" => $campaign[0],
            "brands" => $brands,
            "questions" => $questions,
            "merchandise" => $merchandise,
        ];
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
        $req = $this->request;

        $document_brief = $req->getFile('document_brief');
        if($document_brief && !$document_brief->hasMoved()) {
            $store = $document_brief->store();
            $file = new File(WRITEPATH .'uploads/'. $store);
            $document_brief = $store;
        }

        $logo = $req->getFile('logo');
        if($logo && !$logo->hasMoved()) {
            $store = $logo->store();
            $file = new File(WRITEPATH .'uploads/'. $store);
            $logo = $store;
        }

        $campaignData = [
            "iduser" => $user["iduser"],
        ];
        if($req->getPost("area")==null) $campaign["idarea"] = $req->getPost("area");
        if($req->getPost("name")==null) $campaign["name"] = $req->getPost("name");
        if($req->getPost("status")==null) $campaign["status"] = $req->getPost("status");
        if($req->getPost("quantity")==null) $campaign["quantity"] = $req->getPost("quantity");
        if($req->getPost("theme")==null) $campaign["theme"] = $req->getPost("theme");
        if($req->getPost("box_type")==null) $campaign["box_type"] = $req->getPost("box_type");
        if($req->getPost("start_date")==null) $campaign["start_date"] = $req->getPost("start_date");
        if($req->getPost("end_date")==null) $campaign["end_date"] = $req->getPost("end_date");
        if($req->getPost("size")==null) $campaign["size"] = $req->getPost("size");
        if($req->getPost("desc")==null) $campaign["desc"] = $req->getPost("desc");
        if($req->getPost("objective")==null) $campaign["objective"] = $req->getPost("objective");
        if($req->getPost("key_message")==null) $campaign["key_message"] = $req->getPost("key_message");
        if($req->getPost("creative_direction")==null) $campaign["creative_direction"] = $req->getPost("creative_direction");
        if($req->getPost("adds_merchandise")==null) $campaign["adds_merchandise"] = $req->getPost("adds_merchandise");
        if($req->getPost("custom_box_design")==null) $campaign["custom_box_design"] = $req->getPost("custom_box_design");
        if($req->getPost("digital_campaign")==null) $campaign["digital_campaign"] = $req->getPost("digital_campaign");
        if($req->getPost("event")==null) $campaign["event"] = $req->getPost("event");
        if($req->getPost("feedback_due_date")==null) $campaign["feedback_due_date"] = $req->getPost("feedback_due_date");
        if($req->getPost("target_market")==null) $campaign["target_market"] = $req->getPost("target_market");

        if($document_brief!=null) $campaign["document_brief"] = $document_brief;
        if($logo!=null) $campaign["logo"] = $logo;
            
        $campaign = $this->CampaignModel->store($campaignData);

        if($campaign==false) return $this->respond( tempResponse("00002") );

        $brands = $req->getPost("brands");
        $length = $req->getPost("length");
        $width = $req->getPost("width");
        $weight = $req->getPost("weight");
        $variant = $req->getPost("variant");
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

        $questions = $req->getPost("feedback_question");
        if(is_array($questions)){
            foreach($questions as $k => $v){
                if($v=="") continue;
                $this->CampaignModel->store_question([
                    "idcampaign" => $campaign,
                    "text" => $v,
                ]);
            }
        }

        $text = $req->getPost("merchandise_text");
        $qty = $req->getPost("merchandise_qty");
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
        $req = $this->request;
        $campaign = $req->getPost("key");

        $document_brief = $req->getFile('document_brief');
        if($document_brief && !$document_brief->hasMoved()) {
            $store = $document_brief->store();
            $file = new File(WRITEPATH .'uploads/'. $store);
            $document_brief = $store;
        }

        $logo = $req->getFile('logo');
        if($logo && !$logo->hasMoved()) {
            $store = $logo->store();
            $file = new File(WRITEPATH .'uploads/'. $store);
            $logo = $store;
        }

        $campaignData = [
            "iduser" => $user["iduser"],
            "updatedat" => date("Y-m-d H:i:s"),
        ];
        if($req->getPost("area")==null) $campaign["idarea"] = $req->getPost("area");
        if($req->getPost("name")==null) $campaign["name"] = $req->getPost("name");
        if($req->getPost("status")==null) $campaign["status"] = $req->getPost("status");
        if($req->getPost("quantity")==null) $campaign["quantity"] = $req->getPost("quantity");
        if($req->getPost("theme")==null) $campaign["theme"] = $req->getPost("theme");
        if($req->getPost("box_type")==null) $campaign["box_type"] = $req->getPost("box_type");
        if($req->getPost("start_date")==null) $campaign["start_date"] = $req->getPost("start_date");
        if($req->getPost("end_date")==null) $campaign["end_date"] = $req->getPost("end_date");
        if($req->getPost("size")==null) $campaign["size"] = $req->getPost("size");
        if($req->getPost("desc")==null) $campaign["desc"] = $req->getPost("desc");
        if($req->getPost("objective")==null) $campaign["objective"] = $req->getPost("objective");
        if($req->getPost("key_message")==null) $campaign["key_message"] = $req->getPost("key_message");
        if($req->getPost("creative_direction")==null) $campaign["creative_direction"] = $req->getPost("creative_direction");
        if($req->getPost("adds_merchandise")==null) $campaign["adds_merchandise"] = $req->getPost("adds_merchandise");
        if($req->getPost("custom_box_design")==null) $campaign["custom_box_design"] = $req->getPost("custom_box_design");
        if($req->getPost("digital_campaign")==null) $campaign["digital_campaign"] = $req->getPost("digital_campaign");
        if($req->getPost("event")==null) $campaign["event"] = $req->getPost("event");
        if($req->getPost("feedback_due_date")==null) $campaign["feedback_due_date"] = $req->getPost("feedback_due_date");
        if($req->getPost("target_market")==null) $campaign["target_market"] = $req->getPost("target_market");

        if($document_brief!=null) $campaign["document_brief"] = $document_brief;
        if($logo!=null) $campaign["logo"] = $logo;

        $this->CampaignModel->amend($campaign, $campaignData);

        $brands = $req->getPost("brands");
        $length = $req->getPost("length");
        $width = $req->getPost("width");
        $weight = $req->getPost("weight");
        $variant = $req->getPost("variant");
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

        $questions = $req->getPost("feedback_question");
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

        $text = $req->getPost("merchandise_text");
        $qty = $req->getPost("merchandise_qty");
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

        $id = $this->request->getPost("key");

        $data = $this->_data($id);
        if($data==null) $this->respond( tempResponse("00104") );

        // if($data->)

        $campaign = $this->CampaignModel->amend($id, [
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