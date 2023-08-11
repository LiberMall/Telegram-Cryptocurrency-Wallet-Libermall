<?php
function createCheque(){
    global $chat_id, $link;

    $str2select = "SELECT `rowid` FROM `cheques` WHERE `chatid`='$chat_id'";
    $result = mysqli_query($link, $str2select);

    $arInfo["inline_keyboard"][0][0]["callback_data"] = 40;
    $arInfo["inline_keyboard"][0][0]["text"] = "💸 Создать чек";
    $i = 1;
    if(mysqli_num_rows($result) > 0){
        $arInfo["inline_keyboard"][$i][0]["callback_data"] = 44;
        $arInfo["inline_keyboard"][$i][0]["text"] = "📝 Твои чеки";
        $i++;
    }
    $arInfo["inline_keyboard"][$i][0]["callback_data"] = 15;
    $arInfo["inline_keyboard"][$i][0]["text"] = "⏪ Назад";
    send($chat_id, "Данная функция предназначена для перевода криптовалюты между счетами пользователей бота.
Ты можешь создать здесь чек для конкретного получателя или мультичек с реферальным вознаграждением.", $arInfo);
}
function chequeListCoins(){
    global $chat_id;

    $arInfo["inline_keyboard"][0][0]["callback_data"] = 41;
    $arInfo["inline_keyboard"][0][0]["text"] = "TGR";
    $arInfo["inline_keyboard"][0][1]["callback_data"] = 42;
    $arInfo["inline_keyboard"][0][1]["text"] = "TON";
    $arInfo["inline_keyboard"][1][0]["callback_data"] = 2;
    $arInfo["inline_keyboard"][1][0]["text"] = "⏪ Назад";
    send($chat_id, "Выбери актив для чека:", $arInfo);
}
function chequeWait4Sum($asset){
    global $chat_id, $link, $chequefee;

    $str2select = "SELECT * FROM `users` WHERE `chatid`='$chat_id'";
    $result = mysqli_query($link, $str2select);
    $row = @mysqli_fetch_object($result);

    #$str3select = "SELECT chatid, SUM(`sum`) AS total_sum FROM cheques WHERE `chatid` = '$chat_id' AND `asset` = '$asset'";
    #$result3 = mysqli_query($link, $str3select);
    #$row3 = @mysqli_fetch_object($result3);

    if($asset == "TON"){
        $minlimit = 0.01;
        $availableBalance = $row->ton_ton_full;
        #$availableBalance -= $availableBalance * $chequefee;
        $suminUSD = $availableBalance * getTONrate();
    }else{
        $minlimit = 10;
        $availableBalance = $row->tgr_ton_full + $row->tgr_bep20;
        #$availableBalance -= $availableBalance * $chequefee;
        $suminUSD = $availableBalance * getTGRrate();
    }

    clean_temp_sess();
    save2temp("action", "chqsum|$asset|$availableBalance");

    $shortBalance = round($availableBalance, 2);
    $assetNum = ($asset == "TON") ? 1 : 2;
    unlink("tmp/$chat_id.php");
    $tofile = "<?php \$availableBalance = $availableBalance;";
    file_put_contents("tmp/$chat_id.php", $tofile);

    $arInfo["inline_keyboard"][0][0]["callback_data"] = "CHQSUM|$minlimit|$assetNum";
    $arInfo["inline_keyboard"][0][0]["text"] = "Мин: $minlimit $asset";
    $arInfo["inline_keyboard"][0][1]["callback_data"] = "CHQSUM|$shortBalance|$assetNum";
    $arInfo["inline_keyboard"][0][1]["text"] = "Макс: $shortBalance $asset";
    $arInfo["inline_keyboard"][1][0]["callback_data"] = 40;
    $arInfo["inline_keyboard"][1][0]["text"] = "⏪ Изменить монету";
    send($chat_id, "Пришли сумму чека в $asset. Если хотешь создать мультичек, введи сумму одной активации, кратную твоему балансу.
Твой баланс: $availableBalance $asset (\$$suminUSD):", $arInfo);
}
function chequeHandleSum($data){
    global $chat_id, $link;

    $sum = floatval(trim($data['message']['text']));

    $str5select = "SELECT `action` FROM `temp_sess` WHERE `chatid`='$chat_id' ORDER BY `rowid` DESC LIMIT 1";
    $result5 = mysqli_query($link, $str5select);
    $row5 = @mysqli_fetch_object($result5);
    $r = explode("|", $row5->action);
    $asset = $r[1];
    $availableBalance = $r[2];

    if($sum <= 0) {
        $response = array(
            'chat_id' => $chat_id,
            'text' => "❌ОШИБКА! Ввведенное значение не похоже на сумму. Повтори попытку.",
            'parse_mode' => 'HTML');
        sendit($response, 'sendMessage');
    }
    elseif($sum > $availableBalance){
        $response = array(
            'chat_id' => $chat_id,
            'text' => "❌ОШИБКА! Ввведенное значение превышает доступную сумму баланса. Повтори попытку.",
            'parse_mode' => 'HTML');
        sendit($response, 'sendMessage');
    }else{
        chequeSetRef($sum, $asset, $availableBalance);
    }
}
function chequeSetRef($sum, $asset, $availableBalance){
    global $chat_id, $link;

    if($asset == "TON"){
        $minlimit = 0.01;
    }else{
        $minlimit = 10;
    }

    if($sum > $availableBalance) {
        $response = array(
            'chat_id' => $chat_id,
            'text' => "❌ОШИБКА! Ввведенное значение превышает доступную сумму баланса. Повтори попытку.",
            'parse_mode' => 'HTML');
        sendit($response, 'sendMessage');
    }
    elseif($sum < $minlimit){
        $response = array(
            'chat_id' => $chat_id,
            'text' => "❌ОШИБКА! Ввведенное значение меньше минимального лимита. Повтори попытку.",
            'parse_mode' => 'HTML');
        sendit($response, 'sendMessage');
    }else {

        $tofile = "<?php 
\$sum = $sum;
\$asset = '$asset';";
        file_put_contents("tmp/san_$chat_id.php", $tofile);

        $assetNum = ($asset == "TON") ? 42 : 41;

        $arInfo["inline_keyboard"][0][0]["callback_data"] = "CREF|0";
        $arInfo["inline_keyboard"][0][0]["text"] = "0%";
        $arInfo["inline_keyboard"][0][1]["callback_data"] = "CREF|25";
        $arInfo["inline_keyboard"][0][1]["text"] = "25%";
        $arInfo["inline_keyboard"][0][2]["callback_data"] = "CREF|50";
        $arInfo["inline_keyboard"][0][2]["text"] = "50%";
        $arInfo["inline_keyboard"][0][3]["callback_data"] = "CREF|75";
        $arInfo["inline_keyboard"][0][3]["text"] = "75%";
        $arInfo["inline_keyboard"][0][4]["callback_data"] = "CREF|100";
        $arInfo["inline_keyboard"][0][4]["text"] = "100%";
        $arInfo["inline_keyboard"][1][0]["callback_data"] = $assetNum;
        $arInfo["inline_keyboard"][1][0]["text"] = "⏪ Изменить сумму";
        send($chat_id, "Реферальная награда - это процент от суммы одной активации чека, который будет получать распространитель чека за каждую активацию данного чека по его реферальной ссылке.
    
Выбери оптимальный для тебя процент:", $arInfo);
    }
}
function chequeSetNumActivations($sum, $asset, $availableBalance, $ref){
    global $chat_id, $chequefee;

    $maxnum = floor($availableBalance / (($sum+$sum*$ref/100)+($sum+$sum*$ref/100)*$chequefee));
    $assetNum = ($asset == "TON") ? 1 : 2;

    if($maxnum < 1){
        $response = array(
            'chat_id' => $chat_id,
            'text' => "❌ОШИБКА! Недостаточно средств на балансе. Повтори попытку.",
            'parse_mode' => 'HTML');
        sendit($response, 'sendMessage');
    }else{
        clean_temp_sess();
        save2temp("action", "cnum|$sum|$asset|$maxnum");

        $tofile = "<?php 
    \$sum = $sum;
    \$asset = '$asset';
    \$ref = $ref;";
        file_put_contents("tmp/san_$chat_id.php", $tofile);

        $num = ($asset == "TON") ? 42 : 41;
        $arInfo["inline_keyboard"][0][0]["callback_data"] = "CNUM|1";
        $arInfo["inline_keyboard"][0][0]["text"] = "Пропустить";
        $arInfo["inline_keyboard"][0][1]["callback_data"] = "CNUM|$maxnum";
        $arInfo["inline_keyboard"][0][1]["text"] = "Макс.кол-во - $maxnum";
        $arInfo["inline_keyboard"][1][0]["callback_data"] = $num;
        $arInfo["inline_keyboard"][1][0]["text"] = "⏪ Изменить сумму";
        send($chat_id, "По желанию укажи количество активаций чека, чтобы создать мультичек (до $maxnum активаций)", $arInfo);
    }
}
function chequeHandleNum($data, $row5){
    global $chat_id, $link;

    $num = intval(trim($data['message']['text']));
    $r = explode("|", $row5->action);

    if ($num > $r[3]) {
        $response = array(
            'chat_id' => $chat_id,
            'text' => "❌ОШИБКА! Ввведенное значение превышает доступную сумму баланса. Повтори попытку.",
            'parse_mode' => 'HTML');
        sendit($response, 'sendMessage');
    }
    elseif ($num < 1) {
        $response = array(
            'chat_id' => $chat_id,
            'text' => "❌ОШИБКА! Введно некорректное количество активаций. Повтори попытку.",
            'parse_mode' => 'HTML');
        sendit($response, 'sendMessage');
    } else {
        include "tmp/san_$chat_id.php";
        chequeIssue($sum, $asset, $num, $ref);
    }
}
function chequeIssue($sum, $asset, $num, $ref){
    global $chat_id, $link, $chequefee;

    include "tmp/$chat_id.php";
    $totalChequeSum = ($sum + $sum*$ref/100) * $num;
    $totalChequeSum += $totalChequeSum * $chequefee;

    if($availableBalance < $totalChequeSum){
        $arInfo["inline_keyboard"][0][0]["callback_data"] = "CREF|$ref";
        $arInfo["inline_keyboard"][0][0]["text"] = "Изменить активации";
        send($chat_id, "❌ОШИБКА! Недостаточно баланса для указанных сумм и количества активаций.", $arInfo);
    }else{
        clean_temp_sess();
        unlink("tmp/$chat_id.php");
        unlink("tmp/san_$chat_id.php");

        $ucode = generatePassword(10);
        $ctime = time();
        $refmark = ($ref != 0) ? 1 : 0;
        $str2ins = "INSERT INTO `cheques` (`chatid`,`ucode`,`sum`,`asset`,`activations`,`percent`,`times`,`captcha`,`total_activs`,`ref`) VALUES ('$chat_id','$ucode','$sum','$asset','$num','$ref','$ctime','1','$num','$refmark')";
        mysqli_query($link, $str2ins);
        $chequeno = mysqli_insert_id($link);

        $row = getRowUsers();
        if($asset == "TON"){
            $restBalance = $row->ton_ton_full - $totalChequeSum;
            $str2upd = "UPDATE `users` SET `ton_ton_full`='$restBalance' WHERE `chatid`='$chat_id'";
        }else{
            if($totalChequeSum > $row->tgr_ton_full){
                $extra = $totalChequeSum - $row->tgr_ton_full;
                $restBalanceBEP = $row->tgr_bep20 - $extra;
                $str2upd = "UPDATE `users` SET `tgr_ton_full`='0', `tgr_bep20`='$restBalanceBEP' WHERE `chatid`='$chat_id'";
            }else{
                $restBalance = $row->tgr_ton_full - $totalChequeSum;
                $str2upd = "UPDATE `users` SET `tgr_ton_full`='$restBalance' WHERE `chatid`='$chat_id'";
            }
        }
        mysqli_query($link, $str2upd);

        #unlink("tmp/chno_$chat_id.php");
        #$tofile = "<?php \$chequeno = $chequeno;";
        #file_put_contents("tmp/chno_$chat_id.php", $tofile);

        chequeShowDetails($chequeno, "Чек создан!");
    }
}
function chequeList(){
    global $chat_id, $link;

    $i = 0;
    $str2select = "SELECT * FROM `cheques` WHERE `chatid`='$chat_id' ORDER BY `rowid`";
    $result = mysqli_query($link, $str2select);
    while($row = @mysqli_fetch_object($result)){
        $chDate = date('j-m-Y', $row->times);
        $arInfo["inline_keyboard"][$i][0]["callback_data"] = "CHL|$row->rowid";
        $arInfo["inline_keyboard"][$i][0]["text"] = "$row->sum $row->asset ($chDate)";
        $i++;
    }  // end WHILE MySQL
    $arInfo["inline_keyboard"][$i][0]["callback_data"] = 43;
    $arInfo["inline_keyboard"][$i][0]["text"] = "⏪ Назад в Чеки";
    send($chat_id, "Cписок твоих активных чеков:", $arInfo);
}
function chequeShowDetails($chequeno, $mess){
    global $chat_id, $link;

    $str2select = "SELECT * FROM `cheques` WHERE `rowid`='$chequeno'";
    $result = mysqli_query($link, $str2select);
    $row = @mysqli_fetch_object($result);

    if($row->asset == "TON"){
        $suminUSD = $row->sum * getTONrate();
    }else{
        $suminUSD = $row->sum * getTGRrate();
    }

    #$arInfo["inline_keyboard"][0][0]["switch_inline_query"] = '';
    #$arInfo["inline_keyboard"][0][0]["cache_time"] = 2;
    #$arInfo["inline_keyboard"][0][0]["text"] = "Отправить чек";
    $arInfo["inline_keyboard"][0][0]["callback_data"] = "CHQ|$chequeno";
    $arInfo["inline_keyboard"][0][0]["text"] = "Показать QR-код";
    if($row->descr != '0'){
        $word = "Убрать";
        $suff = 2;
    }else{
        $word = "Добавить";
        $suff = 1;
    }
    $arInfo["inline_keyboard"][1][0]["callback_data"] = "CHD|$chequeno|$suff";
    $arInfo["inline_keyboard"][1][0]["text"] = "$word описание";
    if($row->pass != '0'){
        $wordp = "Убрать";
        $suffp = 2;
    }else{
        $wordp = "Добавить";
        $suffp = 1;
    }
    $arInfo["inline_keyboard"][2][0]["callback_data"] = "CHP|$chequeno|$suffp";
    $arInfo["inline_keyboard"][2][0]["text"] = "$wordp пароль";
    $arInfo["inline_keyboard"][3][0]["callback_data"] = "CHR|$chequeno";
    $arInfo["inline_keyboard"][3][0]["text"] = "Задать/изменить рефералку";
    $switcher = ($row->captcha == 1) ? "Вкл" : "Выкл";
    $arInfo["inline_keyboard"][4][0]["callback_data"] = "CHC|$chequeno";
    $arInfo["inline_keyboard"][4][0]["text"] = "Каптча: $switcher";
    $switcher = ($row->phoneverif == 1) ? "Вкл" : "Выкл";
    $arInfo["inline_keyboard"][5][0]["callback_data"] = "CHV|$chequeno";
    $arInfo["inline_keyboard"][5][0]["text"] = "Верификация по тел.: $switcher";
    $switcher = ($row->notify == 1) ? "Вкл" : "Выкл";
    $arInfo["inline_keyboard"][6][0]["callback_data"] = "CHM|$chequeno";
    $arInfo["inline_keyboard"][6][0]["text"] = "Уведомления активации: $switcher";
    $switcher = ($row->ref == 1) ? "Вкл" : "Выкл";
    $arInfo["inline_keyboard"][7][0]["callback_data"] = "CHA|$chequeno";
    $arInfo["inline_keyboard"][7][0]["text"] = "Реф.система чека: $switcher";
    $switcher = ($row->approved == 1) ? "Вкл" : "Выкл";
    $arInfo["inline_keyboard"][8][0]["callback_data"] = "CHH|$chequeno";
    $arInfo["inline_keyboard"][8][0]["text"] = "Только для привязанных: $switcher";
    $switcher = ($row->subscr == 1) ? "Вкл" : "Выкл";
    $arInfo["inline_keyboard"][9][0]["callback_data"] = "CHY|$chequeno";
    $arInfo["inline_keyboard"][9][0]["text"] = "Проверка подписки: $switcher";
    $arInfo["inline_keyboard"][10][0]["callback_data"] = "CHX|$chequeno";
    $arInfo["inline_keyboard"][10][0]["text"] = "Удалить чек";
    $arInfo["inline_keyboard"][11][0]["callback_data"] = 43;
    $arInfo["inline_keyboard"][11][0]["text"] = "⏪ Назад в Чеки";
    send($chat_id, "$mess 
Сумма: $row->sum [$row->asset] (\$$suminUSD)
<b>Активации: $row->activations/$row->total_activs</b>
Любой может активировать этот чек.

Скопируй ссылку, чтобы поделиться чеком:
<code>https://t.me/LibermallBot?start=c".$chequeno."_".$row->ucode."</code>", $arInfo);
}
function chequeGetQRcode($rowid){
    global $chat_id, $link;

    $str2select = "SELECT * FROM `cheques` WHERE `rowid`='$rowid'";
    $result = mysqli_query($link, $str2select);
    $row = @mysqli_fetch_object($result);

    //get QR code
    $time = time();
    $a = "https://t.me/LibermallBot?start=c".$row->rowid."_".$row->ucode;
    $url = "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=" . urlencode($a) . "&choe=UTF-8";
    $img = file_get_contents($url);
    $filename = "tmp/".$chat_id."_".$time.".jpg";
    file_put_contents($filename, $img);

    $initurl = "https://tegro.exchange/TegroMoneybot/tmp/".$chat_id."_".$time.".jpg";

    $response = array(
        'chat_id' => $chat_id,
        'caption' => '',
        'photo' => $initurl,
        'parse_mode' => 'HTML'
    );
    sendit($response, 'sendPhoto');

    $arInfo["inline_keyboard"][0][0]["callback_data"] = 43;
    $arInfo["inline_keyboard"][0][0]["text"] = "⏪ Назад в Чеки";
    send($chat_id, 'Ссылка на твой чек:
<code>'.$a.'</code>', $arInfo);

    sleep(5);
    unlink($filename);
}
function chequeAddDescription($rowid){
    global $chat_id, $link;

    clean_temp_sess();
    save2temp("action", "chqAddDesc|$rowid");

    $arInfo["inline_keyboard"][0][0]["callback_data"] = 43;
    $arInfo["inline_keyboard"][0][0]["text"] = "⏪ Назад в Чеки";
    send($chat_id, 'Введи текст описания к чеку (не более 255 символов):', $arInfo);
}
function chequeRemoveDescription($rowid){
    global $chat_id, $link;

    $str2upd = "UPDATE `cheques` SET `descr`='0' WHERE `rowid`='$rowid'";
    mysqli_query($link, $str2upd);

    chequeShowDetails($rowid, "Описание удалено!");
}
function chequeSaveDesc($data, $row){
    global $chat_id, $link;

    $chequedesc = trim($data['message']['text']);
    $p = explode("|", $row->action);
    $rowid = $p[1];

    if(strlen($chequedesc) > 255){
        $response = array(
            'chat_id' => $chat_id,
            'text' => "❌ОШИБКА! Ввведенный текст превышает 255 символов. Повтори попытку.",
            'parse_mode' => 'HTML');
        sendit($response, 'sendMessage');
    }else{
        clean_temp_sess();
        $str2upd = "UPDATE `cheques` SET `descr`='$chequedesc' WHERE `rowid`='$rowid'";
        mysqli_query($link, $str2upd);

        $arInfo["inline_keyboard"][0][0]["callback_data"] = 43;
        $arInfo["inline_keyboard"][0][0]["text"] = "⏪ Назад в Чеки";
        send($chat_id, 'Описание сохранено!', $arInfo);
    }
}
function chequeWait4Pass($rowid){
    global $chat_id, $link;

    clean_temp_sess();
    save2temp("action", "chqAddPass|$rowid");

    $arInfo["inline_keyboard"][0][0]["callback_data"] = 43;
    $arInfo["inline_keyboard"][0][0]["text"] = "⏪ Назад в Чеки";
    send($chat_id, 'Введи пароль к чеку (не более 64 символов):', $arInfo);
}
function chequeSavePass($data, $row){
    global $chat_id, $link;

    $chequepass = trim($data['message']['text']);
    $p = explode("|", $row->action);
    $rowid = $p[1];

    if(strlen($chequepass) > 64){
        $response = array(
            'chat_id' => $chat_id,
            'text' => "❌ОШИБКА! Ввведенный текст превышает 64 символа. Повтори попытку.",
            'parse_mode' => 'HTML');
        sendit($response, 'sendMessage');
    }else{
        clean_temp_sess();
        $str2upd = "UPDATE `cheques` SET `pass`='$chequepass' WHERE `rowid`='$rowid'";
        mysqli_query($link, $str2upd);

        $arInfo["inline_keyboard"][0][0]["callback_data"] = 43;
        $arInfo["inline_keyboard"][0][0]["text"] = "⏪ Назад в Чеки";
        send($chat_id, 'Пароль сохранен!', $arInfo);
    }
}
function chequeRemove4Pass($rowid){
    global $chat_id, $link;

    $str2upd = "UPDATE `cheques` SET `pass`='0' WHERE `rowid`='$rowid'";
    mysqli_query($link, $str2upd);

    chequeShowDetails($rowid, "Пароль удален!");
}
function chequeChangeRef($rowid){
    global $chat_id, $link;

    $str2select = "SELECT * FROM `cheques` WHERE `rowid`='$rowid'";
    $result = mysqli_query($link, $str2select);
    $row = @mysqli_fetch_object($result);

    $ql = ($row->percent == 0) ? "🔸" : "";
    $arInfo["inline_keyboard"][0][0]["callback_data"] = "CRF|0|$rowid";
    $arInfo["inline_keyboard"][0][0]["text"] = "0%".$ql;
    $ql = ($row->percent == 25) ? "🔸" : "";
    $arInfo["inline_keyboard"][0][1]["callback_data"] = "CRF|25|$rowid";
    $arInfo["inline_keyboard"][0][1]["text"] = "25%".$ql;
    $ql = ($row->percent == 50) ? "🔸" : "";
    $arInfo["inline_keyboard"][0][2]["callback_data"] = "CRF|50|$rowid";
    $arInfo["inline_keyboard"][0][2]["text"] = "50%".$ql;
    $ql = ($row->percent == 75) ? "🔸" : "";
    $arInfo["inline_keyboard"][0][3]["callback_data"] = "CRF|75|$rowid";
    $arInfo["inline_keyboard"][0][3]["text"] = "75%".$ql;
    $ql = ($row->percent == 100) ? "🔸" : "";
    $arInfo["inline_keyboard"][0][4]["callback_data"] = "CRF|100|$rowid";
    $arInfo["inline_keyboard"][0][4]["text"] = "100%".$ql;
    $arInfo["inline_keyboard"][1][0]["callback_data"] = 43;
    $arInfo["inline_keyboard"][1][0]["text"] = "⏪ Назад в Чеки";
    send($chat_id, "Реферальная награда - это процент от суммы одной активации чека, который будет получать распространитель чека за каждую активацию данного чека по его реферальной ссылке.
Текущий процент помечен значком 🔸     
Выбери оптимальный для тебя процент:", $arInfo);
}
function chequeEditRef($perc, $rowid){
    global $chat_id, $link;

    $row = getRowUsers();

    $str2select = "SELECT * FROM `cheques` WHERE `rowid`='$rowid'";
    $result2 = mysqli_query($link, $str2select);
    $row2 = @mysqli_fetch_object($result2);

    $nofunds = 0;
    if($perc > $row2->percent){
        $diff = $perc - $row2->percent;
        $extrasum = $row2->sum * $row2->activations * $diff / 100;
        $extrafee = $extrasum * 2 / 100;
        $extratotal = $extrasum + $extrafee;
        if($row2->asset == "TON"){
            if($row->ton_ton_full < $extratotal){
                $nofunds = 1;
            }else{
                $newbalance = $row->ton_ton_full - $extratotal;
                $str2upd = "UPDATE `users` SET `ton_ton_full`='$newbalance' WHERE `chatid`='$chat_id'";
                mysqli_query($link, $str2upd);
            }
        }
        elseif ($row2->asset == "TGR"){
            $tgrtotal = $row->tgr_ton_full + $row->tgr_bep20;
            if($tgrtotal < $extratotal){
                $nofunds = 1;
            }else{
                if($row->tgr_ton_full >= $extratotal){
                    $newbalance = $row->tgr_ton_full - $extratotal;
                    $str2upd = "UPDATE `users` SET `tgr_ton_full`='$newbalance' WHERE `chatid`='$chat_id'";
                    mysqli_query($link, $str2upd);
                }else{
                    $d = $extratotal - $row->tgr_ton_full;
                    $newbalanceBEP = $row->tgr_bep20 - $d;
                    $str2upd = "UPDATE `users` SET `tgr_ton_full`='0' WHERE `chatid`='$chat_id'";
                    mysqli_query($link, $str2upd);
                    $str3upd = "UPDATE `users` SET `tgr_bep20`='$newbalanceBEP' WHERE `chatid`='$chat_id'";
                    mysqli_query($link, $str3upd);
                }

            }
        }
        $tomessage = 'С твоего баланса списано:
'.$extrasum.' '.$row2->asset.' на реф.отичления и
'.$extrafee.' '.$row2->asset.' комиссиии';
    }else{
        $diff = $row2->percent - $perc;
        $extrasum = $row2->sum * $row2->activations * $diff / 100;
        $extrafee = $extrasum * 2 / 100;
        $extratotal = $extrasum + $extrafee;
        if($row2->asset == "TON"){
            $newbalance = $row->ton_ton_full + $extratotal;
            $str2upd = "UPDATE `users` SET `ton_ton_full`='$newbalance' WHERE `chatid`='$chat_id'";
            mysqli_query($link, $str2upd);
        }
        elseif ($row2->asset == "TGR"){
            $newbalance = $row->tgr_ton_full + $extratotal;
            $str2upd = "UPDATE `users` SET `tgr_ton_full`='$newbalance' WHERE `chatid`='$chat_id'";
            mysqli_query($link, $str2upd);
        }
        $tomessage = 'На твой баланс возвращено:
'.$extrasum.' '.$row2->asset.' реф.отичлений и
'.$extrafee.' '.$row2->asset.' комиссиии';
    }

    if($nofunds == 1){
        $response = array(
            'chat_id' => $chat_id,
            'text' => "❌ОШИБКА! Недостаточно средств на увеличение реф.процена на $perc%. Повтори попытку.",
            'parse_mode' => 'HTML');
        sendit($response, 'sendMessage');
        chequeChangeRef($rowid);
    }else{
        $str2upd = "UPDATE `cheques` SET `percent`='$perc' WHERE `rowid`='$rowid'";
        mysqli_query($link, $str2upd);

        $arInfo["inline_keyboard"][0][0]["callback_data"] = 43;
        $arInfo["inline_keyboard"][0][0]["text"] = "⏪ Назад в Чеки";
        send($chat_id, 'Реферальный процен сохранен!
'.$tomessage , $arInfo);
    }
}
function chequeSwitcher($col, $rowid){
    global $chat_id, $link;

    $str2select = "SELECT * FROM `cheques` WHERE `rowid`='$rowid'";
    $result2 = mysqli_query($link, $str2select);
    $row2 = @mysqli_fetch_object($result2);

    $ql = ($row2->$col == 0) ? 1 : 0;
    $str2upd = "UPDATE `cheques` SET `$col`='$ql' WHERE `rowid`='$rowid'";
    mysqli_query($link, $str2upd);

    chequeShowDetails($rowid, "Данные сохранены!");
}
function chequeSubscriptionsCheck($rowid){
    global $chat_id, $link;

    $str2select = "SELECT * FROM `cheques_subscr` WHERE `chequeid`='$rowid'";
    $result = mysqli_query($link, $str2select);
    if(mysqli_num_rows($result) > 4){
        $response = array(
            'chat_id' => $chat_id,
            'text' => "❌ОШИБКА! Допустимое количество подписок на 1 чек (5 каналов/групп) достигнуто.",
            'parse_mode' => 'HTML');
        sendit($response, 'sendMessage');
    }else{
        $r = 0;
        while($row = @mysqli_fetch_object($result)){
            $arInfo["inline_keyboard"][$r][0]["url"] = rawurldecode($row->targeturl);
            $arInfo["inline_keyboard"][$r][0]["text"] = $row->targetname;
            $arInfo["inline_keyboard"][$r][1]["callback_data"] = "chqDelGr|$row->rowid";
            $arInfo["inline_keyboard"][$r][1]["text"] = "Удалить";
            $r++;
        }  // end WHILE MySQL
        if(mysqli_num_rows($result) < 5) {
            $arInfo["inline_keyboard"][$r][0]["callback_data"] = "chqAddGr|$rowid";
            $arInfo["inline_keyboard"][$r][0]["text"] = "➕ Добавить канал/группу";
            $r++;
        }
        $arInfo["inline_keyboard"][$r][0]["callback_data"] = 43;
        $arInfo["inline_keyboard"][$r][0]["text"] = "⏪ Назад в Чеки";
        send($chat_id, 'Выбери действие:', $arInfo);
    }
}
function chequeAddChat($rowid){
    global $chat_id, $link;

    clean_temp_sess();
    save2temp("action", "chqsub|$rowid");

    $arInfo["inline_keyboard"][0][0]["callback_data"] = 43;
    $arInfo["inline_keyboard"][0][0]["text"] = "⏪ Назад в Чеки";
    send($chat_id, 'Чтобы привязать канал или группу к чеку, выполни следующее:
1) Добавь этого бота @LibermallBot администратором целевого канала/группы;
2) Запусти бота @username_to_id_bot и следуй его инструкциям чтобы получить chat id целевого канала/группы.

<b>Вышли в ответ на это сообщение chat id целевого канала/группы:</b>', $arInfo);
}
function chequeSaveChat($data, $row5){
    global $chat_id, $link;

    $targetid = intval(trim($data['message']['text']));

    $str5select = "SELECT `action` FROM `temp_sess` WHERE `chatid`='$chat_id' ORDER BY `rowid` DESC LIMIT 1";
    $result5 = mysqli_query($link, $str5select);
    $row5 = @mysqli_fetch_object($result5);
    $r = explode("|", $row5->action);
    $chequeid = $r[1];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,"https://api.telegram.org/bot".TOKEN."/getChat?chat_id=".$targetid);
    curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );
    #curl_setopt($ch, CURLOPT_HTTPHEADER, array('accept: application/json', 'X-API-Key: d67f08a50561a7aea12a8d54ff3bd1d0505989eaac7a54b1cf7fc68d25804771'));

    // receive server response ...
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $server_output = curl_exec ($ch);
    curl_close ($ch);
    $res = json_decode($server_output, true);

    if($res['ok'] == false){
        $response = array(
            'chat_id' => $chat_id,
            'text' => "❌ОШИБКА! @LibermallBot не добавлен администратором в канал/группу либо указан неверный chat id. Повтори попытку.",
            'parse_mode' => 'HTML');
        sendit($response, 'sendMessage');
    } else {
        $targetname = $res['result']['title'];
        $targetname2 = preg_replace('/[^\p{L}\p{N}\s]/u', '', $targetname);
        if(!empty($res['result']['username'])){
            $targeturl = "https://t.me/".$res['result']['username'];
        }
        elseif(!empty($res['result']['invite_link'])){
            $targeturl = $res['result']['invite_link'];
        }
        $str2ins = "INSERT INTO `cheques_subscr` (`chatid`,`chequeid`,`targetid`,`targetname`,`targeturl`) VALUES ('$chat_id','$chequeid','$targetid','$targetname2','$targeturl')";
        mysqli_query($link, $str2ins);

        $str2upd = "UPDATE `cheques` SET `subscr`='1' WHERE `rowid`='$chequeid'";
        mysqli_query($link, $str2upd);

        clean_temp_sess();
        chequeSubscriptionsCheck($chequeid);
    } // end IF res = OK
}
function chequeDelChat($groupRowid){
    global $chat_id, $link;

    $str2select = "SELECT * FROM `cheques_subscr` WHERE `rowid`='$groupRowid'";
    $result = mysqli_query($link, $str2select);
    $row = @mysqli_fetch_object($result);

    $str2del = "DELETE FROM `cheques_subscr` WHERE `rowid` = '$groupRowid'";
    mysqli_query($link, $str2del);

    $str3select = "SELECT * FROM `cheques_subscr` WHERE `chequeid`='".$row->chequeid."'";
    $result3 = mysqli_query($link, $str3select);
    if(mysqli_num_rows($result3) == 0){
        $str2upd = "UPDATE `cheques` SET `subscr`='0' WHERE `rowid`='".$row->chequeid."'";
        mysqli_query($link, $str2upd);
    }
    chequeSubscriptionsCheck($row->chequeid);
}
function chequeDelete($rowid){
    global $chat_id, $link, $chequefee;

    $str2select = "SELECT * FROM `cheques` WHERE `rowid`='$rowid'";
    $result = mysqli_query($link, $str2select);
    $row = @mysqli_fetch_object($result);

    $chequesum = ($row->sum + ($row->sum * $row->percent / 100)) * $row->activations;
    $chequefee = $chequesum * $chequefee;

    $str3select = "SELECT * FROM `users` WHERE `chatid`='$chat_id'";
    $result3 = mysqli_query($link, $str3select);
    $row3 = @mysqli_fetch_object($result3);

    if($row->asset == "TON"){
        $assetCol = "ton_ton_full";
        $newbalance = $row3->ton_ton_full + $chequesum + $chequefee;
    }
    elseif ($row->asset == "TGR"){
        $assetCol = "tgr_ton_full";
        $newbalance = $row3->tgr_ton_full + $chequesum + $chequefee;
    }

    $str2del = "DELETE FROM `cheques` WHERE `rowid` = '$rowid'";
    mysqli_query($link, $str2del);

    $str2upd = "UPDATE `users` SET `$assetCol`='$newbalance' WHERE `rowid`='".$row3->rowid."'";
    mysqli_query($link, $str2upd);

    createCheque();
}

############### CHECK REDEMPTION #################

function incomingChequeStart($ref){
    global $chat_id, $link;

    $p = substr($ref, 1);
    $r = explode("_", $p);
    $rowid = $r[0];
    $ucode = $r[1];
    $referral = $r[2];

    if(!empty($referral)){
        $tofile = '<?php $referral = "'.$referral.'";';
        file_put_contents('tmp/chqref'.$chat_id.'.php', $tofile);
    }

    $str2select = "SELECT * FROM `cheques` WHERE `rowid`='$rowid' AND `ucode`='$ucode'";
    $result = mysqli_query($link, $str2select);
    $row = @mysqli_fetch_object($result);

    $str4select = "SELECT * FROM `cheques_got` WHERE `chatid`='$chat_id' AND `chequeid`='$rowid'";
    $result4 = mysqli_query($link, $str4select);

    $str5select = "SELECT * FROM `cheque_temp` WHERE `chatid`='$chat_id' AND `chequeid`='$rowid'";
    $result5 = mysqli_query($link, $str5select);

    if(mysqli_num_rows($result) == 0){
        $response = array(
            'chat_id' => $chat_id,
            'text' => "❌ОШИБКА! Невалидный чек.",
            'parse_mode' => 'HTML');
        sendit($response, 'sendMessage');
    }
    elseif(mysqli_num_rows($result5) > 0){
        $response = array(
            'chat_id' => $chat_id,
            'text' => "❌ОШИБКА! Ты уже начал активацию данного чека",
            'parse_mode' => 'HTML');
        sendit($response, 'sendMessage');
    }
    elseif($row->activations < 1){
        $response = array(
            'chat_id' => $chat_id,
            'text' => "❌ОШИБКА! Активации для данного чека исчерпаны.",
            'parse_mode' => 'HTML');
        sendit($response, 'sendMessage');
    }
    elseif(mysqli_num_rows($result4) > 0){
        $arInfo["inline_keyboard"][0][0]["callback_data"] = 15;
        $arInfo["inline_keyboard"][0][0]["text"] = "⏪ Назад на главную";
        send($chat_id, "❌ОШИБКА! Ты уже активировал этот чек.", $arInfo);
    }else{
        // Block check activations
        $times = time();
        $str2ins = "INSERT INTO `cheque_temp` (`chatid`,`chequeid`,`times`) VALUES ('$chat_id','$rowid','$times')";
        mysqli_query($link, $str2ins);

        // save referral
        $str3select = "SELECT `ref` FROM `users` WHERE `chatid`='$chat_id'";
        $result3 = mysqli_query($link, $str3select);
        $row3 = @mysqli_fetch_object($result3);
        if($row3->ref == 0){
            $str2upd = "UPDATE `users` SET `ref`='".$row->chatid."' WHERE `chatid`='$chat_id'";
            mysqli_query($link, $str2upd);
        }
        incomingChequeProcess($rowid, 1);
    }
}
function incomingChequeProcess($rowid, $step){
    global $chat_id, $link, $fname, $lname, $uname;

    $str2select = "SELECT * FROM `cheques` WHERE `rowid`='$rowid'";
    $result = mysqli_query($link, $str2select);
    $row = @mysqli_fetch_object($result);

    if($step == 1) {
        if ($row->captcha == 1) {

            $rightAnswer = mt_rand(0, 3);
            $answers = array(0, 1, 2, 3);
            $emoji = array("➡️💄👛","🍷✅😁","🟡🍓🚫","💰👍⛔️");
            shuffle($answers);

            $response = array(
                'chat_id' => $chat_id,
                'caption' => '',
                'photo' => "https://tegro.exchange/TegroMoneybot/images/captcha".($rightAnswer+1).".jpg",
                'parse_mode' => 'HTML'
            );
            sendit($response, 'sendPhoto');

            $k = 0;
            $l = 0;
            for ($i = 0; $i < count($answers); $i++) {
                if($k > 1) $k = 0;
                if($i > 1) $l = 1;
                $ql = ($answers[$i] == $rightAnswer) ? 1 : 0;
                $arInfo["inline_keyboard"][$l][$k]["callback_data"] = "ichqcap1|$rowid|$ql";
                $arInfo["inline_keyboard"][$l][$k]["text"] = $emoji[$answers[$i]];
                $k++;
            } // end FOR
            send($chat_id, 'Выбери комбинацию с картинки:', $arInfo);
        }else{
            incomingChequeProcess($rowid, 2);
        }
    }
    elseif($step == 2) {
        if ($row->phoneverif == 1) {
            $row2 = getRowUsers();
            if($row2->phone == '' || $row2->phone == 0){
                clean_temp_sess();
                save2temp("action", "ichqphone|$rowid");

                $arInfo["keyboard"][0][0]["text"] = "✅ Подтвердить";
                $arInfo["keyboard"][0][0]["request_contact"] = TRUE;
                $arInfo["resize_keyboard"] = TRUE;
                send($chat_id, 'Для получения средств требуется одноразовое подтверждение номера телефона. Нажми на кнопку "Подтвердить" ниже...', $arInfo);
            }else{
                incomingChequeProcess($rowid, 3);
            }
        }else{
            incomingChequeProcess($rowid, 3);
        }
    }
    elseif($step == 3) {
        if ($row->subscr == 1) {
            $str3select = "SELECT * FROM `cheques_subscr` WHERE `chequeid`='$rowid'";
            $result3 = mysqli_query($link, $str3select);
            $i = 0;
            while($row3 = @mysqli_fetch_object($result3)){
                $ch = curl_init('https://api.telegram.org/bot' . TOKEN . '/getChatMember');
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, array('chat_id' => $row3->targetid, 'user_id' => $chat_id));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, false);
                $res = curl_exec($ch);
                curl_close($ch);
                $res = json_decode($res, true);
                if ($res['ok'] != true || $res['result']['status'] == "left"){
                    $arInfo["inline_keyboard"][$i][0]["url"] = rawurldecode($row3->targeturl);
                    $arInfo["inline_keyboard"][$i][0]["text"] = $row3->targetname;
                    $i++;
                }
            }
            if(count($arInfo["inline_keyboard"]) > 0){
                $arInfo["inline_keyboard"][$i][0]["callback_data"] = "ichqsubs|$rowid";
                $arInfo["inline_keyboard"][$i][0]["text"] = "✅ Я подписался, проверьте";
                send($chat_id, 'Для активации чека необходимо быть подписанным на эти каналы и группы:', $arInfo);
            }else{
                incomingChequeProcess($rowid, 4);
            }
        }else{
            incomingChequeProcess($rowid, 4);
        }
    }
    elseif($step == 4) {
        // Check for verified wallets (DO LATER)
        incomingChequeProcess($rowid, 5);
    }
    elseif($step == 5) {
        if(file_exists('tmp/chqref'.$chat_id.'.php')) include 'tmp/chqref'.$chat_id.'.php';

        //Pay referral fee
        if(!empty($referral) && $row->ref == 1){
            $reward = $row->sum * $row->percent / 100;
            if($reward != 0){
                $str4select = "SELECT * FROM `users` WHERE `chatid`='$referral'";
                $result4 = mysqli_query($link, $str4select);
                $row4 = @mysqli_fetch_object($result4);

                $rewardcol = strtolower($row->asset).'_ton_full';
                $newbalance =$row4->$rewardcol + $reward;

                $str2upd = "UPDATE `users` SET `$rewardcol`='$newbalance' WHERE `chatid`='$referral'";
                mysqli_query($link, $str2upd);
            }
        }

        //Subscribe to issuer
        $str6select = "SELECT * FROM `newsletters` WHERE `chatid`='$chat_id'";
        $result6 = mysqli_query($link, $str6select);
        if(mysqli_num_rows($result6) == 0){
            $str2ins = "INSERT INTO `newsletters` (`chatid`,`subscrto`) VALUES ('$chat_id','$row->chatid')";
            mysqli_query($link, $str2ins);
        }

        //Subtract activation
        $newactivs = $row->activations - 1;
        $str3upd = "UPDATE `cheques` SET `activations`='$newactivs' WHERE `rowid`='$rowid'";
        mysqli_query($link, $str3upd);

        // Remove cheque if 0 activations
        if($newactivs < 1){
            $str2del = "DELETE FROM `cheques` WHERE `rowid`='$rowid'";
            mysqli_query($link, $str2del);
        }

        // Notify issuer
        if($row->notify == 1){
            $namestr = "";
            if(!empty($fname)) $namestr .= "$fname";
            if(!empty($lname)) $namestr .= " $lname";
            if(!empty($uname)) $namestr = "<a href='https://t.me/$uname'>$namestr</a>";
            if(empty($namestr)) $namestr = "Пользователь";

            if($row->asset == "TON"){
                $suminUSD = $row->sum * getTONrate();
            }
            elseif($row->asset == "TGR"){
                $suminUSD = $row->sum * getTGRrate();
            }
            $response = array(
                'chat_id' => $row->chatid,
                'text' => "<b>$namestr</b> активировал(а) ваш чек на $row->sum $row->asset ($suminUSD\$)
Осталось: ".($row->activations-1)." / $row->total_activs",
                'parse_mode' => 'HTML');
            sendit($response, 'sendMessage');
        }

        // Add cheque funds
        $str5select = "SELECT * FROM `users` WHERE `chatid`='$chat_id'";
        $result5 = mysqli_query($link, $str5select);
        $row5 = @mysqli_fetch_object($result5);

        $assetcol = strtolower($row->asset).'_ton_full';
        $newbalance =$row5->$assetcol + $row->sum;
        $str4upd = "UPDATE `users` SET `$assetcol`='$newbalance' WHERE `chatid`='$chat_id'";
        mysqli_query($link, $str4upd);

        // Save activation
        $times = time();
        $str3ins = "INSERT INTO `cheques_got` (`chatid`,`chequeid`,`times`) VALUES ('$chat_id','$rowid','$times')";
        mysqli_query($link, $str3ins);

        // Clean temp
        $str2del = "DELETE FROM `cheque_temp` WHERE `chatid` = '$chat_id' AND `chequeid` = '$rowid'";
        mysqli_query($link, $str2del);

        // Generate child check
        $tomessage = "";
        if(($row->activations-1) > 0 && empty($referral) && $row->percent > 0) {
            $a = "https://t.me/LibermallBot?start=c".$row->rowid."_".$row->ucode."_".$chat_id;
            $yourfee = $row->sum * $row->percent / 100;
            $tomessage = "
        
Ты можешь заработать $yourfee $row->asset с каждой активации, распространяя данный чек.
Использую твою персональную ссылку на этот чек:
<code>$a</code>";
        }
        $arInfo["inline_keyboard"][0][0]["callback_data"] = 15;
        $arInfo["inline_keyboard"][0][0]["text"] = "⏪ Назад на главную";
        send($chat_id, "<b>Чек активирован!</b>
Твой баланс пополнен на $row->sum $row->asset ($suminUSD\$).".$tomessage, $arInfo);

        unlink('tmp/chqref'.$chat_id.'.php');
    }
}
function incomingChequeCaptcha($rowid,$right){
    global $chat_id, $link;

    if($right == 0){
        $arInfo["inline_keyboard"][0][0]["callback_data"] = "ichqcap2|$rowid";
        $arInfo["inline_keyboard"][0][0]["text"] = "Попробовать еще раз";
        send($chat_id, '❌ОШИБКА! Ответ неверный.', $arInfo);
    }else{
        incomingChequeProcess($rowid, 2);
    }
}
function incomingChequeSubscrCheck($rowid, $data){
    global $chat_id, $link;

    $str3select = "SELECT * FROM `cheques_subscr` WHERE `chequeid`='$rowid'";
    $result3 = mysqli_query($link, $str3select);
    $yes = 1;
    while($row3 = @mysqli_fetch_object($result3)){
        $ch = curl_init('https://api.telegram.org/bot' . TOKEN . '/getChatMember');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('chat_id' => $row3->targetid, 'user_id' => $chat_id));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $res = curl_exec($ch);
        curl_close($ch);
        $res = json_decode($res, true);
        if ($res['ok'] != true || $res['result']['status'] == "left"){
            $yes = 0;
        }
    }
    if($yes == 0){
        $ch = curl_init('https://api.telegram.org/bot' . TOKEN . '/answerCallbackQuery');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('callback_query_id' => $data['callback_query']['id'], 'text' => "Опс, ты не подписался на все сообщества. Подпишись и повтори проверку", 'show_alert' => 1, 'cache_time' => 0));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $res = curl_exec($ch);
        curl_close($ch);
    }else{
        delMessage("", $data['callback_query']['message']['message_id']);
        incomingChequeProcess($rowid, 4);
    }
}