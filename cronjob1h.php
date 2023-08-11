<?php
set_time_limit(0);

include 'botdata.php';

include "global.php";
$link = mysqli_connect($hostName, $userName, $password, $databaseName) or die ("Error connect to database");
mysqli_set_charset($link, "utf8");

include 'botdata.php'; // keys etc.

$str2select = "SELECT * FROM `users`";
$result = mysqli_query($link, $str2select);
$r = 0;
while($row = @mysqli_fetch_object($result)){
	if($r == 4){
		sleep(1);
		$r = 0;
	}
    addFundsCheckTON("TON", $row->chatid);
    addFundsCheckTON("TGR", $row->chatid);
	$r++;
}  // end WHILE MySQL

function sendit($response, $restype){
	$ch = curl_init('https://api.telegram.org/bot' . TOKEN . '/'.$restype);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $response);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_exec($ch);
	curl_close($ch);
}

function send($id, $message, $keyboard) {

		//Удаление клавы
		if($keyboard == "DEL"){
			$keyboard = array(
				'remove_keyboard' => true
			);
		}
		if($keyboard){
			//Отправка клавиатуры
			$encodedMarkup = json_encode($keyboard);

			$data = array(
				'chat_id'      => $id,
				'text'     => $message,
				'reply_markup' => $encodedMarkup,
				'parse_mode' => 'HTML',
				'disable_web_page_preview' => True
			);
		}else{
			//Отправка сообщения
			$data = array(
				'chat_id'      => $id,
				'text'     => $message,
				'parse_mode' => 'HTML',
				'disable_web_page_preview' => True
			);
		}

        $out = sendit($data, 'sendMessage');
        return $out;
}

function addFundsCheckTON($asset, $chat_id){
	global $link, $tonapikey;

	$address = addFundsGetAddress($asset,"TON", $chat_id);

	if($address != false){
		require_once 'TonClient.php';
		$ton = new TonClient('v4R2', 'http://127.0.0.1:5881/', 'https://toncenter.com/api/v2/jsonRPC', $tonapikey);
		$resp = $ton->getBalance($address);

		$tonbalance = round($resp->balance, 5);
		$tgrbalance = round($resp->jettons->TGR, 5);

		$str2select = "SELECT * FROM `users` WHERE `chatid`='$chat_id'";
		$result = mysqli_query($link, $str2select);
		$row = @mysqli_fetch_object($result);

		$newTONpaid = $tonbalance - $row->ton_ton;
		$allTONbalance = $newTONpaid + $row->ton_ton_full;

			if($asset == "TGR"){
				$oldbalance = $row->tgr_ton;
				$newbalance = $tgrbalance;
				$balance_full = $row->tgr_ton_full;
				$assetcol = "tgr_ton_full";
			}
			elseif($asset == "TON"){
				$oldbalance = $row->ton_ton;
				$newbalance = $tonbalance;
				$balance_full = $row->ton_ton_full;
				$assetcol = "ton_ton_full";
			}

				if($newbalance - $oldbalance > 0){
					$diff = $newbalance - $oldbalance;
					$addedsum_full = $balance_full + $diff;

			if(($asset == "TON" && $addedsum_full >= 0.099999999) || $asset == "TGR"){

			if($asset == "TON"){
			  if($newbalance > 2){
				$str2upd = "UPDATE `users` SET `$assetcol`='$addedsum_full' WHERE `chatid`='$chat_id'";
			  }else{
				$str2upd = "UPDATE `users` SET `ton_ton`='$newbalance' WHERE `chatid`='$chat_id'";
			  }
			}else{
			  $str2upd = "UPDATE `users` SET `$assetcol`='$addedsum_full' WHERE `chatid`='$chat_id'";
			}
		  mysqli_query($link, $str2upd);

		  saveTransaction($diff, $asset, "TON", "add", 0, $chat_id);

		  $arInfo["inline_keyboard"][0][0]["callback_data"] = 25;
		  $arInfo["inline_keyboard"][0][0]["text"] = "⏪ Назад в кошелек";
		  send($chat_id, 'Получено пополнение '.$asset.' в сети TON:
	Сумма: '.$diff.' '.$asset.' (TON)
	Средства зачислены на твой баланс', $arInfo);

		  transfer2MainWallet($asset, $newbalance, $chat_id);
		}
		}
	}
}

function transfer2MainWallet($asset, $newbalance, $chat_id){
	global $link, $tonapikey, $mainWallet;

	$row = getTONWalletRow($chat_id);

	require_once 'TonClient.php';
	try
	{
		$ton = new TonClient('v4R2', 'http://127.0.0.1:5881/', 'https://toncenter.com/api/v2/jsonRPC', $tonapikey);
		if($asset == "TON" && $newbalance > 2){
			$newbalance = $newbalance-1;
			$str2upd = "UPDATE `users` SET `ton_ton`='1' WHERE `chatid`='$chat_id'";
			mysqli_query($link, $str2upd);

			$ton->sendTransaction(
			 	$row->seed,
			 	$mainWallet,
			 	$newbalance,
			 	'from bot');
		}
		elseif($asset == "TGR"){
			$str2upd = "UPDATE `users` SET `tgr_ton`='0' WHERE `chatid`='$chat_id'";
			mysqli_query($link, $str2upd);

			$ton->sendTransactionJetton(
			 	$row->seed,
			 	$mainWallet,
			 	$newbalance,
			 	'TGR');
		}
	}
	catch (Exception $e)
	{
	print 'ERROR: ' . $e->getMessage();
	}
}

function getTONWalletRow($chat_id){
		global $link;

		$str2select = "SELECT * FROM `wallets` WHERE `chatid`='$chat_id' AND `network`='TON' ORDER BY `rowid` DESC LIMIT 1";
		$result = mysqli_query($link, $str2select);
		$row = @mysqli_fetch_object($result);
		return $row;
}

function saveTransaction($sum, $asset, $network, $type, $address, $chat_id){
	global $link;

	$curtime = time();
	$str2ins = "INSERT INTO `transactions` (`chatid`,`times`, `asset`, `network`, `sum`, `type`, `address`) VALUES ('$chat_id','$curtime', '$asset', '$network', '$sum', '$type', '$address')";
	mysqli_query($link, $str2ins);
}

function addFundsGetAddress($asset, $network, $chat_id){
	global $link;

	if($network == "TON"){
		$str2select = "SELECT * FROM `wallets` WHERE `chatid`='$chat_id' AND (`asset`='TON' AND `network`='$network')";
	}else{
		$str2select = "SELECT * FROM `wallets` WHERE `chatid`='$chat_id' AND (`asset`='$asset' AND `network`='$network')";
	}
	$result = mysqli_query($link, $str2select);
	if(mysqli_num_rows($result) > 0){
		$row = @mysqli_fetch_object($result);
		return $row->address;
	}else{
		return false;
	}
}
?>
