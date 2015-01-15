<?php
define("TOKEN", "wlwzzu");
$wechatObj = new wechatCallbackapiTest();
$wechatObj->responseMsg();
//$wechatObj->valid();
class wechatCallbackapiTest {
	public function valid()
	{
		$echoStr = $_GET["echostr"];

		//valid signature , option
		if($this->checkSignature()){
			echo $echoStr;
			exit;
		}
	}
	
	
	public function responseMsg() {
		include "Datebase.php";
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
		if (!empty($postStr)) {
			// 获取初始值
			$fromUserName = $postObj->FromUserName;
			$toUserName = $postObj->ToUserName;
			$formMsgType = $postObj->MsgType;
			$time=$postObj->CreateTime;
			$content=$postObj->Content;
			
			$DateBase = new DateBase();
			$DateBase->msgRecord($toUserName, $fromUserName, $time, $content);
		} else {
			exit();
		}
		// 判断是否是事件
		if($formMsgType == "event") {
			//获取事件类型
			$form_Event = $postObj->Event;
			// 判断是否要打印
			if($form_Event == "click") {
				$form_EventKey = trim($postObj->EventKey);
				// 对键值进行switch
				if (empty($form_EventKey)) {
					$contentStr = "xxxxxxxxxxx";
				} else {
					if($form_EventKey!= "Print") {				
						//无论是否绑定，执行下面代码
						switch ($form_EventKey) {
							case 'Submenu1':
								$contentStr = "1";
							break;
							case 'Submenu2':
								$contentStr = "2";
								break;
							case 'Submenu3':
								$contentStr = "3";
								break;
							default:
								break;
						}
					} else {
						//先判断是否有照片，若有的话，要求输入验证码，否则提示先发送照片
						$DateBase = new DateBase();
						$result = $DateBase->getImage($fromUserName);
						if (empty($result)) {
							$contentStr = "您没有待打印的图片，请上传图片";
						} else {
							$contentStr = "请输入打印机上的验证码";
						}
					}
				}
				
			}
		} elseif($formMsgType == "image") {
			// 判断是否是图片，是的话存到SAE的storage并记录到数据库中
			$picUrl = $postObj->PicUrl;
			$s = new SaeStorage(SAE_ACCESSKEY, SAE_SECRETKEY);
			$img = file_get_contents($picUrl);
			$filter = explode("/", $picUrl);
			$picName = end($filter);
			$newPicUrl = $s->write ( 'photo4printer' ,  $picName , $img );
			// 记录该用户ID和该Url，并生成验证码。均存入数据库
			$DateBase = new DateBase();
			$DateBase->insertImage($fromUserName, $newPicUrl);
			if ($DateBase) {
				$contentStr = "请选择您要打印的尺寸：http://url.com";
			} else {
				$contentStr = "请您重新发送您的照片";
			}
		} elseif ($formMsgType == 'text') {
			// 判断是否已经有照片，若无，提示发送照片，若有匹配验证码
			$form_Content = trim($postObj->Content);
			$contentStr = "欢迎您的使用！";
			//判断是否有照片：
			$DateBase = new DateBase();
			$result = $DateBase->getImage($fromUserName);
			if (empty($result)) {
				$contentStr = "您没有上传图片，请上传图片";
			} elseif (preg_match('/^\d{6}$/', $form_Content)) {
				if ($result["validation_code"] == $form_Content) {
					$contentStr = "照片准备就绪，请准备打印";
					//修改数据库，将其状态改为可打印
					$id = $result["id"];
					$array = array('printable' => 1);
					$result = $DateBase->updateImage($id, $array); 
					if ($result) {
						$contentStr = "照片准备就绪，请准备打印";
					} else {
						$contentStr = "啊哦，出问题了，请联系系统管理员";
					}
				} else {
					$contentStr = "验证码不正确，请重新输入";
				}
			} else {
				$contentStr = "验证码不正确，请重新输入";
			}
		}
		$textTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[%s]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>";
		$msgType = "text";
		$resultStr = sprintf($textTpl, $fromUserName, $toUserName, $time, $msgType, $contentStr);
		echo $resultStr;
		exit;
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