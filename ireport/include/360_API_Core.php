<?php
class Xcrypt
{

    private $mcrypt;

    private $key;

    private $mode;

    public $iv;

    private $blocksize;

    /**
     * 构造函数
     *
     * @param
     *            string
     *            密钥
     * @param
     *            string
     *            模式
     * @param
     *            string
     *            向量（"off":不使用
     *            /
     *            "auto":自动
     *            /
     *            其他:指定值，长度同密钥）
     */
    public function __construct ($key, $mode = 'cbc', $iv = "off")
    {
        switch (strlen($key)) {
            case 8:
                $this->mcrypt = MCRYPT_DES;
                break;
            case 16:
                $this->mcrypt = MCRYPT_RIJNDAEL_128;
                break;
            case 32:
                $this->mcrypt = MCRYPT_RIJNDAEL_256;
                break;
            default:
                die("Key size must be 8/16/32");
        }
        
        $this->key = $key;
        
        switch (strtolower($mode)) {
            case 'ofb':
                $this->mode = MCRYPT_MODE_OFB;
                if ($iv == 'off')
                    die('OFB must give a IV'); // OFB必须有向量
                break;
            case 'cfb':
                $this->mode = MCRYPT_MODE_CFB;
                if ($iv == 'off')
                    die('CFB must give a IV'); // CFB必须有向量
                break;
            case 'ecb':
                $this->mode = MCRYPT_MODE_ECB;
                $iv = 'off'; // ECB不需要向量
                break;
            case 'cbc':
            default:
                $this->mode = MCRYPT_MODE_CBC;
        }
        
        switch (strtolower($iv)) {
            case "off":
                $this->iv = null;
                break;
            case "auto":
                $source = PHP_OS == 'WINNT' ? MCRYPT_RAND : MCRYPT_DEV_RANDOM;
                $this->iv = mcrypt_create_iv(mcrypt_get_block_size($this->mcrypt, $this->mode), $source);
                echo mcrypt_get_block_size($this->mcrypt, $this->mode);
                break;
            default:
                $this->iv = $iv;
        }
    }

    /**
     * 获取向量值
     * 
     * @param
     *            string
     *            向量值编码（base64/hex/bin）
     * @return string
     *         向量值
     */
    public function getIV ($code = 'base64')
    {
        switch ($code) {
            case 'base64':
                $ret = base64_encode($this->iv);
                break;
            case 'hex':
                $ret = bin2hex($this->iv);
                break;
            case 'bin':
            default:
                $ret = $this->iv;
        }
        return $ret;
    }

    /**
     * 加密
     * 
     * @param
     *            string
     *            明文
     * @param
     *            string
     *            密文编码（base64/hex/bin）
     * @return string
     *         密文
     */
    public function encrypt ($str, $code = 'base64')
    {
        if ($this->mcrypt == MCRYPT_DES)
            $str = $this->_pkcs5Pad($str);
        
        if (isset($this->iv)) {
            $result = mcrypt_encrypt($this->mcrypt, $this->key, $str, $this->mode, $this->iv);
        } else {
            @$result = mcrypt_encrypt($this->mcrypt, $this->key, $str, $this->mode);
        }
        
        switch ($code) {
            case 'base64':
                $ret = base64_encode($result);
                break;
            case 'hex':
                $ret = bin2hex($result);
                break;
            case 'bin':
            default:
                $ret = $result;
        }
        
        return $ret;
    }

    /**
     * 解密
     * 
     * @param
     *            string
     *            密文
     * @param
     *            string
     *            密文编码（base64/hex/bin）
     * @return string
     *         明文
     */
    public function decrypt ($str, $code = "base64")
    {
        $ret = false;
        
        switch ($code) {
            case 'base64':
                $str = base64_decode($str);
                break;
            case 'hex':
                $str = $this->_hex2bin($str);
                break;
            case 'bin':
            default:
        }
        
        if ($str !== false) {
            if (isset($this->iv)) {
                $ret = mcrypt_decrypt($this->mcrypt, $this->key, $str, $this->mode, $this->iv);
            } else {
                @$ret = mcrypt_decrypt($this->mcrypt, $this->key, $str, $this->mode);
            }
            if ($this->mcrypt == MCRYPT_DES)
                $ret = $this->_pkcs5Unpad($ret);
            $ret = trim($ret);
        }
        
        return $ret;
    }

    private function _pkcs5Pad ($text)
    {
        $this->blocksize = mcrypt_get_block_size($this->mcrypt, $this->mode);
        $pad = $this->blocksize - (strlen($text) % $this->blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    private function _pkcs5Unpad ($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text))
            return false;
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad)
            return false;
        $ret = substr($text, 0, - 1 * $pad);
        return $ret;
    }

    private function _hex2bin ($hex = false)
    {
        $ret = $hex !== false && preg_match('/^[0-9a-fA-F]+$/i', $hex) ? pack("H*", $hex) : false;
        return $ret;
    }
}  



function xml_to_json($source){
		if(is_file($source)){ //传的是文件，还是xml的string的判断
		$xml_array=simplexml_load_file($source);
		}else{
		$xml_array=simplexml_load_string($source);
		}
		$json = json_encode($xml_array); //php5，以及以上，如果是更早版本，请查看JSON.php
		return $json;
}
	
function xmlToArray($xml){    
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);        
        return $values;
}



function clientLogin_curl_api($urer){
		$data['username'] =  $urer['username'];
		$m = new Xcrypt(substr($urer['apiSecret'], 0, 16), 'cbc', substr($urer['apiSecret'], 16, 16));
		$data['passwd']	  =	 $m->encrypt(md5($urer['password']), 'hex');
		$data['format']	  =  'json';	
		$haader = array(
			"apiKey:{$urer['apiKey']}"
		);	
		$clientLogin_url ='https://api.e.360.cn/account/clientLogin';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$clientLogin_url );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($ch, CURLOPT_POST,           1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,     $data ); //$data是每个接口的json字符串
		curl_setopt($ch, CURLOPT_HTTPHEADER,$haader);
		$requrt =  json_decode(curl_exec($ch));
		$arr['serveToken'] = $requrt->uid;
		$arr['accessToken'] = $requrt->accessToken;
		return $arr;
}
function get_360_cost($urer,$access,$date){
		$data['username'] =  $urer['username'];
		$m = new Xcrypt(substr($urer['apiSecret'], 0, 16), 'cbc', substr($urer['apiSecret'], 16, 16));
		$data['passwd']	  =	 $m->encrypt(md5($urer['password']), 'hex');
		$data['format']	  =  'json';
		$data['startDate'] 	= $date." 00:00:00";
		$data['endDate'] 	= $date." 24:00:00";
		$data['groupBy'] 	= 'account';
		$url  = "https://api.e.360.cn/2.0/report/hourList";
		$haader = array(
			"apiKey:{$urer['apiKey']}",
			"serveToken:{$access['serveToken']}",
			"accessToken:{$access['accessToken']}",
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($ch, CURLOPT_POST,           1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,     $data ); //$data是每个接口的json字符串
		curl_setopt($ch, CURLOPT_HTTPHEADER,$haader);
		$requrt =  json_decode(curl_exec($ch));
		$array['costs']  = 0;
		$array['clicks']  = 0;
		$array['views']  = 0;

		foreach ($requrt->hourList as $val){
			$array['costs']  	+= $val->totalCost;
			$array['clicks']  += $val->clicks;
			$array['views'] 	+= $val->views;
		}	
		return $array;
}


function get_360_cost_group($urer,$access,$date){
		$data['username'] =  $urer['username'];
		$m = new Xcrypt(substr($urer['apiSecret'], 0, 16), 'cbc', substr($urer['apiSecret'], 16, 16));
		$data['passwd']	  =	 $m->encrypt(md5($urer['password']), 'hex');
		$data['format']	  =  'json';
		$data['startDate'] 	= $date;
		$data['endDate'] 	= $date;
		$url  = "https://api.e.360.cn/2.0/report/group";
		$haader = array(
			"apiKey:{$urer['apiKey']}",
			"serveToken:{$access['serveToken']}",
			"accessToken:{$access['accessToken']}",
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($ch, CURLOPT_POST,           1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,     $data ); //$data是每个接口的json字符串
		curl_setopt($ch, CURLOPT_HTTPHEADER,$haader);
		$requrt =  json_decode(curl_exec($ch));
		$array['costs']  = 0;
		$array['clicks']  = 0;
		$array['views']  = 0;
		foreach ($requrt->groupList as $val){
				$array['costs']  	+= $val->totalCost;
				$array['clicks']  += $val->clicks;
				$array['views'] 	+= $val->views;
		}
			
		return $array;

}


function get_360_cost_hour($urer,$access,$date,$time_duan){
		$data['username'] =  $urer['username'];
		$m = new Xcrypt(substr($urer['apiSecret'], 0, 16), 'cbc', substr($urer['apiSecret'], 16, 16));
		$data['passwd']	  =	 $m->encrypt(md5($urer['password']), 'hex');
		$data['format']	  =  'json';
		$data['startDate'] 	= $date." 00:00:00";
		$data['endDate'] 	= $date." ".$time_duan.":00:00";
		$data['groupBy'] 	= 'account';
		$url  = "https://api.e.360.cn/2.0/report/hourList";
		$haader = array(
			"apiKey:{$urer['apiKey']}",
			"serveToken:{$access['serveToken']}",
			"accessToken:{$access['accessToken']}",
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($ch, CURLOPT_POST,           1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,     $data ); //$data是每个接口的json字符串
		curl_setopt($ch, CURLOPT_HTTPHEADER,$haader);
		$requrt =  json_decode(curl_exec($ch));
		$array['costs']  = 0;
		$array['clicks']  = 0;
		$array['views']  = 0;

		foreach ($requrt->hourList as $val){
			$array['costs']  	+= $val->totalCost;
			$array['clicks']  += $val->clicks;
			$array['views'] 	+= $val->views;
		}	
		return $array;
}
function get_360_cost_plan($urer,$access,$date){
		$data['username'] =  $urer['username'];
		$m = new Xcrypt(substr($urer['apiSecret'], 0, 16), 'cbc', substr($urer['apiSecret'], 16, 16));
		$data['passwd']	  =	 $m->encrypt(md5($urer['password']), 'hex');
		$data['format']	  =  'json';
		$data['startDate'] 	= $date;
		$data['endDate'] 	= $date;
		$data['groupBy'] 	= 'account';
		$url  = "https://api.e.360.cn/2.0/report/campaign";
		$haader = array(
			"apiKey:{$urer['apiKey']}",
			"serveToken:{$access['serveToken']}",
			"accessToken:{$access['accessToken']}",
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($ch, CURLOPT_POST,           1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,     $data ); //$data是每个接口的json字符串
		curl_setopt($ch, CURLOPT_HTTPHEADER,$haader);
		$requrt =  json_decode(curl_exec($ch));
		
		return $requrt->campaignList;
		
}




