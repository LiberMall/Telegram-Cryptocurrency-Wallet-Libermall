<?php
define('TOKEN', getenv('BOT_TOKEN'));
$xPayMerchantId = getenv('XPAY_MERCHANT_ID');
$xPayPrivateKey = getenv('XPAY_PRIVATE_KEY');
$tonapikey = getenv('TON_API_KEY');
$mainWallet = getenv('MAIN_WALLET');
$genseed = getenv('GEN_SEED');
$tgrbep20fee = 2; // USD
$tgrtonfee = 0.1; // TON
$tonfee = 0.1; // TON
$tegromoney_shopid = getenv('TEGROMONEY_SHOP_ID');
$tegromoney_secretkey = getenv('TEGROMONEY_SECRET_KEY');
$chequefee = 0.02;
$stakingfee[0][0] = 15;
$stakingfee[0][1] = 18;
$stakingfee[0][2] = 21;
$stakingfee[0][3] = 24;
$stakingfee[1][0] = 9;
$stakingfee[1][1] = 10;
$stakingfee[1][2] = 11;
$stakingfee[1][3] = 12;
$exchangefee = 0.005;
?>
