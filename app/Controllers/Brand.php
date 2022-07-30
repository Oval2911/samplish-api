<?php namespace App\Controllers;
 
use CodeIgniter\RESTful\ResourceController;
use App\Models\BrandModel;
 
class Brand extends ResourceController
{
    private $_exec_time_start;

    public function __construct()
    {
        $this->request = \Config\Services::request();
        
        $this->brand  = new BrandModel();

        helper(['custom', 'rsCode']);
        
        $this->_exec_time_start = microtime(true);
        setlocale(LC_MONETARY, 'en_GB');
        date_default_timezone_set('Asia/Jakarta');
    }

    public function datatable()
    {
        $filters = array(
            "limit" => $this->request->getGet("limit"),
            "order" => $this->request->getGet("order"),
            "search" => $this->request->getGet("search"),
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

        $data = $this->brand->datatable($fields, $filters);

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
 
}