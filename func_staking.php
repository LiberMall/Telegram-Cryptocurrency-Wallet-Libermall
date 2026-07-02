<?php
function stakingMenu(){
    global $chat_id, $link;

    $str2select = "SELECT * FROM `staking` WHERE `chatid`='$chat_id'";
    $result = mysqli_query($link, $str2select);

    $i = 1;
    $arInfo["inline_keyboard"][0][0]["callback_data"] = 50;
    $arInfo["inline_keyboard"][0][0]["text"] = "💵 Открыть депозит";
    if(mysqli_num_rows($result) > 0){
        $arInfo["inline_keyboard"][1][0]["callback_data"] = 51;
        $arInfo["inline_keyboard"][1][0]["text"] = "💰 Мои депозиты";
        $i = 2;
    }
    $arInfo["inline_keyboard"][$i][0]["callback_data"] = 15;
    $arInfo["inline_keyboard"][$i][0]["text"] = t('back_to_main');
    send($chat_id, "Получайте вознаграждение, размещая в стейкинге цифровые активы.", $arInfo);
}
function stakingChooseAsset(){
    global $chat_id, $link;

    clean_temp_sess();
    $arInfo["inline_keyboard"][0][0]["callback_data"] = 52;
    $arInfo["inline_keyboard"][0][0]["text"] = "TGR";
    $arInfo["inline_keyboard"][0][1]["callback_data"] = 53;
    $arInfo["inline_keyboard"][0][1]["text"] = "TON";
    $arInfo["inline_keyboard"][1][0]["callback_data"] = 8;
    $arInfo["inline_keyboard"][1][0]["text"] = "⏪ Назад в стейкинг";
    send($chat_id, "Выбери актив для депозита:", $arInfo);
}
function stakingWait4Sum($asset){
    global $chat_id, $link;

    $nomoney = 0;
    $row = getRowUsers();
    if($asset == "TGR"){
        $minsum = 2500;
        if($row->tgr_ton_full < $minsum) $nomoney = 1;
    }else{
        $minsum = 10;
        if($row->ton_ton_full < $minsum) $nomoney = 1;
    }

    if($nomoney == 1){
        $arInfo["inline_keyboard"][0][0]["callback_data"] = 1;
        $arInfo["inline_keyboard"][0][0]["text"] = "💎 Перейти в кошелёк";
        $arInfo["inline_keyboard"][1][0]["callback_data"] = 50;
        $arInfo["inline_keyboard"][1][0]["text"] = "⏪ К выбору стейкинга";
        send($chat_id, "❌ОШИБКА! Минимум для депозита: $minsum $asset. На твоем балансе недостаточно средств.", $arInfo);
    }else{
        clean_temp_sess();
        save2temp("action", "stakingsum|$asset");

        $arInfo["inline_keyboard"][0][0]["callback_data"] = 50;
        $arInfo["inline_keyboard"][0][0]["text"] = "⏪ K созданию депозита";
        send($chat_id, "Укажи сумму, которую ты хочешь разместить на депозит (минимум: $minsum $asset):", $arInfo);
    }
}
function stakingWait4Term($data, $row5){
    global $chat_id, $link, $stakingfee;

    $sum = floatval(trim($data['message']['text']));
    $r = explode("|", $row5->action);
    $asset = $r[1];

    $nomoney = 0;
    $row = getRowUsers();
    if($asset == "TGR"){
        if($row->tgr_ton_full < $sum) $nomoney = 1;
        $minsum = 2500;
        $e = 0;
    }else{
        if($row->ton_ton_full < $sum) $nomoney = 1;
        $minsum = 10;
        $e = 1;
    }
    if($sum < $minsum) {
        $arInfo["inline_keyboard"][0][0]["callback_data"] = 50;
        $arInfo["inline_keyboard"][0][0]["text"] = "⏪ Назад к созданию депозита";
        send($chat_id, "❌ОШИБКА! Указана сумма ниже допустимого лимита. Минимальная сумма для депозита: $minsum $asset. Повтори попытку:", $arInfo);
    }
    elseif($nomoney == 1){
        $arInfo["inline_keyboard"][0][0]["callback_data"] = 50;
        $arInfo["inline_keyboard"][0][0]["text"] = "⏪ Назад к созданию депозита";
        send($chat_id, "❌ОШИБКА! На твоем балансе недостаточно средств.", $arInfo);
    }else{
        clean_temp_sess();
        $arInfo["inline_keyboard"][0][0]["callback_data"] = "STKT|$asset|$sum|0";
        $arInfo["inline_keyboard"][0][0]["text"] = "3 месяца — ".$stakingfee[$e][0]."% годовых";
        $arInfo["inline_keyboard"][1][0]["callback_data"] = "STKT|$asset|$sum|1";
        $arInfo["inline_keyboard"][1][0]["text"] = "6 месяцев — ".$stakingfee[$e][1]."% годовых";
        $arInfo["inline_keyboard"][2][0]["callback_data"] = "STKT|$asset|$sum|2";
        $arInfo["inline_keyboard"][2][0]["text"] = "9 месяцев — ".$stakingfee[$e][2]."% годовых";
        $arInfo["inline_keyboard"][3][0]["callback_data"] = "STKT|$asset|$sum|3";
        $arInfo["inline_keyboard"][3][0]["text"] = "12 месяцев — ".$stakingfee[$e][3]."% годовых";
        $arInfo["inline_keyboard"][4][0]["callback_data"] = 50;
        $arInfo["inline_keyboard"][4][0]["text"] = "⏪ Назад к созданию депозита";
        send($chat_id, "Укажи срок, на который ты хочешь разместить депозит:", $arInfo);
    }
}
function stakingProcessDepo($asset, $sum, $m){
    global $chat_id, $link, $stakingfee;

    if($asset == "TGR"){
        $e = 0;
    }else{
        $e = 1;
    }

    switch ($m) {
        case 0:
            $days = 92;
            $months = 3;
            break;
        case 1:
            $days = 184;
            $months = 6;
            break;
        case 2:
            $days = 274;
            $months = 9;
            break;
        case 3:
            $days = 365;
            $months = 12;
            break;
    }
    
    $fee = round($sum * ($stakingfee[$e][$m] / 365 * $days) / 100, 2);
    $totalReturn = $sum + $fee;
    $totalReturn = round($totalReturn, 2);

    $str2select = "SELECT * FROM `users` WHERE `chatid`='$chat_id'";
    $result = mysqli_query($link, $str2select);
    $row = @mysqli_fetch_object($result);

    $full_cell = strtolower($asset)."_ton_full";
    $newtotal = $row->$full_cell - $sum;

    $str2upd = "UPDATE `users` SET `$full_cell`='$newtotal' WHERE `chatid`='$chat_id'";
    mysqli_query($link, $str2upd);

    $stime = time();
    $endtime = $stime + 86400 * $days;
    $str2ins = "INSERT INTO `staking` (`chatid`,`asset`,`sum`,`percent`,`months`,`starttime`,`endtime`,`endsum`) VALUES ('$chat_id','$asset','$sum','".$stakingfee[$e][$m]."','$months','$stime','$endtime','$totalReturn')";
    mysqli_query($link, $str2ins);

    saveTransaction($sum, $asset, "TON", "staking", 0);

    $arInfo["inline_keyboard"][0][0]["callback_data"] = 8;
    $arInfo["inline_keyboard"][0][0]["text"] = "⏪ Назад в стейкинг";
    send($chat_id, "<b>Депозит размещен.</b>
Сумма депозита: $sum $asset списана с твоего баланса.
Срок депозита: $months месяцев.
Доход: $fee $asset.", $arInfo);
}
function stakingListDeposits(){
    global $chat_id, $link;

    $i = 0;
    $str2select = "SELECT * FROM `staking` WHERE `chatid`='$chat_id'";
    $result = mysqli_query($link, $str2select);
    while($row = @mysqli_fetch_object($result)){
        $depodate = date("j/m/Y", $row->starttime);
        $arInfo["inline_keyboard"][$i][0]["callback_data"] = "STKR|".$row->rowid;
        $arInfo["inline_keyboard"][$i][0]["text"] = $depodate.": ".$row->sum." ".$row->asset;
        $i++;
    }  // end WHILE MySQL
    $arInfo["inline_keyboard"][$i][0]["callback_data"] = 8;
    $arInfo["inline_keyboard"][$i][0]["text"] = "⏪ Назад в стейкинг";
    send($chat_id, "Список твоих активных депозитов:", $arInfo);
}
function stakingShowDepoDetails($rowid){
    global $chat_id, $link;

    $str2select = "SELECT * FROM `staking` WHERE `rowid`='$rowid'";
    $result = mysqli_query($link, $str2select);
    $row = @mysqli_fetch_object($result);

    $fee = $row->endsum - $row->sum;
    $enddate = date("j/m/Y", $row->endtime);;

    $arInfo["inline_keyboard"][0][0]["callback_data"] = 51;
    $arInfo["inline_keyboard"][0][0]["text"] = "⏪ К списку депозитов";
    send($chat_id, "Сумма депозита: <b>$row->sum $row->asset.</b>
Срок депозита: <b>$row->months месяцев.</b>
Доход: <b>$fee $row->asset.</b>
Возврат депозита: <b>$enddate.</b>", $arInfo);
}