<?php

namespace App\Models;

use CodeIgniter\Model;

class User_model extends Model
{

    public function __construct()
    {
        parent::__construct();

        $this->dbcanvazer = db_connect();
        helper('url', 'date', 'string');
        
    }

    public function logout($userid, $access_token)
    {

        $builder->set('status', 1);
        $builder->where('access_token', $access_token);
        $builder->where('iduser', $userid);
        $builder->update('user_access_login_session');
        $n_affected_rows = $this->dbcanvazer->affectedRows();

        // echo $builder->last_query();
        return $n_affected_rows;
    }

    public function login_bca($gt)
    {

        $res = [];
        $access_token = $this->create_bca_session($gt);

        $res['access_token'] = $access_token;
        return $res;

    }
    public function create_bca_session($gt)
    {

        $builder->where('status=0');
        $data = array(
            'status' => 1, // 1: session not active
        );
        $builder->update('bca_session', $data);
        $n_affected_rows = $this->dbcanvazer->affectedRows();

        $access_token = random_string('alnum', 12);
        $data_session = array(
            // 'iduser' => $userid,
            'access_token' => $access_token,
            'grant_type' => $gt,
            'status' => '0',
        );
        $builder->set('ts_created', 'NOW()', false);
        // $builder->set('ts_last_activity', 'NOW()', false);
        $builder->set('ts_expired', 'DATE_ADD(NOW(),INTERVAL 3600 second)', false);
        // $builder->set('fcm_id', $fcmid);
        $builder->insert('bca_session', $data_session);
        // echo $builder->last_query();
        return $access_token;

    }

// [START_GET_FUNCTIONS]

    public function get_user_role($iduser)
    {

        $col = array('role');
        $filter['filter'] = array('iduser' => $iduser);
        $res = $this->get_user($col, $filter);
        if ($res != null) {
            return $res[0]['role'];
        }

        return null;

    }

    public function get_user($columns = array('*'), $filter = array())
    {

        $builder = $this->dbcanvazer->table('user');
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
        $result = $query->getResultArray();
        // echo $builder->last_query();
        if ($result) {
            return $result;
        }

        return null;
    }

    public function get_userconnection($columns = array('*'), $filter = array())
    {

        $builder->select($columns);

        if (isset($filter['filter'])) {
            $builder->where($filter['filter']);
        }

        if (isset($filter['filternotin'])) {
            $builder->where_not_in('iduser_friend', $filter['filternotin']['iduser_friend']);
        }

        if (isset($filter['limit'])) {
            $builder->limit($filter['limit']['n_item'], $filter['limit']['page'] * $filter['limit']['n_item']);
        }

        if (isset($filter['sort'])) {
            foreach ($filter['sort'] as $key => $value) {
                $builder->order_by($key, $value);
            }
        }

        $builder->join('user', 'user.iduser=user_connection.iduser_friend', 'left');

        $query = $builder->get('user_connection');
        // echo $builder->last_query();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }

        return null;
    }

    public function get_userblock($columns = array('*'), $filter = array())
    {

        $builder->select($columns);

        if (isset($filter['filter'])) {
            $builder->where($filter['filter']);
        }

        if (isset($filter['limit'])) {
            $builder->limit($filter['limit']['n_item'], $filter['limit']['page'] * $filter['limit']['n_item']);
        }

        if (isset($filter['sort'])) {
            foreach ($filter['sort'] as $key => $value) {
                $builder->order_by($key, $value);
            }
        }

        $builder->join('user', 'user.iduser=user_block_user.iduser_block', 'left');

        $query = $builder->get('user_block_user');
        // echo $builder->last_query();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }

        return null;
    }

    public function get_userconnectionrequest($columns = array('*'), $filter = array())
    {

        $builder->select($columns);

        if (isset($filter['filter'])) {
            $builder->where($filter['filter']);
        }

        if (isset($filter['filternotin'])) {
            $builder->where_not_in('iduser_requester', $filter['filternotin']['iduser_requester']);
        }

        if (isset($filter['limit'])) {
            $builder->limit($filter['limit']['n_item'], $filter['limit']['page'] * $filter['limit']['n_item']);
        }

        if (isset($filter['sort'])) {
            foreach ($filter['sort'] as $key => $value) {
                $builder->order_by($key, $value);
            }
        }

        $builder->join('user', 'user.iduser=user_connection_request.iduser_requester', 'left');
        $query = $builder->get('user_connection_request');
        // echo $builder->last_query();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }

        return null;
    }

    public function get_user_access_login_session($columns = array('*'), $filter = array())
    {
        $builder->select($columns);

        if (isset($filter['filter'])) {
            $builder->where($filter['filter']);
        }

        if (isset($filter['limit'])) {
            $builder->limit($filter['limit']['n_item'], $filter['limit']['page'] * $filter['limit']['n_item']);
        }

        if (isset($filter['sort'])) {
            foreach ($filter['sort'] as $key => $value) {
                $builder->order_by($key, $value);
            }
        }

        $query = $builder->get('user_access_login_session');
        //echo $builder->last_query();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }

        return null;
    }

    public function get_user_report_user($columns = array('*'), $filter = array())
    {
        $builder->select($columns);

        if (isset($filter['filter'])) {
            $builder->where($filter['filter']);
        }

        if (isset($filter['limit'])) {
            $builder->limit($filter['limit']['n_item'], $filter['limit']['page'] * $filter['limit']['n_item']);
        }

        if (isset($filter['sort'])) {
            foreach ($filter['sort'] as $key => $value) {
                $builder->order_by($key, $value);
            }
        }

        $query = $builder->get('user_report_user');
        //echo $builder->last_query();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }

        return null;
    }

    public function get_agent_login_session($columns, $filter)
    {
        $builder->select($columns);

        if (isset($filter['filter'])) {
            $builder->where($filter['filter']);
        }
        if (isset($filter['filterIn'])) {
            $builder->where_in('iduser', $filter['filterIn']['iduser']);
        }
        if (isset($filter['limit'])) {
            $builder->limit($filter['limit']['n_item'], $filter['limit']['page'] * $filter['limit']['n_item']);
        }
        if (isset($filter['sort'])) {
            foreach ($filter['sort'] as $key => $value) {
                $builder->order_by($key, $value);
            }
        }

        if (isset($filter['group'])) {
            // foreach ($filter['group'] as $key => $value) {
            $builder->group_by($filter['group']);
            // }
        }

        // $builder->where('fcmid is NOT NULL', null, false);
        $query = $builder->get('user_access_login_session');

        // echo $builder->last_query();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }

        return null;
    }
// [END_GET_FUNCTIONS]

// [START_UPDATE_FUNCTIONS]

    public function update_access_log_user_request($data, $filter)
    {
        $builder->set($data);
        $builder->where($filter);
        $builder->update('user_access_log_request');

        $n_affected_rows = $this->dbcanvazer->affectedRows();
        return $n_affected_rows;
    }

    public function update_access_log_user_response($data, $filter)
    {
        $builder->set($data);
        $builder->where($filter);
        $builder->update('user_access_log_response');

        $n_affected_rows = $this->dbcanvazer->affectedRows();
        return $n_affected_rows;
    }

    public function update_user_access_login_session($iduser, $access_token, $platform = null, $fcm = null)
    {

        $builder = $this->dbcanvazer->table('user_access_login_session');
        $builder->where('iduser', $iduser);
        $builder->where('access_token', $access_token);
        if (isset($platform)) {
            $builder->where('platform', $platform);
        }
        $builder->where('status', '0');

        if (isset($fcm)) {
            $builder->set($fcm);
        }
        $builder->set('ts_last_activity', 'NOW()', false);
        $builder->set('ts_expired', 'DATE_ADD(NOW(), INTERVAL 30 DAY)', false);
        $builder->update();

        // echo $builder->last_query();

        $n_affected_rows = $this->dbcanvazer->affectedRows();

        if ($n_affected_rows > 0) {
            // if have accesss

            return $n_affected_rows;
        } else {

            // don't have access
            $builder->select('*');
            $builder->where('iduser', $iduser);
            $builder->where('access_token', $access_token);
            if (isset($platform)) {
                $builder->where('platform', $platform);
            }
            $builder->where('status', '0');
            $query = $builder->get();

            if ($query->getResultArray() > 0) {
                //return $this->response('00000', NULL);
                return $query->getResultArray();
            }

            //return $this->response('00102', NULL);
            return $n_affected_rows;
        }
    }

    public function update_user($data, $filter)
    {
        $builder = $this->dbcanvazer->table('user');
        $builder->set($data);
        $builder->where($filter);
        $builder->update();
        // echo $builder->last_query();

        $n_affected_rows = $this->dbcanvazer->affectedRows();
        return $n_affected_rows;
    }

    public function update_user_block_user($data, $filter)
    {
        $builder->set($data);
        $builder->where($filter);
        $builder->update('user_block_user');
        // echo $builder->last_query();

        $n_affected_rows = $this->dbcanvazer->affectedRows();
        return $n_affected_rows;
    }

    public function update_userConnectionRequest($data, $filter)
    {
        $builder->set($data);
        $builder->where($filter);
        $builder->update('user_connection_request');
        // echo $builder->last_query();

        $n_affected_rows = $this->dbcanvazer->affectedRows();
        return $n_affected_rows;
    }

    public function update_userNotificationConfig($data, $filter)
    {
        $builder = $this->dbcanvazer->table('user_access_log_request');
        $builder->set($data);
        $builder->where($filter);
        $builder->update('user_notification_config');
        // echo $builder->last_query();

        $n_affected_rows = $this->dbcanvazer->affectedRows();
        return $n_affected_rows;
    }
// [END_UPDATE_FUNCTIONS]

// [START_INSERT_FUNCTIONS]

    public function insert_user_access_log_request($data)
    {
        $builder = $this->dbcanvazer->table('user_access_log_request');
        $builder->set('ts_access', 'NOW()', false);
        $builder->insert($data);

        return $builder->insertID();
    }

    public function insert_user_access_log_response($data)
    {
        $builder->set('ts_response', 'NOW()', false);
        $builder->insert('user_access_log_response', $data);

        // echo $builder->last_query();
        return $builder->insertID();
    }

    public function insert_user($data)
    {
        $builder = $this->dbcanvazer->table('user');
        $builder->set('ts_registration', 'NOW()', false);
        $builder->insert($data);
        // echo $builder->last_query();
        return $this->dbcanvazer->insertID();
    }

    public function insert_user_access_login_session($iduser, $platform, $fcm_id)
    {
        $this->load->helper('string');

        $builder->where('iduser', $iduser);
        $builder->where('platform', $platform);
        $builder->where('status', 0);

        $data = array(
            'status' => 1, // 1: session not active
        );
        $builder->update('user_access_login_session', $data);
        $n_affected_rows = $this->dbcanvazer->affectedRows();

        $access_token = random_string('alnum', 50);
        $data_session = array(
            'iduser' => $iduser,
            'access_token' => $access_token,
            'platform' => $platform,
            'status' => '0',
        );
        $builder->set('ts_created', 'NOW()', false);
        $builder->set('ts_last_activity', 'NOW()', false);
        $builder->set('ts_expired', 'DATE_ADD(NOW(), INTERVAL 30 DAY)', false);
        $builder->set('fcm_id', $fcm_id);
        $builder->insert('user_access_login_session', $data_session);

        return $access_token;
    }

    public function insert_user_sampler($data)
    {        
        $builder = $this->dbcanvazer->table('usampler');
        $builder->insert($data);
        // echo $builder->last_query();
        return $this->dbcanvazer->insertID();
    }
    
    public function insert_user_company($data)
    {
        $builder = $this->dbcanvazer->table('ucompany');
        $builder->insert($data);
        // echo $builder->last_query();
        return $this->dbcanvazer->insertID();
    }

// [END_INSERT_FUNCTIONS]

// [START_DELETE_FUNCTIONS]

    public function delete_user_block_user($filter)
    {
        $builder->where($filter);
        $builder->delete('user_block_user');
        // echo $builder->last_query();

        $n_affected_rows = $this->dbcanvazer->affectedRows();
        return $n_affected_rows;
    }

    public function delete_user_connection($filter)
    {
        $builder->where($filter);
        $builder->delete('user_connection');
        // echo $builder->last_query();

        $n_affected_rows = $this->dbcanvazer->affectedRows();
        return $n_affected_rows;
    }

    public function delete_user_connection_request($filter)
    {
        $builder->where($filter);
        $builder->delete('user_connection_request');
        // echo $builder->last_query();

        $n_affected_rows = $this->dbcanvazer->affectedRows();
        return $n_affected_rows;
    }

// [END_DELETE_FUNCTIONS]

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
    
    public function data($role)
    {        
        return $this->dbcanvazer
            ->table("user AS u")
            ->join("user_profile AS p","p.iduser = u.iduser")
            ->where("u.related_key",$role)
            ->select(["u.fullname as company", "p.name", "p.birthdate", "p.gender", "p.phone",])
            ->get()
            ->getResultArray();
    }

}
