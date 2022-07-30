<?php

namespace App\Models;

use CodeIgniter\Model;

class Otp_model extends Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(array('url'));
        $this->load->helper('date');
        $this->load->helper('string');
    }
    public function get_login_otp($columns = array('*'), $filter = array())
    {

        $this->db->select($columns);

        if (isset($filter['filter'])) {
            foreach ($filter['filter'] as $k => $v) {
                if ($k == 'ts_expired') {
                    $this->db->where($k . ' > "' . $filter['filter']['ts_expired'] . '"');
                } else {
                    $this->db->where($k, $v);
                }
            }
        }

        if (isset($filter['limit'])) {
            $this->db->limit($filter['limit']['n_item'], $filter['limit']['page'] * $filter['limit']['n_item']);
        }

        if (isset($filter['sort'])) {
            foreach ($filter['sort'] as $key => $value) {
                $this->db->order_by($key, $value);
            }
        }

        $query = $this->db->get('otp');
        // echo $this->db->last_query();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }

        return null;
    }
    public function insert_login_otp($data)
    {
        $this->db->set('ts_created', 'NOW()', false);
        $this->db->insert('otp', $data);
        // echo $this->db->last_query();
        return $this->db->insert_id();
    }
    public function update_login_otp($data, $filter)
    {
        $this->db->set($data);
        $this->db->where($filter);
        $this->db->update('otp');

        // echo $this->db->last_query();
        $n_affected_rows = $this->db->affected_rows();
        return $n_affected_rows;
    }
// [OTHER_FUNCTIONS]
    public function uniqINT()
    {
        $digits_needed = 8;

        $random_number = ''; // set up a blank string

        $count = 0;

        while ($count < $digits_needed) {
            $random_digit = mt_rand(0, 9);

            $random_number .= $random_digit;
            $count++;
        }

        return $random_number;
    }

}
