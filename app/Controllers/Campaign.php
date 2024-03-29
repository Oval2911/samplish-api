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
            "wait_pay" => [
                'key' => ["label"=>"Key", "rules"=>"required",],
                'u' => ["label"=>"User", "rules"=>"required",],
                'token' => ["label"=>"Access Token", "rules"=>"required",],
                'price_service' => ["label"=>"Price Service", "rules"=>"required",],
                'price_box_design' => ["label"=>"Price Box Design", "rules"=>"required",],
                'price_event' => ["label"=>"Price Event", "rules"=>"required",],
                'price_digital_marketing' => ["Price Digital Marketing"=>"Key", "rules"=>"required",],
                'price_merchandise' => ["label"=>"Price Merchandise", "rules"=>"required",],
            ],
            "destroy" => [
                'key' => ["label"=>"Key", "rules"=>"required",],
                'u' => ["label"=>"User", "rules"=>"required",],
                'token' => ["label"=>"Access Token", "rules"=>"required",],
            ],
            "join" => [
                'key' => ["label"=>"Key", "rules"=>"required",],
                'u' => ["label"=>"User", "rules"=>"required",],
                'token' => ["label"=>"Access Token", "rules"=>"required",],
                'brands' => ["label"=>"Brands", "rules"=>"required",],
                'length' => ["label"=>"Brands Length", "rules"=>"required",],
                'width' => ["label"=>"Brands Width", "rules"=>"required",],
                'weight' => ["label"=>"Brands Weight", "rules"=>"required",],
                'variant' => ["label"=>"Brands Variant", "rules"=>"required",],
            ],
            "join_sampler" => [
                'key' => ["label"=>"Key", "rules"=>"required",],
                'u' => ["label"=>"User", "rules"=>"required",],
                'token' => ["label"=>"Access Token", "rules"=>"required",],
            ],
        ];
    }

    public function datatable()
    {
        $user = $this->validate_session($this->validation->datatable);

        if($user["related_key"]=="admin"){
            if($this->request->getGet("key")){
                $filters = [
                    "limit" => $this->request->getGet("limit"),
                    "order" => $this->request->getGet("order"),
                    "search" => $this->request->getGet("search"),
                    "user" => $this->request->getGet("key"),
                ];
                $data = $this->CampaignModel->datatable_company_union($filters);
            }else{
                $filters = [
                    "limit" => $this->request->getGet("limit"),
                    "order" => $this->request->getGet("order"),
                    "search" => $this->request->getGet("search"),
                    "user" => $user["iduser"],
                    "searchable" => [ "name", "status", "theme", "box_type", "start_date", "end_date", ],
                ];
                $fields = [ "idcampaign", "name", "status", "theme", "box_type", "start_date", "end_date", ];
                $data = $this->CampaignModel->datatable($fields, $filters);
            }
        }else if($user["related_key"]=="company"){
            $filters = [
                "limit" => $this->request->getGet("limit"),
                "order" => $this->request->getGet("order"),
                "search" => $this->request->getGet("search"),
                "user" => $user["iduser"],
            ];
            $data = $this->CampaignModel->datatable_company_union($filters);
        }else{
            return $this->respond( tempResponse("00104") );
        }
        
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

    public function datatable_overview_sampler()
    {
        $user = $this->validate_session($this->validation->datatable);

        $filters = [
            "limit" => $this->request->getGet("limit"),
            "order" => $this->request->getGet("order"),
            "search" => $this->request->getGet("search"),
            "join" => [
                "campaign_sampler" => "campaign_sampler.idcampaign = campaign.idcampaign",
                "area" => "area.idarea = campaign.idarea",
            ],
            "join_total" => [ "campaign_sampler" => true, ],
            "searchable" => [ "campaign.name", "campaign.desc", "campaign.box_type", "campaign_sampler.status_campaign", "campaign_sampler.status_box", ],
            "user_sampler" => $user["iduser"],
        ];
        $fields = [
            "campaign.idcampaign",
            "campaign.name",
            "campaign.desc",
            "campaign.logo",
            "campaign.box_type",
            "area.name as area",
            "(SELECT COUNT(idbrand) FROM campaign_brand WHERE campaign_brand.idcampaign = campaign.idcampaign) AS item",
            "campaign_sampler.status_campaign",
            "campaign_sampler.status_box",
            "CASE
                WHEN !(campaign.feedback_due_date IS NOT NULL AND campaign.feedback_due_date <= CURDATE()) THEN true
                ELSE false
            END AS is_late",
        ];
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
            "searchable" => [ "name", "box_type", "service", "payment_due_date", "payment_status", "status", ],
        ];
        $fields = [ "idcampaign", "name", "box_type", "service", "payment_due_date", "payment_status", "status", ];
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

    public function datatable_sampler()
    {
        $this->validate_session($this->validation->datatable);

        $fields = [ "u.fullname AS name", "p.gender", "p.birthdate"];
        $filters = [
            "limit" => $this->request->getGet("limit"),
            "order" => $this->request->getGet("order"),
            "search" => $this->request->getGet("search"),
            "campaign" => $this->request->getGet("campaign"),
            "searchable" => $fields,
        ];

        $fields[] = "c.status_campaign";
        $fields[] = "c.status_box";
        $fields[] = "c.iduser";
        $data = $this->CampaignModel->datatable_sampler($fields, $filters);

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

        $fields = [ "user.fullname", "campaign.name", "campaign.box_type", "campaign.status", "campaign.start_date", "campaign.end_date", "campaign.feedback_due_date", "campaign.payment_status", "campaign.payment_due_date", ];
        $filters = [
            "limit" => $this->request->getGet("limit"),
            "order" => $this->request->getGet("order"),
            "search" => $this->request->getGet("search"),
            "status" => "draft",
            "searchable" => $fields,
        ];

        $fields[] = "campaign.idcampaign";
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

    public function datatable_admin_payment_company()
    {
        $this->validate_session($this->validation->datatable);

        $fields = [ "user.fullname", "campaign.name", "campaign.box_type", "campaign.status", "campaign.start_date", "campaign.end_date", "campaign.feedback_due_date", "campaign.payment_status", "campaign.payment_due_date", ];
        $filters = [
            "limit" => $this->request->getGet("limit"),
            "order" => $this->request->getGet("order"),
            "search" => $this->request->getGet("search"),
            "searchable" => $fields,
            "statusNot" => [ 'draft', 'wait_confirm', ],
        ];

        $fields[] = "campaign.idcampaign";
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

    public function datatable_overview()
    {
        $user = $this->validate_session($this->validation->datatable);

        $filters = [
            "limit" => $this->request->getGet("limit"),
            "order" => $this->request->getGet("order"),
            "search" => $this->request->getGet("search"),
            "user" => $user["iduser"],
        ];

        $fields[] = "campaign.idcampaign";
        $data = $this->CampaignModel->datatable_company_union($filters);

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

    public function datatable_on_going()
    {
        $user = $this->validate_session($this->validation->datatable);
        $key = $user["related_key"];
        $id = $user["iduser"];
        $box = false;

        if($key=="company") $box = "mix";
        elseif($key=="sampler") $box = "";
        else return $this->respond( tempResponse('00104') );

        $fields = [
            "campaign.name", "campaign.desc", "area.name as area", "campaign.box_type", "campaign.start_date", "campaign.end_date",
            "campaign_sampler.status_campaign", "campaign_sampler.status_box",
        ];
        $filters = [
            "limit" => $this->request->getGet("limit"),
            "order" => $this->request->getGet("order"),
            "search" => $this->request->getGet("search"),
            "searchable" => $fields,
            "join" => [ "area" => "area.idarea = campaign.idarea", ],
            "left_join" => [ "campaign_sampler" => "campaign_sampler.idcampaign = campaign.idcampaign AND campaign_sampler.iduser = {$id}", ],
            "status" => ['on_going',],
            "box" => $box,
            "inRange" => date("Y-m-d"),
        ];

        $fields[] = "campaign.idcampaign";
        $fields[] = "campaign.photo";
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

    public function datatable_upcoming()
    {
        $user = $this->validate_session($this->validation->datatable);
        $key = $user["related_key"];
        $id = $user["iduser"];
        $box = false;

        if($key=="company") $box = "mix";
        elseif($key=="sampler") $box = "";
        else return $this->respond( tempResponse('00104') );
        
        $fields = [
            "campaign.name", "campaign.desc", "area.name as area", "campaign.box_type", "campaign.start_date", "campaign.end_date",
            "campaign_sampler.status_campaign", "campaign_sampler.status_box",
        ];
        $filters = [
            "limit" => $this->request->getGet("limit"),
            "order" => $this->request->getGet("order"),
            "search" => $this->request->getGet("search"),
            "searchable" => $fields,
            "join" => [ "area" => "area.idarea = campaign.idarea", ],
            "left_join" => [ "campaign_sampler" => "campaign_sampler.idcampaign = campaign.idcampaign AND campaign_sampler.iduser = {$id}", ],
            "status" => ['on_going',],
            "box" => $box,
            "notInRange" => date("Y-m-d"),
        ];

        $fields[] = "campaign.idcampaign";
        $fields[] = "campaign.photo";
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

    public function datatable_admin_assign()
    {
        $this->validate_session($this->validation->datatable);

        $fields = [ "user.fullname", "campaign.name", "campaign.box_type", "campaign.status", "campaign.start_date", "campaign.end_date", "campaign.feedback_due_date", "campaign.payment_status", "campaign.payment_due_date", ];
        $filters = [
            "limit" => $this->request->getGet("limit"),
            "order" => $this->request->getGet("order"),
            "search" => $this->request->getGet("search"),
            "status" => "on_going",
            "searchable" => $fields,
        ];

        $fields[] = "campaign.idcampaign";
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

    public function datatable_brands()
    {
        $this->validate_session($this->validation->datatable);

        $fields = [ "b.name", "c.variant", "u.fullname" ];
        $filters = [
            "limit" => $this->request->getGet("limit"),
            "order" => $this->request->getGet("order"),
            "search" => $this->request->getGet("search"),
            "searchable" => $fields,
            "campaign" => $this->request->getGet("campaign"),
        ];

        $fields[] = "b.idbrand as key";
        $data = $this->CampaignModel->datatable_brands($fields, $filters);

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

    public function datatable_sampler_feedback()
    {
        $user = $this->validate_session($this->validation->datatable);

        $fields = [ "campaign.name", "campaign.box_type", "campaign.feedback_due_date", ];
        $filters = [
            "limit" => $this->request->getGet("limit"),
            "order" => $this->request->getGet("order"),
            "search" => $this->request->getGet("search"),
            "searchable" => $fields,
            "campaign" => $this->request->getGet("campaign"),
            "status_box" => [ "review", "done", ],
            "user_sampler" => $user["iduser"],
            "join" => [
                "campaign_sampler" => "campaign_sampler.idcampaign = campaign.idcampaign",
            ],
            "join_total" => [ "campaign_sampler" => true, ],
        ];

        $fields[] = "campaign.idcampaign as key";
        $fields[] = "(SELECT COUNT(idbrand) FROM campaign_brand WHERE idcampaign = campaign.idcampaign) AS items";
        $fields[] = "campaign_sampler.status_box";
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
            "inbound_date",
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

    public function data_sampler()
    {
        $user = $this->validate_session($this->validation->data);

        $id = $this->request->getGet("key");
        $fields = [ "photo", "logo", "name", "theme", "box_type", "desc", "feedback_due_date", ];
        $filters = [ "filter" => ["idcampaign" => $id] ];
        $campaign = $this->CampaignModel->get_campaign($fields,$filters);

        if( !($campaign!=null && count($campaign)==1) ) return $this->respond( tempResponse("00104") );
        $campaign = $campaign[0];

        $fields = [ "status_campaign", "status_box", ];
        $filters["filter"]["iduser"] = $user["iduser"];
        $sampler = $this->CampaignModel->get_campaign_sampler($fields,$filters);

        if( $sampler!=null && count($sampler)==1 ) $sampler = $sampler[0];
        else $sampler = [ "status_campaign" => null, "status_box" => null, ];

        return $this->respond( tempResponse("00000", (object)array_merge($campaign,$sampler)) );
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
            "status",
            "custom_box_design",
            "digital_campaign",
            "event",
            "box_type",
            "service",
            "service_address",
            "service_due_date",
            "contact_name",
            "contact_number",
            "receipt_payment",
            "payment_price_service",
            "payment_price_box_design",
            "payment_price_event",
            "payment_price_digital_marketing",
            "payment_price_merchandise",
            "payment_total",
            "inbound_date",
        ];
        $filters = [ "filter" => ["idcampaign" => $id] ];
        $campaign = $this->CampaignModel->get_campaign($fields,$filters);

        if( !($campaign!=null && count($campaign)==1) ) return $this->respond( tempResponse("00104") );
        
        $brands = $this->CampaignModel->get_campaign_brand_details(["brand.name","campaign_brand.variant","campaign_brand.quantity"],$filters);

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

        $photo = $req->getFile('photo');
        if($photo && !$photo->hasMoved()) {
            $store = $photo->store();
            $file = new File(WRITEPATH .'uploads/'. $store);
            $photo = $store;
        }

        $campaignData = [
            "iduser" => $user["iduser"],
            "createdat" => date("Y-m-d H:i:s"),
            "updatedat" => date("Y-m-d H:i:s"),
        ];
        if($req->getPost("area")!=null) $campaignData["idarea"] = $req->getPost("area");
        if($req->getPost("name")!=null) $campaignData["name"] = $req->getPost("name");
        if($req->getPost("status")!=null) $campaignData["status"] = $req->getPost("status");
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
        if($req->getPost("inbound_date")!=null) $campaignData["inbound_date"] = $req->getPost("inbound_date");

        if($document_brief!=null) $campaignData["document_brief"] = $document_brief;
        if($logo!=null) $campaignData["logo"] = $logo;
        if($photo!=null) $campaignData["photo"] = $photo;
            
        $campaign = $this->CampaignModel->store($campaignData);

        if($campaign==false) return $this->respond( tempResponse("00002") );

        $brands = $req->getPost("brands");
        $length = $req->getPost("length");
        $width = $req->getPost("width");
        $weight = $req->getPost("weight");
        $variant = $req->getPost("variant");
        $quantity = $req->getPost("quantity");
        if(is_array($brands) && is_array($length) && is_array($width) && is_array($weight) && is_array($variant) && is_array($quantity)){
            foreach($brands as $k => $v){
                if($v=="") continue;
                $this->CampaignModel->store_brand([
                    "idcampaign" => $campaign,
                    "idbrand" => $v,
                    "variant" => $variant[$k],
                    "length" => $length[$k],
                    "width" => $width[$k],
                    "weight" => $weight[$k],
                    "quantity" => $quantity[$k],
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

        $photo = $req->getFile('photo');
        if($photo && !$photo->hasMoved()) {
            $store = $photo->store();
            $file = new File(WRITEPATH .'uploads/'. $store);
            $photo = $store;
        }

        $campaignData = [
            "iduser" => $user["iduser"],
            "updatedat" => date("Y-m-d H:i:s"),
        ];
        if($req->getPost("area")!=null) $campaignData["idarea"] = $req->getPost("area");
        if($req->getPost("name")!=null) $campaignData["name"] = $req->getPost("name");
        if($req->getPost("status")!=null) $campaignData["status"] = $req->getPost("status");
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
        if($req->getPost("inbound_date")!=null) $campaignData["inbound_date"] = $req->getPost("inbound_date");

        if($document_brief!=null) $campaignData["document_brief"] = $document_brief;
        if($logo!=null) $campaignData["logo"] = $logo;
        if($photo!=null) $campaignData["photo"] = $photo;

        $this->CampaignModel->amend($campaign, $campaignData);

        $brands = $req->getPost("brands");
        $length = $req->getPost("length");
        $width = $req->getPost("width");
        $weight = $req->getPost("weight");
        $variant = $req->getPost("variant");
        $quantity = $req->getPost("quantity");
        $this->CampaignModel->destroy_brands($campaign);
        if(is_array($brands) && is_array($length) && is_array($width) && is_array($weight) && is_array($variant) && is_array($quantity)){
            foreach($brands as $k => $v){
                if($v=="") continue;
                $this->CampaignModel->store_brand([
                    "idcampaign" => $campaign,
                    "idbrand" => $v,
                    "variant" => $variant[$k],
                    "length" => $length[$k],
                    "width" => $width[$k],
                    "weight" => $weight[$k],
                    "quantity" => $quantity[$k],
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
        $req = $this->request;
        $campaign = $req->getPost("key");

        $receipt_payment = $req->getFile('receipt_payment');
        if($receipt_payment && !$receipt_payment->hasMoved()) {
            $store = $receipt_payment->store();
            $file = new File(WRITEPATH .'uploads/'. $store);
            $receipt_payment = $store;
        }

        $campaignData = [
            "updatedat" => date("Y-m-d H:i:s"),
        ];
        if($req->getPost("service")!=null) $campaignData["service"] = $req->getPost("service");
        if($req->getPost("service_address")!=null) $campaignData["service_address"] = $req->getPost("service_address");
        if($req->getPost("service_due_date")!=null) $campaignData["service_due_date"] = $req->getPost("service_due_date");
        if($req->getPost("contact_name")!=null) $campaignData["contact_name"] = $req->getPost("contact_name");
        if($req->getPost("contact_number")!=null) $campaignData["contact_number"] = $req->getPost("contact_number");
        if($receipt_payment!=null) $campaignData["receipt_payment"] = $receipt_payment;
        if($req->getPost("process_admin")!=null) $campaignData["status"] = "process_admin";
        if($req->getPost("process_admin_pay")!=null) $campaignData["status"] = "process_admin_pay";
        if($req->getPost("paid")!=null) $campaignData["payment_status"] = "paid";

        $data = $this->CampaignModel->amend($campaign,$campaignData);

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
        $quantity = $req->getPost("quantity");
        $this->CampaignModel->destroy_brands_user($campaign,$user["iduser"]);
        if(is_array($brands) && is_array($length) && is_array($width) && is_array($weight) && is_array($variant) && is_array($quantity)){
            foreach($brands as $k => $v){
                if($v=="") continue;
                $this->CampaignModel->store_brand([
                    "idcampaign" => $campaign,
                    "idbrand" => $v,
                    "variant" => $variant[$k],
                    "length" => $length[$k],
                    "width" => $width[$k],
                    "weight" => $weight[$k],
                    "quantity" => $quantity[$k],
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

    public function wait_confirm()
    {
        $this->validate_session($this->validation->payment);

        $id = $this->request->getPost("key");

        $data = $this->_data($id);
        if($data==null) return $this->respond( tempResponse("00104") );

        $msg = "Can not proceed to payment.";
        if($data->campaign->name==null) return $this->respond( tempResponse("00104",false,"$msg Campaign Name is required") );
        if( !($data->brands!=null && count($data->brands)>0) ) return $this->respond( tempResponse("00104",false,"$msg Brands is required") );
        if($data->campaign->status!="draft") return $this->respond( tempResponse("00104") );
        if($data->campaign->box_type==null) return $this->respond( tempResponse("00104",false,"$msg Package is required") );
        if($data->campaign->start_date==null) return $this->respond( tempResponse("00104",false,"$msg Distribution Date is required") );
        if($data->campaign->end_date==null) return $this->respond( tempResponse("00104",false,"$msg Distribution Date is required") );
        if(strtotime($data->campaign->start_date)>strtotime($data->campaign->end_date)) return $this->respond( tempResponse("00104",false,"$msg Invalid Distribution Date") );
        if($data->campaign->idarea==null) return $this->respond( tempResponse("00104",false,"$msg Area Distribution is required") );
        if($data->campaign->theme==null) return $this->respond( tempResponse("00104",false,"$msg Theme is required") );
        if($data->campaign->size==null) return $this->respond( tempResponse("00104",false,"$msg Box Size is required") );

        $campaign = $this->CampaignModel->amend($id, [
            "status" => "wait_confirm",
            "payment_status" => "unpaid",
            "updatedat" => date("Y-m-d H:i:s"),
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

    public function reject()
    {
        $this->validate_session($this->validation->draft);

        $campaign = $this->CampaignModel->amend($this->request->getPost("key"), [
            "status" => "reject",
            "payment_status" => "unpaid",
            "updatedat" => date("Y-m-d H:i:s"),
        ]);

        if($campaign==false) return $this->respond( tempResponse("00003") );

        return $this->respond( tempResponse("00000") );
    }

    public function confirm()
    {
        $this->validate_session($this->validation->draft);

        $campaign = $this->CampaignModel->amend($this->request->getPost("key"), [
            "status" => "on_going",
            "updatedat" => date("Y-m-d H:i:s"),
        ]);

        if($campaign==false) return $this->respond( tempResponse("00003") );

        return $this->respond( tempResponse("00000") );
    }

    public function nego()
    {
        $this->validate_session($this->validation->draft);

        $campaign = $this->CampaignModel->amend($this->request->getPost("key"), [
            "status" => "on_nego",
            "updatedat" => date("Y-m-d H:i:s"),
        ]);

        if($campaign==false) return $this->respond( tempResponse("00003") );

        return $this->respond( tempResponse("00000") );
    }

    public function wait_pay()
    {
        $this->validate_session($this->validation->wait_pay);
        
        $price = (object)[
            "mix" => 4500,
            "brand" => (object)[
                "small" => 21500,
                "medium" => 23500,
                "large" => 25500,
            ],
        ];

        $key = $this->request->getPost("key");
        $price_service = $this->request->getPost("price_service");
        $price_box_design = $this->request->getPost("price_box_design");
        $price_event = $this->request->getPost("price_event");
        $price_digital_marketing = $this->request->getPost("price_digital_marketing");
        $price_merchandise = $this->request->getPost("price_merchandise");
        $date = date_create(date("Y-m-d H:i:s"));
        date_add($date,date_interval_create_from_date_string("1 days"));

        $data = $this->_data($key);
        $price_brands = 0;
        foreach($data->brands as $k => $v){
            $qty = $v["quantity"];
            $box = $data->campaign->box_type;
            $size = $data->campaign->size;
            $price_brands += $box=="mix"
                ? $qty / 10 * $price->mix
                : $qty / 10 * $price->brand->{$size};
        }

        $total = $price_service + $price_box_design + $price_event + $price_digital_marketing + $price_merchandise + $price_brands;

        $campaign = $this->CampaignModel->amend($key, [
            "status" => "wait_pay",
            "payment_due_date" => date_format($date,"Y-m-d H:i:s"),
            "payment_price_service" => $price_service,
            "payment_price_box_design" => $price_box_design,
            "payment_price_event" => $price_event,
            "payment_price_digital_marketing" => $price_digital_marketing,
            "payment_price_merchandise" => $price_merchandise,
            "payment_total" => $total,
            "updatedat" => date("Y-m-d H:i:s"),
        ]);

        if($campaign==false) return $this->respond( tempResponse("00003") );

        return $this->respond( tempResponse("00000",$key) );
    }

    public function join()
    {
        $this->validate_session($this->validation->join);
        
        $req = $this->request;
        $campaign = $req->getPost("key");

        $brands = $req->getPost("brands");
        $length = $req->getPost("length");
        $width = $req->getPost("width");
        $weight = $req->getPost("weight");
        $variant = $req->getPost("variant");
        $quantity = $req->getPost("quantity");
        
        if(is_array($brands) && is_array($length) && is_array($width) && is_array($weight) && is_array($variant) && is_array($quantity)){
            foreach($brands as $k => $v){
                if($v=="") continue;
                $this->CampaignModel->store_brand([
                    "idcampaign" => $campaign,
                    "idbrand" => $v,
                    "variant" => $variant[$k],
                    "length" => $length[$k],
                    "width" => $width[$k],
                    "weight" => $weight[$k],
                    "quantity" => $quantity[$k],
                ]);
            }

            return $this->respond( tempResponse("00000", $campaign) );
        }

        return $this->respond( tempResponse("00104", false, "Invalid data") );
    }

    public function join_sampler()
    {
        $user = $this->validate_session($this->validation->join_sampler);
        
        $req = $this->request;

        $data = $this->CampaignModel->store_sampler([
            "idcampaign" => $req->getPost("key"),
            "iduser" => $user["iduser"],
            "status_campaign" => "check",
            "status_box" => NULL,
        ]);

        if($data==false) return $this->respond( tempResponse("00104", false, "Invalid data") );
        
        return $this->respond( tempResponse("00000", true) );
    }

    public function joined()
    {
        $this->validate_session($this->validation->draft);
        $users = [];
        $user = $this->request->getPost("user");

        if( is_array($user) ) $users = $user;
        else $users[] = $user;

        foreach($users as $user){
            $campaign = $this->CampaignModel->amend_sampler(
                $this->request->getPost("key"),
                $user,
                [ "status_campaign" => "joined", "status_box" => "prepare", ]
            );

            if($campaign==false) return $this->respond( tempResponse("00003") );
        }

        return $this->respond( tempResponse("00000") );
    }

    public function rejected()
    {
        $this->validate_session($this->validation->draft);

        $campaign = $this->CampaignModel->amend_sampler(
            $this->request->getPost("key"),
            $this->request->getPost("user"),
            [ "status_campaign" => "rejected", "status_box" => NULL, ]
        );

        if($campaign==false) return $this->respond( tempResponse("00003") );

        return $this->respond( tempResponse("00000") );
    }

    public function otw()
    {
        $this->validate_session($this->validation->draft);

        $campaign = $this->CampaignModel->amend_sampler(
            $this->request->getPost("key"),
            $this->request->getPost("user"),
            [ "status_campaign" => "joined", "status_box" => "otw", ]
        );

        if($campaign==false) return $this->respond( tempResponse("00003") );

        return $this->respond( tempResponse("00000") );
    }

    public function not_received()
    {
        $user = $this->validate_session($this->validation->draft);

        $campaign = $this->CampaignModel->amend_sampler(
            $this->request->getPost("key"),
            $user["iduser"],
            [ "status_box" => "not_received", ]
        );

        if($campaign==false) return $this->respond( tempResponse("00003") );

        return $this->respond( tempResponse("00000") );
    }
 
}