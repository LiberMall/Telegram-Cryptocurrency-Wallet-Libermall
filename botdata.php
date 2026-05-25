<?php
// ────────────────────────────────────────────────────────────────────────────
// SECURITY NOTICE
//
// This file previously contained real production credentials (xPay merchant
// key, TON API key, a 24-word wallet seed mnemonic, TegroMoney secret).
// They were committed to a PUBLIC GitHub repository from 2023-08-12 onward
// and propagated to forks before the leak was discovered on 2026-05-25.
//
// All of the following have been ROTATED and the original wallet drained:
//   • xPay merchant + private key
//   • TON API key (toncenter.com)
//   • TegroMoney shop id + secret key
//   • Hot wallet seed mnemonic (the funding address has been emptied)
//
// Treat ANY commit of this file before 2026-05-25 as compromised.
// Do NOT use any value from the historical version anywhere.
//
// ────────────────────────────────────────────────────────────────────────────
//
// Setup:
//   1. Copy botdata_DEMO.php → botdata.php (this file)
//   2. Fill in YOUR credentials below.
//   3. Confirm `botdata.php` is in .gitignore. NEVER commit real values.
//
// Better: load these from environment variables. See `env.php`.
// ────────────────────────────────────────────────────────────────────────────

$xPayMerchantId       = getenv('XPAY_MERCHANT_ID')      ?: 'XXXXXXX';   // 0xPay merchant id
$xPayPrivateKey       = getenv('XPAY_PRIVATE_KEY')      ?: 'XXXXXXX';   // 0xPay private key
$tonapikey            = getenv('TON_API_KEY')           ?: 'XXXXXXX';   // toncenter.com API key
$mainWallet           = getenv('MAIN_WALLET')           ?: 'XXXXXXX';   // Hot wallet address (public)
$genseed              = getenv('WALLET_SEED')           ?: 'XXXXXXX';   // 24-word mnemonic — NEVER COMMIT
$tegromoney_shopid    = getenv('TEGROMONEY_SHOP_ID')    ?: 'XXXXXXX';   // tegro.money shop id
$tegromoney_secretkey = getenv('TEGROMONEY_SECRET_KEY') ?: 'XXXXXXX';   // tegro.money secret

$tgrbep20fee = 2;     // USD
$tgrtonfee   = 0.1;   // TON
$tonfee      = 0.1;   // TON
$chequefee   = 0.02;
$exchangefee = 0.005;

$stakingfee[0][0] = 15;
$stakingfee[0][1] = 18;
$stakingfee[0][2] = 21;
$stakingfee[0][3] = 24;
$stakingfee[1][0] = 9;
$stakingfee[1][1] = 10;
$stakingfee[1][2] = 11;
$stakingfee[1][3] = 12;
