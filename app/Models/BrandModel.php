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

    public function get($columns = array('*'), $filter = array())
    {

        $builder = $this->dbCanvazer->table('brand');
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
        if ($query->getResultArray() > 0) {
            $result = $query->getResultArray();
            return $result;
        } else {
            return false;
        }
    }

    public function datatable($columns = array('*'), $filter = array())
    {
        $data = $this->dbCanvazer->table('brand');
        $data->select($columns);

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

        // if (isset($filter['sort'])) {
        //     foreach ($filter['sort'] as $key => $value) {
        //         $data->orderBy($key, $value);
        //     }
        // }

        $total = $total->get()->getResultArray()[0]['amount'];

        return (object)[
            "data" => $data->get()->getResultArray(),
            "total" => $total,
            "total_pages" => round($total / $filter['limit']['n_item']),
        ];
    }

// [END_GET_FUNCTIONS]

// [START_UPDATE_FUNCTIONS]

    public function update_seller($data, $filter)
    {
        $builder->set($data);
        $builder->where($filter);
        $builder->update('t_user_seller');
        // echo $builder->last_query();

        $n_affected_rows = $builder->affected_rows();
        return $n_affected_rows;
    }

// [END_UPDATE_FUNCTIONS]

// [START_INSERT_FUNCTIONS]

    public function insert_seller($data)
    {
        $builder->set('ts_registration', 'NOW()', false);
        $builder->insert('t_user_seller', $data);
        // echo $builder->last_query();
        return $builder->insert_id();
    }


// [END_INSERT_FUNCTIONS]

// [START_DELETE_FUNCTIONS]

    public function delete_seller($filter)
    {
        $builder->where($filter);
        $builder->delete('t_user_seller');
        // echo $builder->last_query();

        $n_affected_rows = $builder->affected_rows();
        return $n_affected_rows;
    }


// [END_DELETE_FUNCTIONS]


}
