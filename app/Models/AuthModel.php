<?php namespace App\Models;

use CodeIgniter\Model;
use App\Models\SamplersModel;
use App\Models\BrandsModel;

class AuthModel extends Model
{

    protected $db_commerce;

    public function __construct()
    {
        parent::__construct();

        $this->dbcanvazer = db_connect();
        // $this->dbcanvazer = db_connect("ecommerce");
        
        $this->samplersModel  = new SamplersModel();
        $this->brandsModel  = new BrandsModel();

    }

    public function login($filter, $platform, $fcmid, $role)
    {
        $builder = $this->dbcanvazer->table('user');
        $builder->select('*');
        $builder->where($filter);
        
        $query = $builder->get();
                                //    echo $builder->last_query();
                                //    echo '<br>';
        $result = $query->getResultArray();
        if ($result) {
            // return $result;
            
        // print_r($result);
            $user = array();
            if($result[0]['related_key'] == 'sampler'){
                $user = $this->samplersModel->get_sampler(array('*', 'idusampler as iduser'),array('filter'=>array('idusampler'=>$result[0]['related_id'])));
            }elseif($result[0]['related_key']  == 'company'){
                $user = $this->brandsModel->get_brand(array('*', 'iducompany as iduser'),array('filter'=>array('iducompany'=>$result[0]['related_id'])));
            }

            if ($user) {
                $res = [];
                $access_token = $this->create_session($user[0]['iduser'], $platform, $fcmid, $role);

                $res['access_token'] = $access_token;
                $res['role'] = $result[0]['related_key'];
                $res['user_profile'] = $user[0];
                return $res;

            } else {

                return null;

            }
            
        } else {
            return null;
        }

        $user = array();
        if($role == 'sampler'){
            $user = $this->samplersModel->get_sampler(array('*', 'idusampler as iduser'),array('filter'=>$filter));
        }elseif($role == 'company'){
            $user = $this->brandsModel->get_brand(array('*', 'iducompany as iduser'),array('filter'=>$filter));
        }

        if ($user) {
            $res = [];
            $access_token = $this->create_session($user[0]['iduser'], $platform, $fcmid, $role);

            $res['access_token'] = $access_token;
            $res['user_profile'] = $user[0];
            return $res;

        } else {

            return null;

        }
    }

    public function create_session($userid, $platform, $fcmid, $role)
    {

        $builder = $this->dbcanvazer->table('user_access_login_session');
        $builder->where('iduser', $userid);
        $builder->where('platform', $platform);
        // $builder->where('role', $role);
        $builder->where('status=0');

        $data = array(
            'status' => 1, // 1: session not active
        );
        $builder->update($data);
        $n_affected_rows = $this->dbcanvazer->affectedRows();

        $access_token = random_string('alnum', 50);
        $data_session = array(
            'iduser' => $userid,
            'access_token' => $access_token,
            'platform' => $platform,
            'status' => '0',
            // 'role' => $role,
        );
        $builder->set('ts_created', 'NOW()', false);
        $builder->set('ts_last_activity', 'NOW()', false);
        $builder->set('ts_expired', 'DATE_ADD(NOW(),INTERVAL 30 DAY)', false);
        $builder->set('fcm_id', $fcmid);
        $builder->insert($data_session);
        // echo $this->dbcanvazer->getLastQuery();
        return $access_token;

    }

// [START_GET_FUNCTIONS]

    public function get_user_access_log_request($columns = array('*'), $filter = array())
    {
        $this->db->select($columns);

        if (isset($filter['filter'])) {
            $this->db->where($filter['filter']);
        }

        if (isset($filter['limit'])) {
            $this->db->limit($filter['limit']['n_item'], $filter['limit']['page'] * $filter['limit']['n_item']);
        }

        if (isset($filter['sort'])) {
            foreach ($filter['sort'] as $key => $value) {
                $this->db->order_by($key, $value);
            }
        }

        $query = $this->db->get('user_access_log_request');
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }

        return null;
    }

    public function get_user_access_log_response($columns = array('*'), $filter = array())
    {
        $this->db->select($columns);

        if (isset($filter['filter'])) {
            $this->db->where($filter['filter']);
        }

        if (isset($filter['limit'])) {
            $this->db->limit($filter['limit']['n_item'], $filter['limit']['page'] * $filter['limit']['n_item']);
        }

        if (isset($filter['sort'])) {
            foreach ($filter['sort'] as $key => $value) {
                $this->db->order_by($key, $value);
            }
        }

        $query = $this->db->get('user_access_log_response');
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }

        return null;
    }


// [END_GET_FUNCTIONS]

// [START_UPDATE_FUNCTIONS]

    public function update_access_log_user_request($data, $filter)
    {
        $this->db->set($data);
        $this->db->where($filter);
        $this->db->update('user_access_log_request');

        $n_affected_rows = $this->db->affected_rows();
        return $n_affected_rows;
    }

    public function update_access_log_user_response($data, $filter)
    {
        $this->db->set($data);
        $this->db->where($filter);
        $this->db->update('user_access_log_response');

        $n_affected_rows = $this->db->affected_rows();
        return $n_affected_rows;
    }

    public function update_user_access_login_session($iduser, $access_token, $platform = null, $fcm = null)
    {

        $this->db->where('iduser', $iduser);
        $this->db->where('access_token', $access_token);
        if (isset($platform)) {
            $this->db->where('platform', $platform);
        }
        $this->db->where('status', '0');

        if (isset($fcm)) {
            $this->db->set($fcm);
        }
        $this->db->set('ts_last_activity', 'NOW()', false);
        $this->db->set('ts_expired', 'DATE_ADD(NOW(), INTERVAL 30 DAY)', false);
        $this->db->update('user_access_login_session');

        // echo $this->db->last_query();

        $n_affected_rows = $this->db->affected_rows();

        if ($n_affected_rows > 0) {
            // if have accesss

            return $n_affected_rows;
        } else {

            // don't have access
            $this->db->select('*');
            $this->db->where('iduser', $iduser);
            $this->db->where('access_token', $access_token);
            if (isset($platform)) {
                $this->db->where('platform', $platform);
            }
            $this->db->where('status', '0');
            $this->db->from('user_access_login_session');
            $query = $this->db->get();

            if ($query->num_rows() > 0) {
                //return $this->response('00000', NULL);
                return $query->num_rows();
            }

            //return $this->response('00102', NULL);
            return $n_affected_rows;
        }
    }

    public function update_user($data, $filter)
    {
        $this->db->set($data);
        $this->db->where($filter);
        $this->db->update('user');
        // echo $this->db->last_query();

        $n_affected_rows = $this->db->affected_rows();
        return $n_affected_rows;
    }

// [END_UPDATE_FUNCTIONS]

// [START_INSERT_FUNCTIONS]

    public function insert_user_access_log_request($data)
    {
        
        $builder = $this->dbcanvazer->table('user_access_log_request');
        $builder->set('ts_access', 'NOW()', false);
        $builder->insert($data);

        return $this->dbcanvazer->insertID();
    }

    public function insert_user_access_log_response($data)
    {
        $builder = $this->dbcanvazer->table('user_access_log_response');
        $builder->set('ts_response', 'NOW()', false);
        $builder->insert($data);

        // echo $this->db->last_query();
        return $this->dbcanvazer->insertId();
    }

    public function insert_user($data)
    {
        $builder = $this->dbcanvazer->table('user');
        $builder->set('ts_registration', 'NOW()', false);
        $builder->insert($data);
        // echo $this->db->last_query();
        return $this->dbcanvazer->insertID();
    }


// [END_INSERT_FUNCTIONS]

// [START_DELETE_FUNCTIONS]

    public function delete_user_block_user($filter)
    {
        $this->db->where($filter);
        $this->db->delete('user_block_user');
        // echo $this->db->last_query();

        $n_affected_rows = $this->db->affected_rows();
        return $n_affected_rows;
    }


// [END_DELETE_FUNCTIONS]


}
