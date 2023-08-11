# LibermallBot

1. Fill in the variables in botdata.php according to the comments

2. Enter the link to _0xpay_postback.php file in the postback field in the 0xpay control panel

3. Fill the link to tm_postback.php into the postback field in the tegro.money control panel.

4. Place the following files on the cronjob:
cronjob30s.php - to be executed once every 30 sec.
cronjob1h.php - to be executed 1 time per hour
cronjob24h.php - to be executed once a day.

5. All functions of each bot section are centered in separate files:
func_gen.php - general functions
func_wallet.php - Wallet
func_cheque.php - Cheques
func_staking.php - Staking
func_exchange.php - Swap
func_exchange2.php - Exchange
