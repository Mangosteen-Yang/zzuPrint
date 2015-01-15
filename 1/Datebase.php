<?php
//$unit = new DateBase;
//echo "<pre>";

//echo "<br>lixiao获取图片：";
//$result = $unit->getImage("lixiao");
//var_dump($result);
//echo "<br>打印机获取图片：";
//$result = $unit->getImage("printer");
//var_dump($result);
//echo "<br>是否更新成功：";
//$array = array('image_url' => "as111d", "user_id" => "lixiaoo");
//$result = $unit->updateImage($id = 1, $array);
//echo $result;
//echo "<br>修改后内容：";

//$result = $unit->getImage("lixiaoo");
//var_dump($result);
//echo "<br>";
class DateBase {
	
	function __construct() {
		$link = mysql_connect(SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT,SAE_MYSQL_USER,SAE_MYSQL_PASS);
		if($link) {
			mysql_select_db(SAE_MYSQL_DB,$link);
		}
	}

	public function msgRecord($toUserName, $fromUserName, $time, $content){
		$query = "INSERT INTO record(ToUserName, FromUserName, CreateTime, Content) VALUES('$toUserName', '$fromUserName', '$time', '$content')";
		echo $query;
		$result = mysql_query($query);
		if ($result)
			return ture;
		return false;
	}

	public function insertImage($userId = '', $imageUrl = '') {
		$validationCode = rand(10000,99999);
		$query = "INSERT INTO queue(user_id, image_url, validation_code, printable, is_deleted) VALUES('$userId', '$imageUrl', $validationCode, 0, 0)";
      	$result = mysql_query($query);
		//成功返回
		if ($result)
			return ture;
		return false;
	}

	public function getImage($userId = '',$id = '') {
        if ($id == '') {
	        if ($userId == 'printer') {
				$query = "SELECT id, image_url, validation_code FROM queue WHERE is_deleted = 0 ORDER BY id DESC LIMIT 10";
        	    // echo "调用打印机中<br>";
			} else {
				$query = "SELECT id, image_url, validation_code FROM queue WHERE user_id = '$userId' AND is_deleted = 0 ORDER BY id DESC LIMIT 1";
            	// echo "直接打印<br>";
			}
        } else {
        	$query = "SELECT id, image_url, validation_code FROM queue WHERE id = $id";
        }
        
        $res = mysql_query($query);
        while($row=mysql_fetch_array($res, MYSQL_ASSOC)){
			$result[] = $row;
		}
		return $result;
	}

	public function updateImage($id = "", $array = null) {
		$query = "UPDATE queue SET ";		
		foreach ($array as $key => $value) {
			if (!empty($value)) {
				$query = $query.$key."='".$value."',";
			}
		}
		$query = trim($query, ",");
		$query .= " WHERE id = $id";
        // echo "$query";
		$result = mysql_query($query);
		return $result;
	}
}
?>