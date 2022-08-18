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

    public function store_profile($id, $user, $user_profile)
    {
        $this->dbCanvazer->table('user')->where("iduser",$id)->update($user);
        $user = $this->dbCanvazer->affectedRows();

        $this->dbCanvazer->table('user_profile')->insert($user_profile);
        $user_profile = $this->dbCanvazer->affectedRows();

        return $user || $user_profile ? $id : false;
    }

    public function amend_profile($id, $user, $user_profile)
    {
        $this->dbCanvazer->table('user')->where("iduser",$id)->update($user);
        $user = $this->dbCanvazer->affectedRows();

        $this->dbCanvazer->table('user_profile')->where("iduser",$id)->update($user_profile);
        $user_profile = $this->dbCanvazer->affectedRows();

        return $user || $user_profile ? $id : false;
    }
}
