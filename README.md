## TON Multifunctional Telegram Bot

TON Multifunctional Bot is an indispensable assistant for working with TON. From a convenient crypto wallet to direct integrations with NFT Marketplace and DEX, this bot is designed to bring the power of blockchain and decentralized finance directly into your messenger.

### **Features**:
1. **Wallet**: Securely store and receive the TON cryptocurrency and tokens on its blockchain.
2. **NFT Marketplace Integration**: Engage with your NFTs on the marketplace directly from the messenger.
3. **Multichecks**: Send cryptocurrency to users via virtual cheques.
4. **Bills**: Accept payment in cryptocurrency, rubles, dollars, or euros with just one click in the Telegram bot.
5. **Staking**: Deposit your cryptocurrency for staking and earn profit percentages, including on tokens on the TON blockchain.
6. **DEX Integration**: Trade on a decentralized exchange without leaving the Telegram bot.
7. **Discount System**: Avail a 10% discount on bot commission when owning 1 NFT from partner collections. Also, boost your staking percentage and referral program rate.

### **Setup and Launch**:

1. Fill in the variables in `botdata.php` as per the comments provided in the file.
2. Add the link to `_0xpay_postback.php` in the postback field of the 0xpay control panel.
3. Insert the link to `tm_postback.php` in the postback field of the tegro.money control panel.
4. Set up the cronjobs as follows:
   - `cronjob30s.php`: Execute every 30 seconds.
   - `cronjob1h.php`: Execute once every hour.
   - `cronjob24h.php`: Execute once daily.

### **Bot Section Files**:

For better manageability and modularity, functions for each section of the bot are centered in separate files:

- `func_gen.php`: General functions.
- `func_wallet.php`: Functions related to Wallet.
- `func_cheque.php`: Functions related to Cheques.
- `func_staking.php`: Functions related to Staking.
- `func_exchange.php`: Functions for Swapping.
- `func_exchange2.php`: Functions for Exchange.

### **Contribution**:

Contributions, feedback, and issues are welcome. Feel free to open an issue or submit a pull request!

---

**Note**: Always remember to stay safe and never share your private keys or sensitive information with the bot or any unverified platforms.
