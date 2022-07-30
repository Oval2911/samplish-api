<?php namespace App\Models;

use CodeIgniter\Model;

class BrandsModel extends Model
{

    protected $db_commerce;

    public function __construct()
    {
        parent::__construct();

        $this->dbcanvazer = db_connect();
        $this->dbcommerce = db_connect("ecommerce");

    }

    public function get_brand($columns = array('*'), $filter = array())
    {

        $builder = $this->dbcanvazer->table('ucompany');
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

    public function update_buyer($data, $filter)
    {
        $this->db->set($data);
        $this->db->where($filter);
        $this->db->update('t_user_buyer');
        // echo $this->db->last_query();

        $n_affected_rows = $this->db->affected_rows();
        return $n_affected_rows;
    }

// [END_UPDATE_FUNCTIONS]

// [START_INSERT_FUNCTIONS]

    public function insert_buyer($data)
    {
        $this->db->set('ts_registration', 'NOW()', false);
        $this->db->insert('t_user_buyer', $data);
        // echo $this->db->last_query();
        return $this->db->insert_id();
    }


// [END_INSERT_FUNCTIONS]

// [START_DELETE_FUNCTIONS]

    public function delete_buyer($filter)
    {
        $this->db->where($filter);
        $this->db->delete('t_user_buyer');
        // echo $this->db->last_query();

        $n_affected_rows = $this->db->affected_rows();
        return $n_affected_rows;
    }


// [END_DELETE_FUNCTIONS]


}
