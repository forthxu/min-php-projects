<?php
/**
  * 徐福盛，forthxu.com
  * 微信公众平台类，修改自官方SDK
  */

//define your token
define("TOKEN", "dgsfhdgthrthgsrgv");//与平台约定token
$wechatObj = new wechatCallbackapiTest(TOKEN,true);//初始化类，token和是否为debug模式
//$wechatObj->valid();//注意！！！初始化和平台对接验证时开启，其余时间注释
$wechatObj->responseMsg();//使用时开启，互动回复在此函数类编写，也可放弃此函数将互动逻辑需要的地方使用$wechatObj->makeMsg($type='text',$data='',$flag=0)方法

class wechatCallbackapiTest
{
	var $time = '';
	var $token = '';
	var $debug = false;
	var $textTpl = '
					<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime><![CDATA[%s]]></CreateTime>
					<MsgType><![CDATA[text]]></MsgType>
					<Content><![CDATA[%s]]></Content>
					<FuncFlag>%d</FuncFlag>
					</xml>
					';
	var $musicTpl = '
					 <xml>
					 <ToUserName><![CDATA[%s]]></ToUserName>
					 <FromUserName><![CDATA[%s]]></FromUserName>
					 <CreateTime><![CDATA[%s]]></CreateTime>
					 <MsgType><![CDATA[music]]></MsgType>
					 <Music>
					 <Title><![CDATA[%s]]></Title>
					 <Description><![CDATA[%s]]></Description>
					 <MusicUrl><![CDATA[%s]]></MusicUrl>
					 <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
					 </Music>
					 <FuncFlag>%d</FuncFlag>
					 </xml>
					';
	var $newsTplWrap = '
					 <xml>
					 <ToUserName><![CDATA[%s]]></ToUserName>
					 <FromUserName><![CDATA[%s]]></FromUserName>
					 <CreateTime><![CDATA[%s]]></CreateTime>
					 <MsgType><![CDATA[news]]></MsgType>
					 <ArticleCount><![CDATA[%s]]></ArticleCount>
					 <Articles>%s
					 </Articles>
					 <FuncFlag>%d</FuncFlag>
					 </xml> 
 					';
	var $newsTplItem = '
					<item>
					 <Title><![CDATA[%s]]></Title> 
					 <Description><![CDATA[%s]]></Description>
					 <PicUrl><![CDATA[%s]]></PicUrl>
					 <Url><![CDATA[%s]]></Url>
 					</item>';
	
	/**
	* 类实例化，配置参数并获取平台传递的信息
	*/
	function __construct($token,$debug){
        $this->token = $token;
        $this->debug = $debug;
		$this->time = time();
		$this->getMsg();
	}
	
	/**
	* 初始化用于获取平台传递的信息
	*/
	private function getMsg(){
		$this->postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		if (!empty($this->postStr)){
                
              	$this->postObj = simplexml_load_string($this->postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
				
                $this->fromUsername = $this->postObj->FromUserName;
                $this->toUsername = $this->postObj->ToUserName;
				$this->msgType = $this->postObj->MsgType;
				$this->createTime = $this->postObj->CreateTime;

				if($this->postObj)
                {
              		switch($this->msgType){
						case 'text':
							$this->Content = trim($this->postObj->Content);
							$this->MsgId = $this->postObj->MsgId;
							break;
						case 'image':
							$this->PicUrl = $this->postObj->PicUrl;
							$this->MsgId = $this->postObj->MsgId;
							break;
						case 'location':
							$this->Location_X = $this->postObj->Location_X;
							$this->Location_Y = $this->postObj->Location_Y;
							$this->Scale = $this->postObj->Scale;
							$this->Label = $this->postObj->Label;
							$this->MsgId = $this->postObj->MsgId;
							break;
						case 'link':
							$this->Title = $this->postObj->Title;
							$this->Description = $this->postObj->Description;
							$this->Url = $this->postObj->Url;
							$this->MsgId = $this->postObj->MsgId;
							break;
						case 'event':
							$this->Event = $this->postObj->Event;
							$this->EventKey = $this->postObj->EventKey;
							break;
						default:
							$this->setLog("消息类型错误，传过来的类型：{$this->msgType}！");
					}
                }else{
                	$this->setLog('解析xml错误！');
                }

        }else{
        	$this->setLog('没能收到平台POST的数据！');
        }
	}

	/**
	* 按逻辑回复信息，主要互动回复需要编辑的地方
	*/
    public function responseMsg()
    {
		$keyword = trim($this->Content);
		if(!empty( $keyword ))
		{
			switch($keyword){//发送的是普通文本信息
				case 'Hello2BizUser':
					$MsgType = "text";
					$data['Content'] = "谢谢关注，回复help查询指令";
					break;
				case 'help':
					$MsgType = "text";
					$data['Content'] = "Welcome!\n可回复一下指令!\nhelp：出现帮助信息\n下载：下载我写的app\nabout：关于我\nmusic：返回一首歌\nnews：返回两篇文章\n城市天气：查询天气，如：厦门天气\n关键字电影：查询电影，如：张艺谋电影\n关键字音乐：查询音乐，如：周杰伦音乐\n关键字图书：查询图书，如：简爱图书";
					break;
				case 'about':
					$MsgType = "text";
					$data['Content'] = "徐福盛编写的微信类！";
					break;
				case 'music':
					$MsgType = "music";
					$data=array('Title'=>'值得聆听的国外英文好曲','Description'=>'值得聆听的国外英文好曲值得聆听的国外英文好曲值得聆听的国外英文好曲值得聆听的国外英文好曲','MusicUrl'=>'http://qqmusic.djwma.com/mp3/%E5%80%BC%E5%BE%97%E8%81%86%E5%90%AC%E7%9A%84%E5%9B%BD%E5%A4%96%E8%8B%B1%E6%96%87%E5%A5%BD%E6%9B%B2.mp3','HQMusicUrl'=>'http://qqmusic.djwma.com/mp3/%E5%80%BC%E5%BE%97%E8%81%86%E5%90%AC%E7%9A%84%E5%9B%BD%E5%A4%96%E8%8B%B1%E6%96%87%E5%A5%BD%E6%9B%B2.mp3');
					break;
				case 'news':
					$MsgType = "news";
					$data=array(
						0=>array('Title'=>'有哪些值得推荐的学习网站？','Description'=>'综合类： TED: Ideas worth spreadinghttp://www.ted.co...','PicUrl'=>'http://xmit.sinaapp.com/public/main/dd5pic.jpg','Url'=>'http://xmit.sinaapp.com/detail-13.html'),
						1=>array('Title'=>'青春、励志、心情、人生、语录 、受伤、暖文章、治愈系、致梦 ','Description'=>'学会选择，懂得放弃，人生才能如鱼得水。 选择是一种量力而行的睿智与远见，放弃是一种顾全大局的果断和胆识。 ...','PicUrl'=>'http://wk.impress.sinaimg.cn/maxwidth.600/sto.kan.weibo.com/dd6581ebb979925a27d2578a01d1c6d8.jpg?width=580&height=387','Url'=>'http://xmit.sinaapp.com/detail-9.html')
					);
					break;
				case '下载':
					$MsgType = "text";
					$data['Content'] = "http://t1.dfs.kuaipan.cn/cdlsched/getdl?fid=5773492607813803&acc_params=entryid:5773492607813803,pickupCode:&extm=1364808228-59e2ec135844837077308a3615bd7a3a&snk_in_get=1";
					break;
				case '大转盘':
					$MsgType = "text";
					$data['Content'] = 'http://barragan.com.ne.kr/roulette/';
					break;
				case 'user':
					$MsgType = "text";
					$data['Content'] = "fromUsername->{$this->fromUsername}\n toUsername->{$this->toUsername}\n msgType->{$this->msgType}\n createTime->{$this->createTime}";
					break;
				default:
					$keyword2[0] = substr($keyword, -6, strlen($keyword));
					$keyword2[1] = trim(substr($keyword, 0, strlen($keyword) - 6));
					switch($keyword2[0]){
						case '天气':
							$url = "http://api2.sinaapp.com/search/weather/?appkey=20130430&appsecert=fa6095e113cd28fd&reqtype=text&keyword={$keyword2[1]}";
							$weatherJson = file_get_contents($url);
							$weather = json_decode($weatherJson, true);
							if($weather['text']['content']){
								$MsgType = "text";
								$data['Content'] = $weather['text']['content'];
							}else{
								$this->setLog('没能查询到您指定地点的天气！');
							}
							break;
						case '电影':
							if($keyword2[1] == 'top'){
								$url = "http://api.douban.com/v2/movie/top250";
							}elseif($keyword2[1] == '票房'){
								$url = "http://api.douban.com/v2/movie/us_box";
							}else{
								$url = "http://api.douban.com/v2/movie/search?q={$keyword2[1]}&count=10";
							}
							$filedata = file_get_contents($url);
							$jsondata = json_decode($filedata, true);
							if($jsondata['total']>0){
								$MsgType = "news";
								foreach($jsondata['subjects'] as $key=>$item){
									//print_r($item);
									$data[$key]['Title']=$item['title'];
									$data[$key]['Description']="平均评分：{$item['rating']['average']}★：{$item['rating']['stars']}year：{$item['year']}";
									$data[$key]['PicUrl']=$item['images']['medium'];
									$data[$key]['Url']=$item['alt'];
								}
							}else{
								$this->setLog('没能查询到您指定的电影！'.$jsondata['total']);
							}
							break;
						case '图书':
							$url = "http://api.douban.com/v2/book/search?q={$keyword2[1]}&count=10";
							$filedata = file_get_contents($url);
							$jsondata = json_decode($filedata, true);
							if($jsondata['total']>0){
								$MsgType = "news";
								foreach($jsondata['books'] as $key=>$item){
									//print_r($item);
									$data[$key]['Title']=$item['title'];
									$data[$key]['Description']="平均评分：{$item['rating']['average']}简介：{$item['summary']}year：{$item['pubdate']}";
									$data[$key]['PicUrl']=$item['images']['medium'];
									$data[$key]['Url']=$item['alt'];
								}
							}else{
								$this->setLog('没能查询到您指定的图书！'.$jsondata['total']);
							}
							break;
						case '音乐':
							$url = "http://api.douban.com/v2/music/search?q={$keyword2[1]}&count=10";
							$filedata = file_get_contents($url);
							$jsondata = json_decode($filedata, true);
							if($jsondata['total']>0){
								$MsgType = "news";
								foreach($jsondata['musics'] as $key=>$item){
									//print_r($item);
									$data[$key]['Title']=$item['title'];
									$data[$key]['Description']="平均评分：{$item['rating']['average']}简介：{$item['attrs']['singer'][0]}";
									$data[$key]['PicUrl']=$item['image'];
									$data[$key]['Url']=$item['mobile_link'];
								}
							}else{
								$this->setLog('没能查询到您指定的音乐！'.$jsondata['total']);
							}
							break;
						case '留言':
							$MsgType = "text";
							$data['Content'] = "fromUsername->{$this->fromUsername}\n toUsername->{$this->toUsername}\n msgType->{$this->msgType}\n createTime->{$this->createTime}\n留言内容：\n{$keyword2[1]}";
							echo $this->makeMsg($MsgType,$data,1);exit;
							break;
						default:
							$this->setLog("您发的指令有错!回复help查询指令！{$keyword}");
					}
			}
			echo $this->makeMsg($MsgType,$data);
		}elseif($this->msgType == 'image'){//用户发图片过来
			$MsgType = "news";
			$data=array(0=>array('Title'=>'您发送过来的图片','Description'=>'图片描述','PicUrl'=>$this->PicUrl,'Url'=>'http://xmit.sinaapp.com'));
			echo $this->makeMsg($MsgType,$data);
		}elseif($this->msgType == 'location'){//用户发gps过来
			$MsgType = "text";
			$data['Content'] = "{$this->Label} http://api.map.baidu.com/geocoder?location={$this->Location_X},{$this->Location_Y}&coord_type=gcj02&zoom={$this->Scale}&output=html";
			echo $this->makeMsg($MsgType,$data);
		}elseif($this->msgType == 'link'){//用户发链接过来
			$MsgType = "news";
			$data=array(0=>array('Title'=>$this->Title,'Description'=>$this->Description,'PicUrl'=>'','Url'=>$this->Url));
			echo $this->makeMsg($MsgType,$data);
		}elseif($this->msgType == 'event'){//用户发事件过来
			if($this->Event == 'subscribe'){//订阅事件
				$MsgType = "text";
				$data['Content'] = "谢谢关注，回复help查询指令。";
			}else{//其他事件
				$MsgType = "text";
				$data['Content'] = "事件并且非订阅信息，事件类型：{$this->Event},事件key：{$this->EventKey}";
			}
			echo $this->makeMsg($MsgType,$data);
		}else{//发送的是图片、地理位置、链接消息、时间推送
			$this->setLog("非文本命令、非图片、非位置、非链接、非订阅、非事件！回复help查询指令。{$this->msgType}");
		}
    }
	
	/**
	* 创建消息
	* 开发文档上说可回复的信息有文本、图文、语音、视频、音乐（这三个都指向music）和对收到的消息进行星标操作
	* $type = 
	* text $data=array('Content'=>'');
	* music $data=array('Title'=>'','Description'=>'','MusicUrl'=>'','HQMusicUrl'=>'');
	* news $data=array(0=>array('Title'=>'','Description'=>'','PicUrl'=>'','Url'=>''),1=>array('Title'=>'','Description'=>'','PicUrl'=>'','Url'=>''));
	* $flag=1做星号
	*/
	public function makeMsg($type='text',$data='',$flag=0,$msgTime=null){
		$msgTime = $msgTime ? $msgTime : $this->time;
		switch($type){
			case 'text':
				$result = sprintf($this->textTpl, $this->fromUsername, $this->toUsername, $msgTime, $data['Content'] ,$flag);
				break;
			case 'music':
				$result = sprintf($this->musicTpl, $this->fromUsername, $this->toUsername, $msgTime, $data['Title'], $data['Description'], $data['MusicUrl'], $data['HQMusicUrl'] ,$flag);
				break;
			case 'news':
				$news = $data;
				$items = '';
				foreach($news as $key=>$value){
					$items .= sprintf($this->newsTplItem, $value['Title'], $value['Description'], $value['PicUrl'], $value['Url']);
				}
				$result = sprintf($this->newsTplWrap, $this->fromUsername, $this->toUsername, $msgTime, count($news), $items ,$flag);
				break; 
			default :
				$this->setLog('不能创建非指定信息！');
				$result = false;
		}
		return $result;
	}
	
	/**
	* 调试
	*/
	private function setLog($msg="错误",$stop=true){
		if($this->debug){
			$msgX['Content'] = $msg;
			echo $this->makeMsg('text',$msgX,1);
		}
		if($stop)exit;
	}
	
	/**
	* 平台对接验证
	*/
	public function valid()
    {
        $w_echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
        	echo $w_echoStr;
        	exit;
        }
    }
	private function checkSignature()
	{
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];	
        		
		$token = $this->token;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
}

?>