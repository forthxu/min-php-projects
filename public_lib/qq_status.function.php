<?php
/**
 * 获取qq在线状态
 *
 * @author forthxu <forthxu.com>
 * @version 1.0.0.201306081913
 */
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
?>