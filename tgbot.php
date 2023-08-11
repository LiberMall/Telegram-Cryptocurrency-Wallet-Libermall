<?php
error_reporting(E_ALL & ~E_NOTICE);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');
#error_reporting(E_ALL & ~E_NOTICE);

$data = file_get_contents('php://input');
$data = json_decode($data, true);

if (empty($data['message']['chat']['id']) AND empty($data['callback_query']['message']['chat']['id']) AND empty($data['inline_query']['from']['id']))
{
	exit();
}

include "global.php";
$link = mysqli_connect($hostName, $userName, $password, $databaseName) or die ("Error connect to database");
mysqli_set_charset($link, "utf8");

include 'botdata.php'; // keys etc.
include 'func_gen.php';
include 'func_wallet.php';
include 'func_cheque.php';
include 'func_staking.php';
include 'func_exchange.php';
include 'func_exchange2.php';

#################################

if (isset($data['message']['chat']['id']))
{
	$chat_id = $data['message']['chat']['id'];
}
elseif(isset($data['callback_query']['message']['chat']['id']))
{
	$chat_id = $data['callback_query']['message']['chat']['id'];
}
elseif(isset($data['inline_query']['from']['id']))
{
	$chat_id = $data['inline_query']['from']['id'];
}

// Register new user in DB
if(isset($data['callback_query']['message']['chat']['username']) && $data['callback_query']['message']['chat']['username'] != ''){
	$fname = $data['callback_query']['message']['chat']['first_name'];
	$lname = $data['callback_query']['message']['chat']['last_name'];
	$uname = $data['callback_query']['message']['chat']['username'];
} else{
	$fname = $data['message']['from']['first_name'];
	$lname = $data['message']['from']['last_name'];
	$uname = $data['message']['from']['username'];
}
$time = time();

	if(empty($uname))$uname = 'undefined';

	$str2select = "SELECT * FROM `users` WHERE `chatid`='$chat_id'";
	$result = mysqli_query($link, $str2select);
	if(mysqli_num_rows($result) == 0){
		$str2ins = "INSERT INTO `users` (`chatid`,`username`,`tgr_ton`,`tgr_bep20`,`ton_ton`,`tgr_ton_full`,`ton_ton_full`,`ref`,`phone`) VALUES ('$chat_id','$uname', '0', '0', '0', '0', '0', '0', '0')";
		mysqli_query($link, $str2ins);
		$result = mysqli_query($link, $str2select);
	}
	$row = @mysqli_fetch_object($result);

// Register new user in DB

checkInlineQuery();

############### START ###############
if( preg_match("/\/start/i", $data['message']['text'] )){

//register subscriber
$newrecord = $chat_id."|".addslashes($data['message']['from']['first_name'])." ".addslashes($data['message']['from']['last_name'])."|".addslashes($data['message']['from']['username']);
if(file_exists('subscribers.php')) include 'subscribers.php';
if(isset($user) && count($user) > 0){
	if(!in_array($newrecord, $user)){
		$towrite = "\$user[] = '".addslashes($newrecord)."';\n";

	}
}else{
	$towrite = "\$user[] = '".addslashes($newrecord)."';\n";
} // end IF-ELSE count($user) > 0

if(isset($towrite) && $towrite != ''){
	if($file = fopen("subscribers.php", "a+")){
		fputs($file,$towrite);
		fclose($file);
	} // end frite to file
}
//register subscriber

$r = saveReferral($data);

if($r == true) mainMenu();

}
elseif( preg_match("/В главное меню/", $data['message']['text'] )){
    delMessage("", $data['callback_query']['message']['message_id']);
    mainMenu();
}
elseif( preg_match("/\/wallet/", $data['message']['text'] )){

  delMessage2("", $data['callback_query']['message']['message_id']);
  walletMenu();

}
elseif( preg_match("/\/checks/", $data['message']['text'] )){

    delMessage2("", $data['callback_query']['message']['message_id']);
    createCheque();

}
elseif( preg_match("/\/deposits/", $data['message']['text'] )){

    delMessage2("", $data['callback_query']['message']['message_id']);
    stakingMenu();

}
elseif( preg_match("/\/swap/", $data['message']['text'] )){

    delMessage2("", $data['callback_query']['message']['message_id']);
    exchangeStart();

}
elseif( preg_match("/\/exchange/", $data['message']['text'] )){

    delMessage2("", $data['callback_query']['message']['message_id']);
    exchange2Start();

}
elseif( preg_match("/\/help/i", $data['message']['text'] )){

}
elseif( preg_match("/stop/i", $data['message']['text'] )){

	}
elseif( !empty($data['message']['contact']['phone_number'])){
	validatePhone($data);
}
else{

  if(isset($data['callback_query']['data']) && $data['callback_query']['data'] != ''){

// Wallet
		if( $data['callback_query']['data'] == 1 || $data['callback_query']['data'] == 25 || $data['callback_query']['data'] == 34 ){
      delMessage2("", $data['callback_query']['message']['message_id']);
      walletMenu();
    }
		elseif( $data['callback_query']['data'] == 26 ){
			// if QR code
      delMessage2("", $data['callback_query']['message']['message_id']);
      walletMenu();
    }
		elseif( $data['callback_query']['data'] == 14 ){
      delMessage("", $data['callback_query']['message']['message_id']);
      historyMenu();
		}
    elseif( $data['callback_query']['data'] == 15 ){
      delMessage("", $data['callback_query']['message']['message_id']);
      mainMenu();
		}
    elseif( $data['callback_query']['data'] == 11  || $data['callback_query']['data'] == 35 ){
      delMessage("", $data['callback_query']['message']['message_id']);
      addFundsListCoins();
		}
    elseif( $data['callback_query']['data'] == 21 || $data['callback_query']['data'] == 22 ){
			if($data['callback_query']['data'] == 21){
				$asset2addFunds = "TGR";
			}
			elseif($data['callback_query']['data'] == 22){
				$asset2addFunds = "TON";
			}
      delMessage("", $data['callback_query']['message']['message_id']);
      addFundsListNetworks($asset2addFunds);
    }
		elseif( $data['callback_query']['data'] == 12 ){
      delMessage("", $data['callback_query']['message']['message_id']);
      checkPhone();
		}
        elseif( $data['callback_query']['data'] == 16 ){
            delMessage("", $data['callback_query']['message']['message_id']);
            buyTGRwait4sum();
        }
		elseif( $data['callback_query']['data'] == 36 ){
      delMessage("", $data['callback_query']['message']['message_id']);
      withdrawFundsListCoins();
		}
		elseif( $data['callback_query']['data'] == 31 || $data['callback_query']['data'] == 32 ){
			if($data['callback_query']['data'] == 31){
				$asset2wdwFunds = "TGR";
			}
			elseif($data['callback_query']['data'] == 32){
				$asset2wdwFunds = "TON";
			}
			delMessage("", $data['callback_query']['message']['message_id']);
			withdrawFundsListNetworks($asset2wdwFunds);
		}
		elseif( preg_match("/ADD\|/", $data['callback_query']['data'])){
      delMessage("", $data['callback_query']['message']['message_id']);
      $p = explode("|", $data['callback_query']['data']);
			addFundsShowAddress($p[1],$p[2]);
		}
		elseif( preg_match("/QR\|/", $data['callback_query']['data'])){
      delMessage("", $data['callback_query']['message']['message_id']);
      $p = explode("|", $data['callback_query']['data']);
			addFundsGetQRcode($p[1],$p[2]);
		}
		elseif( preg_match("/CHECK\|/", $data['callback_query']['data'])){
      delMessage("", $data['callback_query']['message']['message_id']);
      $p = explode("|", $data['callback_query']['data']);
			addFundsCheck($p[1],$p[2]);
		}
		elseif( preg_match("/WDW\|/", $data['callback_query']['data'])){
      delMessage("", $data['callback_query']['message']['message_id']);
      $p = explode("|", $data['callback_query']['data']);
			withdrawFundsWait4Address($p[1],$p[2]);
		}
		elseif( preg_match("/HISTORY\|/", $data['callback_query']['data'])){
      delMessage("", $data['callback_query']['message']['message_id']);
      $p = explode("|", $data['callback_query']['data']);
			showHistory($p[1], 0);
		}
		elseif( preg_match("/HISTN\|/", $data['callback_query']['data'])){
      delMessage("", $data['callback_query']['message']['message_id']);
      $p = explode("|", $data['callback_query']['data']);
			showHistory($p[1], $p[2]);
		}
		elseif( $data['callback_query']['data'] == 13 ){
      delMessage("", $data['callback_query']['message']['message_id']);
      transferFunds();
		}
        elseif( $data['callback_query']['data'] == 2 ){
//Cheques
            delMessage2("", $data['callback_query']['message']['message_id']);
            createCheque();
        }
        elseif( $data['callback_query']['data'] == 43 ){
            // if from QR code
            delMessage2("", $data['callback_query']['message']['message_id']);
            createCheque();
        }
        elseif( $data['callback_query']['data'] == 40 ){
            delMessage("", $data['callback_query']['message']['message_id']);
            chequeListCoins();
        }
        elseif( $data['callback_query']['data'] == 41 || $data['callback_query']['data'] == 42 ){
            if($data['callback_query']['data'] == 41){
                $asset = "TGR";
            }
            elseif($data['callback_query']['data'] == 42){
                $asset = "TON";
            }
            delMessage("", $data['callback_query']['message']['message_id']);
            chequeWait4Sum($asset);
        }
        elseif( preg_match("/CHQSUM\|/", $data['callback_query']['data'])){
            delMessage("", $data['callback_query']['message']['message_id']);
            $p = explode("|", $data['callback_query']['data']);
            $asset = ($p[2] == 1) ? "TON" : "TGR";
            include "tmp/$chat_id.php";
            chequeSetRef($p[1], $asset, $availableBalance);
        }
        elseif( preg_match("/CNUM\|/", $data['callback_query']['data'])){
            delMessage("", $data['callback_query']['message']['message_id']);
            $p = explode("|", $data['callback_query']['data']);
            #$asset = ($p[2] == 1) ? "TON" : "TGR";
            include "tmp/san_$chat_id.php";
            chequeIssue($sum, $asset, $p[1], $ref);
        }
        elseif( preg_match("/CREF\|/", $data['callback_query']['data'])){
            delMessage("", $data['callback_query']['message']['message_id']);
            $p = explode("|", $data['callback_query']['data']);
            include "tmp/san_$chat_id.php";
            include "tmp/$chat_id.php";
            chequeSetNumActivations($sum, $asset, $availableBalance, $p[1]);
        }
        elseif( preg_match("/CHQ\|/", $data['callback_query']['data'])){
            delMessage("", $data['callback_query']['message']['message_id']);
            $p = explode("|", $data['callback_query']['data']);
            chequeGetQRcode($p[1]);
        }
        elseif( preg_match("/CHD\|/", $data['callback_query']['data'])){
            delMessage("", $data['callback_query']['message']['message_id']);
            $p = explode("|", $data['callback_query']['data']);
            if($p[2] == 1){
                chequeAddDescription($p[1]);
            }else{
                chequeRemoveDescription($p[1]);
            }
        }
        elseif( $data['callback_query']['data'] == 44 ){
            delMessage("", $data['callback_query']['message']['message_id']);
            chequeList();
        }
        elseif( preg_match("/CHL\|/", $data['callback_query']['data'])){
            delMessage("", $data['callback_query']['message']['message_id']);
            $p = explode("|", $data['callback_query']['data']);
            chequeShowDetails($p[1], '');
        }
        elseif( preg_match("/CHP\|/", $data['callback_query']['data'])){
            delMessage("", $data['callback_query']['message']['message_id']);
            $p = explode("|", $data['callback_query']['data']);
            if($p[2] == 1){
                chequeWait4Pass($p[1]);
            }else{
                chequeRemove4Pass($p[1]);
            }
        }
        elseif( preg_match("/CHR\|/", $data['callback_query']['data'])){
            delMessage("", $data['callback_query']['message']['message_id']);
            $p = explode("|", $data['callback_query']['data']);
            chequeChangeRef($p[1]);
        }
        elseif( preg_match("/CRF\|/", $data['callback_query']['data'])){
            delMessage("", $data['callback_query']['message']['message_id']);
            $p = explode("|", $data['callback_query']['data']);
            chequeEditRef($p[1], $p[2]);
        }
        elseif( preg_match("/CHC\|/", $data['callback_query']['data'])){
            delMessage("", $data['callback_query']['message']['message_id']);
            $p = explode("|", $data['callback_query']['data']);
            chequeSwitcher("captcha", $p[1]);
        }
        elseif( preg_match("/CHV\|/", $data['callback_query']['data'])){
            delMessage("", $data['callback_query']['message']['message_id']);
            $p = explode("|", $data['callback_query']['data']);
            chequeSwitcher("phoneverif", $p[1]);
        }
        elseif( preg_match("/CHM\|/", $data['callback_query']['data'])){
            delMessage("", $data['callback_query']['message']['message_id']);
            $p = explode("|", $data['callback_query']['data']);
            chequeSwitcher("notify", $p[1]);
        }
        elseif( preg_match("/CHA\|/", $data['callback_query']['data'])){
            delMessage("", $data['callback_query']['message']['message_id']);
            $p = explode("|", $data['callback_query']['data']);
            chequeSwitcher("ref", $p[1]);
        }
        elseif( preg_match("/CHH\|/", $data['callback_query']['data'])){
            delMessage("", $data['callback_query']['message']['message_id']);
            $p = explode("|", $data['callback_query']['data']);
            chequeSwitcher("approved", $p[1]);
        }
        elseif( preg_match("/CHY\|/", $data['callback_query']['data'])){
            delMessage("", $data['callback_query']['message']['message_id']);
            $p = explode("|", $data['callback_query']['data']);
            chequeSubscriptionsCheck($p[1]);
        }
        elseif( preg_match("/chqAddGr\|/", $data['callback_query']['data'])){
            delMessage("", $data['callback_query']['message']['message_id']);
            $p = explode("|", $data['callback_query']['data']);
            chequeAddChat($p[1]);
        }
        elseif( preg_match("/chqDelGr\|/", $data['callback_query']['data'])){
            delMessage("", $data['callback_query']['message']['message_id']);
            $p = explode("|", $data['callback_query']['data']);
            chequeDelChat($p[1]);
        }
        elseif( preg_match("/CHX\|/", $data['callback_query']['data'])){
            delMessage("", $data['callback_query']['message']['message_id']);
            $p = explode("|", $data['callback_query']['data']);
            chequeDelete($p[1]);
        }
        elseif( preg_match("/ichqcap1\|/", $data['callback_query']['data'])){
//Check redemption
            delMessage2("", $data['callback_query']['message']['message_id']);
            $p = explode("|", $data['callback_query']['data']);
            incomingChequeCaptcha($p[1], $p[2]);
        }
        elseif( preg_match("/ichqcap2\|/", $data['callback_query']['data'])){
            delMessage2("", $data['callback_query']['message']['message_id']);
            $p = explode("|", $data['callback_query']['data']);
            incomingChequeProcess($p[1], 1);
        }
        elseif( preg_match("/ichqsubs\|/", $data['callback_query']['data'])){
            $p = explode("|", $data['callback_query']['data']);
            incomingChequeSubscrCheck($p[1], $data);
        }
        elseif( $data['callback_query']['data'] == 8 ){
//Staking
            delMessage2("", $data['callback_query']['message']['message_id']);
            stakingMenu();
        }
        elseif( $data['callback_query']['data'] == 50 ){
            delMessage2("", $data['callback_query']['message']['message_id']);
            stakingChooseAsset();
        }
        elseif( $data['callback_query']['data'] == 51 ){
            delMessage2("", $data['callback_query']['message']['message_id']);
            stakingListDeposits();
        }
        elseif( $data['callback_query']['data'] == 52 ){
            delMessage2("", $data['callback_query']['message']['message_id']);
            stakingWait4Sum("TGR");
        }
        elseif( $data['callback_query']['data'] == 53 ){
            delMessage2("", $data['callback_query']['message']['message_id']);
            stakingWait4Sum("TON");
        }
        elseif( preg_match("/STKT\|/", $data['callback_query']['data'])){
            $p = explode("|", $data['callback_query']['data']);
            delMessage2("", $data['callback_query']['message']['message_id']);
            stakingProcessDepo($p[1], $p[2], $p[3]);
        }
        elseif( preg_match("/STKR\|/", $data['callback_query']['data'])){
            $p = explode("|", $data['callback_query']['data']);
            delMessage2("", $data['callback_query']['message']['message_id']);
            stakingShowDepoDetails($p[1]);
        }
        elseif( $data['callback_query']['data'] == 3 ){
// Exchange
            delMessage2("", $data['callback_query']['message']['message_id']);
            exchangeStart();
        }
        elseif( $data['callback_query']['data'] == 60 ){
            delMessage("", $data['callback_query']['message']['message_id']);
            exchangeChooseDirection("TON");
        }
        elseif( $data['callback_query']['data'] == 61 ){
            delMessage("", $data['callback_query']['message']['message_id']);
            exchangeChooseDirection("TGR");
        }
        elseif( preg_match("/exc\|/", $data['callback_query']['data'])){
            $p = explode("|", $data['callback_query']['data']);
            delMessage("", $data['callback_query']['message']['message_id']);
            exchangeRequestSum($p[1],$p[2]);
        }
        elseif( preg_match("/exsmax/", $data['callback_query']['data'])){
            delMessage("", $data['callback_query']['message']['message_id']);
            exchangePreProcessSum();
        }
        elseif( preg_match("/exf\|/", $data['callback_query']['data'])){
            $p = explode("|", $data['callback_query']['data']);
            delMessage("", $data['callback_query']['message']['message_id']);
            exchangeFinish($p[1]);
        }
        elseif( $data['callback_query']['data'] == 4 ){
// Exchange2
            delMessage2("", $data['callback_query']['message']['message_id']);
            exchange2Start();
        }
        elseif( $data['callback_query']['data'] == 70 ){
            delMessage("", $data['callback_query']['message']['message_id']);
            exchange2pairsList();
        }
        elseif( preg_match("/EXCH\|/", $data['callback_query']['data'])){
            $p = explode("|", $data['callback_query']['data']);
            delMessage("", $data['callback_query']['message']['message_id']);
            exchange2listOffers($p[1],$p[2]);
        }
        elseif( preg_match("/EXCN\|/", $data['callback_query']['data'])){
            $p = explode("|", $data['callback_query']['data']);
            delMessage("", $data['callback_query']['message']['message_id']);
            exchange2NewBid($p[1],$p[2]);
        }
        elseif( preg_match("/EXCB\|/", $data['callback_query']['data'])){
            $p = explode("|", $data['callback_query']['data']);
            delMessage("", $data['callback_query']['message']['message_id']);
            exchange2NewBidBuy($p[1],$p[2]);
        }
        elseif( preg_match("/EXCS\|/", $data['callback_query']['data'])){
            $p = explode("|", $data['callback_query']['data']);
            delMessage("", $data['callback_query']['message']['message_id']);
            exchange2NewBidSell($p[1],$p[2]);
        }
        elseif( preg_match("/EXCP\|/", $data['callback_query']['data'])){
            $p = explode("|", $data['callback_query']['data']);
            delMessage("", $data['callback_query']['message']['message_id']);
            exchange2NewBidBuy2($p[1],$p[2],$p[3]);
        }
  }else{

		$str5select = "SELECT `action` FROM `temp_sess` WHERE `chatid`='$chat_id' ORDER BY `rowid` DESC LIMIT 1";
		$result5 = mysqli_query($link, $str5select);
		$row5 = @mysqli_fetch_object($result5);
// Wallet
		if(preg_match("/withdrawWallet\|/", $row5->action)){
			withdrawFundsWait4Sum($data, $row5);
		}
		elseif(preg_match("/withdrawSum\|/", $row5->action)){
			withdrawFundsProcessSum($data, $row5);
		}
        elseif(preg_match("/buyTGRsum/", $row5->action)){
            buyTGRProcessSum($data);
        }
        elseif(preg_match("/chqsum\|/", $row5->action)){
//Cheques
            chequeHandleSum($data);
        }
        elseif(preg_match("/cnum/", $row5->action)){
            chequeHandleNum($data, $row5);
        }
        elseif(preg_match("/chqAddDesc\|/", $row5->action)){
            delMessage($data['message']['message_id'], "");
            chequeSaveDesc($data, $row5);
        }
        elseif(preg_match("/chqAddPass\|/", $row5->action)){
            delMessage($data['message']['message_id'], "");
            chequeSavePass($data, $row5);
        }
        elseif(preg_match("/chqsub\|/", $row5->action)){
            delMessage($data['message']['message_id'], "");
            chequeSaveChat($data, $row5);
        }
        elseif(preg_match("/stakingsum\|/", $row5->action)){
//Staking
            delMessage($data['message']['message_id'], "");
            stakingWait4Term($data, $row5);
        }
        elseif(preg_match("/exs\|/", $row5->action)){
// Exchange
            delMessage($data['message']['message_id'], "");
            exchangeProcessSum(trim($data['message']['text']), $row5);
        }
        elseif(preg_match("/ex2sum\|/", $row5->action)){
//Exchange
            $p = explode("|", $row5->action);
            delMessage($data['message']['message_id'], "");
            exchange2NewBidBuy2($p[1],$p[2],$data['message']['text']);
        }
	}

} // if-else /start

exit('ok'); //Обязательно возвращаем "ok", чтобы телеграмм не подумал, что запрос не дошёл
