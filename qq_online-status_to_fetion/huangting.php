<?php
//安全需要
if(!isset($_GET['num']) || $_GET['num']!='472767085')exit;
//设置编码
header("Content-type: text/html; charset=utf-8");
//引入文件
requrie_once("./public_lib/fetion.class.php");
requrie_once("./public_lib/qq_status.function.php");
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
?>