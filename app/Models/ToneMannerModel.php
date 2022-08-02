<?php namespace App\Models;

use CodeIgniter\Model;

class ToneMannerModel extends Model
{

    protected $dbCanvazer;

    public function __construct()
    {
        parent::__construct();

        $this->dbCanvazer = db_connect();
        // $this->dbCommerce = db_connect("ecommerce");

        helper("text");

    }

    public function get_tone_manner($columns = array('*'), $filter = array())
    {

        $builder = $this->dbCanvazer->table('tone_manner');
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
                $builder->orderBy($key, $value);
            }
        }

        $query = $builder->get();
        $result = $query->getResultArray();
        if ($result) {
            return $result;
        }

        return null;
    }
}
