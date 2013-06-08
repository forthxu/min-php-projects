<?php
if(!isset($_GET['num']) || $_GET['num']!='472767085')exit;//安全需要
header("Content-type: text/html; charset=utf-8"); 
//配置
$qqnums=array(
	'ht'=>array(
		'qq'=>263967133,
		'feixins'=>array(
			0=>array(
			'from'=>array('phone'=>18205999765,'pw'=>'18205999765'),
			'to'=>array(0=>18205999765)
			)
		)
	),
	'xfs'=>array(
		'qq'=>472767085,
		'feixins'=>array(
			0=>array(
			'from'=>array('phone'=>18205999765,'pw'=>'18205999765'),
			'to'=>array(0=>18205999765)
			)
		)
	)
);
$isSae = true;//是否为sae环境
$file = "huangtingdata.php";//本地环境写入的文件，确保存在此文件
$mmc=false;

if($isSae){
	$mmc=memcache_init();
}else{
	if(file_exists($file) == false){
		die($file.'文件不存在');
	}else{
		if(is_readable($file) == false){
			die($file.'无法不可读取');
		}else{
			$fileDataTmp = file_get_contents($file);
			if(!empty($fileDataTmp))$fileData = unserialize($fileDataTmp);
		}
	}
}

foreach($qqnums as $key=>$user){
	$online_data = php_qq_status($user['qq']);
	$online = $online_data['status'];
	//echo $online;
	$message = '';
	if($mmc==false && $isSae){
		$message = 'memcache初始失败';
	}else{
		if($isSae){
			$online_old = memcache_get($mmc,"u".$key."online");
		}else{
			$online_old = $fileData["u".$key."online"];
		}
		//echo "--$online---$online_old--";
		if($online!=$online_old && $online!='3'  && $online!='2'){
		//if($online!=$online_old){
			if($isSae){
				memcache_set($mmc,"u".$key."online",$online);
			}else{
				if(is_writable($file) == false){
					die($file.'不能写入文件');
				}else{
					$fileData["u".$key."online"] = $online;
					file_put_contents($file, serialize($fileData));
				}
			}
			$time = date('Y-m-d H:i:s',time());
			if($online=='0'){
				$message = $time.'不在线';
				//$message = $time.'不在线:【'.$online_data['msg'].'】';
			}elseif($online=='1'){
				$message = $time.'在线';
				//$message = $time.'在线:【'.$online_data['msg'].'】';
			}elseif($online=='2'){
				$message = $time.'状态获取异常:【'.$online_data['msg'].'】';
			}elseif($online=='3'){
				$message = $time.'状态25异常:【'.$online_data['msg'].'】';
				//$message = '';
			}
		}
	}
	if($message){
		if($user['feixins']){
			foreach($user['feixins'] as $key2=>$feixin){
				//require 'PHPFetion.php';
				$fetion = new PHPFetion($feixin['from']['phone'], $feixin['from']['pw']); // 手机号、飞信密码
				foreach($feixin['to'] as $key3=>$touser){
					//$message = iconv('gbk', 'utf-8', $message);
					$fetion->send($touser, $key.':'.$message);
				}
			}
		}
		echo $key.':'.$message.'<br/>';
	}else{
		$time = date('Y-m-d H:i:s',time());
		if($online=='0'){
			$message = $time.'不在线';
			//$message = $time.'不在线:【'.$online_data['msg'].'】';
		}elseif($online=='1'){
			$message = $time.'在线';
			//$message = $time.'在线:【'.$online_data['msg'].'】';
		}elseif($online=='3'){
			$message = $time.'状态25异常:【'.$online_data['msg'].'】';
		}elseif($online=='2'){
			$message = $time.'状态获取异常:【'.$online_data['msg'].'】';
		}
		echo $key.':'.'不变:'.$message.'<br/>';
	}
}

//获取qq在线状态
function php_qq_status($qqnum) {
		// 测试用的浏览器信息
		$browsers = array(
			"standard" => array (
				"user_agent" => "User-Agent:Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.91 Safari/537.11",
				"language" => "Accept-Language:en-US,en;q=0.8,zh-CN;q=0.6,zh;q=0.4"
				),
			"iphone" => array (
				"user_agent" => "Mozilla/5.0 (iPhone; U; CPU like Mac OS X; en) AppleWebKit/420+ (KHTML, like Gecko) Version/3.0 Mobile/1A537a Safari/419.3",
				"language" => "en"
				),
			"french" => array (
				"user_agent" => "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; GTB6; .NET CLR 2.0.50727)",
				"language" => "fr,fr-FR;q=0.5"
				)
		);
  $header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,"; 
  $header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5"; 
  $header[] = "Cache-Control: max-age=0"; 
  $header[] = "Connection: keep-alive"; 
  $header[] = "Keep-Alive: 300"; 
  $header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7"; 
  $header[] = "Accept-Language: en-us,en;q=0.5"; 
  $header[] = "Pragma: "; // browsers keep this blank. 
  
	  $url='http://wpa.qq.com/pa?p=1:'.$qqnum.':1';
	  $curl = curl_init();
	  curl_setopt($curl, CURLOPT_URL, $url);
	  curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
	  // 设置浏览器的特定header
	  curl_setopt($curl, CURLOPT_HTTPHEADER, $browsers['standard']);//浏览器
	  //curl_setopt($curl, CURLOPT_HTTPHEADER, $header); 
	  //curl_setopt($curl, CURLOPT_FRESH_CONNECT,1);//强制获取一个新的连接，替代缓存中的连接。
	  curl_setopt($curl, CURLOPT_HEADER, 1);//显示header
	  curl_setopt($curl, CURLOPT_NOBODY, 1);//不显示body
	  curl_setopt($curl, CURLOPT_ENCODING, "gzip,deflate,sdch");//HTTP请求头中"Accept-Encoding: "的值。支持的编码有"identity"，"deflate"和"gzip"。如果为空字符串""，请求头会发送所有支持的编码类型。
	  $data=curl_exec($curl);
	  curl_close($curl);
	  $status=explode("Content-Length: ",$data);
	  $status=explode("\r\n",$status[1]);
	  if($status[0]=="0"){
		  $location=explode("Location: ",$data);
		  $location=explode("\r\n",$location[1]);
		  
		  if ($location[0]=="http://pub.idqqimg.com/qconn/wpa/button/button_old_10.gif"){
		  $result['status']="0";$result['msg']=$data;}
		  elseif ($location[0]=="http://pub.idqqimg.com/qconn/wpa/button/button_old_11.gif"){
		  $result['status']="1";$result['msg']=$data;}
		  else{
		  $result['status']="2";$result['msg']=$data;}
		  return $result;
	  }
	  
	  if ($status[0]=="2262"){
	  $result['status']="0";$result['msg']=$data;}
	  elseif ($status[0]=="2329"){
	  $result['status']="1";$result['msg']=$data;}
	  elseif ($status[0]=="25"){
	  $result['status']="3";$result['msg']=$data;}
	  else{
	  $result['status']="2";$result['msg']=$data;}
	  return $result;
}

/**
 * PHP飞信发送类
 *
 * @author quanhengzhuang <blog.quanhz.com>
 * @version 1.5.0
 */
class PHPFetion
{

    /**
     * 发送者手机号
     * @var string
     */
    protected $_mobile;

    /**
     * 飞信密码
     * @var string
     */
    protected $_password;

    /**
     * Cookie字符串
     * @var string
     */
    protected $_cookie = '';

    /**
     * Uid缓存
     * @var array
     */
    protected $_uids = array();

    /**
     * csrfToken
     * @var string
     */
    protected $_csrfToten = null;

    /**
     * 构造函数
     * @param string $mobile 手机号(登录者)
     * @param string $password 飞信密码
     */
    public function __construct($mobile, $password)
    {
        if ($mobile === '' || $password === '')
        {
            return;
        }
        
        $this->_mobile = $mobile;
        $this->_password = $password;
        
        $this->_login();
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        $this->_logout();
    }

    /**
     * 登录
     * @return string
     */
    protected function _login()
    {
        $uri = '/huc/user/space/login.do?m=submit&fr=space';
        $data = 'mobilenum='.$this->_mobile.'&password='.urlencode($this->_password);
        
        $result = $this->_postWithCookie($uri, $data);

        //解析Cookie
        preg_match_all('/.*?\r\nSet-Cookie: (.*?);.*?/si', $result, $matches);
        if (isset($matches[1]))
        {
            $this->_cookie = implode('; ', $matches[1]);
        }
        
        $result = $this->_postWithCookie('/im/login/cklogin.action', '');

        return $result;
    }

    /**
     * 向指定的手机号发送飞信
     * @param string $mobile 手机号(接收者)
     * @param string $message 短信内容
     * @return string
     */
    public function send($mobile, $message)
    {
        if ($message === '')
        {
            return '';
        }

        //判断是给自己发还是给好友发
        if ($mobile == $this->_mobile)
        {
            return $this->_toMyself($message);
        }
        else
        {
            $uid = $this->_getUid($mobile);

            return $uid === '' ? '' : $this->_toUid($uid, $message);
        }
    }

    /**
     * 获取飞信ID
     * @param string $mobile 手机号
     * @return string
     */
    protected function _getUid($mobile)
    {
        if (empty($this->_uids[$mobile]))
        {
            $uri = '/im/index/searchOtherInfoList.action';
            $data = 'searchText='.$mobile;
            
            $result = $this->_postWithCookie($uri, $data);
            
            //匹配
            preg_match('/toinputMsg\.action\?touserid=(\d+)/si', $result, $matches);

            $this->_uids[$mobile] = isset($matches[1]) ? $matches[1] : '';
        }
        
        return $this->_uids[$mobile];
    }

    /**
     * 获取csrfToken，给好友发飞信时需要这个字段
     * @param string $uid 飞信ID
     * @return string
     */
    protected function _getCsrfToken($uid)
    {
        if ($this->_csrfToten === null)
        {
            $uri = '/im/chat/toinputMsg.action?touserid='.$uid;
            
            $result = $this->_postWithCookie($uri, '');
            
            preg_match('/name="csrfToken".*?value="(.*?)"/', $result, $matches);

            $this->_csrfToten = isset($matches[1]) ? $matches[1] : '';
        }

        return $this->_csrfToten;
    }

    /**
     * 向好友发送飞信
     * @param string $uid 飞信ID
     * @param string $message 短信内容
     * @return string
     */
    protected function _toUid($uid, $message)
    {
        $uri = '/im/chat/sendMsg.action?touserid='.$uid;
        $csrfToken = $this->_getCsrfToken($uid);
        $data = 'msg='.urlencode($message).'&csrfToken='.$csrfToken;
        
        $result = $this->_postWithCookie($uri, $data);
        
        return $result;
    }

    /**
     * 给自己发飞信
     * @param string $message
     * @return string
     */
    protected function _toMyself($message)
    {
        $uri = '/im/user/sendMsgToMyselfs.action';
        $result = $this->_postWithCookie($uri, 'msg='.urlencode($message));

        return $result;
    }

    /**
     * 退出飞信
     * @return string
     */
    protected function _logout()
    {
        $uri = '/im/index/logoutsubmit.action';
        $result = $this->_postWithCookie($uri, '');
        
        return $result;
    }

    /**
     * 携带Cookie向f.10086.cn发送POST请求
     * @param string $uri
     * @param string $data
     */
    protected function _postWithCookie($uri, $data)
    {
        $fp = fsockopen('f.10086.cn', 80);
        fputs($fp, "POST $uri HTTP/1.1\r\n");
        fputs($fp, "Host: f.10086.cn\r\n");
        fputs($fp, "Cookie: {$this->_cookie}\r\n");
        fputs($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
        fputs($fp, "User-Agent: Mozilla/5.0 (Windows NT 5.1; rv:14.0) Gecko/20100101 Firefox/14.0.1\r\n");
        fputs($fp, "Content-Length: ".strlen($data)."\r\n");
        fputs($fp, "Connection: close\r\n\r\n");
        fputs($fp, $data);

        $result = '';
        while (!feof($fp))
        {
            $result .= fgets($fp);
        }

        fclose($fp);

        return $result;
    }

}
?>