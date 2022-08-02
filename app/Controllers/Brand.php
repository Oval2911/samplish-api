<?php namespace App\Controllers;
 
use CodeIgniter\RESTful\ResourceController;
use App\Models\BrandModel;
use App\Models\User_model;
use CodeIgniter\HTTP\ResponseInterface;
 
class Brand extends ResourceController
{
    private $_exec_time_start;

    public function __construct()
    {
        $this->request = \Config\Services::request();
        
        $this->BrandModel  = new BrandModel();
        $this->User_model  = new User_model();

        helper(['custom', 'rsCode']);
        
        $this->_exec_time_start = microtime(true);
        setlocale(LC_MONETARY, 'en_GB');
        date_default_timezone_set('Asia/Jakarta');
    }

    public function datatable()
    {
        if(!isExist("get","u")) return $this->respond( tempResponse("00104") );
        if(!isExist("get","limit")) return $this->respond( tempResponse("00104") );
        if(!isExist("get","token")) return $this->respond( tempResponse("00104") );

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

    public function save()
    {
        if(!isExist("post","u")) return $this->respond( tempResponse("00104") );
        if(!isExist("post","token")) return $this->respond( tempResponse("00104") );
        if(!isExist("post","category")) return $this->respond( tempResponse("00104") );
        if(!isExist("post","name")) return $this->respond( tempResponse("00104") );

        $user = $this->User_model->get_user(array('iduser'), array("filter" => array('related_id' => $this->request->getPost("u"))));
        if ($user==null) return $this->respond( tempResponse("00102") );
        $user = $user[0];

        $access = $this->User_model->update_user_access_login_session(
            $this->request->getPost("u"),
            $this->request->getPost("token")
        );
        if ($access == 0) return $this->respond( tempResponse("00102") );

        $data = $this->BrandModel->store(array(
            "idcategorybrand" => $this->request->getPost("category"),
            "iduser" => $user["iduser"],
            "name" => $this->request->getPost("name"),
            "variant" => $this->request->getPost("variant"),
            "mission" => $this->request->getPost("mission"),
            "targetmarket" => $this->request->getPost("market"),
            "desc" => $this->request->getPost("desc"),
        ));

        return $data;

        // return $this->respond(
        //     tempResponse(
        //         '00000',
        //         array(
        //             'page' => $filters["limit"]["page"],
        //             'per_page' => $filters["limit"]["n_item"],
        //             'total' => $data->total,
        //             'total_pages' => $data->total_pages,
        //             'records' => $data->data,
        //         )
        //     )
        // );
    }
 
}