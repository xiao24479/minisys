<?php
require_once(APP_PATH."/include/libs.php");
class indexes extends cls_base{
	
	var $db;
	
	var $template;
	
	// init
	
	function init() {
	
		global $_DB,$_TEMPLATE;
	
		$this->db = $_DB;
	
		$this->template = $_TEMPLATE;
		
		require_once(ROOT_PATH . "_ext/auth_admin.php");
		
		require_once(APP_PATH . '/include/lib_common.php');
		
		require_once(APP_PATH . '/include/lib_indexes.php');
		
		require_once(ROOT_PATH."_ext/page.class.php");
		cuckhid(get_hid());
		cuckaid(get_hid(),get_aid());
	}
	
	/**
	 * 校验机器人是否符合这个类型
	 */
	private function checkStyle($aid,$style){
		
		$sql="select style from gossip_access where id=".$aid;
		$val=$this->db->get_one($sql);
		if($val!=$style){
			exit("机器人类型不一致");
		}
	}
	

	/**
	 * 添加索引
	 */
	
	public function add_indexes(){
		
        $this->checkStyle(get_aid(),1);
		$this->template->display("indexes/add_indexes.htm");
		
	}
	
	
	/**
	 * 添加索引操作
	 */
	
	public function add_indexes_action(){


		// get the parameter.
		
		
		$hid=get_hid();
		
		$aid=get_aid();
		$table_name='wisdom_answer_'.$aid;

		$hidden_count = get_data("hidden_count","i");
		
		if($hidden_count<1) {
		
			redirect("没有索引数据，正在返回...","main.php?m=indexes&a=add_indexes",1);
		
			exit;
		
		}
		
		$genre = get_data("genre","a");
		
		$noun = get_data("noun","a");
		
		$verb = get_data("verb","a");
		
		$doubt = get_data("doubt","a");
		
		$answer = get_data("answer","a");
		
		$score = get_data("score","a");


		
		$isNull = false;
		
		// 调整参数
		
		$apply_indexes_rs = deal_apply_indexes_data($table_name,$genre,$noun,$verb,$doubt,$answer,$score);
		
		if(!empty($apply_indexes_rs)) {
		
			foreach($apply_indexes_rs as $ap) {
		
				addIndexesData($table_name,$ap['genre'],$ap['noun'],$ap['verb'],$ap['doubt'],$ap['answer'],$ap['score']);
		
			}
		
		}else {
		
			$isNull = true;
		
		}
		
		if($isNull) {
		
			redirect("提交索引失败，正在返回...","main.php?m=indexes&a=add_indexes",1);
		
		}else {
		
			redirect("提交索引成功，正在跳转...","main.php?m=indexes&a=add_indexes",1);
		
		}
	}
	
	
	public function indexes_list(){
		
		$this->checkStyle(get_aid(),1);
		
		$genre=get_data('genre');
		
		$add_time=get_data('add_time');
		
		$end_time=get_data('end_time');

		$noun=get_data('noun');
		
		$verb=get_data('verb');
		
		$doubt=get_data('doubt');
		
		$answer=get_data('answer');

		$hid=get_hid();
		
		$aid=get_aid();
		
		$table_name='wisdom_answer_'.$aid;
		
		$w=' 1= 1 ';
			
		if($genre!='')
			$w.="  and  genre = '".$genre."'";
		if($add_time!='' && $end_time!=''){
			$w.="  and addTime between ".strtotime($add_time.' 00:00:00')." and ".strtotime($end_time.' 23:59:59');
		}
		if($noun!='')
			$w.="  and  noun = '".$noun."'";
		if($verb!='')
			$w.="  and  verb = '".$verb."'";
		if($doubt!='')
			$w.="  and  doubt = '".$doubt."'";
		if($answer!='')
			$w.="  and answer like '%".$answer."%'";

		//查出索引总数
		$sql="SELECT count(*) as a FROM $table_name where $w";
		
		$count=$this->db->get_one($sql);//总记录数数

		$parameter  = '';
		if($genre!=''){
			$parameter .= '&genre='.$genre;
		}
		if($add_time!=''){
			$parameter .= '&add_time='.$add_time;
		}
		if($end_time!=''){
			$parameter .= '&end_time='.$end_time;
		}
		if($noun!=''){
			$parameter .= '&noun='.$noun;
		}
		if($verb!=''){
			$parameter .= '&verb='.$verb;
		}
		if($doubt!=''){
			$parameter .= '&doubt='.$doubt;
		}
		if($answer!=''){
			$parameter .= '&answer='.$answer;
		}
		
		
		$p = new Page($count,50,$parameter);
		
		$sql="SELECT * FROM $table_name where $w ORDER BY `id`  DESC LIMIT ".$p->firstRow.",".$p->listRows." ;"; //按更新时间查询
		
		$data=$this->db->get_all($sql);
		
		$this->template->assign('genre',$genre);
		
		$this->template->assign('add_time',$add_time);
		
		$this->template->assign('end_time',$end_time);
		
		$this->template->assign('noun',$noun);
		
		$this->template->assign('verb',$verb);
		
		$this->template->assign('doubt',$doubt);
		
		$this->template->assign('answer',$answer);

		$this->template->assign('data',$data);
		
		$this->template->assign('page',$p->show2());

		$this->template->display("indexes/indexes_list.htm");

	}
	
	
	/**
	 * 
	 * 修改字段值
	 */
	
	public function edit_cost_data(){
		
		$hid=get_hid();
		$aid=get_aid();
		$table_name='wisdom_answer_'.$aid;
		$id = get_data('id'); //取得id
		$name = get_data('fd'); //取得字段名称
		$value = get_data('val'); //取得修改值
		$noun_check = get_data('noun_check'); //获取名词的值，名词不能为空
		$verb_check = get_data('verb_check'); //获取动词的值
		$doubt_check = get_data('doubt_check'); //获取疑问词的值
		$answer_check = get_data('answer_check'); //获取问题的值

		$time = time();
		$resp_array = array();
		$resp_array['id'] = $id;
		if(empty($id)){
			$resp_array['status'] = 'error';
		}
		if(empty($name)){
			$resp_array['status'] = 'error';
		}
		if(empty($noun_check)){
			$resp_array['status'] = 'isnull';
		}
		$comu_array =array('noun','verb','doubt','answer','score');
		
		if(!in_array($name,$comu_array)){
			$resp_array['status'] = 'error';
		}
		
		//查询是否存在重复数据
		if(strcmp($resp_array['status'],'error') == 0 || strcmp($resp_array['status'],'isnull') == 0){
			echo json_encode($resp_array);exit;
		}

		//查询出插入的数据
		$sql="select * FROM $table_name WHERE   `id` ='".$id."'";
		$all=$this->db->get_row($sql);
		if($all){
			$score=$all['score']+1;
			if(strcmp($name,'noun') == 0 ){
				if(empty($value)){
					$resp_array['status'] = 'isnull';
				}else{
					
					if(indexes_is_exist($table_name,$value,$verb_check,$doubt_check,$answer_check,$id)){//去重
						$resp_array['status'] = 'repeat';
					}else{
						$sql = "UPDATE $table_name SET  `noun` =  '{$value}',`score` =  '{$score}',`editTime`='{$time}' WHERE  `id` ={$id};";
						$this->db->query($sql);
						$resp_array['status'] = 'success';
					}
				}
			}
			if(strcmp($name,'verb') == 0 ){
				
				if(indexes_is_exist($table_name,$noun_check,$value,$doubt_check,$answer_check,$id)){//去重
					$resp_array['status'] = 'repeat';
				}else{
					
					$sql = "UPDATE $table_name SET  `verb` =  '{$value}',`score` =  '{$score}', `editTime`='{$time}' WHERE  `id` ={$id};";
					$this->db->query($sql);
					$resp_array['status'] = 'success';
				}
			}
			if(strcmp($name,'doubt') == 0 ){
				if(indexes_is_exist($table_name,$noun_check,$verb_check,$value,$answer_check,$id)){//去重
					$resp_array['status'] = 'repeat';
				}else{
					
					$sql = "UPDATE $table_name SET  `doubt` =  '{$value}',`score` =  '{$score}',`editTime`='{$time}' WHERE  `id` ={$id};";
					$this->db->query($sql);
					$resp_array['status'] = 'success';
				}
			}
			if(strcmp($name,'answer') == 0 ){
				if(indexes_is_exist($table_name,$noun_check,$verb_check,$doubt_check,$value,$id)){//去重
					$resp_array['status'] = 'repeat';
				}else{
					
					$sql = "UPDATE $table_name SET  `answer` =  '{$value}',`score` =  '{$score}',`editTime`='{$time}' WHERE  `id` ={$id};";
					$this->db->query($sql);
					$resp_array['status'] = 'success';
				}
			}
			if(strcmp($name,'score') == 0 ){
				$sql = "UPDATE $table_name SET  `score` =  '{$value}',`editTime`='{$time}'  WHERE  `id` ={$id};";
				$this->db->query($sql);
				$resp_array['status'] = 'success';
			}
		}
		echo json_encode($resp_array);exit;
		
	}
	
	
	/**
	 * 修改类别
	 */
	
	public function edit_genre_data(){
		
		$id = get_data('id'); //取得id
		$genre = get_data('genre'); //分类
		$time=time();
		$hid=get_hid();
		$aid=get_aid();
		$table_name='wisdom_answer_'.$aid;
		$resp_array = array();
		if($hid=='' || $hid==0 || $aid=='' || $aid==0){
			$resp_array['status']='isnull';
			echo json_encode($resp_array);exit;
		}

		$sql = "UPDATE $table_name SET  `genre` =  '{$genre}',`editTime`='{$time}'  WHERE  `id` ={$id};";
		$res=$this->db->query($sql);
		if($res){
			$resp_array['status']='success';
		}else{
			$resp_array['status']='error';
		}
		echo json_encode($resp_array);exit;
	}
	
	/**
	 * 删除操作
	 */
	
	public function delete_indexes(){
		
		$id = get_data('id'); //取得id
		$hid=get_hid();
		$aid=get_aid();
		$table_name='wisdom_answer_'.$aid;
		if(empty($id)){
			make_json_result('false', '删除失败', array());
		}
		
		$sql="DELETE FROM $table_name WHERE id=$id";
		
		$res=$this->db->query($sql);
		if($res){
			make_json_result('true', '删除成功', array());
		}else{
			make_json_result('false', '删除失败', array());
		}
	}
	
	
	/**
	 * 添加问诊病种
	 */
	public function add_disease(){
		
		$this->checkStyle(get_aid(),2);
		
		$this->template->display("indexes/add_disease.htm");
	}
	
	
	/**
	 * 添加问诊病种方法
	 */
	
	public function add_disease_action(){
	
	
		// get the parameter.

		$hid=get_hid();
	
		$aid=get_aid();
		$table_name='wisdom_answer_'.$aid;
	
		$hidden_count = get_data("hidden_count","i");
	
		if($hidden_count<1) {
	
			redirect("没有索引数据，正在返回...","main.php?m=indexes&a=add_disease",1);
	
			exit;
	
		}
	
		$question = get_data("question","a");
	
		$answer = get_data("answer","a");
	
		$express = get_data("express","a");
		
		$proof = get_data("proof","a");
		
		$nicety = get_data("nicety","a");
	
		$keyword = get_data("keyword","a");

		$reference = get_data("reference","a");
		
		$yesid = get_data("yesid","a");
		
		$notid = get_data("notid","a");
	
		$groupid = get_data("groupid","a");
	
		$score = get_data("score","a");

	
		$isNull = false;
	
		// 调整参数
	
		$apply_disease_rs = deal_apply_disease_data($table_name,$question,$answer,$express,$proof,$nicety,$keyword,$reference,$yesid,$notid,$groupid,$score);
	
		if(!empty($apply_disease_rs)) {
	
			foreach($apply_disease_rs as $ap) {
	
				addDiseaseData($table_name,$ap['question'],$ap['answer'],$ap['express'],$ap['proof'],$ap['nicety'],$ap['keyword'],$ap['reference'],$ap['yesid'],$ap['notid'],$ap['groupid'],$ap['score']);
	
			}
	
		}else {
	
			$isNull = true;
	
		}
	
		if($isNull) {
	
			redirect("提交索引失败，正在返回...","main.php?m=indexes&a=add_disease",1);
	
		}else {
	
			redirect("提交索引成功，正在跳转...","main.php?m=indexes&a=add_disease",1);
	
		}
	}
	
	/**
	 * 问诊索引列表
	 */
	public function disease_list(){
	
		$this->checkStyle(get_aid(),2);
	
		$add_time=get_data('add_time');
	
		$end_time=get_data('end_time');
		
		$keyword=get_data('keyword');

		$groupid=get_data('groupid');
	
		$hid=get_hid();
	
		$aid=get_aid();
	
		$table_name='wisdom_answer_'.$aid;
	
		$w=' 1 = 1 ';
			
		if($keyword!='')
			$w.="  and keyword like '%".$keyword."%'";
		if($groupid!='')
			$w.="  and  groupid = '".$groupid."'";
		if($add_time!='' && $end_time!=''){
			$w.="  and addTime between ".strtotime($add_time.' 00:00:00')." and ".strtotime($end_time.' 23:59:59');
		}

		//获得所有分组
		$sql="SELECT distinct groupid FROM $table_name ORDER BY `groupid`";
		
		$group_list=$this->db->get_all($sql);
		
	
		//查出索引总数
		$sql="SELECT count(*) as a FROM $table_name where $w";
	
		$count=$this->db->get_one($sql);//总记录数数
	
		$parameter  = '';
		if($keyword!=''){
			$parameter .= '&keyword='.$keyword;
		}
		if($groupid!=''){
			$parameter .= '&groupid='.$groupid;
		}
		if($add_time!=''){
			$parameter .= '&add_time='.$add_time;
		}
		if($end_time!=''){
			$parameter .= '&end_time='.$end_time;
		}

		$p = new Page($count,50,$parameter);
	
		$sql="SELECT * FROM $table_name where $w ORDER BY  keyword ASC,`groupid`  ASC LIMIT ".$p->firstRow.",".$p->listRows." ;"; //按更新时间查询
	
		$data=$this->db->get_all($sql);
	
		$this->template->assign('keyword',$keyword);
		
		$this->template->assign('groupid',$groupid);
	
		$this->template->assign('add_time',$add_time);
	
		$this->template->assign('end_time',$end_time);
		
		$this->template->assign('group_list',$group_list);
	
		$this->template->assign('data',$data);
	
		$this->template->assign('page',$p->show2());
	
		$this->template->display("indexes/disease_list.htm");
	
	}
	
	
	/**
	 * 删除问诊操作
	 */
	
	public function delete_disease(){
	
		$id = get_data('id'); //取得id
		$hid=get_hid();
		$aid=get_aid();
		$table_name='wisdom_answer_'.$aid;
		if(empty($id)){
			make_json_result('false', '删除失败', array());
		}
	
		$sql="DELETE FROM $table_name WHERE id=$id";
	
		$res=$this->db->query($sql);
		if($res){
			make_json_result('true', '删除成功', array());
		}else{
			make_json_result('false', '删除失败', array());
		}
	}
	
	
	/**
	 *
	 * 修改问诊字段值
	 */
	
	public function edit_field_data(){
	
		$hid=get_hid();
		$aid=get_aid();
		$table_name='wisdom_answer_'.$aid;
		$id = get_data('id'); //取得id
		$name = get_data('fd'); //取得字段名称
		$value = get_data('val'); //取得修改值
		$question_check = get_data('question_check'); 
		$answer_check = get_data('answer_check'); 
		$express_check = get_data('express_check'); 
		$proof_check = get_data('proof_check');
		$nicety_check = get_data('nicety_check');
		$keyword_check = get_data('keyword_check');
		$reference_check = get_data('reference_check');
		$yesid_check = get_data('yesid_check');
		$notid_check = get_data('notid_check');
		$groupid_check = get_data('groupid_check');
		$score_check = get_data('score_check');
	
		$time = time();
		$resp_array = array();
		$resp_array['id'] = $id;
		if(empty($id)){
			$resp_array['status'] = 'error';
		}
		if(empty($name)){
			$resp_array['status'] = 'error';
		}
		if(empty($question_check) || empty($answer_check) || empty($express_check) || empty($proof_check) || empty($nicety_check) || empty($keyword_check)){
			$resp_array['status'] = 'isnull';
		}
		$comu_array =array('question','answer','express','proof','nicety','keyword','reference','yesid','notid','groupid','score');
	
		if(!in_array($name,$comu_array)){
			$resp_array['status'] = 'error';
		}
	
		//查询是否存在重复数据
		if(strcmp($resp_array['status'],'error') == 0 || strcmp($resp_array['status'],'isnull') == 0){
			echo json_encode($resp_array);exit;
		}
	
		//查询出插入的数据
		$sql="select * FROM $table_name WHERE   `id` ='".$id."'";
		$all=$this->db->get_row($sql);
		if($all){
			$score=$all['score']+1;
			if(strcmp($name,'question') == 0 ){
				if(empty($value)){
					$resp_array['status'] = 'isnull';
				}else{
						
					if(disease_is_exist($table_name,$value,$answer_check,$express_check,$proof_check,$nicety_check,$keyword_check,$reference_check,$yesid_check,$notid_check,$groupid_check,$id)){//去重
						$resp_array['status'] = 'repeat';
					}else{
						$sql = "UPDATE $table_name SET  `question` =  '{$value}',`score` =  '{$score}',`editTime`='{$time}' WHERE  `id` ={$id};";
						$this->db->query($sql);
						$resp_array['status'] = 'success';
					}
				}
			}
			if(strcmp($name,'answer') == 0 ){
	
				if(disease_is_exist($table_name,$question_check,$value,$express_check,$proof_check,$nicety_check,$keyword_check,$reference_check,$yesid_check,$notid_check,$groupid_check,$id)){//去重
					$resp_array['status'] = 'repeat';
				}else{
						
					$sql = "UPDATE $table_name SET  `answer` =  '{$value}',`score` =  '{$score}', `editTime`='{$time}' WHERE  `id` ={$id};";
					$this->db->query($sql);
					$resp_array['status'] = 'success';
				}
			}
			if(strcmp($name,'express') == 0 ){
				if(disease_is_exist($table_name,$question_check,$answer_check,$value,$proof_check,$nicety_check,$keyword_check,$reference_check,$yesid_check,$notid_check,$groupid_check,$id)){//去重
					$resp_array['status'] = 'repeat';
				}else{
						
					$sql = "UPDATE $table_name SET  `express` =  '{$value}',`score` =  '{$score}',`editTime`='{$time}' WHERE  `id` ={$id};";
					$this->db->query($sql);
					$resp_array['status'] = 'success';
				}
			}
			if(strcmp($name,'proof') == 0 ){
				if(disease_is_exist($table_name,$question_check,$answer_check,$express_check,$value,$nicety_check,$keyword_check,$reference_check,$yesid_check,$notid_check,$groupid_check,$id)){//去重
					$resp_array['status'] = 'repeat';
				}else{
			
					$sql = "UPDATE $table_name SET  `proof` =  '{$value}',`score` =  '{$score}',`editTime`='{$time}' WHERE  `id` ={$id};";
					$this->db->query($sql);
					$resp_array['status'] = 'success';
				}
			}
			if(strcmp($name,'nicety') == 0 ){
				if(disease_is_exist($table_name,$question_check,$answer_check,$express_check,$proof_check,$value,$keyword_check,$reference_check,$yesid_check,$notid_check,$groupid_check,$id)){//去重
					$resp_array['status'] = 'repeat';
				}else{
						
					$sql = "UPDATE $table_name SET  `nicety` =  '{$value}',`score` =  '{$score}',`editTime`='{$time}' WHERE  `id` ={$id};";
					$this->db->query($sql);
					$resp_array['status'] = 'success';
				}
			}

			if(strcmp($name,'keyword') == 0 ){
				
				if(disease_is_exist($table_name,$question_check,$answer_check,$express_check,$proof_check,$nicety_check,$value,$reference_check,$yesid_check,$notid_check,$groupid_check,$id)){//去重
					$resp_array['status'] = 'repeat';
				}else{
			
					$sql = "UPDATE $table_name SET  `keyword` =  '{$value}',`score` =  '{$score}',`editTime`='{$time}' WHERE  `id` ={$id};";
					$this->db->query($sql);
					$resp_array['status'] = 'success';
				}
			}
			if(strcmp($name,'groupid') == 0 ){
				if(disease_is_exist($table_name,$question_check,$answer_check,$express_check,$proof_check,$nicety_check,$keyword_check,$reference_check,$yesid_check,$notid_check,$value,$id)){//去重
					$resp_array['status'] = 'repeat';
				}else{
						
					$sql = "UPDATE $table_name SET  `groupid` =  '{$value}',`score` =  '{$score}',`editTime`='{$time}' WHERE  `id` ={$id};";
					$this->db->query($sql);
					$resp_array['status'] = 'success';
				}
			}
			if(strcmp($name,'reference') == 0 ){
			
				if(disease_is_exist($table_name,$question_check,$answer_check,$express_check,$proof_check,$nicety_check,$keyword_check,$value,$yesid_check,$notid_check,$groupid_check,$id)){//去重
					$resp_array['status'] = 'repeat';
				}else{
			
					$sql = "UPDATE $table_name SET  `reference` =  '{$value}',`score` =  '{$score}', `editTime`='{$time}' WHERE  `id` ={$id};";
					$this->db->query($sql);
					$resp_array['status'] = 'success';
				}
			}
			if(strcmp($name,'yesid') == 0 ){
				if(disease_is_exist($table_name,$question_check,$answer_check,$express_check,$proof_check,$nicety_check,$keyword_check,$reference_check,$value,$notid_check,$groupid_check,$id)){//去重
					$resp_array['status'] = 'repeat';
				}else{
			
					$sql = "UPDATE $table_name SET  `yesid` =  '{$value}',`score` =  '{$score}',`editTime`='{$time}' WHERE  `id` ={$id};";
					$this->db->query($sql);
					$resp_array['status'] = 'success';
				}
			}
			if(strcmp($name,'notid') == 0 ){
				if(disease_is_exist($table_name,$question_check,$answer_check,$express_check,$proof_check,$nicety_check,$keyword_check,$reference_check,$yesid_check,$value,$groupid_check,$id)){//去重
					$resp_array['status'] = 'repeat';
				}else{
						
					$sql = "UPDATE $table_name SET  `notid` =  '{$value}',`score` =  '{$score}',`editTime`='{$time}' WHERE  `id` ={$id};";
					$this->db->query($sql);
					$resp_array['status'] = 'success';
				}
			}
			if(strcmp($name,'score') == 0 ){
				$sql = "UPDATE $table_name SET  `score` =  '{$value}',`editTime`='{$time}'  WHERE  `id` ={$id};";
				$this->db->query($sql);
				$resp_array['status'] = 'success';
			}
		}
		echo json_encode($resp_array);exit;
	
	}


	/**
	 * 问题列表
	 +----------------------------------------------------------
	 * Time:2018-02-06
	 +----------------------------------------------------------
	 */
	
	public function problem_list(){
		$this->template->display("indexes/problem_list.htm");
	}
	
	/**
	 * 问题列表查询接口
	 +----------------------------------------------------------
	 * Time:2018-02-06
	 +----------------------------------------------------------
	 */
	public function problem_action(){
		
		$hid=get_hid();
		
		$aid=get_aid();
		
		$table_name='wisdom_ask_'.$aid;
		
		$questions = trim(get_data('questions'));
		$w = " 1=1 ";
		if($questions!=''){
			$w .= " and question like '%{$questions}%' ";
		}
		$count = $this->db->get_one("select count(*) from $table_name where $w;");
		$p = new Page($count,20);  //分页初始化
	
		$sql = "select * from $table_name where $w order by id desc LIMIT ".$p->firstRow.",".$p->listRows.";";
		$all = $this->db->get_all($sql);
		$this->template->assign('all',$all);
		$this->template->assign('page',$p->show('problem_action'));
		make_json_result($this->template->fetch("indexes/problem_action.htm"),1,array());
	
	}
	
	/**
	 * 添加问题
	 +----------------------------------------------------------
	 * Time:2018-02-06
	 +----------------------------------------------------------
	 */
	public function add_problem(){
		make_json_result($this->template->fetch("indexes/add_problem.htm"),1,array());
	}
	
	/**
	 * 添加问题接口
	 +----------------------------------------------------------
	 * Time:2018-02-06
	 +----------------------------------------------------------
	 */
	public function add_problem_action(){
	
		$hid=get_hid();
		
		$aid=get_aid();
		
		$table_name='wisdom_ask_'.$aid;
		
		$question = trim(get_data('question'));
		$answer = trim(get_data('answer'));

		if($question) {
				$order = array("\r\n", "\n", "\r");
				$question=str_replace($order,",",$question);
		}
	
		$sql = "select * from $table_name where question='{$question}' and answer='{$answer}';";
		$row = $this->db->get_row($sql);
	
		if($row){
			make_json_result('该问题已经存在！',0,array());
		}else{
			$sql = "insert into $table_name(`question`,`answer`) values('{$question}','{$answer}');";
			if($this->db->query($sql)){
				make_json_result('添加成功！',1,array());
			}else{
				make_json_result('添加失败！',0,array());
			}
		}
	}
	
	
	/**
	 * 修改问题
	 +----------------------------------------------------------
	 * Time:2018-02-06
	 +----------------------------------------------------------
	 */
	public function problem_modify(){
		
		$hid=get_hid();
		
		$aid=get_aid();
		
		$table_name='wisdom_ask_'.$aid;
	
		$id = get_data('id');
		$sql = "select * from $table_name where id='{$id}';";
		$row = $this->db->get_row($sql);
		$this->template->assign('row',$row);
		make_json_result($this->template->fetch("indexes/problem_modify.htm"),1,array());
	
	}
	
	/**
	 * 修改问题接口
	 +----------------------------------------------------------
	 * Time:2018-02-06
	 +----------------------------------------------------------
	 */
	public function problem_modify_action(){
	
		$hid=get_hid();
		
		$aid=get_aid();
		
		$table_name='wisdom_ask_'.$aid;
		
		$id = get_data('id');
		
		$question = trim(get_data('question'));
		$answer = trim(get_data('answer'));

		$sql = "update $table_name set question='{$question}',answer='{$answer}' where id='{$id}';";
		$this->db->query($sql);
		make_json_result('修改成功',1,array());
	}
	
	
	/** * 检索是否存在**/
	function problem_is_exist($table_name,$question='',$answer='',$id=0){
		$where=' 1=1 ';
		if(!empty($question)){
			$where.="  and question = '".$question."'";
		}
		if(!empty($answer)){
			$where.="  and answer = '".$answer."'";
		}
		if($id!=0){
			$where.="  and id != ".$id;
		}
		$sql = "select id from $table_name where ".$where;
		$aid = $this->db->get_one($sql);
		if($aid>0){
			return true;
		}else{
			return false;
		}
	}
	

	/**
	 *
	 * 修改问题字段值
	 */
	
	public function edit_problem_data(){
	
		$hid=get_hid();
		$aid=get_aid();
		$table_name='wisdom_ask_'.$aid;
		$id = get_data('id'); //取得id
		$name = get_data('fd'); //取得字段名称
		$value = get_data('val'); //取得修改值
		$question_check = get_data('question_check');
		$answer_check = get_data('answer_check');

		$resp_array = array();
		$resp_array['id'] = $id;
		if(empty($id)){
			$resp_array['status'] = 'error';
		}
		if(empty($question_check) || empty($answer_check)){
			$resp_array['status'] = 'isnull';
		}
		$comu_array =array('question','answer');
	
		if(!in_array($name,$comu_array)){
			$resp_array['status'] = 'error';
		}
	
		//查询是否存在重复数据
		if(strcmp($resp_array['status'],'error') == 0 || strcmp($resp_array['status'],'isnull') == 0){
			echo json_encode($resp_array);exit;
		}
	
		//查询出插入的数据
		$sql="select * FROM $table_name WHERE   `id` ='".$id."'";
		$all=$this->db->get_row($sql);
		if($all){
			if(strcmp($name,'question') == 0 ){
				if(empty($value)){
					$resp_array['status'] = 'isnull';
				}else{

					if($this->problem_is_exist($table_name,$value,$answer_check,$id)){//去重
						$resp_array['status'] = 'repeat';
					}else{
						$sql = "UPDATE $table_name SET  `question` =  '{$value}' WHERE  `id` ={$id};";
						$this->db->query($sql);
						$resp_array['status'] = 'success';
					}
				}
			}
			if(strcmp($name,'answer') == 0 ){
	
				if(empty($value)){
					$resp_array['status'] = 'isnull';
				}else{
					if($this->problem_is_exist($table_name,$question_check,$value,$id)){//去重
						$resp_array['status'] = 'repeat';
					}else{
					
						$sql = "UPDATE $table_name SET  `answer` =  '{$value}' WHERE  `id` ={$id};";
						$this->db->query($sql);
						$resp_array['status'] = 'success';
					}
				}
			}
		}
		echo json_encode($resp_array);exit;
	
	}
	

	/**
	 * 删除问题
	 +----------------------------------------------------------
	 * Time:2018-02-06
	 +----------------------------------------------------------
	 */
	public function problem_delete(){
		
		$hid=get_hid();
		
		$aid=get_aid();
		
		$table_name='wisdom_ask_'.$aid;
	
		$id = get_data('id');
	
		$sql = "delete from $table_name where id='{$id}';";
		$this->db->query($sql);
	
		make_json_result('删除成功',1,array());
	}


}
