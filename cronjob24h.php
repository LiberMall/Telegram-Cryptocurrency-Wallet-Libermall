<?php
set_time_limit(0);

include 'botdata.php';

include "global.php";
$link = mysqli_connect($hostName, $userName, $password, $databaseName) or die ("Error connect to database");
mysqli_set_charset($link, "utf8");

include 'botdata.php'; // keys etc.

$curtime = time();
$r = 0;
$str2select = "SELECT * FROM `staking` WHERE `endtime` < '$curtime'";
$result = mysqli_query($link, $str2select);
while($row = @mysqli_fetch_object($result)){
    if($r == 4){
        sleep(1);
        $r = 0;
    }

    $str3select = "SELECT * FROM `users` WHERE `chatid`='".$row->chatid."'";
    $result3 = mysqli_query($link, $str3select);
    $row3 = @mysqli_fetch_object($result3);

    $full_cell = strtolower($row->asset)."_ton_full";
    $newtotal = $row3->$full_cell + $row->endsum;

    $str2upd = "UPDATE `users` SET `$full_cell`='$newtotal' WHERE `chatid`='".$row->chatid."'";
    mysqli_query($link, $str2upd);

    $depodate = date("j/m/Y", $row->starttime);
    $response = array(
        'chat_id' => $row->chatid,
        'text' => "Срок твоего депозита от $depodate в сумме $row->sum $row->asset истек.
На твой базанс зачислено: <b>$row->endsum $row->asset</b>",
        'parse_mode' => 'HTML');
    sendit($response, 'sendMessage');

    $str2del = "DELETE FROM `staking` WHERE `rowid` = '".$row->rowid."'";
    mysqli_query($link, $str2del);

    saveTransaction($row->endsum, $row->asset, "TON", "r_staking", 0);

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
