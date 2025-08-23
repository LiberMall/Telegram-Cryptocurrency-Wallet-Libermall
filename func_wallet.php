<?php
function walletMenu(){
	global $chat_id, $link, $langcode, $text;

	$balances = getBalance();
	$final_balance_tgr = $balances[0] + $balances[1];

	// TRG in usd
	if($final_balance_tgr == 0){
		$final_balance_usd = "0.0";
	}else{
		$usdrate = getTGRrate();
		$final_balance_usd = $final_balance_tgr * $usdrate;
	}

	// TON in usd
	if($balances[2] == 0){
		$final_balance_TON_usd = "0.0";
	}else{
		$usdrate = getTONrate();
		$final_balance_TON_usd = $balances[2] * $usdrate;
	}

  $arInfo["inline_keyboard"][0][0]["callback_data"] = 11;
  $arInfo["inline_keyboard"][0][0]["text"] = "📥 Пополнить";
  $arInfo["inline_keyboard"][0][1]["callback_data"] = 12;
  $arInfo["inline_keyboard"][0][1]["text"] = "📤 Вывести";
  $arInfo["inline_keyboard"][1][0]["callback_data"] = 13;
  $arInfo["inline_keyboard"][1][0]["text"] = "♻️ Перевести";
  $arInfo["inline_keyboard"][1][1]["callback_data"] = 14;
  $arInfo["inline_keyboard"][1][1]["text"] = "🗓 История";
  $arInfo["inline_keyboard"][2][0]["callback_data"] = 16;
  $arInfo["inline_keyboard"][2][0]["text"] = "🏆 Купить TGR";
  $arInfo["inline_keyboard"][3][0]["callback_data"] = 15;
  $arInfo["inline_keyboard"][3][0]["text"] = "⏪ Назад";
  send($chat_id, "Твой баланс:
$final_balance_tgr TGR ($final_balance_usd USD)
$balances[2] TON ($final_balance_TON_usd USD)", $arInfo);
}
function addFundsListCoins(){
	global $chat_id;

    $row = getRowUsers();
    if($row->ton_ton_full < 0.1){
        $arInfo["inline_keyboard"][0][0]["callback_data"] = 22;
        $arInfo["inline_keyboard"][0][0]["text"] = "TON";
        $arInfo["inline_keyboard"][1][0]["callback_data"] = 25;
        $arInfo["inline_keyboard"][1][0]["text"] = "⏪ Назад";
        send($chat_id, "Выбери актив для пополнения:", $arInfo);
    }else{
        $arInfo["inline_keyboard"][0][0]["callback_data"] = 21;
        $arInfo["inline_keyboard"][0][0]["text"] = "TGR";
        $arInfo["inline_keyboard"][0][1]["callback_data"] = 22;
        $arInfo["inline_keyboard"][0][1]["text"] = "TON";
        $arInfo["inline_keyboard"][1][0]["callback_data"] = 25;
        $arInfo["inline_keyboard"][1][0]["text"] = "⏪ Назад";
        send($chat_id, "Выбери актив для пополнения:", $arInfo);
    }
}

function addFundsListNetworks($asset2addFunds){
	global $chat_id;

    $row = getRowUsers();
    if($row->ton_ton_full < 0.1){
            $arInfo["inline_keyboard"][0][0]["callback_data"] = "ADD|TON|TON";
            $arInfo["inline_keyboard"][0][0]["text"] = "TON";
            $arInfo["inline_keyboard"][1][0]["callback_data"] = 35;
            $arInfo["inline_keyboard"][1][0]["text"] = "⏪ Назад";
            send($chat_id, "Выбери сеть для $asset2addFunds:", $arInfo);
    }else{
        if($asset2addFunds == "TGR"){
            $arInfo["inline_keyboard"][0][0]["callback_data"] = "ADD|TGR|TON";
            $arInfo["inline_keyboard"][0][0]["text"] = "TON";
            $arInfo["inline_keyboard"][0][1]["callback_data"] = "ADD|TGR|BEP20";
            $arInfo["inline_keyboard"][0][1]["text"] = "BEP20";
            $arInfo["inline_keyboard"][1][0]["callback_data"] = 35;
            $arInfo["inline_keyboard"][1][0]["text"] = "⏪ Назад";
            send($chat_id, "Выбери сеть для $asset2addFunds:", $arInfo);
        }
        elseif($asset2addFunds == "TON"){
            $arInfo["inline_keyboard"][0][0]["callback_data"] = "ADD|TON|TON";
            $arInfo["inline_keyboard"][0][0]["text"] = "TON";
            $arInfo["inline_keyboard"][1][0]["callback_data"] = 35;
            $arInfo["inline_keyboard"][1][0]["text"] = "⏪ Назад";
            send($chat_id, "Выбери сеть для $asset2addFunds:", $arInfo);
        }
    }

}

function addFundsGetAddress($asset,$network){
	global $chat_id, $link;

	if($network == "TON"){
		$str2select = "SELECT * FROM `wallets` WHERE `chatid`='$chat_id' AND (`asset`='TON' AND `network`='$network')";
	}else{
		$str2select = "SELECT * FROM `wallets` WHERE `chatid`='$chat_id' AND (`asset`='$asset' AND `network`='$network')";
	}
	$result = mysqli_query($link, $str2select);
	$row = @mysqli_fetch_object($result);

	if(mysqli_num_rows($result) == 0){

		if($asset == "TGR"){
			if($network == "TON"){
				$address =  createAPITONaddress("TGR");
				#$str2ins = "INSERT INTO `wallets` (`chatid`,`asset`,`network`,`address`) VALUES ('$chat_id','$asset','$network','$address')";
				#mysqli_query($link, $str2ins);
			}
			elseif($network == "BEP20"){
				$address =  create0xpayTGRaddress();
				$str2ins = "INSERT INTO `wallets` (`chatid`,`asset`,`network`,`address`,`seed`,`publicKey`,`privateKey`) VALUES ('$chat_id','$asset','$network','$address','0','0','0')";
				mysqli_query($link, $str2ins);
			}
		}
		elseif($asset == "TON"){
			if($network == "TON"){
				$address =  createAPITONaddress("TON");
				#$str2ins = "INSERT INTO `wallets` (`chatid`,`asset`,`network`,`address`) VALUES ('$chat_id','$asset','$network','$address')";
				#mysqli_query($link, $str2ins);
			}
		}

	}else{
		$address = $row->address;
	}
	return $address;
}

function addFundsShowAddress($asset,$network){
	global $chat_id, $link;

	$address = addFundsGetAddress($asset,$network);

	$balances = getBalance();
	$firstton = '';
	if($network == 'TON' && $balances[2] == 0){
		$firstton .= '<b>ВНИМАНИЕ!</b> Это твое первое пополнение в сети TON. Для активации кошелька необхоимо перевести не менее 0.1 TON и сумма в размере 0.1 TON будет зарезервирована без возможности ее вывода.

';
	}

	if($network == "BEP20"){
		$tgrrate = getTGRrate();
		$mintgr = ceil(1/$tgrrate);
		$minlimitmessage = "
ВАЖНО! Минимальная сумма пополнения в BEP20: $mintgr TGR. Перевод на меньшую сумму будет отклонен.";
	}else{
		$minlimitmessage = "";
	}

	$arInfo["inline_keyboard"][0][0]["callback_data"] = "QR|$asset|$network";
  $arInfo["inline_keyboard"][0][0]["text"] = "Показать QR-код";
	$arInfo["inline_keyboard"][1][0]["callback_data"] = "CHECK|$asset|$network";
	$arInfo["inline_keyboard"][1][0]["text"] = "Проверить перевод";
	$arInfo["inline_keyboard"][2][0]["callback_data"] = 25;
  $arInfo["inline_keyboard"][2][0]["text"] = "⏪ Назад в кошелек";
  send($chat_id, $firstton. 'Выполни перевод на указанный ниже адрес:
<code>'.$address.'</code>'.$minlimitmessage.'
Убедись, что ты переводишь в сети '.$network.'!', $arInfo);
}

function addFundsGetQRcode($asset,$network){
	global $chat_id, $link;

	$address = addFundsGetAddress($asset,$network);

	//get QR code
    $time = time();
    $url = "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=".$address."&choe=UTF-8";
    $img = file_get_contents($url);
    ensure_tmp_dir(TMP_DIR);
    $filename = TMP_DIR."/".$chat_id."_".$time.".jpg";
    file_put_contents($filename, $img);

    $response = array(
            'chat_id' => $chat_id,
            'caption' => '',
            'photo' => new CURLFile($filename),
            'parse_mode' => 'HTML'
    );
    sendit($response, 'sendPhoto');

	$arInfo["inline_keyboard"][0][0]["callback_data"] = 26;
  $arInfo["inline_keyboard"][0][0]["text"] = "⏪ Назад в кошелек";
  send($chat_id, 'Выполни перевод на указанный ниже адрес:
<code>'.$address.'</code>
Убедись, что ты переводишь в сети '.$network.'!', $arInfo);

	sleep(5);
    unlink($filename);
}

function addFundsCheck($asset,$network){
	global $chat_id, $link;

	$address = addFundsGetAddress($asset,$network);

	if($asset == "TGR" && $network == "TON"){
			addFundsCheckTON("TGR");
	}
	elseif($asset == "TGR" && $network == "BEP20"){
		$arInfo["inline_keyboard"][0][0]["callback_data"] = 25;
	  $arInfo["inline_keyboard"][0][0]["text"] = "⏪ Назад в кошелек";
	  send($chat_id, 'Системой зафиксирован запрос на пополнение '.$asset.' баланса на следующий адрес в сети '.$network.':
<code>'.$address.'</code>
Как только средства поступят, ты получишь уведомление от системы.', $arInfo);
	}
	elseif($asset == "TON" && $network == "TON"){
		addFundsCheckTON("TON");
	}
}

function addFundsCheckTON($asset){
	global $chat_id, $link, $tonapikey;

	$address = addFundsGetAddress($asset,"TON");

	require_once 'TonClient.php';
	$ton = new TonClient('v4R2', 'http://127.0.0.1:5881/', 'https://toncenter.com/api/v2/jsonRPC', $tonapikey);
	$resp = $ton->getBalance($address);

	$tonbalance = $resp->balance;
	$tgrbalance = $resp->jettons->TGR;

	$str2select = "SELECT * FROM `users` WHERE `chatid`='$chat_id'";
	$result = mysqli_query($link, $str2select);
	$row = @mysqli_fetch_object($result);

	//$row->ton_ton = fact online balance after all the operations
	$newTONpaid = $tonbalance - $row->ton_ton;
	$allTONbalance = $newTONpaid + $row->ton_ton_full;

	if($allTONbalance < 0.099999999){

		$arInfo["inline_keyboard"][0][0]["callback_data"] = "CHECK|$asset|TON";
		$arInfo["inline_keyboard"][0][0]["text"] = "Проверить перевод";
		$arInfo["inline_keyboard"][1][0]["callback_data"] = 25;
	  $arInfo["inline_keyboard"][1][0]["text"] = "⏪ Назад в кошелек";
	  send($chat_id, 'Баланс в TON ниже минимального лимита в 0.1 TON. Возможно средства еще не поступили, повтори проверку позже или перечисли сумму минимум 0.1 TON для активации кошелька на свой адрес:
<code>'.$address.'</code>', $arInfo);

	}
	elseif($row->ton_ton_full < 0.1 && $newTONpaid > 0.1){

		$arInfo["inline_keyboard"][0][0]["callback_data"] = 25;
		$arInfo["inline_keyboard"][0][0]["text"] = "⏪ Назад в кошелек";
		send($chat_id, 'Получено первое пополнение TON в сети TON:
Сумма: '.$newTONpaid.' TON (TON)
Средства зачислены на твой баланс', $arInfo);

	$str2upd = "UPDATE `users` SET `ton_ton_full`='$allTONbalance' WHERE `chatid`='$chat_id'";
	mysqli_query($link, $str2upd);
	saveTransaction($newTONpaid, "TON", "TON", "add", 0);

	transfer2MainWallet("TON", $tonbalance);

	}else{

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

				$str2upd = "UPDATE `users` SET `$assetcol`='$addedsum_full' WHERE `chatid`='$chat_id'";
				mysqli_query($link, $str2upd);

				saveTransaction($diff, $asset, "TON", "add", 0);

				$arInfo["inline_keyboard"][0][0]["callback_data"] = 25;
			  $arInfo["inline_keyboard"][0][0]["text"] = "⏪ Назад в кошелек";
			  send($chat_id, 'Получено пополнение '.$asset.' в сети TON:
Сумма: '.$diff.' '.$asset.' (TON)
Средства зачислены на твой баланс', $arInfo);

				transfer2MainWallet($asset, $newbalance);

		}else{
			$arInfo["inline_keyboard"][0][0]["callback_data"] = "CHECK|$asset|TON";
			$arInfo["inline_keyboard"][0][0]["text"] = "Проверить перевод";
			$arInfo["inline_keyboard"][1][0]["callback_data"] = 25;
		  $arInfo["inline_keyboard"][1][0]["text"] = "⏪ Назад в кошелек";
		  send($chat_id, 'Средства еще не поступили, повтори проверку позже.', $arInfo);
		}
	}
}

function transfer2MainWallet($asset, $newbalance){
	global $chat_id, $link, $tonapikey, $mainWallet;

	$row = getTONWalletRow($chat_id);

	require_once 'TonClient.php';
	try
	{
		$ton = new TonClient('v4R2', 'http://127.0.0.1:5881/', 'https://toncenter.com/api/v2/jsonRPC', $tonapikey);
		if($asset == "TON" && $newbalance > 2){
			$newbalance = $newbalance-1.006;
			$str2upd = "UPDATE `users` SET `ton_ton`='1' WHERE `chatid`='$chat_id'";
			mysqli_query($link, $str2upd);

			$ton->sendTransaction(
			 	$row->seed,
			 	$mainWallet,
			 	$newbalance,
			 	'from bot');
		}
		elseif($asset == "TON" && $newbalance <= 2){
			$str2upd = "UPDATE `users` SET `ton_ton`='$newbalance' WHERE `chatid`='$chat_id'";
			mysqli_query($link, $str2upd);
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

function checkPhone(){
	global $chat_id, $link;
	
	$row = getRowUsers();
	if($row->phone == '' || $row->phone == 0){
		$arInfo["keyboard"][0][0]["text"] = "✅ Подтвердить";
		$arInfo["keyboard"][0][0]["request_contact"] = TRUE;
		$arInfo["resize_keyboard"] = TRUE;
		send($chat_id, 'Для операций вывода средств требуется одноразовое подтверждение номера телефона. Нажми на кнопку "Подтвердить" ниже...', $arInfo); 
	}else{
		withdrawFundsListCoins();
	}
}

function validatePhone($data){
	global $chat_id, $link;

    if(!empty($data['message']['contact']['phone_number'])){
		
		$phone = $data['message']['contact']['phone_number'];
		$str2upd = "UPDATE `users` SET `phone`='$phone' WHERE `chatid`='$chat_id'";
		mysqli_query($link, $str2upd);
		
		// remove keywoard
		send($chat_id, "Телефон подтвержден", "DEL");

        $str5select = "SELECT `action` FROM `temp_sess` WHERE `chatid`='$chat_id' ORDER BY `rowid` DESC LIMIT 1";
        $result5 = mysqli_query($link, $str5select);
        $row5 = @mysqli_fetch_object($result5);

        if(preg_match("/ichqphone\|/", $row5->action)){
            delMessage($data['message']['message_id'], "");
            $p = explode("|", $row5->action);
            clean_temp_sess();
            incomingChequeProcess($p[1], 3);
        }else{
		    withdrawFundsListCoins();
        }
		
	}else{
		$response = array(
			'chat_id' => $chat_id,
			'text' => "❌ОШИБКА! Ввведенное значение не похоже на номер телефона. Повтори попытку. ",
			'parse_mode' => 'HTML');
		sendit($response, 'sendMessage');		
	}
	
}

function withdrawFundsListCoins(){
	global $chat_id;

	$arInfo["inline_keyboard"][0][0]["callback_data"] = 31;
  $arInfo["inline_keyboard"][0][0]["text"] = "TGR";
	$arInfo["inline_keyboard"][0][1]["callback_data"] = 32;
  $arInfo["inline_keyboard"][0][1]["text"] = "TON";
	$arInfo["inline_keyboard"][1][0]["callback_data"] = 34;
  $arInfo["inline_keyboard"][1][0]["text"] = "⏪ Назад";
  send($chat_id, "Выбери актив для вывода:", $arInfo);
}
function withdrawFundsListNetworks($asset2wdwFunds){
	global $chat_id;

	if($asset2wdwFunds == "TGR"){
		$arInfo["inline_keyboard"][0][0]["callback_data"] = "WDW|TGR|TON";
	  $arInfo["inline_keyboard"][0][0]["text"] = "TON";
		$arInfo["inline_keyboard"][0][1]["callback_data"] = "WDW|TGR|BEP20";
	  $arInfo["inline_keyboard"][0][1]["text"] = "BEP20";
		$arInfo["inline_keyboard"][1][0]["callback_data"] = 36;
	  $arInfo["inline_keyboard"][1][0]["text"] = "⏪ Назад";
	  send($chat_id, "Выбери сеть для $asset2wdwFunds:", $arInfo);
	}
	elseif($asset2wdwFunds == "TON"){
		$arInfo["inline_keyboard"][0][0]["callback_data"] = "WDW|TON|TON";
	  $arInfo["inline_keyboard"][0][0]["text"] = "TON";
		$arInfo["inline_keyboard"][1][0]["callback_data"] = 36;
	  $arInfo["inline_keyboard"][1][0]["text"] = "⏪ Назад";
	  send($chat_id, "Выбери сеть для $asset2wdwFunds:", $arInfo);
	}
}
function withdrawFundsWait4Address($asset,$network){
	global $chat_id, $link;

	clean_temp_sess();
	clean_temp_wallet();
	save2temp("action", "withdrawWallet|$asset|$network");

	$response = array(
		'chat_id' => $chat_id,
		'text' => "Укажи кошелек получателя для перевода $asset в сети $network:",
		'parse_mode' => 'HTML');
	sendit($response, 'sendMessage');
}
function withdrawFundsWait4Sum($data, $row5){
	global $chat_id, $link, $tgrbep20fee, $tgrtonfee, $tonfee;

	if(strlen(trim($data['message']['text'])) < 20){
		$response = array(
			'chat_id' => $chat_id,
			'text' => "❌ОШИБКА! Ввведенное значение не похоже на кошелек. Повтори попытку. ",
			'parse_mode' => 'HTML');
		sendit($response, 'sendMessage');
	}else{
		$walletno = trim($data['message']['text']);
		$p = explode("|", $row5->action);
		$asset = $p[1];
		$network = $p[2];
		$ctime = time();
		$str2ins = "INSERT INTO `temp_wallet` (`chatid`,`wallet`,`times`) VALUES ('$chat_id','$walletno','$ctime')";
		mysqli_query($link, $str2ins);

		$minlim = "";
		$tonrate = getTONrate();
        $tgrrate = getTGRrate();
        $balances = getBalance();
		if($asset == "TGR" && $network == "BEP20"){
			$fee = round($tgrbep20fee / $tonrate, 2);
            $feeinTGR = round($tgrbep20fee / $tgrrate, 2);
            $available = $balances[1] - $feeinTGR;
			$minlim = "Минимальная сумма вывода: 100 TGR.
Комиссия за вывод: $fee TON.
Твой баланс: $balances[1] $asset.
Доступно для вывода (за вычетом комиссии): $available $asset.
";
		}
		elseif($asset == "TGR" && $network == "TON"){
			$fee = $tgrtonfee;
            $feeinTGR = round($tgrtonfee * $tonrate / $tgrrate, 2);
            $available = $balances[0] - $feeinTGR;
			$minlim = "Минимальная сумма вывода: 100 TGR.
Комиссия за вывод: $fee TON.
Твой баланс: $balances[0] $asset.
Доступно для вывода (за вычетом комиссии): $available $asset.
";
		}
		elseif($asset == "TON" && $network == "TON"){
			$fee = $tonfee;
            $available = $balances[2] - $fee;
			$minlim = "Минимальная сумма вывода: 0.2 TON.
Комиссия за вывод: $fee TON.
Твой баланс: $balances[2] $asset.
Доступно для вывода (за вычетом комиссии): $available $asset.
";
		}

		if($balances[2] < $fee){

			$str2del = "DELETE FROM `temp_wallet` WHERE `chatid` = '$chat_id'";
			mysqli_query($link, $str2del);

			$arInfo["inline_keyboard"][0][0]["callback_data"] = 11;
		  $arInfo["inline_keyboard"][0][0]["text"] = "📥 Пополнить";
		  $arInfo["inline_keyboard"][1][0]["callback_data"] = 25;
		  $arInfo["inline_keyboard"][1][0]["text"] = "⏪ Назад";
		  send($chat_id, $minlim."Баланс: ".$balances[2]." TON.
Недостаточно средств для комиссии за вывод", $arInfo);

		}else{

			clean_temp_sess();
			save2temp("action", "withdrawSum|$asset|$network");

            //Calculate available sum


			$response = array(
				'chat_id' => $chat_id,
				'text' => $minlim."Укажи сумму перевода:",
				'parse_mode' => 'HTML');
			sendit($response, 'sendMessage');
		}
	}
}
function withdrawFundsProcessSum($data, $row5){
	global $chat_id, $link, $tonfee, $tgrtonfee, $tgrbep20fee;

	if(floatval(trim($data['message']['text'])) <= 0){
		$response = array(
			'chat_id' => $chat_id,
			'text' => "❌ОШИБКА! Ввведенное значение не похоже на сумму. Повтори попытку.",
			'parse_mode' => 'HTML');
		sendit($response, 'sendMessage');
	}else{
		$balances = getBalance();
        $tonrate = getTONrate();
        $tgrrate = getTGRrate();

		$p = explode("|", $row5->action);
		$asset = $p[1];
		$network = $p[2];
		$sum = trim($data['message']['text']);

		if($asset == "TON" && $network == "TON"){
			$minsum = 0.2;
            $available = $balances[2] - $tonfee;
			if($sum < $minsum){
				$response = array(
					'chat_id' => $chat_id,
					'text' => "❌ОШИБКА! Минимальная сумма для вывода TON в сети TON: $minsum. Повтори попытку.",
					'parse_mode' => 'HTML');
				sendit($response, 'sendMessage');
			}
			elseif($sum > $available){
				$arInfo["inline_keyboard"][0][0]["callback_data"] = 11;
			  $arInfo["inline_keyboard"][0][0]["text"] = "📥 Пополнить";
			  $arInfo["inline_keyboard"][1][0]["callback_data"] = 25;
			  $arInfo["inline_keyboard"][1][0]["text"] = "⏪ Назад";
			  send($chat_id, "Баланс: ".$balances[2]." TON.
Доступно для вывода (за вычетом комиссии): $available TON.			  
Недостаточно средств на балансе для выполнения операции", $arInfo);
			}else{
				$fee = takeFee($asset, $network);
				payOut($asset, $network, $sum, $fee);
				referralFee(0.01);
				clean_temp_sess();
				clean_temp_wallet();
			}
		}
		elseif($asset == "TGR" && $network == "TON"){
			$minsum = 100;
            $feeinTGR = round($tgrtonfee * $tonrate / $tgrrate, 2);
            $available = $balances[0] - $feeinTGR;
			if($sum < $minsum){
				$response = array(
					'chat_id' => $chat_id,
					'text' => "❌ОШИБКА! Минимальная сумма для вывода TRG в сети TON: $minsum. Повтори попытку.",
					'parse_mode' => 'HTML');
				sendit($response, 'sendMessage');
			}
			elseif($sum > $available){
				$arInfo["inline_keyboard"][0][0]["callback_data"] = 11;
			  $arInfo["inline_keyboard"][0][0]["text"] = "📥 Пополнить";
			  $arInfo["inline_keyboard"][1][0]["callback_data"] = 25;
			  $arInfo["inline_keyboard"][1][0]["text"] = "⏪ Назад";
			  send($chat_id, "Баланс: ".$balances[0]." TGR(TON).
Доступно для вывода (за вычетом комиссии): $available TGR(TON).				  
Недостаточно средств на балансе для выполнения операции", $arInfo);
			}else{
                $fee = takeFee($asset, $network);
                payOut($asset, $network, $sum, $fee);
				referralFee(0.01);
				clean_temp_sess();
				clean_temp_wallet();
			}
		}
		elseif($asset == "TGR" && $network == "BEP20"){
			$minsum = 100;
            $feeinTGR = round($tgrbep20fee / $tgrrate, 2);
            $available = $balances[1] - $feeinTGR;
			if($sum < $minsum){
				$response = array(
					'chat_id' => $chat_id,
					'text' => "❌ОШИБКА! Минимальная сумма для вывода TRG в сети BEP20: $minsum. Повтори попытку.",
					'parse_mode' => 'HTML');
				sendit($response, 'sendMessage');
			}
			elseif($sum > $available){
				$arInfo["inline_keyboard"][0][0]["callback_data"] = 11;
			  $arInfo["inline_keyboard"][0][0]["text"] = "📥 Пополнить";
			  $arInfo["inline_keyboard"][1][0]["callback_data"] = 25;
			  $arInfo["inline_keyboard"][1][0]["text"] = "⏪ Назад";
			  send($chat_id, "Баланс: ".$balances[1]." TGR(BEP20).
Доступно для вывода (за вычетом комиссии): $available TGR(BEP20).				  
Недостаточно средств на балансе для выполнения операции", $arInfo);
			}else{
                $fee = takeFee($asset, $network);
                payOut($asset, $network, $sum, $fee);
				referralFee(0.01);
				clean_temp_sess();
				clean_temp_wallet();
			}
		}
	}
}

function transferFunds(){
	global $chat_id;

	clean_temp_sess();
	clean_temp_wallet();
    @unlink(TMP_DIR."/chno_$chat_id.json");

	send2('sendMessage',
	[
		'chat_id' => $chat_id,
		'text' => "Пригласи друга прямо отсюда и получи бонус!",
		'reply_markup' =>
		[
			'inline_keyboard' =>
			[
				[
					[
						'text' => "🎁 Пригласить друзей",
						'switch_inline_query' => ''
					]
				],
				[
					[
						'text' => "⏪ Назад",
						'callback_data' => 25
					]
				]
			]
		]
	]);
}

function historyMenu(){
	global $chat_id;

	$arInfo["inline_keyboard"][0][0]["callback_data"] = "HISTORY|add";
	$arInfo["inline_keyboard"][0][0]["text"] = "Пополнения";
	$arInfo["inline_keyboard"][0][1]["callback_data"] = "HISTORY|pauout";
	$arInfo["inline_keyboard"][0][1]["text"] = "Выводы";
	$arInfo["inline_keyboard"][1][0]["callback_data"] = "HISTORY|trans";
	$arInfo["inline_keyboard"][1][0]["text"] = "Переводы";
	$arInfo["inline_keyboard"][1][1]["callback_data"] = "HISTORY|exchange";
	$arInfo["inline_keyboard"][1][1]["text"] = "Обмены";
	$arInfo["inline_keyboard"][2][0]["callback_data"] = 25;
	$arInfo["inline_keyboard"][2][0]["text"] = "⏪ Назад";
	send($chat_id, "Выбери тип операций:", $arInfo);
}

function showHistory($type, $start){
	global $chat_id, $link;

	$str2select = "SELECT * FROM `transactions` WHERE `chatid`='$chat_id' AND `type`='$type'";
	$result = mysqli_query($link, $str2select);
	$total = mysqli_num_rows($result);

	switch ($type) {
		case 'add':
		$typename = 'пополнения';
		break;
		case 'pauout':
		$typename = 'вывода';
		break;
		case 'trans':
		$typename = 'перевода';
		break;
		case 'exchange':
		$typename = 'обмена';
		break;
	}

	$message = 'Операции '.$typename. 'по твоему кошельку:
	
';
	$str3select = "SELECT * FROM `transactions` WHERE `chatid`='$chat_id' AND `type`='$type' ORDER BY `rowid` DESC LIMIT $start, 10";
	$result3 = mysqli_query($link, $str3select);
	while($row3 = @mysqli_fetch_object($result3)){
		$message .= date('d/m/Y G:i', $row3->times).' '.$row3->sum.' '.$row3->asset.'('.$row3->network.')
';
	}  // end WHILE MySQL

	if($start == 0){
		$arInfo["inline_keyboard"][0][0]["callback_data"] = "HISTN|$type|10";
		$arInfo["inline_keyboard"][0][0]["text"] = "➡️";
		$i = 1;
	}
	elseif(($total - $start) <= 10 ){
		$backnav = $start - 10;
		$arInfo["inline_keyboard"][0][0]["callback_data"] = "HISTN|$type|$backnav";
		$arInfo["inline_keyboard"][0][0]["text"] = "⬅️";
		$i = 1;
	}
	elseif($total <= 10 ){
		$i = 0;
	}else{
		$backnav = $start - 10;
		$forwnav = $start + 10;
		$arInfo["inline_keyboard"][0][0]["callback_data"] = "HISTN|$type|$backnav";
		$arInfo["inline_keyboard"][0][0]["text"] = "⬅️";
		$arInfo["inline_keyboard"][0][1]["callback_data"] = "HISTN|$type|$forwnav";
		$arInfo["inline_keyboard"][0][1]["text"] = "➡️";
		$i = 1;
	}
	$arInfo["inline_keyboard"][$i][0]["callback_data"] = 14;
	$arInfo["inline_keyboard"][$i][0]["text"] = "⏪ Назад";
	send($chat_id, $message, $arInfo);

}

function buyTGRwait4sum(){
    global $link, $chat_id;

    clean_temp_sess();
    clean_temp_wallet();
    save2temp("action", "buyTGRsum");

    $response = array(
        'chat_id' => $chat_id,
        'text' => "Укажи сумму TGR, которую ты хочешь купить:
<i>Мин: 15,000 TGR, макс: 850,000 TGR</i>",
        'parse_mode' => 'HTML');
    sendit($response, 'sendMessage');

}
function buyTRGmakeLink($sum){
    global $link, $chat_id, $tegromoney_shopid, $tegromoney_secretkey;

    $curtime = time();
    $str2ins = "INSERT INTO `paylinks` (`chatid`,`times`,`status`,`sum`) VALUES ('$chat_id','$curtime','0','$sum')";
    mysqli_query($link, $str2ins);
    $last_id = mysqli_insert_id($link);

    $currency = 'USD';
    $order_id = $chat_id.':'.$last_id;

    $data = array(
        'shop_id'=>$tegromoney_shopid,
        'amount'=>$sum,
        'currency'=>$currency,
        'order_id'=>$order_id
        #'test'=>1
    );
    ksort($data);
    $str = http_build_query($data);
    $sign = md5($str . $tegromoney_secretkey);

    $link = 'https://tegro.money/pay/?'.$str.'&sign='.$sign;
    return $link;
}
function buyTGRProcessSum($data){
    global $link, $chat_id;

    $sumTGR = trim(intval($data['message']['text']));
    if ($sumTGR < 15000){
        $response = array(
            'chat_id' => $chat_id,
            'text' => "❌ОШИБКА! Минимальная сумма для покупки TRG: 15000. Повтори попытку.",
            'parse_mode' => 'HTML');
        sendit($response, 'sendMessage');
    }
    elseif ($sumTGR > 850000){
        $response = array(
            'chat_id' => $chat_id,
            'text' => "❌ОШИБКА! Максимальная сумма для покупки TRG: 850000. Повтори попытку.",
            'parse_mode' => 'HTML');
        sendit($response, 'sendMessage');
    }else{
        clean_temp_sess();
        $tgrrate = getTGRrate();
        $fee = $sumTGR / 100 * 5;
        $sum = round(($sumTGR + $fee) * $tgrrate, 2);

        $link = buyTRGmakeLink($sum);
        $arInfo["inline_keyboard"][0][0]["text"] = "Оплатить $sumTGR TGR";
        $arInfo["inline_keyboard"][0][0]["url"] = rawurldecode($link);
        $arInfo["inline_keyboard"][1][0]["callback_data"] = 25;
        $arInfo["inline_keyboard"][1][0]["text"] = "⏪ Назад";
        send($chat_id, "Перейди по кнопке для оплаты покупки $sumTGR TGR.
<i>Важно: курс TGR фиксируется непосредственно в момент поступления оплаты. 
Комиссия за операцию: 5% от суммы.</i>", $arInfo);
    }
}