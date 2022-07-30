<?php namespace App\Models;

use CodeIgniter\Model;

class SamplersModel extends Model
{

    protected $dbCanvazer;

    public function __construct()
    {
        parent::__construct();

        $this->dbCanvazer = db_connect();
        // $this->dbCommerce = db_connect("ecommerce");

        helper("text");

    }

    public function get_sampler($columns = array('*'), $filter = array())
    {

        $builder = $this->dbCanvazer->table('usampler');
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
                $builder->order_by($key, $value);
            }
        }
        $query = $builder->get();
//                                    echo $builder->last_query();
        //                            echo '<br>';
        if ($query->getResultArray() > 0) {
            $result = $query->getResultArray();
            return $result;
        } else {
            return false;
        }
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
