<?php namespace App\Models;

use CodeIgniter\Model;

class AreaModel extends Model
{

    protected $dbCanvazer;

    public function __construct()
    {
        parent::__construct();

        $this->dbCanvazer = db_connect();
        // $this->dbCommerce = db_connect("ecommerce");

        helper("text");

    }

    public function get_category($columns = array('*'), $filter = array())
    {

        $builder = $this->dbCanvazer->table('area');
        $builder->select($columns);

        if (isset($filter['filter'])) {
            $builder->where($filter['filter']);
        }
        if (isset($filter['filternot'])) {
            $builder->where($filter['filternot']);
        }
        if (isset($filter['filterLike'])) {
            $builder->like($filter['filterLike']);
        }

        if (isset($filter['limit'])) {
            $builder->limit($filter['limit']['n_item'], $filter['limit']['page'] * $filter['limit']['n_item']);
        }

        if (isset($filter['sort'])) {
            foreach ($filter['sort'] as $key => $value) {
                $builder->orderBy($key, $value);
            }
        }

        $query = $builder->get();
        $result = $query->getResultArray();
        if ($result) {
            return $result;
        }

        return null;
    }

    public function datatable($columns = ['*'], $filters = [])
    {
        $data = $this->dbCanvazer->table('area')
            ->select($columns);

        $total = $this->dbCanvazer->table('area')
            ->select("COUNT(idarea) as amount");

        if ($filters['search']!=null) {
            foreach($filters["searchable"] as $k => $v){
                if($k==0){
                    $data->like($v,$filters['search']);
                    $total->like($v,$filters['search']);
                }else{
                    $data->orLike($v,$filters['search']);
                    $total->orLike($v,$filters['search']);
                }
            }
        }

        $data->limit($filters['limit']['n_item'], $filters['limit']['page'] * $filters['limit']['n_item']);

        if (
            is_array($filters['order'])
            && array_key_exists("column",$filters['order'])
            && array_key_exists("direction",$filters['order'])
        ) $data->orderBy($filters['order']['column'], $filters['order']['direction']);

        $total = $total->get()->getResultArray()[0]['amount'];

        return (object)[
            "data" => $data->get()->getResultArray(),
            "total" => $total,
            "total_pages" => round($total / $filters['limit']['n_item']),
        ];
    }
    
    public function store($data)
    {
        $data["idarea"] = uniqid();
        $this->dbCanvazer->table('area')->insert($data);
        return $this->dbCanvazer->affectedRows() ? $data["idarea"] : false;
    }

    public function amend($id, $data)
    {
        $this->dbCanvazer->table('area')->where("idarea",$id)->update($data);
        return $this->dbCanvazer->affectedRows() ? $id : false;
    }
    
    public function destroy($id)
    {
        $this->dbCanvazer->table('area')->delete(["idarea",$id]);
        return $this->dbCanvazer->affectedRows() ? true : false;
    }
}
