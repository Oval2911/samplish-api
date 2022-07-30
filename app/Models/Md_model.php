<?php

namespace App\Models;

use CodeIgniter\Model;

class Md_model extends Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(array('url'));
        $this->load->helper('date');
        $this->load->helper('string');
    }

    public function get_country($columns = array('*'), $filter = array())
    {

        $this->db->select($columns);

        if (isset($filter['filter'])) {
            $this->db->where($filter['filter']);
        }

        if (isset($filter['filterLike'])) {
            $this->db->like($filter['filterLike']);
        }

        if (isset($filter['limit'])) {
            $this->db->limit($filter['limit']['n_item'], $filter['limit']['page'] * $filter['limit']['n_item']);
        }

        if (isset($filter['sort'])) {
            foreach ($filter['sort'] as $key => $value) {
                $this->db->order_by($key, $value);
            }
        }

        $query = $this->db->get('md_country');
        // echo $this->db->last_query();
        if ($this->db->affected_rows() > 0) {
            return $query->result_array();
        }

        return null;
    }

    public function insert_country($data)
    {
        // $this->db->set('ts_created', 'NOW()', false);
        $this->db->insert('md_country', $data);
        // echo $this->db->last_query();
        return $this->db->insert_id();
    }

    public function get_state($columns = array('*'), $filter = array())
    {

        $this->db->select($columns);

        if (isset($filter['filter'])) {
            $this->db->where($filter['filter']);
        }

        if (isset($filter['filterLike'])) {
            $this->db->like($filter['filterLike']);
        }

        if (isset($filter['limit'])) {
            $this->db->limit($filter['limit']['n_item'], $filter['limit']['page'] * $filter['limit']['n_item']);
        }

        if (isset($filter['sort'])) {
            foreach ($filter['sort'] as $key => $value) {
                $this->db->order_by($key, $value);
            }
        }

        $this->db->order_by('name', 'ASC');
        $query = $this->db->get('md_state');
        if ($this->db->affected_rows() > 0) {
            return $query->result_array();
        }

        return null;
    }

    public function insert_state($data)
    {
        // $this->db->set('ts_created', 'NOW()', false);
        $this->db->insert('md_state', $data);
        // echo $this->db->last_query();
        return $this->db->insert_id();
    }

    public function get_city($columns = array('*'), $filter = array())
    {

        $this->db->select($columns);

        if (isset($filter['filter'])) {
            $this->db->where($filter['filter']);
        }

        if (isset($filter['filterLike'])) {
            $this->db->like($filter['filterLike']);
            // $this->db->or_where($filter['filterLike']);
        }

        if (isset($filter['limit'])) {
            $this->db->limit($filter['limit']['n_item'], $filter['limit']['page'] * $filter['limit']['n_item']);
        }

        if (isset($filter['sort'])) {
            foreach ($filter['sort'] as $key => $value) {
                $this->db->order_by($key, $value);
            }
        }
        // $this->db->join('md_state', 'md_state.idmd_state = md_city.idmd_state', 'inner');

        // $this->db->where('md_state.idmd_country = 1');
        $query = $this->db->get('md_city');

        // echo $this->db->last_query();
        if ($this->db->affected_rows() > 0) {
            return $query->result_array();
        }

        return null;
    }
    public function get_city_dest($columns = array('*'), $filter = array())
    {

        $this->db->select($columns);

        if (isset($filter['filter'])) {
            $this->db->where($filter['filter']);
        }

        if (isset($filter['filterLike'])) {
            $this->db->like($filter['filterLike']);
        }

        if (isset($filter['limit'])) {
            $this->db->limit($filter['limit']['n_item'], $filter['limit']['page'] * $filter['limit']['n_item']);
        }

        if (isset($filter['sort'])) {
            foreach ($filter['sort'] as $key => $value) {
                $this->db->order_by($key, $value);
            }
        }

        $this->db->join('md_state', 'md_state.idmd_state = md_city.idmd_state', 'inner');
        // $this->db->join('md_country', 'md_state.idmd_country = md_country.idmd_country', 'inner');

        $this->db->where('md_state.idmd_country = 1');
        $query = $this->db->get('md_city');

        // echo $this->db->last_query();
        if ($this->db->affected_rows() > 0) {
            return $query->result_array();
        }

        return null;
    }

    public function get_origin($columns = array('*'), $filter = array())
    {

        $this->db->select($columns);

        if (isset($filter['filter'])) {
            $this->db->where($filter['filter']);
        }

        if (isset($filter['filterLike'])) {
            $this->db->like($filter['filterLike']);
        }

        if (isset($filter['limit'])) {
            $this->db->limit($filter['limit']['n_item'], $filter['limit']['page'] * $filter['limit']['n_item']);
        }

        if (isset($filter['sort'])) {
            foreach ($filter['sort'] as $key => $value) {
                $this->db->order_by($key, $value);
            }
        }

        $query = $this->db->get('temp_origin');

        // echo $this->db->last_query();
        if ($this->db->affected_rows() > 0) {
            return $query->result_array();
        }

        return null;
    }

    public function get_district($columns = array('*'), $filter = array())
    {

        $this->db->select($columns);

        if (isset($filter['filter'])) {
            $this->db->where($filter['filter']);
        }

        if (isset($filter['filterLike'])) {
            $this->db->like($filter['filterLike']);
        }

        if (isset($filter['limit'])) {
            $this->db->limit($filter['limit']['n_item'], $filter['limit']['page'] * $filter['limit']['n_item']);
        }

        if (isset($filter['sort'])) {
            foreach ($filter['sort'] as $key => $value) {
                $this->db->order_by($key, $value);
            }
        }

        $query = $this->db->get('md_district');

        // echo $this->db->last_query();
        if ($this->db->affected_rows() > 0) {
            return $query->result_array();
        }

        return null;
    }

    public function get_subdistrict($columns = array('*'), $filter = array())
    {

        $this->db->select($columns);

        if (isset($filter['filter'])) {
            $this->db->where($filter['filter']);
        }

        if (isset($filter['filterLike'])) {
            $this->db->like($filter['filterLike']);
        }

        if (isset($filter['limit'])) {
            $this->db->limit($filter['limit']['n_item'], $filter['limit']['page'] * $filter['limit']['n_item']);
        }

        if (isset($filter['sort'])) {
            foreach ($filter['sort'] as $key => $value) {
                $this->db->order_by($key, $value);
            }
        }

        $query = $this->db->get('md_subdistrict');

        // echo $this->db->last_query();
        if ($this->db->affected_rows() > 0) {
            return $query->result_array();
        }

        return null;
    }

    public function get_branch($columns = array('*'), $filter = array())
    {

        $this->db->select($columns);

        if (isset($filter['filter'])) {
            $this->db->where($filter['filter']);
        }

        if (isset($filter['filterLike'])) {
            $this->db->like($filter['filterLike']);
        }

        if (isset($filter['limit'])) {
            $this->db->limit($filter['limit']['n_item'], $filter['limit']['page'] * $filter['limit']['n_item']);
        }

        if (isset($filter['sort'])) {
            foreach ($filter['sort'] as $key => $value) {
                $this->db->order_by($key, $value);
            }
        }

        $query = $this->db->get('branch');

        // echo $this->db->last_query();
        if ($this->db->affected_rows() > 0) {
            return $query->result_array();
        }

        return null;
    }

    public function insert_city($data)
    {
        // $this->db->set('ts_created', 'NOW()', false);
        $this->db->insert('md_city', $data);
        // echo $this->db->last_query();
        return $this->db->insert_id();
    }
    public function insert_branch($data)
    {
        // $this->db->set('ts_created', 'NOW()', false);
        $this->db->insert('branch', $data);
        // echo $this->db->last_query();
        return $this->db->insert_id();
    }
    public function insert_origin($data)
    {
        // $this->db->set('ts_created', 'NOW()', false);
        $this->db->insert('temp_origin', $data);
        // echo $this->db->last_query();
        return $this->db->insert_id();
    }
    public function insert_tempdistrict($data)
    {
        // $this->db->set('ts_created', 'NOW()', false);
        $this->db->insert('md_district', $data);
        // echo $this->db->last_query();
        return $this->db->insert_id();
    }
    public function insert_subdistrict($data)
    {
        // $this->db->set('ts_created', 'NOW()', false);
        $this->db->insert('md_subdistrict', $data);
        // echo $this->db->last_query();
        return $this->db->insert_id();
    }

    public function delete_city($filter)
    {
        $this->db->where($filter);
        $this->db->delete('md_city');
        // echo $this->db->last_query();

        $n_affected_rows = $this->db->affected_rows();
        return $n_affected_rows;
    }

    public function delete_state($filter)
    {
        $this->db->where($filter);
        $this->db->delete('md_state');
        // echo $this->db->last_query();

        $n_affected_rows = $this->db->affected_rows();
        return $n_affected_rows;
    }

    public function update_country($data, $filter)
    {
        $this->db->set($data);
        $this->db->where($filter);
        $this->db->update('md_country');
        // echo $this->db->last_query();

        $n_affected_rows = $this->db->affected_rows();
        return $n_affected_rows;
    }

    public function update_city($data, $filter)
    {
        $this->db->set($data);
        $this->db->where($filter);
        $this->db->update('md_city');
        // echo $this->db->last_query();

        $n_affected_rows = $this->db->affected_rows();
        return $n_affected_rows;
    }

    public function update_district($data, $filter)
    {
        $this->db->set($data);
        $this->db->where($filter);
        $this->db->update('md_district');
        echo $this->db->last_query();

        $n_affected_rows = $this->db->affected_rows();
        return $n_affected_rows;
    }

}
