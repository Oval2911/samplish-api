<?php namespace App\Models;

use CodeIgniter\Model;

class ProfileModel extends Model
{

    protected $dbCanvazer;

    public function __construct()
    {
        parent::__construct();

        $this->dbCanvazer = db_connect();
        // $this->dbCommerce = db_connect("ecommerce");

        helper("text");

    }

    public function get_user($columns = array('*'), $filter = array())
    {

        $builder = $this->dbCanvazer->table('user');
        $builder->select($columns);

        if (isset($filter['filter'])) $builder->where($filter['filter']);

        $query = $builder->get();
        $result = $query->getResultArray();

        if ($result) return $result;
        return null;
    }

    public function get_profile($columns = array('*'), $filter = array())
    {

        $builder = $this->dbCanvazer->table('user_profile');
        $builder->select($columns);

        if (isset($filter['filter'])) $builder->where($filter['filter']);

        $query = $builder->get();
        $result = $query->getResultArray();

        if ($result) return $result;
        return null;
    }

    public function get_address($columns = array('*'), $filter = array())
    {

        $builder = $this->dbCanvazer->table('user_address');
        $builder->select($columns);

        if (isset($filter['filter'])) $builder->where($filter['filter']);

        $query = $builder->get();
        $result = $query->getResultArray();

        if ($result) return $result;
        return null;
    }

    public function store($data)
    {
        $this->dbCanvazer->table('user_profile')->insert($data);
        return $this->dbCanvazer->affectedRows() ? $data["iduser"] : false;
    }

    public function store_profile($id, $user, $user_profile)
    {
        $this->dbCanvazer->table('user')->where("iduser",$id)->update($user);
        $user = $this->dbCanvazer->affectedRows();

        $this->dbCanvazer->table('user_profile')->insert($user_profile);
        $user_profile = $this->dbCanvazer->affectedRows();

        return $user || $user_profile ? $id : false;
    }

    public function store_address($data)
    {
        $data["id"] = uniqid();
        $this->dbCanvazer->table('user_address')->insert($data);

        return $this->dbCanvazer->affectedRows() ? $data["id"] : false;
    }

    public function store_community($data)
    {
        $this->dbCanvazer->table('user_community')->insert($data);

        return $this->dbCanvazer->affectedRows() ? true : false;
    }

    public function store_interest($data)
    {
        $this->dbCanvazer->table('user_interests')->insert($data);

        return $this->dbCanvazer->affectedRows() ? true : false;
    }

    public function amend($id, $data)
    {
        $this->dbCanvazer->table('user_profile')->where("iduser",$id)->update($data);
        return $this->dbCanvazer->affectedRows() ? $id : false;
    }

    public function amend_profile($id, $user, $user_profile)
    {
        $this->dbCanvazer->table('user')->where("iduser",$id)->update($user);
        $user = $this->dbCanvazer->affectedRows();

        $this->dbCanvazer->table('user_profile')->where("iduser",$id)->update($user_profile);
        $user_profile = $this->dbCanvazer->affectedRows();

        return $user || $user_profile ? $id : false;
    }
    
    public function destroys_address($id)
    {
        $this->dbCanvazer->table('user_address')->delete(["iduser"=>$id]);
        return $this->dbCanvazer->affectedRows() ? true : false;
    }
    
    public function destroys_community($id)
    {
        $this->dbCanvazer->table('user_community')->delete(["iduser"=>$id]);
        return $this->dbCanvazer->affectedRows() ? true : false;
    }
    
    public function destroys_interest($id)
    {
        $this->dbCanvazer->table('user_interests')->delete(["iduser"=>$id]);
        return $this->dbCanvazer->affectedRows() ? true : false;
    }
}
