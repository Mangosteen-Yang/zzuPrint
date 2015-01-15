<?php
define('TOKEN', 'printerrrr');

include 'Datebase.php';
$resepond = new Api();
if (empty($_GET) || isset(($_GET["token"]) || isset($_GET["quest"])) {
	exit(0);
} elseif (TOKEN != $_GET["token"]) {
	exit(0);
} else {
	$quest = $_GET["quest"]; 
	if (isset($_GET["id"])) {
		$id = $_GET["id"];
	}
}
switch ($quest) {
	case 'ShowQueue':
		$result = $resepond->ShowQueue();
		break;
	case 'HavePrint':
		$result = $resepond->HavePrint($id);
		break;
	case 'Printable':
		$result = $resepond->Printable($id);
		break;
	default:
		# code...
		break;
}
$result = json_encode($result);
echo $result;


/**
* 
*/
class Api {
	//获取当前队列图片
	function ShowQueue() {
		$Datebase = new Datebase();
		$result = $Datebase->getImage("printer");
		return $result;
	}

	//打印成功后删除该图片
	function HavePrint($id) {
		$array = array('is_deleted' => , 1);
		$Datebase = new Datebase();
		$result = $Datebase->updateImage($id, $array);
		return $result;
	}

	//询问是否可以打印
	public function Printable($id) {
		$array = array('printable' => , 1);
		$Datebase = new Datebase();
		$result = $Datebase->updateImage($id, $array);
		return $result;
	}

}

?>