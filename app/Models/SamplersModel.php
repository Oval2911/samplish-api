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

    public function get_sampler($params)
    {
        $builder = $this->dbCanvazer->table('user');
        $builder->select('user.iduser, user.fullname, user_profile.birthdate, user_profile.gender,  user_profile.amount60th as SES, user_profile.status, usampler.address, user.related_id');
        $builder->join('usampler', 'user.related_id = usampler.idUsampler', 'left');
        $builder->join('user_profile', 'user.iduser = user_profile.iduser', 'left');
        $builder->where('usampler.is_join',0);

        if(isset($params['search'])){
            $search = $params['search'];
            $builder->like('user.fullname', $search);
            $builder->orLike('user_profile.birthdate', $search);
            $builder->orLike('user_profile.gender', $search);
            $builder->orLike('user_profile.amount60th', $search);
            $builder->orLike('user_profile.status', $search);
            $builder->orLike('usampler.address', $search);
        }

        $per_page = 20; 
        $offset = 0;
        $page = 1; 
        if(isset($params['per_page'])){
           $per_page = $params['per_page'];
        }
        if(isset($params['page'])){
            $page = $params['page'];
            $offset = ($page-1)*$per_page;
        }

        $builder->limit($per_page, $offset);
    
        $query = $builder->get();
        if ($query->getResultArray() > 0) {
            $data = $query->getResultArray();

            $builder = $this->dbCanvazer->table('user');
            $builder->select('COUNT(user.iduser) as total');
            $builder->join('usampler', 'user.related_id = usampler.idUsampler', 'left');
            $builder->join('user_profile', 'user.iduser = user_profile.iduser', 'left');
            $builder->where('usampler.is_join',0);
            if(isset($params['search'])){
                $search = $params['search'];
                $builder->like('user.fullname', $search);
                $builder->orLike('user_profile.birthdate', $search);
                $builder->orLike('user_profile.gender', $search);
                $builder->orLike('user_profile.amount60th', $search);
                $builder->orLike('user_profile.status', $search);
                $builder->orLike('usampler.address', $search);
            }
            
            $query = $builder->get();
            $total = $query->getResultArray();
            $total = $total[0]["total"];

            $result = array(
                "page"=>$page,
                "per_page"=>$per_page,
                "total"=>$total,
                "total_pages"=>ceil($total/$per_page),
                "records"=>$data
            );
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
