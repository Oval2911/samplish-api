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

    private function query_brand($columns, $user, $type){
        return "SELECT $columns
            FROM campaign AS c
            JOIN area AS a ON a.idarea = c.idarea
            WHERE
                c.iduser = {$user}
                AND c.box_type = {$type}";
    }
    private function query_mix($columns, $user, $status, $type){
        return "SELECT $columns
            FROM campaign AS c
            JOIN area AS a ON a.idarea = c.idarea
            LEFT JOIN campaign_brand AS cb ON cb.idcampaign = c.idcampaign
            LEFT JOIN brand AS b ON b.idbrand = cb.idbrand
            WHERE
                b.iduser = {$user}
                AND c.status = {$status}
                AND c.box_type = {$type}
            GROUP BY c.idcampaign";
    }
    private function query_union($un1, $un2){
        return "SELECT * FROM ( ($un1) UNION ($un2) ) q";
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

    public function get_campaign_sampler($columns=["*"], $filter=[])
    {
        return $this->get_data("campaign_sampler", $columns, $filter);
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
            ->select("COUNT(campaign.idcampaign) as amount");

        if (array_key_exists('join',$filters)) {
            foreach($filters["join"] as $k => $v){
                $data->join($k,$v);
                if (array_key_exists('join_total',$filters) && array_key_exists($k,$filters["join_total"])) {
                    $total->join($k,$v);
                }
            }
        }

        if (array_key_exists('left_join',$filters)) {
            foreach($filters["left_join"] as $k => $v){
                $data->join($k,$v,"left");
                if (array_key_exists('left_join_total',$filters) && array_key_exists($k,$filters["left_join_total"])) {
                    $total->join($k,$v,"left");
                }
            }
        }

        if (array_key_exists('user',$filters)) {
            $data->where("campaign.iduser",$filters["user"]);
            $total->where("campaign.iduser",$filters["user"]);
        }

        if (array_key_exists('user_sampler',$filters)) {
            $data->where("campaign_sampler.iduser",$filters["user_sampler"]);
            $total->where("campaign_sampler.iduser",$filters["user_sampler"]);
        }

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

        if (array_key_exists('statusNot',$filters)) {
            if(is_array($filters["statusNot"])){
                $where = "(";
                foreach($filters["statusNot"] as $k => $v){
                    $v  = $this->dbCanvazer->escape($v);
                    $where .= $k==0 ? "" : " AND ";
                    $where .= "campaign.status!={$v}";
                }
                $where .= ")";
                
                $data->where($where);
                $total->where($where);
            }else{
                $data->where("campaign.status !=",$filters["statusNot"]);
                $total->where("campaign.status !=",$filters["statusNot"]);
            }
        }

        if (array_key_exists('box',$filters)) {
            if($filters["box"]==""){
                $data->where("campaign.box_type IS NOT NULL");
                $total->where("campaign.box_type IS NOT NULL");
            }else{
                $data->where("campaign.box_type",$filters["box"]);
                $total->where("campaign.box_type",$filters["box"]);
            }
        }

        if (array_key_exists('inRange',$filters)) {
            $data->where("campaign.start_date <=",$filters["inRange"]);
            $data->where("campaign.end_date >=",$filters["inRange"]);
            $total->where("campaign.start_date <=",$filters["inRange"]);
            $total->where("campaign.end_date >=",$filters["inRange"]);
        }else if (array_key_exists('notInRange',$filters)) {
            $v  = $this->dbCanvazer->escape($filters["notInRange"]);
            $data->where(" !(campaign.start_date <= {$v} AND campaign.end_date >= {$v}) ");
            $total->where(" !(campaign.start_date <= {$v} AND campaign.end_date >= {$v}) ");
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

        $isOrder = is_array($filters['order']) && array_key_exists("column",$filters['order']) && array_key_exists("direction",$filters['order']);
        if ($isOrder) $data->orderBy($filters['order']['column'], $filters['order']['direction']);
        else $data->orderBy("campaign.updatedat", "desc");

        $total = $total->get()->getResultArray()[0]['amount'];

        return (object)[
            "data" => $data->get()->getResultArray(),
            "total" => $total,
            "total_pages" => round($total / $filters['limit']['n_item']),
        ];
    }

    public function datatable_company_union($filters = [])
    {
        $searchable = "c.name, c.desc, a.name as area, c.box_type, c.start_date, c.end_date, c.status, c.theme";
        $columns = $searchable . ", c.idcampaign, c.updatedat, c.photo";
        $count = "COUNT(c.idcampaign) as amount";
        $status  = $this->dbCanvazer->escape("on_going");
        $brand  = $this->dbCanvazer->escape("brand");
        $mix  = $this->dbCanvazer->escape("mix");
        $user  = $this->dbCanvazer->escape($filters["user"]);
        $limit  = $filters['limit']['n_item'];
        $offset  = $filters['limit']['page'] * $filters['limit']['n_item'];

        $data = $this->query_union(
            $this->query_brand($columns, $user, $brand),
            $this->query_mix($columns, $user, $status, $mix),
        );
        $total = $this->query_union(
            $this->query_brand($count, $user, $brand),
            $this->query_mix($count, $user, $status, $mix),
        );

        if ($filters['search']!=null) {
            $where = "(";
            foreach(explode(", ",$searchable) as $k => $col){
                $v  = $this->dbCanvazer->escape("%".$filters['search']."%");
                $where .= $k==0 ? "" : " OR ";
                $where .= $col ." LIKE {$v}";
            }
            $where .= ")";
            
            $data .= " WHERE $where ";
            $total .= " WHERE $where ";
        }

        $isOrder = is_array($filters['order']) && array_key_exists("column",$filters['order']) && array_key_exists("direction",$filters['order']);
        if ($isOrder) $data .= " ORDER BY " .$filters['order']['column']. " " .$filters['order']['direction'] ." ";
        else $data .= " ORDER BY updatedat DESC";

        $data .= " LIMIT $limit OFFSET $offset ";

        $data = $this->dbCanvazer->query($data)->getResultArray();
        $total = $this->dbCanvazer->query($total)->getResultArray()[0]['amount'];

        return (object)[
            "data" => $data,
            "total" => $total,
            "total_pages" => round($total / $limit),
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

        if (array_key_exists('statusNot',$filters)) {
            if(is_array($filters["statusNot"])){
                $where = "(";
                foreach($filters["statusNot"] as $k => $v){
                    $v  = $this->dbCanvazer->escape($v);
                    $where .= $k==0 ? "" : " AND ";
                    $where .= "campaign.status!={$v}";
                }
                $where .= ")";
                
                $data->where($where);
                $total->where($where);
            }else{
                $data->where("campaign.status !=",$filters["statusNot"]);
                $total->where("campaign.status !=",$filters["statusNot"]);
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

        $isOrder = is_array($filters['order']) && array_key_exists("column",$filters['order']) && array_key_exists("direction",$filters['order']);
        if ($isOrder) $data->orderBy($filters['order']['column'], $filters['order']['direction']);
        else $data->orderBy("campaign.updatedat", "desc");

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

    public function datatable_sampler($columns = ['*'], $filters = [])
    {
        $data = $this->dbCanvazer->table('campaign_sampler AS c')
            ->join("user AS u","u.iduser = c.iduser")
            ->join("user_profile AS p","u.iduser = p.iduser")
            ->where("c.idcampaign", $filters["campaign"])
            ->select($columns);

        $total = $this->dbCanvazer->table('campaign_sampler AS c')
            ->where("c.idcampaign", $filters["campaign"])
            ->select("COUNT(c.iduser) as amount");

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

        $isOrder = is_array($filters['order']) && array_key_exists("column",$filters['order']) && array_key_exists("direction",$filters['order']);
        if ($isOrder) $data->orderBy($filters['order']['column'], $filters['order']['direction']);

        $total = $total->get()->getResultArray()[0]['amount'];

        return (object)[
            "data" => $data->get()->getResultArray(),
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
    
    public function store_sampler($data)
    {
        $this->dbCanvazer->table('campaign_sampler')->insert($data);
        return $this->dbCanvazer->affectedRows() ? true : false;
    }

    public function amend($id, $data)
    {
        $this->dbCanvazer->table('campaign')->where("idcampaign",$id)->update($data);
        return $this->dbCanvazer->affectedRows() ? $id : false;
    }

    public function amend_sampler($id, $user, $data)
    {
        $this->dbCanvazer->table('campaign_sampler')->where("idcampaign",$id)->where("iduser",$user)->update($data);
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
