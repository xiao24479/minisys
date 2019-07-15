<?php
/**
 * 小程序API
 * Author: Henry
 * Date: 2019/6/6
 */

require_once(APP_PATH."/include/libs.php");
require_once(APP_PATH."/include/FileCache.php");

class Api extends cls_base {

    var $appid;
    var $secret;
    var $authcode2Session = "https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code";

    var $db;

    public function init() {

        global $_DB;

        $this->db = $_DB;

    }

    public function getToken()
    {
        $appid = trim(get_data('appid'));
        $code = get_data('code');
        $sql = "select * from irpt_interface where appId = '{$appid}'";
        $row = $this->db->get_row($sql);

        if (!$row){
            return $this->response("","params failed",4);
        }

        $this->appid = $row['appId'];
        $this->secret = $row['token'];

        $url = sprintf($this->authcode2Session,$this->appid,$this->secret,$code);

        $return = $this->httpGet($url);

        if (!array_key_exists("openid",$return)){
            return $this->response($return,$return['errmsg'],$return['errcode']);
        }

        $cache_dir = APP_PATH."_cache/token";
        $cache = new FileCache($cache_dir);
        $token = $this->randomStr();
        $bool = $cache->set($token,$return,3600);
        if (!$bool){
            return $this->response("","cache failed",4);
        }

        return $this->response(array("token"=>$token));

    }

    public function verifyToken()
    {
        $token = get_data("token");
        if (empty($token)){
            return $this->response("","token is empty",4);
        }

        $cache_dir = APP_PATH."_cache/token";
        $cache = new FileCache($cache_dir);

        $bool = $cache->has($token);

        if (!$bool){
            return $this->response("","token not existed",4);
        }
        $data = $cache->get($token);
        return $this->response($data);

    }

    public function formOrder()
    {
        $token = $_SERVER["HTTP_TOKEN"];
        if (empty($token)){
            return $this->response("","invalid request",40001);
        }

        $cache_dir = APP_PATH."_cache/token";
        $cache = new FileCache($cache_dir);

        $bool = $cache->has($token);

        if (!$bool){
            return $this->response("","token not existed",4);
        }

        $info = $cache->get($token);

        if (!$info) {
            return $this->response("","token value is null",40002);
        }

        $appid = trim(get_data('appid'));
        $vistor_name = trim(get_data('vistor_name'));
        $vistor_id   = get_data('vistor_id');
        $phone       = trim(get_data('phone'));
        $account     = trim(get_data('account'));
        $today       = strtotime(date('Y-m-d', time()));

        if(!preg_match("/^1[3-8]\d{9}$/", $phone)){
            return $this->response("","请输入正确的手机号",40003);
        }

        $sql  = " select id from gossip_order where  phone='{$phone}' and vistor_name='{$vistor_name}' and createTime > {$today}";
        $omne = $this->db->get_all($sql);

        //同个患者每天只能提交3次预约信息
        if(count($omne) >= 3){
            return $this->response("","您今天的预约次数已用完!",40004);
        }

        $sql    = "select id_hospital,name from irpt_interface where appId = '{$appid}'";
        $row    = $this->db->get_row($sql);

        $hos_id = $row['id_hospital'];
        $source = $row['name'];

        $sql = "insert into gossip_order(`hos_id`,`vistor_id`,`vistor_name`,`phone`,`type`,`account`, `source`, `createTime`) 
				values('{$hos_id}','{$vistor_id}','{$vistor_name}','{$phone}',0,'{$account}', '{$source}','".time()."');";

        if($this->db->query($sql)){
            return $this->response("","预约成功!");
        }else{
            return $this->response("","数据错误，请联系客服！",40005);
        }
    }

    protected function response($data=array(),$msg="success",$errno=0)
    {
        $return = array(
            "errno" => $errno,
            "msg" => $msg,
            "data" => $data,
        );
        echo json_encode($return,JSON_UNESCAPED_UNICODE);
        exit();
    }


    protected function randomStr($length=10)
    {
        $wholeStr = 'abcdefghijklmnopqrstuvwxyzABCDEFGHJKLMNPQEST123456789';
        $wholeStr = str_shuffle($wholeStr);
        $max_len = strlen($wholeStr)-1;
        $str = '';
        for ($i = 0; $i < $length-1; $i++) {
            $str.= substr($wholeStr,rand(0,$max_len),1);
        }
        return $str;
    }

    protected function httpGet($url)
    {
        //初始化
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        //显示获得的数据
        return json_decode($data,true);
    }

    protected function httpPost($data,$url)
    {
        //初始化
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //设置post方式提交
        curl_setopt($curl, CURLOPT_POST, 1);
        //设置post数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        //执行命令
        $resp = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        //显示获得的数据
        return json_decode($resp,true);
    }

}