# LibermallBot
<br/><br/>
1. Fill in the variables in botdata.php according to the comments
<br/><br/>
2. Enter the link to _0xpay_postback.php file in the postback field in the 0xpay control panel
<br/><br/>
3. Fill the link to tm_postback.php into the postback field in the tegro.money control panel.
<br/><br/>
4. Place the following files on the cronjob:<br/>
cronjob30s.php - to be executed once every 30 sec.<br/>
cronjob1h.php - to be executed 1 time per hour<br/>
cronjob24h.php - to be executed once a day.
<br/><br/>
5. All functions of each bot section are centered in separate files:<br/>
func_gen.php - general functions<br/>
func_wallet.php - Wallet<br/>
func_cheque.php - Cheques<br/>
func_staking.php - Staking<br/>
func_exchange.php - Swap<br/>
func_exchange2.php - Exchange
