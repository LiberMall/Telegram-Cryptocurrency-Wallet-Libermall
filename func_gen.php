<?php
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

function send2($method, $request)
{

	$ch = curl_init('https://api.telegram.org/bot' . TOKEN . '/' . $method);
	curl_setopt_array($ch,
		[
			CURLOPT_HEADER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => json_encode($request),
			CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
			CURLOPT_SSL_VERIFYPEER => false,
		]
	);
	$result = curl_exec($ch);
	curl_close($ch);

	return $result;
}

function mainMenu(){
	global $chat_id, $link, $langcode, $text;

    $arInfo["keyboard"][0][0]["text"] = "↩️ В главное меню";
    $arInfo["resize_keyboard"] = TRUE;
    send($chat_id, ' Рады приветствовать тебя в криптовалютном кошельке Libermall Bot!', $arInfo);

    unset($arInfo);

    $arInfo["inline_keyboard"][0][0]["callback_data"] = 1;
  $arInfo["inline_keyboard"][0][0]["text"] = "💎 Кошелек";
  $arInfo["inline_keyboard"][0][1]["callback_data"] = 2;
  $arInfo["inline_keyboard"][0][1]["text"] = "🏷 Чеки";
  #$arInfo["inline_keyboard"][1][0]["callback_data"] = 3;
  #$arInfo["inline_keyboard"][1][0]["text"] = "🗳 Обмен";
  $arInfo["inline_keyboard"][1][0]["callback_data"] = 4;
  $arInfo["inline_keyboard"][1][0]["text"] = "💹 Биржа";
  $arInfo["inline_keyboard"][2][0]["callback_data"] = 5;
  $arInfo["inline_keyboard"][2][0]["text"] = "💸 Маркет";
  $arInfo["inline_keyboard"][2][1]["callback_data"] = 6;
  $arInfo["inline_keyboard"][2][1]["text"] = "🪪 Счета";
  $arInfo["inline_keyboard"][3][0]["callback_data"] = 7;
  $arInfo["inline_keyboard"][3][0]["text"] = "💰 Сделки";
  $arInfo["inline_keyboard"][3][1]["callback_data"] = 8;
  $arInfo["inline_keyboard"][3][1]["text"] = "📈 Стейкинг";
  $arInfo["inline_keyboard"][4][0]["callback_data"] = 9;
  $arInfo["inline_keyboard"][4][0]["text"] = "🖼 NFT";
  $arInfo["inline_keyboard"][4][1]["callback_data"] = 10;
  $arInfo["inline_keyboard"][4][1]["text"] = "⚙️ Настройки";

	send($chat_id, "Мультивалютный криптокошелек libermall.com. Покупайте, продавайте, храните и платите криптовалютой когда хотите.

Подписывайтесь на <a href='https://t.me/LibermallRUS'>наш канал</a> и вступайте в <a href='https://t.me/libermallton'>наш чат</a>.", $arInfo);
}

function getRowUsers(){
                global $link, $chat_id;

                $stmt = mysqli_prepare($link, "SELECT * FROM `users` WHERE `chatid`=? ORDER BY `rowid` DESC LIMIT 1");
                if ($stmt) {
                        mysqli_stmt_bind_param($stmt, 's', $chat_id);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        $row = $result ? mysqli_fetch_object($result) : null;
                        mysqli_stmt_close($stmt);
                } else {
                        error_log('DB prepare failed: '.mysqli_error($link));
                        $row = null;
                }
                return $row;
}

function getTONWalletRow($chat_id){
                global $link;

                $stmt = mysqli_prepare($link, "SELECT * FROM `wallets` WHERE `chatid`=? AND `network`='TON' ORDER BY `rowid` DESC LIMIT 1");
                if ($stmt) {
                        mysqli_stmt_bind_param($stmt, 's', $chat_id);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        $row = $result ? mysqli_fetch_object($result) : null;
                        mysqli_stmt_close($stmt);
                } else {
                        error_log('DB prepare failed: '.mysqli_error($link));
                        $row = null;
                }
                return $row;
}

function clean_temp_sess(){
        global $chat_id, $link;

        $stmt = mysqli_prepare($link, "DELETE FROM `temp_sess` WHERE `chatid` = ?");
        if ($stmt) {
                mysqli_stmt_bind_param($stmt, 's', $chat_id);
                if (!mysqli_stmt_execute($stmt)) {
                        error_log('DB delete failed: '.mysqli_stmt_error($stmt));
                }
                mysqli_stmt_close($stmt);
        } else {
                error_log('DB prepare failed: '.mysqli_error($link));
        }
}

function clean_temp_wallet(){
        global $chat_id, $link;

        $stmt = mysqli_prepare($link, "DELETE FROM `temp_wallet` WHERE `chatid` = ?");
        if ($stmt) {
                mysqli_stmt_bind_param($stmt, 's', $chat_id);
                if (!mysqli_stmt_execute($stmt)) {
                        error_log('DB delete failed: '.mysqli_stmt_error($stmt));
                }
                mysqli_stmt_close($stmt);
        } else {
                error_log('DB prepare failed: '.mysqli_error($link));
        }
}

function save2temp($field, $val){

        global $link, $chat_id;
        $curtime = time();

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $field)) {
                error_log('Invalid field name');
                return;
        }
        $query = "INSERT INTO `temp_sess` (`chatid`,`$field`, `times`) VALUES (?,?,?)";
        $stmt = mysqli_prepare($link, $query);
        if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'ssi', $chat_id, $val, $curtime);
                if (!mysqli_stmt_execute($stmt)) {
                        error_log('DB insert failed: '.mysqli_stmt_error($stmt));
                }
                mysqli_stmt_close($stmt);
        } else {
                error_log('DB prepare failed: '.mysqli_error($link));
        }

}

function delMessage($mid, $cid){
	global $chat_id;
		if($mid != ''){
			$message_id = $mid-1;
		}
		elseif($cid != ''){
			$message_id = $cid;
		}

		$ch2 = curl_init('https://api.telegram.org/bot' . TOKEN . '/deleteMessage');
		curl_setopt($ch2, CURLOPT_POST, 1);
		curl_setopt($ch2, CURLOPT_POSTFIELDS, array('chat_id' => $chat_id, 'message_id' => $message_id));
		curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch2, CURLOPT_HEADER, false);
		$res2 = curl_exec($ch2);
		curl_close($ch2);
}

function delMessage2($mid, $cid){
	global $chat_id;
		if($mid != ''){
			$message_id = $mid-1;
		}
		elseif($cid != ''){
			$message_id = $cid;
		}

		$ch2 = curl_init('https://api.telegram.org/bot' . TOKEN . '/deleteMessage');
		curl_setopt($ch2, CURLOPT_POST, 1);
		curl_setopt($ch2, CURLOPT_POSTFIELDS, array('chat_id' => $chat_id, 'message_id' => ($cid-1)));
		curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch2, CURLOPT_HEADER, false);
		$res2 = curl_exec($ch2);
		curl_close($ch2);

		$ch2 = curl_init('https://api.telegram.org/bot' . TOKEN . '/deleteMessage');
		curl_setopt($ch2, CURLOPT_POST, 1);
		curl_setopt($ch2, CURLOPT_POSTFIELDS, array('chat_id' => $chat_id, 'message_id' => $message_id));
		curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch2, CURLOPT_HEADER, false);
		$res2 = curl_exec($ch2);
		curl_close($ch2);

}

function getTGRrate(){
                global $link;

        $stmt = mysqli_prepare($link, "SELECT * FROM `tgr_rate` WHERE `rowid`=?");
        if ($stmt) {
                $one = 1;
                mysqli_stmt_bind_param($stmt, 'i', $one);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $row = $result ? mysqli_fetch_object($result) : null;
                mysqli_stmt_close($stmt);
        } else {
                error_log('DB prepare failed: '.mysqli_error($link));
                $row = null;
        }

        $tgrRate = $row ? $row->tgr_rate : 0;
        return $tgrRate;
}

function getTONrate(){
                global $link;

        $stmt = mysqli_prepare($link, "SELECT * FROM `ton_rate` WHERE `rowid`=?");
        if ($stmt) {
                $one = 1;
                mysqli_stmt_bind_param($stmt, 'i', $one);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $row = $result ? mysqli_fetch_object($result) : null;
                mysqli_stmt_close($stmt);
        } else {
                error_log('DB prepare failed: '.mysqli_error($link));
                $row = null;
        }

        $tonRate = $row ? $row->ton_rate : 0;
        return $tonRate;
}

function getBalance(){
        global $chat_id, $link;

        $stmt = mysqli_prepare($link, "SELECT * FROM `users` WHERE `chatid`=?");
        if ($stmt) {
                mysqli_stmt_bind_param($stmt, 's', $chat_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $row = $result ? mysqli_fetch_object($result) : null;
                mysqli_stmt_close($stmt);
        } else {
                error_log('DB prepare failed: '.mysqli_error($link));
                $row = null;
        }
        $balances = array($row->tgr_ton_full ?? 0,$row->tgr_bep20 ?? 0,$row->ton_ton_full ?? 0);

        return $balances;
}

function saveReferral($data){
	global $chat_id, $link;

	$ref = trim(str_replace("/start", "", $data['message']['text']));
	if($ref != ''){
        $firstChar = substr($ref, 0, 1);
        //if cheque
        if($firstChar == "c"){
            incomingChequeStart($ref);
            return false;
        }
		elseif($ref != $chat_id){
        // if referral
                        $stmt = mysqli_prepare($link, "SELECT `ref` FROM `users` WHERE `chatid`=?");
                        mysqli_stmt_bind_param($stmt, 's', $chat_id);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        $row = $result ? mysqli_fetch_object($result) : null;
                        mysqli_stmt_close($stmt);
                        if($row && $row->ref == 0){
                                $stmt2 = mysqli_prepare($link, "UPDATE `users` SET `ref`=? WHERE `chatid`=?");
                                if ($stmt2) {
                                        mysqli_stmt_bind_param($stmt2, 'ss', $ref, $chat_id);
                                        if (!mysqli_stmt_execute($stmt2)) {
                                                error_log('DB update failed: '.mysqli_stmt_error($stmt2));
                                        }
                                        mysqli_stmt_close($stmt2);
                                } else {
                                        error_log('DB prepare failed: '.mysqli_error($link));
                                }

				$response = array(
						'chat_id' => $ref,
						'text' => hex2bin('F09F92B0').' '.$data['message']['from']['first_name'].' '.$data['message']['from']['last_name'].' зарегистрировался по вашей партнерской ссылке.

	Используйте эту ссылку для приглашения пользователей:
	https://t.me/LibermallBot?start='.$ref);
				sendit($response, 'sendMessage');
			}
            return true;
		}
	}else{
        return true;
    }
}
function referralFee($value){
        global $chat_id, $link;

        $stmt = mysqli_prepare($link, "SELECT `ref` FROM `users` WHERE `chatid`=?");
        if ($stmt) {
                mysqli_stmt_bind_param($stmt, 's', $chat_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $row = $result ? mysqli_fetch_object($result) : null;
                mysqli_stmt_close($stmt);
        } else {
                error_log('DB prepare failed: '.mysqli_error($link));
                $row = null;
        }

	if($row->ref != 0){
                $stmt3 = mysqli_prepare($link, "SELECT * FROM `users` WHERE `chatid`=?");
                if ($stmt3) {
                        mysqli_stmt_bind_param($stmt3, 's', $row->ref);
                        mysqli_stmt_execute($stmt3);
                        $result3 = mysqli_stmt_get_result($stmt3);
                        $row3 = $result3 ? mysqli_fetch_object($result3) : null;
                        mysqli_stmt_close($stmt3);
                } else {
                        error_log('DB prepare failed: '.mysqli_error($link));
                        $row3 = null;
                }

		$newtotalTon = $row3->ton_ton_full + $value;
                $stmtUpd = mysqli_prepare($link, "UPDATE `users` SET `ton_ton_full`=? WHERE `chatid`=?");
                if ($stmtUpd) {
                        mysqli_stmt_bind_param($stmtUpd, 'ds', $newtotalTon, $row->ref);
                        if (!mysqli_stmt_execute($stmtUpd)) {
                                error_log('DB update failed: '.mysqli_stmt_error($stmtUpd));
                        }
                        mysqli_stmt_close($stmtUpd);
                } else {
                        error_log('DB prepare failed: '.mysqli_error($link));
                }

		$times = time();
                $stmtIns = mysqli_prepare($link, "INSERT INTO `refcases` (`chatid`,`refid`,`sum`,`times`) VALUES (?,?,?,?)");
                if ($stmtIns) {
                        mysqli_stmt_bind_param($stmtIns, 'ssdi', $row->ref, $chat_id, $value, $times);
                        if (!mysqli_stmt_execute($stmtIns)) {
                                error_log('DB insert failed: '.mysqli_stmt_error($stmtIns));
                        }
                        mysqli_stmt_close($stmtIns);
                } else {
                        error_log('DB prepare failed: '.mysqli_error($link));
                }
	}
}
function generatePassword($length) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

##################################

function create0xpayTGRaddress(){
    global $xPayPrivateKey, $xPayMerchantId, $chat_id;

    $timestamp = time();
		$uri = "/merchants/addresses";

    $params = array(
        'blockchain' => 'BINANCE_SMART_CHAIN',
        "meta" => "$chat_id"
    );

    $string = strtoupper("POST") . $uri . json_encode($params) . $timestamp;

    $signature = hash_hmac('sha256', $string, $xPayPrivateKey);

    $myCurl = curl_init();
    curl_setopt_array($myCurl, array(
        CURLOPT_URL => 'https://public.api.0xpay.app/merchants/addresses',
        CURLOPT_HTTPHEADER => array(
            'Content-type: application/json',
            'merchant-id:'.$xPayMerchantId,
            'signature:'.$signature,
            'timestamp:'.$timestamp),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($params)
    ));
    $response = curl_exec($myCurl);
    curl_close($myCurl);

    $res = json_decode($response, true);

		if($res['meta'] == $chat_id){
			$newaddress = $res['address'];
		}else{
			$newaddress = "error";
		}
		return $newaddress;
}

function createAPITONaddress($asset){
    global $chat_id, $tonapikey, $link;

		require_once 'TonClient.php';
		$ton = new TonClient('v4R2', 'http://127.0.0.1:5881/', 'https://toncenter.com/api/v2/jsonRPC', $tonapikey);

    $response = $ton->createWallet();

                $stmt = mysqli_prepare($link, "INSERT INTO `wallets` (`chatid`,`asset`,`network`,`address`,`seed`,`publicKey`,`privateKey`) VALUES (?,?,?,?,?,?,?)");
                if ($stmt) {
                        $assetVal = 'TON';
                        $networkVal = 'TON';
                        mysqli_stmt_bind_param($stmt, 'sssssss', $chat_id, $assetVal, $networkVal, $response->address, $response->mnemonicStr, $response->publicKey, $response->privateKey);
                        if (!mysqli_stmt_execute($stmt)) {
                                error_log('DB insert failed: '.mysqli_stmt_error($stmt));
                        }
                        mysqli_stmt_close($stmt);
                } else {
                        error_log('DB prepare failed: '.mysqli_error($link));
                }

		return $response->address;
}

function saveTransaction($sum, $asset, $network, $type, $address){
	global $chat_id, $link;

        $curtime = time();
        $stmt = mysqli_prepare($link, "INSERT INTO `transactions` (`chatid`,`times`, `asset`, `network`, `sum`, `type`, `address`) VALUES (?,?,?,?,?,?,?)");
        if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'sissdss', $chat_id, $curtime, $asset, $network, $sum, $type, $address);
                if (!mysqli_stmt_execute($stmt)) {
                        error_log('DB insert failed: '.mysqli_stmt_error($stmt));
                }
                mysqli_stmt_close($stmt);
        } else {
                error_log('DB prepare failed: '.mysqli_error($link));
        }
}

function takeFee($asset, $network){
    global $chat_id, $link, $tgrbep20fee, $tgrtonfee, $tonfee;

		$tonrate = getTONrate();
		if($asset == "TGR" && $network == "BEP20"){
			$fee = round($tgrbep20fee / $tonrate, 2);
		}
		elseif($asset == "TGR" && $network == "TON"){
			$fee = $tgrtonfee;
		}
		elseif($asset == "TON" && $network == "TON"){
			$fee = $tonfee;
		}

                $row = getRowUsers();
                $newTotalTon = $row->ton_ton_full - $fee;
                $stmt = mysqli_prepare($link, "UPDATE `users` SET `ton_ton_full`=? WHERE `rowid`=?");
                if ($stmt) {
                        mysqli_stmt_bind_param($stmt, 'di', $newTotalTon, $row->rowid);
                        if (!mysqli_stmt_execute($stmt)) {
                                error_log('DB update failed: '.mysqli_stmt_error($stmt));
                        }
                        mysqli_stmt_close($stmt);
                } else {
                        error_log('DB prepare failed: '.mysqli_error($link));
                }

        return $fee;
}

function payOut($asset, $network, $sum, $fee){
    global $chat_id, $tonapikey, $link, $xPayPrivateKey, $xPayMerchantId, $tonapikey, $genseed;

                $stmt5 = mysqli_prepare($link, "SELECT `wallet` FROM `temp_wallet` WHERE `chatid`=? ORDER BY `rowid` DESC LIMIT 1");
                if ($stmt5) {
                        mysqli_stmt_bind_param($stmt5, 's', $chat_id);
                        mysqli_stmt_execute($stmt5);
                        $result5 = mysqli_stmt_get_result($stmt5);
                        $row5 = $result5 ? mysqli_fetch_object($result5) : null;
                        mysqli_stmt_close($stmt5);
                } else {
                        error_log('DB prepare failed: '.mysqli_error($link));
                        $row5 = null;
                }

		$success = 0;
		$trxurl = "https://tonviewer.com/";
		if($asset == "TGR" && $network == "BEP20"){

			$timestamp = time();
			$uri = "/merchants/withdrawals/crypto";
		    $params = array(
	        'ticker' => 'TGR',
	        'blockchain' => 'BINANCE_SMART_CHAIN',
	        'to' => $row5->wallet,
	        'amount' => strval($sum),
	        "meta" => strval($chat_id)
	    );
      $string = strtoupper("POST") . $uri . json_encode($params) . $timestamp;

	    $signature = hash_hmac('sha256', $string, $xPayPrivateKey);

	    $myCurl = curl_init();
	    curl_setopt_array($myCurl, array(
	        CURLOPT_URL => 'https://public.api.0xpay.app/merchants/withdrawals/crypto',
	        CURLOPT_HTTPHEADER => array(
	            'Content-type: application/json',
	            'merchant-id:'.$xPayMerchantId,
	            'signature:'.$signature,
	            'timestamp:'.$timestamp),
	        CURLOPT_RETURNTRANSFER => true,
	        CURLOPT_POST => true,
	        CURLOPT_POSTFIELDS => json_encode($params)
	    ));
	    $response = curl_exec($myCurl);
	    curl_close($myCurl);
	    $res = json_decode($response, true);
			if(isset($res['id']) && !empty($res['id'])) $success = 1;
			$trxurl = "https://bscscan.com/address/";
		}
		elseif($asset == "TGR" && $network == "TON"){

			require_once 'TonClient.php';
			$ton = new TonClient('v4R2', 'http://127.0.0.1:5881/', 'https://toncenter.com/api/v2/jsonRPC', $tonapikey);
			$resp = $ton->sendTransactionJetton(
			 	$genseed,
			 	$row5->wallet,
			 	$sum,
			 	'TGR');
			if($resp->{'@type'} == "ok")$success = 1;

		}
		elseif($asset == "TON" && $network == "TON"){

			require_once 'TonClient.php';
			$ton = new TonClient('v4R2', 'http://127.0.0.1:5881/', 'https://toncenter.com/api/v2/jsonRPC', $tonapikey);
			$resp = $ton->sendTransaction(
			 	$genseed,
			 	$row5->wallet,
			 	$sum,
			 	'from Libermall Bot');
			if($resp->{'@type'} == "ok")$success = 1;

		}

		if($success == 1){

			$row = getRowUsers();
            $tonrate = getTONrate();
            $tgrrate = getTGRrate();

                        if($asset == "TGR" && $network == "BEP20"){
                                $newTotal = $row->tgr_bep20 - $sum;
                                $stmtUpd = mysqli_prepare($link, "UPDATE `users` SET `tgr_bep20`=? WHERE `rowid`=?");
                                if ($stmtUpd) {
                                        mysqli_stmt_bind_param($stmtUpd, 'di', $newTotal, $row->rowid);
                                        if (!mysqli_stmt_execute($stmtUpd)) {
                                                error_log('DB update failed: '.mysqli_stmt_error($stmtUpd));
                                        }
                                        mysqli_stmt_close($stmtUpd);
                                } else {
                                        error_log('DB prepare failed: '.mysqli_error($link));
                                }
                $suminUSD = $sum * $tgrrate;
                $takenSum = $sum." TGR и $fee TON";
                        }
                        elseif($asset == "TGR" && $network == "TON"){
                                $newTotal = $row->tgr_ton_full - $sum;
                                $stmtUpd = mysqli_prepare($link, "UPDATE `users` SET `tgr_ton_full`=? WHERE `rowid`=?");
                                if ($stmtUpd) {
                                        mysqli_stmt_bind_param($stmtUpd, 'di', $newTotal, $row->rowid);
                                        if (!mysqli_stmt_execute($stmtUpd)) {
                                                error_log('DB update failed: '.mysqli_stmt_error($stmtUpd));
                                        }
                                        mysqli_stmt_close($stmtUpd);
                                } else {
                                        error_log('DB prepare failed: '.mysqli_error($link));
                                }
                $suminUSD = $sum * $tgrrate;
                $takenSum = $sum." TGR и $fee TON";
                        }
                        elseif($asset == "TON" && $network == "TON"){
                                $newTotal = $row->ton_ton_full - $sum;
                                $stmtUpd = mysqli_prepare($link, "UPDATE `users` SET `ton_ton_full`=? WHERE `rowid`=?");
                                if ($stmtUpd) {
                                        mysqli_stmt_bind_param($stmtUpd, 'di', $newTotal, $row->rowid);
                                        if (!mysqli_stmt_execute($stmtUpd)) {
                                                error_log('DB update failed: '.mysqli_stmt_error($stmtUpd));
                                        }
                                        mysqli_stmt_close($stmtUpd);
                                } else {
                                        error_log('DB prepare failed: '.mysqli_error($link));
                                }
                $suminUSD = $sum * $tonrate;
                $takenSum = ($sum+$fee)." TON";
                        }
                        // update already executed in branches above
                        $feeinUSD = $fee * $tonrate;

			saveTransaction($sum, $asset, $network, "payout", $row5->wallet);

            $link = $trxurl.$row5->wallet;
            $arInfo["inline_keyboard"][0][0]["text"] = "Проверить транзакцию";
            $arInfo["inline_keyboard"][0][0]["url"] = rawurldecode($link);
			$arInfo["inline_keyboard"][1][0]["callback_data"] = 25;
			$arInfo["inline_keyboard"][1][0]["text"] = "⏪ Назад в кошелек";
			send($chat_id, '✅ <b>Квитанция о выводе средств: '.$asset.'</b>
			
<b>Адрес:</b> '.$row5->wallet.'
			
<b>Сумма:</b> '.$sum.' '.$asset.' в сети '.$network.' ('.$suminUSD.' USD)
<b>Комиссия:</b> '.$fee.' TON ('.$feeinUSD.' USD)
<i>Транзакция завершена. Сумма'.$takenSum.' списана с твоего баланса.</i>', $arInfo);

		}else{
            $arInfo["inline_keyboard"][0][0]["callback_data"] = 25;
            $arInfo["inline_keyboard"][0][0]["text"] = "⏪ Назад в кошелек";
            send($chat_id, '❌ОШИБКА! Перевод не выполнен. Попробуй повторить попытку спустя некоторое время.', $arInfo);
        }
}

function uuid()
{
	return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		// 32 bits for "time_low"
		mt_rand(0, 0xffff), mt_rand(0, 0xffff),

		// 16 bits for "time_mid"
		mt_rand(0, 0xffff),

		// 16 bits for "time_hi_and_version",
		// four most significant bits holds version number 4
		mt_rand(0, 0x0fff) | 0x4000,

		// 16 bits, 8 bits for "clk_seq_hi_res",
		// 8 bits for "clk_seq_low",
		// two most significant bits holds zero and one for variant DCE1.1
		mt_rand(0, 0x3fff) | 0x8000,

		// 48 bits for "node"
		mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
	);
}

function checkInlineQuery()
{
	global $langcode, $text;
	$request = json_decode(file_get_contents('php://input'));

	if (isset($request->inline_query))
	{

		$chatid = $request->inline_query->from->id;

		#file_put_contents('debug', print_r($request, true) . PHP_EOL . json_encode($request) . PHP_EOL . $result . PHP_EOL, FILE_APPEND);

		// https://core.telegram.org/bots/api#answerinlinequery
		send2('answerInlineQuery',
			[
				'inline_query_id' => $request->inline_query->id,

				// InlineQueryResult https://core.telegram.org/bots/api#inlinequeryresult
				'results' =>
				[
					[
						// InlineQueryResultArticle https://core.telegram.org/bots/api#inlinequeryresultarticle
						'type' => 'article',
						'id' => uuid(),
						// 'id' => 0,
						'title' => "Отправить приглашение",
						'description' => "Пригласи друга и получай 10% с транзакций.",
						'thumb_url' => 'https://tegro.exchange/TegroMoneybot/images/512x512libermall.png',

						// InputMessageContent https://core.telegram.org/bots/api#inputmessagecontent
						'input_message_content' =>
						[
							// InputTextMessageContent https://core.telegram.org/bots/api#inputtextmessagecontent
							'message_text' => "Приглашаем вас в Libermall Bot!",
						],

						// InlineKeyboardMarkup https://core.telegram.org/bots/api#inlinekeyboardmarkup
						'reply_markup' =>
						[
							'inline_keyboard' =>
							[
								// InlineKeyboardButton https://core.telegram.org/bots/api#inlinekeyboardbutton
								[
									[
										'text' => "📲 Присоединится",
										'url' => 'https://t.me/LibermallBot?start='.$chatid,
									],
								],
							],
						],
					],
				],
			]
		);
	}
}
