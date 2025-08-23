<?php
require_once __DIR__ . '/env.php';

$xPayMerchantId = getenv('XPAY_MERCHANT_ID');
$xPayPrivateKey = getenv('XPAY_PRIVATE_KEY');
$tonapikey = getenv('TON_API_KEY');
$mainWallet = getenv('MAIN_WALLET');
$genseed = getenv('GEN_SEED');

$tgrbep20fee = getenv('TGR_BEP20_FEE');
$tgrtonfee = getenv('TGR_TON_FEE');
$tonfee = getenv('TON_FEE');

$tegromoney_shopid = getenv('TEGROMONEY_SHOPID');
$tegromoney_secretkey = getenv('TEGROMONEY_SECRETKEY');

$chequefee = getenv('CHEQUE_FEE');

$stakingfee[0][0] = getenv('STAKING_FEE_0_0');
$stakingfee[0][1] = getenv('STAKING_FEE_0_1');
$stakingfee[0][2] = getenv('STAKING_FEE_0_2');
$stakingfee[0][3] = getenv('STAKING_FEE_0_3');
$stakingfee[1][0] = getenv('STAKING_FEE_1_0');
$stakingfee[1][1] = getenv('STAKING_FEE_1_1');
$stakingfee[1][2] = getenv('STAKING_FEE_1_2');
$stakingfee[1][3] = getenv('STAKING_FEE_1_3');

$exchangefee = getenv('EXCHANGE_FEE');
?>

