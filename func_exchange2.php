<?php
function exchange2Start(){
    global $chat_id, $link;

    clean_temp_sess();
    $arInfo["inline_keyboard"][0][0]["callback_data"] = 70;
    $arInfo["inline_keyboard"][0][0]["text"] = "Начать обмен";
    $arInfo["inline_keyboard"][0][1]["callback_data"] = 71;
    $arInfo["inline_keyboard"][0][1]["text"] = "История обменов";
    $arInfo["inline_keyboard"][1][0]["callback_data"] = 15;
    $arInfo["inline_keyboard"][1][0]["text"] = "⏪ Назад";
    send($chat_id, "В этом разделе ты можешь обменять один из своих крипто-активов на другой в режиме P2P, взаимодействуя с другими участниками биржи.
<i>Комиссия за обмен: 0.5%</i>", $arInfo);
}
function exchange2pairsList(){
    global $chat_id, $link;

    $arInfo["inline_keyboard"][0][0]["callback_data"] = "EXCH|TON|TGR";
    $arInfo["inline_keyboard"][0][0]["text"] = "TON -> TGR";
    $arInfo["inline_keyboard"][1][0]["callback_data"] = "EXCH|TGR|TON";
    $arInfo["inline_keyboard"][1][0]["text"] = "TGR -> TON";
    $arInfo["inline_keyboard"][2][0]["callback_data"] = 4;
    $arInfo["inline_keyboard"][2][0]["text"] = "⏪ Назад в Биржу";
    send($chat_id, "Выбери направление обмена:", $arInfo);
}
function exchange2listOffers($coinfrom,$cointo){
    global $chat_id, $link;

    $i = 0;
    $str2select = "SELECT * FROM `exchanges` WHERE `coinfrom`='$cointo' AND (`cointo`='$coinfrom' AND `status`='0')";
    $result = mysqli_query($link, $str2select);
    if(mysqli_num_rows($result) == 0){
        $tomess = "Нет заявок в указанном направлении. Ты можешь создать новую заявку:";
    }else{
        $tomess = "Выбери заявку в указанном направлении ниже, или создай новую заявку:";
        while($row = @mysqli_fetch_object($result)){
            $rate = $row->sumget / $row->sumgive;
            $arInfo["inline_keyboard"][$i][0]["callback_data"] = "EXCL|".$row->rowid;
            $arInfo["inline_keyboard"][$i][0]["text"] = "$row->sumget $coinfrom -> $row->sumgive $cointo ($rate)";
            $i++;
        }  // end WHILE MySQL
    }
    $arInfo["inline_keyboard"][$i][0]["callback_data"] = "EXCH|$coinfrom|$cointo";
    $arInfo["inline_keyboard"][$i][0]["text"] = "🔄 Обновить";
    $i++;
    $arInfo["inline_keyboard"][$i][0]["callback_data"] = "EXCN|$coinfrom|$cointo";
    $arInfo["inline_keyboard"][$i][0]["text"] = "❇️ Новая заявка";
    $i++;
    $arInfo["inline_keyboard"][$i][0]["callback_data"] = "EXCI|$coinfrom|$cointo";
    $arInfo["inline_keyboard"][$i][0]["text"] = "🗄 История";
    $i++;
    $arInfo["inline_keyboard"][$i][0]["callback_data"] = 70;
    $arInfo["inline_keyboard"][$i][0]["text"] = "⏪ Назад в Биржу";
    send($chat_id, $tomess, $arInfo);
}
function exchange2NewBid($coinfrom,$cointo){
    global $chat_id, $link;

    $arInfo["inline_keyboard"][0][0]["callback_data"] = "EXCB|$coinfrom|$cointo";
    $arInfo["inline_keyboard"][0][0]["text"] = "🟢 Купить $coinfrom";
    $arInfo["inline_keyboard"][0][1]["callback_data"] = "EXCS|$coinfrom|$cointo";
    $arInfo["inline_keyboard"][0][1]["text"] = "🔴 Продать $coinfrom";
    $arInfo["inline_keyboard"][1][0]["callback_data"] = 70;
    $arInfo["inline_keyboard"][1][0]["text"] = "⏪ Назад в Биржу";
    send($chat_id, "Выбери направление для обмена $coinfrom на $cointo.", $arInfo);
}
function exchange2NewBidBuy($coinfrom,$cointo){
    global $chat_id, $link;

    clean_temp_sess();
    save2temp("action", "ex2sum|$coinfrom|$cointo");

    $i = 0;
    $str2select = "SELECT * FROM `exchanges` WHERE `coinfrom`='$coinfrom' AND (`cointo`='$cointo') SORT BY `rowid` DESC LIMIT 1";
    $result = mysqli_query($link, $str2select);
    if(mysqli_num_rows($result) == 0){
        $tomess = "";
    }else{
        $row = @mysqli_fetch_object($result);
        $rate = $row->sumget / $row->sumgive;
        $arInfo["inline_keyboard"][$i][0]["callback_data"] = "EXCP|$coinfrom|$cointo|$rate";
        $arInfo["inline_keyboard"][$i][0]["text"] = "$rate $coinfrom";
        $i++;
        $tomess = "
Последняя цена за $cointo: <b>$rate $coinfrom</b>";
    }
    $arInfo["inline_keyboard"][$i][0]["callback_data"] = 70;
    $arInfo["inline_keyboard"][$i][0]["text"] = "⏪ Назад в Биржу";
    send($chat_id, "Пришли цену в $coinfrom для покупки $cointo.".$tomess, $arInfo);

}
function exchange2NewBidBuy2($coinfrom,$cointo,$sum2buy){
    global $chat_id, $link;

}
function exchange2NewBidSell($coinfrom,$cointo){
    global $chat_id, $link;

}