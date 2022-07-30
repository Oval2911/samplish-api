<?php namespace App\Models;

use CodeIgniter\Model;

class BrandModel extends Model
{

    protected $dbCanvazer;

    public function __construct()
    {
        parent::__construct();

        $this->dbCanvazer = db_connect();
        // $this->dbCommerce = db_connect("ecommerce");

        helper("text");

    }

    public function datatable($columns = array('*'), $filter = array())
    {
        $data = $this->dbCanvazer->table('brand');
        $data->select($columns)
            ->join("brand_category","brand_category.idcategorybrand = brand.idcategorybrand");

        $total = $this->dbCanvazer->table('brand');
        $total->select("COUNT(idbrand) as amount");

        if (isset($filter['filter'])) {
            $data->where($filter['filter']);
            $total->where($filter['filter']);
        }
        if (isset($filter['filternot'])) {
            $data->where($filter['filternot']);
            $total->where($filter['filternot']);
        }
        if (isset($filter['filterLike'])) {
            $data->like($filter['filterLike']);
            $total->like($filter['filterLike']);
        }

        $data->limit($filter['limit']['n_item'], $filter['limit']['page'] * $filter['limit']['n_item']);

        if (isset($filter['sort'])) {
            foreach ($filter['sort'] as $key => $value) {
                $data->orderBy($key, $value);
            }
        }

        $total = $total->get()->getResultArray()[0]['amount'];

        return (object)[
            "data" => $data->get()->getResultArray(),
            "total" => $total,
            "total_pages" => round($total / $filter['limit']['n_item']),
        ];
    }
}
