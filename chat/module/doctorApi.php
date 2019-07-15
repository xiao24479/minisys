<?php
header("Cache-Control: max-age=3600");

require_once(APP_PATH."/include/libs.php");

class doctorApi extends cls_base
{
	protected $db;
	// protected $score = 9.9;				//医生评分
	// protected $fans = 210;				//医生粉丝
	// protected $consult_count = 1203;	//医生咨询量
	// protected $current_consult = 73;	//医生当前咨询人数
	// protected $doc_order = '1.5万';		//医生预约数
	// protected $dep_order = 2540;		//科室预约数
	
	public function init()
	{
		global $_DB;
		$this->db = $_DB;
	}


	public function getAllDoctors() {
		$hid    = get_data('hid');
		$dep_id = get_data('depId');
		$p      = get_data('p');

		$offset  = 10;
		$start   = $p * $offset;

		$dep_order = $this->db->get_one("select dep_order from gossip_department where hid = {$hid} and id = {$dep_id}");

		$sql = "select d.* from gossip_doctor as d join gossip_doc_dep as dd on d.id = dd.doc_id 
				where d.hid = {$hid} and dd.dep_id = {$dep_id} limit {$start},{$offset}";
		$doctors = $this->db->get_all($sql);

		foreach($doctors as $k => $v){
			$doctors[$k] = $v;
		}

		$count  = count($doctors);
		$end    = $count < $offset ? 1 : 0;

		if($p==0 && $count==0){
			$msg = '该科室下暂无医生';

		}elseif($count < $offset){
			$msg = '已显示全部';

		}elseif($count >= $offset){
			$msg = '点击获取更多';
		}

		make_json_result($doctors, $msg, ['end'=>$end, 'dep_order'=>$dep_order]);
	}


	public function getDocDetail() {
		$id = get_data('id');
		$hid = get_data('hid');
		$detail = $this->db->get_row("select * from gossip_doctor where hid={$hid} and id = {$id}");
		make_json_result($detail, 'ok', []);
	}

	//===============================================================

	public function lists()
    {
        $appid    = get_data('appid');
        $page = get_data('page','i')?get_data('page','i'):0;              

        $offset = 20;
        $start = $page*$offset;

        $sql = "select id_hospital from irpt_interface where appId = '{$appid}'";
        $row = $this->db->get_row($sql);

        $hid = $row['id_hospital'];

        $sql = "select id,hid,pic,name,job,skill,doc_order from gossip_doctor where hid = {$hid} order by add_time desc limit {$start},{$offset}";
        $doctors = $this->db->get_all($sql);

        foreach($doctors as $k => $v) {
            $doctors[$k]['skill'] = mb_substr($v['skill'], 0, 70, 'utf8');
        }

        $return = array(
            "errno" => 0,
            "msg" => "success",
            "data" => $doctors,
        );

        echo json_encode($return,JSON_UNESCAPED_UNICODE);
    }

}

?>