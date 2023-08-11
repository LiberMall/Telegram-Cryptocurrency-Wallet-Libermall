<?php
include "global.php";
$link = mysqli_connect($hostName, $userName, $password, $databaseName) or die ("Error connect to database");
mysqli_set_charset($link, "utf8");

$response = file_get_contents('https://api.coingecko.com/api/v3/simple/price?ids=tegro&vs_currencies=usd');
$res = json_decode($response, true);
$tgrRate = $res['tegro']['usd'];

$response2 = file_get_contents('https://api.coingecko.com/api/v3/simple/price?ids=the-open-network&vs_currencies=usd');
$res2 = json_decode($response2, true);
$tonRate = $res2['the-open-network']['usd'];

$str2upd = "UPDATE `tgr_rate` SET `tgr_rate`='$tgrRate', `times`='".time()."' WHERE `rowid`='1'";
mysqli_query($link, $str2upd);

$str2upd = "UPDATE `ton_rate` SET `ton_rate`='$tonRate', `times`='".time()."' WHERE `rowid`='1'";
mysqli_query($link, $str2upd);
?>
