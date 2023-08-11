<?php
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

$data = file_get_contents('php://input');
$data = json_decode($data, true);

include 'botdata.php';

include "global.php";
$link = mysqli_connect($hostName, $userName, $password, $databaseName) or die ("Error connect to database");
mysqli_set_charset($link, "utf8");

include 'func_gen.php';

if($data['status'] == "Done"){
	$chat_id = $data['meta'];
	$addedSum = $data['amount'];

	$str2select = "SELECT * FROM `users` WHERE `chatid`='$chat_id'";
	$result = mysqli_query($link, $str2select);
	$row = @mysqli_fetch_object($result);

	$newbalance = $row->tgr_bep20 + $addedSum;

	$str2upd = "UPDATE `users` SET `tgr_bep20`='$newbalance' WHERE `chatid`='$chat_id'";
	mysqli_query($link, $str2upd);

	saveTransaction($addedSum, "TGR", "BEP20", "add", 0);

	$arInfo["inline_keyboard"][0][0]["callback_data"] = 25;
  	$arInfo["inline_keyboard"][0][0]["text"] = "⏪ Назад в кошелек";
	  send($chat_id, 'Получено пополнение TGR в сети BEP20:
Сумма: '.$addedSum.' TGR (BEP20)
Средства зачислены на твой баланс', $arInfo);

}

$results = date("G:H d/F/Y");
$results .= "
====================
";
$results .= print_r($data, true);

if($file = fopen("test_response.txt", "a")){
		fputs($file, $results);
		fclose($file);
} // end frite to file
?>
