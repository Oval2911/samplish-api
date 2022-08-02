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
        $data = $this->dbCanvazer->table('brand')
            ->select($columns)
            ->join("brand_category","brand_category.idcategorybrand = brand.idcategorybrand");

        $total = $this->dbCanvazer->table('brand')
            ->select("COUNT(brand.idbrand) as amount")
            ->join("brand_category","brand_category.idcategorybrand = brand.idcategorybrand");

        if ($filters['search']!=null) {
            foreach($filters["searchable"] as $v){
                $data->orLike($v,$filters['search']);
                $total->orLike($v,$filters['search']);
            }
        }

        $data->where("brand.iduser",$filters["user"]);

        $data->limit($filters['limit']['n_item'], $filters['limit']['page'] * $filters['limit']['n_item']);

        if (is_array($filters['order'])) $data->orderBy($filters['order']['column'], $filters['order']['direction']);

        $total = $total->get()->getResultArray()[0]['amount'];

        return (object)[
            "data" => $data->get()->getResultArray(),
            "total" => $total,
            "total_pages" => round($total / $filters['limit']['n_item']),
        ];
    }
    
    public function store($data)
    {
        $id = uniqid();
        $builder = $this->dbCanvazer->table('brand');
        $builder->set('idbrand', $id);
        $builder->set('idcategorybrand', $data["idcategorybrand"]);
        $builder->set('iduser', $data["iduser"]);
        $builder->set('name', $data["name"]);
        $builder->set('variant', $data["variant"]);
        $builder->set('mission', $data["mission"]);
        $builder->set('targetmarket', $data["targetmarket"]);
        $builder->set('desc', $data["desc"]);
        $builder->insert($data);
        return $this->dbCanvazer->affectedRows() ? $id : false;
    }

    public function update_user($data, $filter)
    {
        $builder = $this->dbCanvazer->table('user');
        $builder->set($data);
        $builder->where($filter);
        $builder->update();
        // echo $builder->last_query();

        $n_affected_rows = $this->dbCanvazer->affectedRows();
        return $n_affected_rows;
    }
}
