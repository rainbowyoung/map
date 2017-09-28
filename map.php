<?php
/**
  * wechat php test
  */

//define your token
define("TOKEN", "weixin");
$wechatObj = new wechatCallbackapiTest();
//$wechatObj->valid();
$wechatObj->responseMsg();
class wechatCallbackapiTest
{
	public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
        	echo $echoStr;
        	exit;
        }
    }

    public function responseMsg()
    {
		//get post data, May be due to the different environments
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

      	//extract post data
		if (!empty($postStr)){
                
              	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $fromUsername = $postObj->FromUserName;
                $toUsername = $postObj->ToUserName;
				$type = $postObj->MsgType;
				$customrevent = $postObj->Event;
				$latitude  = $postObj->Location_X;
				$longitude = $postObj->Location_Y;
                $keyword = trim($postObj->Content);
                $time = time();
                $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";             
				switch ($type)
			{   case "event";
				if ($customrevent=="subscribe")
				    {$contentStr = "感谢你的关注\n回复1查看联系方式\n回复2查看最新资讯\n回复3查看法律文书";}
				break;
				case "image";
				$contentStr = "你的图片很棒！";
				break;
				case "location";
				$newsTpl = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[news]]></MsgType>
						 <ArticleCount>1</ArticleCount>
						 <Articles>
						 <item>
						 <Title><![CDATA[导航]]></Title> 
						 <Description><![CDATA[点击后导航到我的公司]]></Description>
						 <PicUrl><![CDATA[]]></PicUrl>
						 <Url><![CDATA[%s]]></Url>
						</item>
						</Articles>
						<FuncFlag>0</FuncFlag>
						</xml>";
			$url="http://mo.amap.com/?from={$latitude},{$longitude}(你的位置)&to=23.378341,116.706653(我的公司)&type=0&opt=1&dev=1";  
			
			$resultStr = sprintf($newsTpl, $fromUsername, $toUsername, $time,$url);
            echo $resultStr;
		
				break;
				case "link" ;
				$contentStr = "你的链接有病毒吧！";
				break;
				case "text";
				$weatherurl="http://api.map.baidu.com/telematics/v2/weather?location={$keyword}&ak=1a3cde429f38434f1811a75e1a90310c";
				 $apistr=file_get_contents($weatherurl);
				 $apiobj=simplexml_load_string($apistr);
				 $placeobj=$apiobj->currentCity;//读取城市
				 $todayobj=$apiobj->results->result[0]->date;//读取星期
				 $weatherobj=$apiobj->results->result[0]->weather;//读取天气
				 $windobj=$apiobj->results->result[0]->wind;//读取风力
				 $temobj=$apiobj->results->result[0]->temperature;//读取温度
				 $contentStr = "{$placeobj}{$todayobj}天气{$weatherobj}，风力{$windobj}，温度{$temobj}";
				  break;					
			default;
			$contentStr ="此项功能尚未开发";	
			}
				$msgType="text";
				$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
                

        }else {
        	echo "";
        	exit;
        }
    }
		
	private function checkSignature()
	{
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];	
        		
		$token = TOKEN;
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