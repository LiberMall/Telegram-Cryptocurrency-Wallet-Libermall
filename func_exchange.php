<?php
function exchangeStart(){
    global $chat_id, $link;

    clean_temp_sess();
    $i = 0;
    $c = 0;
    $row = getRowUsers();
    if($row->ton_ton_full > 0.1){
        $arInfo["inline_keyboard"][$i][0]["callback_data"] = 60;
        $arInfo["inline_keyboard"][$i][0]["text"] = "TON";
        #$i++;
        $c++;
    }
    if(($row->tgr_ton_full + $row->tgr_bep20) > 0){
        $arInfo["inline_keyboard"][$i][$c]["callback_data"] = 61;
        $arInfo["inline_keyboard"][$i][$c]["text"] = "TGR";
        $i++;
    }
    $arInfo["inline_keyboard"][$i][0]["callback_data"] = 15;
    $arInfo["inline_keyboard"][$i][0]["text"] = "⏪ Назад в меню";
    send($chat_id, "В этом разделе ты можешь обменять один из своих крипто-активов на другой.
<i>Комиссия за обмен: 0.5%</i>
Выбери криптовалюту:", $arInfo);
}
function exchangeChooseDirection($source){
    global $chat_id, $link;

    $assets = array("TON","TGR");
    $c = 0;
    for ($i = 0; $i < count($assets); $i++) {
        if($assets[$i] == $source) continue;
        $arInfo["inline_keyboard"][0][$c]["callback_data"] = "exc|$source|$assets[$i]";
        $arInfo["inline_keyboard"][0][$c]["text"] = $assets[$i];
        $c++;
    } // end FOR
    $arInfo["inline_keyboard"][1][0]["callback_data"] = 3;
    $arInfo["inline_keyboard"][1][0]["text"] = "⏪ Назад к обменам";
    send($chat_id, "Выбери направление обмена:", $arInfo);
}
function exchangeRequestSum($source,$direction){
    global $chat_id, $link, $exchangefee;

    $row = getRowUsers();
    if($source == "TON"){
        $maxsum = $row->ton_ton_full - 0.1;
    }elseif($source == "TGR"){
        $maxsum = $row->tgr_ton_full + $row->tgr_bep20;
    }
    $maxsum = $maxsum - $maxsum * $exchangefee;

    clean_temp_sess();
    save2temp("action", "exs|$source|$direction|$maxsum");

    $arInfo["inline_keyboard"][0][0]["callback_data"] = "exsmax";
    $arInfo["inline_keyboard"][0][0]["text"] = "Макс.: $maxsum $source";
    $arInfo["inline_keyboard"][1][0]["callback_data"] = 3;
    $arInfo["inline_keyboard"][1][0]["text"] = "⏪ Назад к обменам";
    send($chat_id, "Укажи сумму в $source для обмена на $direction:", $arInfo);
}
function exchangePreProcessSum(){
    global $chat_id, $link;

    $str5select = "SELECT `action` FROM `temp_sess` WHERE `chatid`='$chat_id' ORDER BY `rowid` DESC LIMIT 1";
    $result5 = mysqli_query($link, $str5select);
    $row5 = @mysqli_fetch_object($result5);

    if(preg_match("/exs\|/", $row5->action)){
        exchangeProcessSum(-1000, $row5);
    }else{
        $arInfo["inline_keyboard"][0][0]["callback_data"] = 3;
        $arInfo["inline_keyboard"][0][0]["text"] = "⏪ Назад к обменам";
        send($chat_id, "❌ОШИБКА! Произошла неизвестная ошибка. Повтори попытку.", $arInfo);
    }
}
function exchangeProcessSum($sum, $row5){
    global $chat_id, $link;

    $r = explode("|", $row5->action);
    $source = $r[1];
    $direction = $r[2];
    if($sum == -1000) $sum = $r[3];
    $maxsum = $r[3];

    if($sum > $maxsum) {
        $arInfo["inline_keyboard"][0][0]["callback_data"] = 3;
        $arInfo["inline_keyboard"][0][0]["text"] = "⏪ Назад к обменам";
        send($chat_id, "❌ОШИБКА! Сумма обмена ($sum $source) превышает сумму на балансе ($maxsum $source). Повтори попытку.", $arInfo);
    }
    elseif($sum <= 0){
        $arInfo["inline_keyboard"][0][0]["callback_data"] = 3;
        $arInfo["inline_keyboard"][0][0]["text"] = "⏪ Назад к обменам";
        send($chat_id, "❌ОШИБКА! Введенное значение не похоже на правильную сумму. Повтори попытку.", $arInfo);
    }else{
        $arInfo["inline_keyboard"][0][0]["callback_data"] = "exf|$sum";
        $arInfo["inline_keyboard"][0][0]["text"] = "✅ Обменять";
        $arInfo["inline_keyboard"][0][1]["callback_data"] = 3;
        $arInfo["inline_keyboard"][0][1]["text"] = "⛔️ Отменить";
        $arInfo["inline_keyboard"][1][0]["callback_data"] = 3;
        $arInfo["inline_keyboard"][1][0]["text"] = "⏪ Назад к обменам";
        send($chat_id, "Информация по обмену:
Обмен <b>$sum $source на $direction</b>
Подтверди операцию:", $arInfo);
    }
}
function exchangeFinish($sum){
    global $chat_id, $link, $exchangefee;

    $str5select = "SELECT `action` FROM `temp_sess` WHERE `chatid`='$chat_id' ORDER BY `rowid` DESC LIMIT 1";
    $result5 = mysqli_query($link, $str5select);
    $row5 = @mysqli_fetch_object($result5);

    $r = explode("|", $row5->action);
    $source = $r[1];
    $direction = $r[2];

    //checking balance once again
    $row = getRowUsers();
    if($source == "TON"){
        $maxsum = $row->ton_ton_full - 0.1;
    }elseif($source == "TGR"){
        $maxsum = $row->tgr_ton_full + $row->tgr_bep20;
    }
    $maxsum = $maxsum - $maxsum * $exchangefee;

    if($sum > $maxsum) {
        $arInfo["inline_keyboard"][0][0]["callback_data"] = 3;
        $arInfo["inline_keyboard"][0][0]["text"] = "⏪ Назад к обменам";
        send($chat_id, "❌ОШИБКА! Сумма обмена ($sum $source) превышает сумму на балансе ($maxsum $source). Повтори попытку.", $arInfo);
    }else{
        //getting rates
        $response = file_get_contents('https://tegro.money/rates/TON-TGR/');
        $res = json_decode($response, true);
        $tonTgrRate = $res['data']['value'];

        if($tonTgrRate != 0 && !empty($tonTgrRate)){
            //processing exchange
            //taking fee from the user balance
            $totalMinus = $sum + $sum * $exchangefee;
            if($source == "TON" && $direction == "TGR"){
                $resultSum = $sum * $tonTgrRate;
                $sourceRestSumTon = $row->ton_ton_full - $totalMinus;
                $newTotal = $row->tgr_ton_full + $resultSum;
                $str2upd = "UPDATE `users` SET `ton_ton_full`='$sourceRestSumTon', `tgr_ton_full`='$newTotal' WHERE `chatid`='$chat_id'";
            }else{
                $resultSum = $sum / $tonTgrRate;
                $newTotal = $row->ton_ton_full + $resultSum;
                if($row->tgr_ton_full >= $totalMinus){
                    $sourceRestSumTgrTon = $row->tgr_ton_full - $totalMinus;
                    $sourceRestSumTgrBep = $row->tgr_bep20;
                }else{
                    $minusInBep = $totalMinus - $row->tgr_ton_full;
                    $sourceRestSumTgrTon = 0;
                    $sourceRestSumTgrBep = $row->tgr_bep20 - $minusInBep;
                }
                $str2upd = "UPDATE `users` SET `tgr_ton_full`='$sourceRestSumTgrTon', `tgr_bep20`='$sourceRestSumTgrBep', `ton_ton_full`='$newTotal' WHERE `chatid`='$chat_id'";
            }
            mysqli_query($link, $str2upd);

            //applying referral fee
            if($row->ref > 10){
                $str6select = "SELECT * FROM `users` WHERE `chatid`='".$row->ref."'";
                $result6 = mysqli_query($link, $str6select);
                $row6 = @mysqli_fetch_object($result6);

                $refFee = $sum * $exchangefee / 2;
                $refFeeCellName = strtolower($source)."_ton_full";
                $newtotal = $row6->$refFeeCellName + $refFee;

                $str2upd = "UPDATE `users` SET `$refFeeCellName`='$newtotal' WHERE `chatid`='".$row->ref."'";
                mysqli_query($link, $str2upd);

                $arInfo["inline_keyboard"][0][0]["callback_data"] = 1;
                $arInfo["inline_keyboard"][0][0]["text"] = "💎 В кошелек";
                send($row->ref, "Твой реферал выполнил новый обмен.
Твоя комиссия составила: <b>$refFee $source</b>
Данная сумма зачислена на твой баланс.", $arInfo);
            }

            //save transaction
            saveTransaction($sum, $source, "TON", "swap-send", 0);
            saveTransaction($newTotal, $direction, "TON", "swap-get", 0);

            clean_temp_sess();
            $arInfo["inline_keyboard"][0][0]["callback_data"] = 1;
            $arInfo["inline_keyboard"][0][0]["text"] = "💎 В кошелек";
            $arInfo["inline_keyboard"][1][0]["callback_data"] = 3;
            $arInfo["inline_keyboard"][1][0]["text"] = "⏪ К обменам";
            send($chat_id, "Обмен выполнен.
<b>$sum $source на $direction</b>
$resultSum $direction зачислены на твой баланс.", $arInfo);
        }
        else{
            $arInfo["inline_keyboard"][0][0]["callback_data"] = 3;
            $arInfo["inline_keyboard"][0][0]["text"] = "⏪ К обменам";
            send($chat_id, "❌ОШИБКА! Произошла неизвестная ошибка, код 431. Повтори попытку.", $arInfo);
        }
    }
}