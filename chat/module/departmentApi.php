<?php
header("Cache-Control: max-age=3600");
require_once(APP_PATH."/include/libs.php");

class departmentApi extends cls_base
{
	protected $db;
	
	public function init()
	{
		global $_DB;
		$this->db = $_DB;
	}


	public function getDeps() {
		$hid = get_data('hid');
		$all = $this->db->get_all("select * from gossip_department where hid = {$hid}");
		$hot_dep = $this->db->get_all("select * from gossip_department where hid = {$hid} and is_hot = 1");
		$meal = $this->db->get_all("select id,title as name from gossip_meal where hid = {$hid} and is_discounts = 1");

		$arr = [
			['id'=>"-1", 'name'=>'热门科室', 'childs'=>$hot_dep],
			['id'=>"-2", 'name'=>'优惠活动', 'childs'=>$meal, 'type'=>'meal'],
		];

		$all = array_merge($arr, $all);

		foreach ($all as $key => $value) {
			if($value['top_id']==0){
				foreach($all as $k => $v){
					if($v['top_id'] > 0 && $v['top_id']==$value['id']){
						$all[$key]['childs'][] = $v;
						unset($all[$k]);
					}
				}
			}
		}
		make_json_result($all, $hid, []);
	}


	public function getComments() {
		$comments = [
			[
				'name'=>'诗**', 
				'dep'=>'不孕不育', 
				'time'=>'2019-03-08', 
				'content'=>'我患有多囊，身边有人多囊治了四年都没有治好，被确诊多囊的时候心里像压了块石头，也做好了长期治疗的打算。万万没想到第一次促排就成功怀孕了，当医生告诉我这个消息的时候自己都不敢相信！衷心感谢，圆了做父母的梦。', 
				'doc_name'=>'柳晓春',
				'doc_id'=>11,
				'star'=>[1,1,1,1,1]
			],
			[
				'name'=>'张**', 
				'dep'=>'阴道松弛', 
				'time'=>'2019-03-09', 
				'content'=>'我产后8年，当时的顺差侧切并没缝合的很好，产后也没注意锻炼pc肌，阴道松弛了，闺蜜认识这个医生介绍的很放心，术衷无疼，手术从术前的4指做到现在的1指半， 术后4月同房了，老公说明显比术前精致。谢谢医生的妙手回春。', 
				'doc_name'=>'邓楚芳',
				'doc_id'=>16,
				'star'=>[1,1,1,1,1]
			],
			[
				'name'=>'程**', 
				'dep'=>'无痛分娩', 
				'time'=>'2019-03-11', 
				'content'=>'我前阵阵痛过程几乎都没怎么受罪，又睡了一觉补觉了体力，所以进产房生的很顺利，45分钟完成任务。关于顺产还是剖腹产，侧切这类的选择问题，因人而异，毕竟胎儿大小和胎位情况都不一样，我这样的真的属于比较幸运的。', 
				'doc_name'=>'范舒凌',
				'doc_id'=>22,
				'star'=>[1,1,1,1,1]
			],
		];

		make_json_result($comments, 'ok', []);
	}


	public function getCommentList() {
		$comments = [
			[
				'name'=>'诗**', 
				'dep'=>'不孕不育', 
				'time'=>'2019-03-08', 
				'content'=>'我患有多囊，身边有人多囊治了四年都没有治好，被确诊多囊的时候心里像压了块石头，也做好了长期治疗的打算。万万没想到第一次促排就成功怀孕了，当医生告诉我这个消息的时候自己都不敢相信！衷心感谢，圆了做父母的梦。', 
				'doc_name'=>'柳晓春',
				'doc_id'=>11,
				'star'=>[1,1,1,1,1]
			],
			[
				'name'=>'张**', 
				'dep'=>'阴道松弛', 
				'time'=>'2019-03-09', 
				'content'=>'我产后8年，当时的顺差侧切并没缝合的很好，产后也没注意锻炼pc肌，阴道松弛了，闺蜜认识这个医生介绍的很放心，术衷无疼，手术从术前的4指做到现在的1指半， 术后4月同房了，老公说明显比术前精致。谢谢医生的妙手回春。', 
				'doc_name'=>'邓楚芳',
				'doc_id'=>16,
				'star'=>[1,1,1,1,1]
			],
			[
				'name'=>'程**', 
				'dep'=>'无痛分娩', 
				'time'=>'2019-03-11', 
				'content'=>'我前阵阵痛过程几乎都没怎么受罪，又睡了一觉补觉了体力，所以进产房生的很顺利，45分钟完成任务。关于顺产还是剖腹产，侧切这类的选择问题，因人而异，毕竟胎儿大小和胎位情况都不一样，我这样的真的属于比较幸运的。', 
				'doc_name'=>'范舒凌',
				'doc_id'=>22,
				'star'=>[1,1,1,1,1]
			],
			[
				'name'=>'黄**', 
				'dep'=>'孕期产检', 
				'time'=>'2019-03-12', 
				'content'=>'医生态度很好，对待病人很温暖。', 
				'doc_name'=>'柳晓春',
				'doc_id'=>11,
				'star'=>[1,1,1,1,1]
			],
			[
				'name'=>'孟**', 
				'dep'=>'四维彩超', 
				'time'=>'2019-03-13', 
				'content'=>'医生挺好说话，很温和，第一次去。', 
				'doc_name'=>'郭家菊',
				'doc_id'=>25,
				'star'=>[1,1,1,1,1]
			],
			[
				'name'=>'梁**', 
				'dep'=>'妇科检查', 
				'time'=>'2019-03-16', 
				'content'=>'态度不错比较有耐心，我问了好多问题，都很仔细的回答没有不耐烦，开心。', 
				'doc_name'=>'陈俊兰',
				'doc_id'=>19,
				'star'=>[1,1,1,1,1]
			],
			[
				'name'=>'李**', 
				'dep'=>'月经不调', 
				'time'=>'2019-03-20', 
				'content'=>'月经周期正常，量过少色暗，持续调理了几个月现在改善很多了。', 
				'doc_name'=>'贾丽',
				'doc_id'=>17,
				'star'=>[1,1,1,1,1]
			],
		];

		make_json_result($comments, 'ok', []);
	}

}

?>