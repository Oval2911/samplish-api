<?php namespace App\Models;

use CodeIgniter\Model;

class CampaignModel extends Model
{

    protected $dbCanvazer;

    public function __construct()
    {
        parent::__construct();

        $this->dbCanvazer = db_connect();
        // $this->dbCommerce = db_connect("ecommerce");

        helper("text");

    }

    public function get_campaign($columns=["*"], $filter=[])
    {
        return $this->get_data("campaign", $columns, $filter);
    }

    public function get_campaign_brands($columns=["*"], $filter=[])
    {
        return $this->get_data("campaign_brand", $columns, $filter);
    }

    public function get_campaign_brand_details($columns=["*"], $filter=[])
    {
        $builder = $this->dbCanvazer->table("campaign_brand")
            ->select($columns)
            ->join("brand","brand.idbrand = campaign_brand.idbrand");

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

    public function get_campaign_question($columns=["*"], $filter=[])
    {
        return $this->get_data("campaign_feedback_question", $columns, $filter);
    }

    public function get_campaign_merchandise($columns=["*"], $filter=[])
    {
        return $this->get_data("campaign_merchandise", $columns, $filter);
    }

    private function get_data($table, $columns=["*"], $filter=[])
    {
        $builder = $this->dbCanvazer->table($table);
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

    public function datatable($columns = ['*'], $filters = [])
    {
        $data = $this->dbCanvazer->table('campaign')
            ->select($columns);

        $total = $this->dbCanvazer->table('campaign')
            ->select("COUNT(idcampaign) as amount");

        if (array_key_exists('user',$filters)) {
            $data->where("iduser",$filters["user"]);
            $total->where("iduser",$filters["user"]);
        }

        if (array_key_exists('status',$filters)) {
            if(is_array($filters["status"])){
                $where = "(";
                foreach($filters["status"] as $k => $v){
                    $v  = $this->dbCanvazer->escape($v);
                    $where .= $k==0 ? "" : " OR ";
                    $where .= "status={$v}";
                }
                $where .= ")";
                
                $data->where($where);
                $total->where($where);
            }else{
                $data->where("status",$filters["status"]);
                $total->where("status",$filters["status"]);
            }
        }

        if (array_key_exists('statusNot',$filters)) {
            if(is_array($filters["statusNot"])){
                $where = "(";
                foreach($filters["statusNot"] as $k => $v){
                    $v  = $this->dbCanvazer->escape($v);
                    $where .= $k==0 ? "" : " OR ";
                    $where .= "status!={$v}";
                }
                $where .= ")";
                
                $data->where($where);
                $total->where($where);
            }else{
                $data->where("status !=",$filters["statusNot"]);
                $total->where("status !=",$filters["statusNot"]);
            }
        }

        if ($filters['search']!=null) {
            $where = "(";
            foreach($filters["searchable"] as $k => $col){
                $v  = $this->dbCanvazer->escape("%".$filters['search']."%");
                $where .= $k==0 ? "" : " OR ";
                $where .= $col ." LIKE {$v}";
            }
            $where .= ")";
            
            $data->where($where);
            $total->where($where);
        }

        $data->limit($filters['limit']['n_item'], $filters['limit']['page'] * $filters['limit']['n_item']);

        if (
            is_array($filters['order'])
            && array_key_exists("column",$filters['order'])
            && array_key_exists("direction",$filters['order'])
        ) $data->orderBy($filters['order']['column'], $filters['order']['direction']);

        $total = $total->get()->getResultArray()[0]['amount'];

        return (object)[
            "data" => $data->get()->getResultArray(),
            "total" => $total,
            "total_pages" => round($total / $filters['limit']['n_item']),
        ];
    }

    public function datatable_all_company($columns = ['*'], $filters = [])
    {
        $data = $this->dbCanvazer->table('campaign')
            ->select($columns)
            ->join("user","user.iduser = campaign.iduser")
            ->where("user.related_key", "company");

        $total = $this->dbCanvazer->table('campaign')
            ->select("COUNT(campaign.idcampaign) as amount")
            ->join("user","user.iduser = campaign.iduser")
            ->where("user.related_key", "company");

        if (array_key_exists('status',$filters)) {
            if(is_array($filters["status"])){
                $where = "(";
                foreach($filters["status"] as $k => $v){
                    $v  = $this->dbCanvazer->escape($v);
                    $where .= $k==0 ? "" : " OR ";
                    $where .= "campaign.status={$v}";
                }
                $where .= ")";
                
                $data->where($where);
                $total->where($where);
            }else{
                $data->where("campaign.status",$filters["status"]);
                $total->where("campaign.status",$filters["status"]);
            }
        }

        if (array_key_exists('payment_status',$filters)) {
            if(is_array($filters["payment_status"])){
                $where = "(";
                foreach($filters["payment_status"] as $k => $v){
                    $v  = $this->dbCanvazer->escape($v);
                    $where .= $k==0 ? "" : " OR ";
                    $where .= "campaign.payment_status={$v}";
                }
                $where .= ")";
                
                $data->where($where);
                $total->where($where);
            }else{
                $data->where("campaign.payment_status",$filters["payment_status"]);
                $total->where("campaign.payment_status",$filters["payment_status"]);
            }
        }

        if ($filters['search']!=null) {
            $where = "(";
            foreach($filters["searchable"] as $k => $col){
                $v  = $this->dbCanvazer->escape("%".$filters['search']."%");
                $where .= $k==0 ? "" : " OR ";
                $where .= $col ." LIKE {$v}";
            }
            $where .= ")";
            
            $data->where($where);
            $total->where($where);
        }

        $data->limit($filters['limit']['n_item'], $filters['limit']['page'] * $filters['limit']['n_item']);

        if (
            is_array($filters['order'])
            && array_key_exists("column",$filters['order'])
            && array_key_exists("direction",$filters['order'])
        ) $data->orderBy($filters['order']['column'], $filters['order']['direction']);

        $data = $data
            ->groupBy("campaign.idcampaign")
            ->get()
            ->getResultArray();
        $total = $total
            ->get()
            ->getResultArray()[0]['amount'];

        return (object)[
            "data" => $data,
            "total" => $total,
            "total_pages" => round($total / $filters['limit']['n_item']),
        ];
    }
    
    public function store($data)
    {
        $data["idcampaign"] = uniqid();
        $this->dbCanvazer->table('campaign')->insert($data);
        return $this->dbCanvazer->affectedRows() ? $data["idcampaign"] : false;
    }
    
    public function store_brand($data)
    {
        $this->dbCanvazer->table('campaign_brand')->insert($data);
        return $this->dbCanvazer->affectedRows() ? true : false;
    }
    
    public function store_question($data)
    {
        $this->dbCanvazer->table('campaign_feedback_question')->insert($data);
        return $this->dbCanvazer->affectedRows() ? true : false;
    }
    
    public function store_merchandise($data)
    {
        $this->dbCanvazer->table('campaign_merchandise')->insert($data);
        return $this->dbCanvazer->affectedRows() ? true : false;
    }

    public function amend($id, $data)
    {
        $this->dbCanvazer->table('campaign')->where("idcampaign",$id)->update($data);
        return $this->dbCanvazer->affectedRows() ? $id : false;
    }
    
    public function destroy($id)
    {
        $this->destroy_brands($id);
        $this->destroy_questions($id);
        $this->destroy_merchandises($id);
        $this->dbCanvazer->table('campaign')->delete(["idcampaign"=>$id]);
        return $this->dbCanvazer->affectedRows() ? true : false;
    }
    
    public function destroy_brands($id)
    {
        $this->dbCanvazer->table('campaign_brand')->delete(["idcampaign"=>$id]);
        return $this->dbCanvazer->affectedRows() ? true : false;
    }
    
    public function destroy_brands_user($campaign,$user)
    {
        $brands = $this->dbCanvazer
            ->table('brand')
            ->select("idbrand")
            ->where("iduser",$user)
            ->get()
            ->getResultArray();

        foreach($brands as $k => $v){
            $this->dbCanvazer->table('campaign_brand')->delete(["idcampaign"=>$campaign, "idbrand"=>$v["idbrand"]]);
        }

        return true;
    }
    
    public function destroy_questions($id)
    {
        $this->dbCanvazer->table('campaign_feedback_question')->delete(["idcampaign"=>$id]);
        return $this->dbCanvazer->affectedRows() ? true : false;
    }
    
    public function destroy_merchandises($id)
    {
        $this->dbCanvazer->table('campaign_merchandise')->delete(["idcampaign"=>$id]);
        return $this->dbCanvazer->affectedRows() ? true : false;
    }
}
