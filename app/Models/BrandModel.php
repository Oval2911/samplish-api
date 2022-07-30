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

    public function datatable($columns = array('*'), $filters = array())
    {
        $data = $this->dbCanvazer->table('brand');
        $data->select($columns)
            ->join("brand_category","brand_category.idcategorybrand = brand.idcategorybrand");

        $total = $this->dbCanvazer->table('brand');
        $total->select("COUNT(idbrand) as amount");

        if (isset($filters['filter'])) {
            $data->where($filters['filter']);
            $total->where($filters['filter']);
        }
        if (isset($filters['filternot'])) {
            $data->where($filters['filternot']);
            $total->where($filters['filternot']);
        }
        if (isset($filters['filterLike'])) {
            $data->like($filters['filterLike']);
            $total->like($filters['filterLike']);
        }

        $data->limit($filters['limit']['n_item'], $filters['limit']['page'] * $filters['limit']['n_item']);

        if (is_array($filters['order'])) $data->orderBy($filters['order']['column'], $filters['order']['direction']);

        $total = $total->get()->getResultArray()[0]['amount'];

        return (object)[
            "data" => $data->get()->getResultArray(),
            "total" => $total,
            "total_pages" => round($total / $filters['limit']['n_item']),
        ];
    }
}
