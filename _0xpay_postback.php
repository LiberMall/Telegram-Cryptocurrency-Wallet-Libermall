<?php
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

$data = file_get_contents('php://input');
$data = json_decode($data, true);

include 'botdata.php';

include "global.php";
$link = mysqli_connect($hostName, $userName, $password, $databaseName);
if (!$link) {
    error_log('DB connection error: ' . mysqli_connect_error());
    exit();
}
mysqli_set_charset($link, "utf8");

include 'func_gen.php';

if($data['status'] == "Done"){
        $chat_id = intval($data['meta']);
        $addedSum = floatval($data['amount']);

        $stmtSel = $link->prepare("SELECT * FROM `users` WHERE `chatid` = ?");
        if ($stmtSel === false) {
            error_log('Prepare failed: ' . $link->error);
            exit();
        }
        $stmtSel->bind_param('i', $chat_id);
        if (!$stmtSel->execute()) {
            error_log('SQL Error: ' . $stmtSel->error);
        }
        $result = $stmtSel->get_result();
        $row = @mysqli_fetch_object($result);
        $stmtSel->close();

        $newbalance = $row->tgr_bep20 + $addedSum;

        $stmtUpd = $link->prepare("UPDATE `users` SET `tgr_bep20` = ? WHERE `chatid` = ?");
        if ($stmtUpd === false) {
            error_log('Prepare failed: ' . $link->error);
            exit();
        }
        $stmtUpd->bind_param('di', $newbalance, $chat_id);
        if (!$stmtUpd->execute()) {
            error_log('SQL Error: ' . $stmtUpd->error);
        }
        $stmtUpd->close();

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
