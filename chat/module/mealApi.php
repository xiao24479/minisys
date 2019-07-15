<?php
header("Cache-Control: max-age=3600");

require_once(APP_PATH."/include/libs.php");

class mealApi extends cls_base
{
	protected $db;
	
	public function init()
	{
		global $_DB;
		$this->db = $_DB;
	}


	public function getMeals() {
		$hid = get_data('hid');
		$meals = $this->db->get_all("select * from gossip_meal where hid = {$hid} and is_discounts = 1 limit 0,10");
		make_json_result($meals, 'ok', []);
	}


	public function getRecMeals() {
		$hid = get_data('hid');
		$count = $this->db->get_one("select count(*) from gossip_meal where hid = {$hid}");
		$offset = 4;
		$start = mt_rand(0, $count-4);

		if($start < 0){
			$start = 0;
		}
		$meals = $this->db->get_all("select * from gossip_meal where hid = {$hid} limit {$start},{$offset}");

		foreach($meals as $k => $v){
			//根据售价和原价是否相等,不等则算出折扣额, 然后在小程序根据是否有折扣额字段来决定如何显示价格
			if($v['original_price'] != $v['sell_price']){
				$meals[$k]['discount_num'] = round($v['sell_price'] / $v['original_price'], 2) * 10;
			}
		}

		make_json_result($meals, 'ok', []);
	}


	public function getAllMeals() {
		$hid = get_data('hid');
		$p = get_data('p');
		$offset = 10;
		$start = $p * $offset;

		$meals = $this->db->get_all("select * from gossip_meal where hid = {$hid} limit {$start},{$offset}");

		foreach($meals as $k => $v){
			//根据售价和原价是否相等,不等则算出折扣额, 然后在小程序根据是否有折扣额字段来决定如何显示价格
			if($v['original_price'] != $v['sell_price']){
				$meals[$k]['discount_num'] = round($v['sell_price'] / $v['original_price'], 2) * 10;
			}
		}

		$count = count($meals);
		$msg = $count < $offset ? '已显示全部' : '点击获取更多';
		$end = $count < $offset ? 1 : 0;

		make_json_result($meals, $msg, ['end'=>$end]);
	}


	public function getMealDetail() {
		$id = get_data('id');
		$hid = get_data('hid');

		$detail = $this->db->get_row("select * from gossip_meal where hid={$hid} and id = {$id}");
		$imgs = $this->db->get_all("select * from gossip_meal_detail where mid = {$id}");
		$detail['imgs'] = $imgs;

		//根据售价和原价是否相等,不等则算出折扣额, 然后在小程序根据是否有折扣额字段来决定如何显示价格
		if($detail['original_price'] != $detail['sell_price']){
			$detail['discount_num'] = round($detail['sell_price'] / $detail['original_price'], 2) * 10;
		}

		make_json_result($detail, 'ok', []);
	}
}

?>