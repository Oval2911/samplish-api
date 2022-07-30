<?php

namespace App\Models;

use CodeIgniter\Model;

class Notif_model extends Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(array('url'));
        $this->load->helper('date');
        $this->load->helper('string');
    }

    public function get_notification($columns = array('*'), $filter = array())
    {

        $this->db->select($columns);

        if (isset($filter['filter'])) {
            $this->db->where($filter['filter']);
        }

        if (isset($filter['filterLike'])) {
            $this->db->like($filter['filterLike']);
        }

        if (isset($filter['filternotin'])) {
            $this->db->where_not_in('modul', $filter['filternotin']['modul']);
        }
        if (isset($filter['limit'])) {
            $this->db->limit($filter['limit']['n_item'], $filter['limit']['page'] * $filter['limit']['n_item']);
        }

        if (isset($filter['sort'])) {
            foreach ($filter['sort'] as $key => $value) {
                $this->db->order_by($key, $value);
            }
        }

        $query = $this->db->get('notification');
        // echo $this->db->last_query();
        if ($this->db->affected_rows() > 0) {
            return $query->result_array();
        }

        return null;
    }

    public function get_notificationUser($columns = array('*'), $filter = array())
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

        $query = $this->db->get('notification_user');
        // echo $this->db->last_query();
        if ($this->db->affected_rows() > 0) {
            return $query->result_array();
        }

        return null;
    }

    public function get_userNotificationConfig($columns = array('*'), $filter = array())
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

        $query = $this->db->get('user_notification_config');
        // echo $this->db->last_query();
        if ($this->db->affected_rows() > 0) {
            return $query->result_array();
        }

        return null;
    }

    public function insert_notification($data)
    {
        $this->db->set('ts_created', 'NOW()', false);
        $this->db->insert('notification', $data);
        // echo $this->db->last_query();
        return $this->db->insert_id();
    }

    public function insert_userNotificationConfig($data)
    {
        // $this->db->set('ts_created', 'NOW()', false);
        $this->db->insert('user_notification_config', $data);
        // echo $this->db->last_query();
        return $this->db->insert_id();
    }

    public function insert_notificationuser($data)
    {
        // $this->db->set('ts_created', 'NOW()', false);
        $this->db->insert('notification_user', $data);
        // echo $this->db->last_query();
        return $this->db->insert_id();
    }

    public function update_notificationUser($data, $filter)
    {
        $this->db->set($data);
        $this->db->where($filter);
        $this->db->update('notification_user');
        // echo $this->db->last_query();

        $n_affected_rows = $this->db->affected_rows();
        return $n_affected_rows;
    }

    public function update_notification($data, $filter)
    {
        $this->db->set($data);
        $this->db->where($filter);
        $this->db->update('notification');
        // echo $this->db->last_query();

        $n_affected_rows = $this->db->affected_rows();
        return $n_affected_rows;
    }

}
