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
            "datatable_all_company" => [
                'u' => ["label"=>"User", "rules"=>"required",],
                'token' => ["label"=>"Access Token", "rules"=>"required",],
                'limit' => ["label"=>"Pagination", "rules"=>"required",],
            ],
            "data" => [
                'u' => ["label"=>"User", "rules"=>"required",],
                'token' => ["label"=>"Access Token", "rules"=>"required",],
                'key' => ["label"=>"Key", "rules"=>"required",],
            ],
            "data_payment" => [
                'u' => ["label"=>"User", "rules"=>"required",],
                'token' => ["label"=>"Access Token", "rules"=>"required",],
                'key' => ["label"=>"Key", "rules"=>"required",],
            ],
            "dropdown_mix" => [
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
                'service' => ["label"=>"Pick Up Service", "rules"=>"required",],
                'service_address' => ["label"=>"Address", "rules"=>"required",],
                'service_due_date' => ["label"=>"Date", "rules"=>"required",],
                'contact_name' => ["label"=>"Contact Name", "rules"=>"required",],
                'contact_number' => ["label"=>"Contact Number", "rules"=>"required",],
                'receipt_payment' => ["label"=>"Proof of Payment", "rules"=>"required",],
            ],
            "amend_brands" => [
                'key' => ["label"=>"Key", "rules"=>"required",],
                'u' => ["label"=>"User", "rules"=>"required",],
                'token' => ["label"=>"Access Token", "rules"=>"required",],
            ],
            "payment" => [
                'key' => ["label"=>"Key", "rules"=>"required",],
                'u' => ["label"=>"User", "rules"=>"required",],
                'token' => ["label"=>"Access Token", "rules"=>"required",],
            ],
            "draft" => [
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

    public function datatable_all_company()
    {
        $this->validate_session($this->validation->datatable);

        $filters = [
            "limit" => $this->request->getGet("limit"),
            "order" => $this->request->getGet("order"),
            "search" => $this->request->getGet("search"),
            "searchable" => [ "user.fullname", "campaign.name", "campaign.box_type", "campaign.status", "campaign.start_date", "campaign.end_date", "campaign.feedback_due_date", ],
        ];
        $fields = [ "campaign.idcampaign", "user.fullname", "campaign.name", "campaign.box_type", "campaign.status", "campaign.start_date", "campaign.end_date", "campaign.feedback_due_date", ];
        $data = $this->CampaignModel->datatable_all_company($fields, $filters);

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

    public function data_payment()
    {
        $this->validate_session($this->validation->data);

        $id = $this->request->getGet("key");
        $fields = [
            "idcampaign",
            "payment_status",
            "payment_due_date",
            "name",
            "size",
            "custom_box_design",
            "digital_campaign",
            "event",
            "quantity",
            "box_type",
            "service",
            "service_address",
            "service_due_date",
            "contact_name",
            "contact_number",
            "receipt_payment",
        ];
        $filters = [ "filter" => ["idcampaign" => $id] ];
        $campaign = $this->CampaignModel->get_campaign($fields,$filters);

        if( !($campaign!=null && count($campaign)==1) ) return $this->respond( tempResponse("00104") );
        
        $brands = $this->CampaignModel->get_campaign_brand_details(["brand.name","brand.variant"],$filters);

        return $this->respond(
            tempResponse(
                "00000",
                (object)[
                    "campaign" => $campaign[0],
                    "brands" => $brands,
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
            "campaign" => (object)$campaign[0],
            "brands" => $brands,
            "questions" => $questions,
            "merchandise" => $merchandise,
        ];
    }

    public function dropdown_mix()
    {
        $this->validate_session($this->validation->dropdown_mix);

        $fields = ["idcampaign as value","name as label", "theme", "idarea", "start_date", "end_date",];
        $filters = [ "filter" => ["box_type" => "mix"] ];
        $data = $this->CampaignModel->get_campaign($fields,$filters);
        
        $data = $data!=null ? $data : [];

        return $this->respond( tempResponse("00000",$data) );
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
        if($req->getPost("area")!=null) $campaignData["idarea"] = $req->getPost("area");
        if($req->getPost("name")!=null) $campaignData["name"] = $req->getPost("name");
        if($req->getPost("status")!=null) $campaignData["status"] = $req->getPost("status");
        if($req->getPost("quantity")!=null) $campaignData["quantity"] = $req->getPost("quantity");
        if($req->getPost("theme")!=null) $campaignData["theme"] = $req->getPost("theme");
        if($req->getPost("box_type")!=null) $campaignData["box_type"] = $req->getPost("box_type");
        if($req->getPost("start_date")!=null) $campaignData["start_date"] = $req->getPost("start_date");
        if($req->getPost("end_date")!=null) $campaignData["end_date"] = $req->getPost("end_date");
        if($req->getPost("size")!=null) $campaignData["size"] = $req->getPost("size");
        if($req->getPost("desc")!=null) $campaignData["desc"] = $req->getPost("desc");
        if($req->getPost("objective")!=null) $campaignData["objective"] = $req->getPost("objective");
        if($req->getPost("key_message")!=null) $campaignData["key_message"] = $req->getPost("key_message");
        if($req->getPost("creative_direction")!=null) $campaignData["creative_direction"] = $req->getPost("creative_direction");
        if($req->getPost("adds_merchandise")!=null) $campaignData["adds_merchandise"] = $req->getPost("adds_merchandise");
        if($req->getPost("custom_box_design")!=null) $campaignData["custom_box_design"] = $req->getPost("custom_box_design");
        if($req->getPost("digital_campaign")!=null) $campaignData["digital_campaign"] = $req->getPost("digital_campaign");
        if($req->getPost("event")!=null) $campaignData["event"] = $req->getPost("event");
        if($req->getPost("feedback_due_date")!=null) $campaignData["feedback_due_date"] = $req->getPost("feedback_due_date");
        if($req->getPost("target_market")!=null) $campaignData["target_market"] = $req->getPost("target_market");

        if($document_brief!=null) $campaignData["document_brief"] = $document_brief;
        if($logo!=null) $campaignData["logo"] = $logo;
            
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
        if($req->getPost("area")!=null) $campaignData["idarea"] = $req->getPost("area");
        if($req->getPost("name")!=null) $campaignData["name"] = $req->getPost("name");
        if($req->getPost("status")!=null) $campaignData["status"] = $req->getPost("status");
        if($req->getPost("quantity")!=null) $campaignData["quantity"] = $req->getPost("quantity");
        if($req->getPost("theme")!=null) $campaignData["theme"] = $req->getPost("theme");
        if($req->getPost("box_type")!=null) $campaignData["box_type"] = $req->getPost("box_type");
        if($req->getPost("start_date")!=null) $campaignData["start_date"] = $req->getPost("start_date");
        if($req->getPost("end_date")!=null) $campaignData["end_date"] = $req->getPost("end_date");
        if($req->getPost("size")!=null) $campaignData["size"] = $req->getPost("size");
        if($req->getPost("desc")!=null) $campaignData["desc"] = $req->getPost("desc");
        if($req->getPost("objective")!=null) $campaignData["objective"] = $req->getPost("objective");
        if($req->getPost("key_message")!=null) $campaignData["key_message"] = $req->getPost("key_message");
        if($req->getPost("creative_direction")!=null) $campaignData["creative_direction"] = $req->getPost("creative_direction");
        if($req->getPost("adds_merchandise")!=null) $campaignData["adds_merchandise"] = $req->getPost("adds_merchandise");
        if($req->getPost("custom_box_design")!=null) $campaignData["custom_box_design"] = $req->getPost("custom_box_design");
        if($req->getPost("digital_campaign")!=null) $campaignData["digital_campaign"] = $req->getPost("digital_campaign");
        if($req->getPost("event")!=null) $campaignData["event"] = $req->getPost("event");
        if($req->getPost("feedback_due_date")!=null) $campaignData["feedback_due_date"] = $req->getPost("feedback_due_date");
        if($req->getPost("target_market")!=null) $campaignData["target_market"] = $req->getPost("target_market");

        if($document_brief!=null) $campaignData["document_brief"] = $document_brief;
        if($logo!=null) $campaignData["logo"] = $logo;

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

        $data = $this->CampaignModel->amend(
            $campaign,
            [
                "service" => $this->request->getPost("service"),
                "service_address" => $this->request->getPost("service_address"),
                "service_due_date" => $this->request->getPost("service_due_date"),
                "contact_name" => $this->request->getPost("contact_name"),
                "contact_number" => $this->request->getPost("contact_number"),
                "receipt_payment" => $receipt_payment,
                "status" => "process_admin",
                "updatedat" => date("Y-m-d H:i:s"),
            ]
        );

        if($data==false) return $this->respond( tempResponse("00104") );
        
        return $this->respond( tempResponse("00000", $campaign) );
    }

    public function amend_brands()
    {
        $user = $this->validate_session($this->validation->amend_brands);
        $req = $this->request;
        $campaign = $req->getPost("key");

        $brands = $req->getPost("brands");
        $length = $req->getPost("length");
        $width = $req->getPost("width");
        $weight = $req->getPost("weight");
        $variant = $req->getPost("variant");
        $this->CampaignModel->destroy_brands_user($campaign,$user["iduser"]);
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

        return $this->respond( tempResponse("00000", $campaign) );
    }

    public function destroy()
    {
        $this->validate_session($this->validation->destroy);
        $id = $this->request->getPost("key");

        $data = $this->_data($id);
        if($data==null) return $this->respond( tempResponse("00104") );

        if($data->campaign->status!="draft") return $this->respond( tempResponse("00104",false,"Campaign is not allowed to delete") );

        $data = $this->CampaignModel->destroy($id);
        
        $code = $data==false ? "00007" : "00000";

        return $this->respond( tempResponse($code, $data) );
    }

    public function payment()
    {
        $this->validate_session($this->validation->payment);

        $id = $this->request->getPost("key");

        $data = $this->_data($id);
        if($data==null) return $this->respond( tempResponse("00104") );

        $msg = "Can not proceed to payment.";
        if($data->campaign->name==null) return $this->respond( tempResponse("00104",false,"$msg Campaign Name is required") );
        if( !($data->brands!=null && count($data->brands)>0) ) return $this->respond( tempResponse("00104",false,"$msg Brands is required") );
        if($data->campaign->status!="draft") return $this->respond( tempResponse("00104") );
        if($data->campaign->quantity==null) return $this->respond( tempResponse("00104",false,"$msg Sampling Quantity is required") );
        if($data->campaign->box_type==null) return $this->respond( tempResponse("00104",false,"$msg Package is required") );
        if($data->campaign->start_date==null) return $this->respond( tempResponse("00104",false,"$msg Distribution Date is required") );
        if($data->campaign->end_date==null) return $this->respond( tempResponse("00104",false,"$msg Distribution Date is required") );
        if(strtotime($data->campaign->start_date)>strtotime($data->campaign->end_date)) return $this->respond( tempResponse("00104",false,"$msg Invalid Distribution Date") );
        if($data->campaign->idarea==null) return $this->respond( tempResponse("00104",false,"$msg Area Distribution is required") );
        if($data->campaign->theme==null) return $this->respond( tempResponse("00104",false,"$msg Theme is required") );
        if($data->campaign->size==null) return $this->respond( tempResponse("00104",false,"$msg Box Size is required") );

        $now = date("Y-m-d H:i:s");
        $due = date_create($now);
        date_add($due,date_interval_create_from_date_string("1 days"));
        $campaign = $this->CampaignModel->amend($id, [
            "status" => "wait_pay",
            "payment_status" => "unpaid",
            "payment_due_date" => date_format($due,"Y-m-d H:i:s"),
            "updatedat" => $now,
        ]);

        if($campaign==false) return $this->respond( tempResponse("00003") );

        return $this->respond( tempResponse("00000") );
    }

    public function draft()
    {
        $this->validate_session($this->validation->draft);

        $campaign = $this->CampaignModel->amend($this->request->getPost("key"), [
            "status" => "draft",
            "updatedat" => date("Y-m-d H:i:s"),
        ]);

        if($campaign==false) return $this->respond( tempResponse("00003") );

        return $this->respond( tempResponse("00000") );
    }
 
}