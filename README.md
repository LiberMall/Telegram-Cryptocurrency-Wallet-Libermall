<div align="center">

# Telegram Cryptocurrency Wallet — Libermall

**Reference implementation** of a Telegram bot that acts as a TON wallet, multi-cheque sender, fiat-bill acceptor, staking front-end and DEX gateway — all inside a chat. Open-sourced by Libermall in 2023.

[![License: MIT](https://img.shields.io/badge/License-MIT-FFD60A.svg?style=flat-square)](LICENSE)
[![Status](https://img.shields.io/badge/status-reference%20implementation-orange?style=flat-square)](#-security--production-readiness)
[![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![TON](https://img.shields.io/badge/Network-TON-0098EA?style=flat-square)](https://ton.org/)
[![Stars](https://img.shields.io/github/stars/LiberMall/Telegram-Cryptocurrency-Wallet-Libermall?style=flat-square)](https://github.com/LiberMall/Telegram-Cryptocurrency-Wallet-Libermall/stargazers)
[![Forks](https://img.shields.io/github/forks/LiberMall/Telegram-Cryptocurrency-Wallet-Libermall?style=flat-square)](https://github.com/LiberMall/Telegram-Cryptocurrency-Wallet-Libermall/network/members)

[**Libermall**](https://libermall.com) · [**Libermall ID**](https://id.libermall.com) · [**TNT NFT tool**](https://github.com/LiberMall/tnt) · [**FunC contracts**](https://github.com/LiberMall/Contracts)

</div>

---

> ## ⚠️ Security & production-readiness
>
> **This is a reference implementation, not a production-grade wallet.** It was open-sourced as a starting point for developers who want to learn how to wire a TON wallet into Telegram. Do **NOT** deploy as-is for real-money use without an independent security audit.
>
> **Disclosed leak (2026-05-25):** prior commits of `botdata.php` shipped real production credentials (xPay merchant key, TON API key, a 24-word wallet seed mnemonic, TegroMoney secret). All have been **rotated** and the funding wallet drained. Treat any credential found in this repo's history before 2026-05-25 as compromised. Because the leak propagated to several public forks, history rewrites on the original repo do not fully neutralise the exposure — credential rotation was the only effective remedy.
>
> **For real-money operations today**, use the actively-maintained surfaces:
> - [Libermall ID](https://id.libermall.com) — modern OAuth / OIDC identity layer
> - [PayLibermall](https://pay.libermall.com) — production payment platform
> - [Libermall Card](https://card.libermall.com) — virtual crypto-backed cards
> - [Libermall DEX](https://dex.libermall.com) — production TON DEX

---

## What this repository is

A single-tenant PHP codebase that turns a Telegram bot into a TON wallet with these features:

- 🪙 **Wallet** — receive and send TON, jettons, BEP-20 (USDT bridge)
- 🎟 **Multi-cheques** — send crypto to users via shareable Telegram cheques
- 💵 **Invoicing / bills** — accept payment in crypto, RUB, USD, EUR (via 0xpay + tegro.money)
- 📈 **Staking** — flexible and fixed-term, accrual cron-driven
- 🔄 **Exchange / swap** — internal swap rails between supported assets
- 🎁 **Discounts** — NFT-holder discount on bot fees, referral program

It's deliberately self-contained PHP — no frameworks, no service mesh, just files you drop on a LAMP host with a couple of crons.

## Architecture

```
Telegram Bot API ──────► tgbot.php  (webhook entry-point)
                              │
                              ├──► func_wallet.php   (TON ops)
                              ├──► func_cheque.php   (multi-cheques)
                              ├──► func_staking.php  (staking accrual)
                              ├──► func_exchange.php (swap)
                              ├──► func_gen.php      (shared helpers)
                              └──► MySQL              (state)

External integrations:
  • TON Center API (via $tonapikey)
  • 0xpay              (card / crypto acquiring)        ← _0xpay_postback.php
  • tegro.money        (RUB / fiat acquiring)            ← tm_postback.php
  • Local TonClient    (http://127.0.0.1:5881 — ton-http-api)

Periodic jobs (set up via cron):
  • cronjob30s.php  — every 30 seconds   (deposit polling)
  • cronjob1h.php   — every hour         (rates, batch settlements)
  • cronjob24h.php  — once a day         (staking accrual, cleanups)
```

## Repository layout

```
Telegram-Cryptocurrency-Wallet-Libermall/
├── README.md            ← you are here
├── LICENSE              ← MIT
├── SECURITY.md
├── CODE_OF_CONDUCT.md
├── tgbot.php            ← Telegram webhook handler
├── botdata.php          ← config placeholders (XXXXXXX); fill locally, do NOT commit real values
├── botdata_DEMO.php     ← reference template for botdata.php
├── env.php              ← optional .env loader (preferred over botdata.php)
├── global.php           ← global state
├── _0xpay_postback.php  ← 0xpay webhook receiver
├── tm_postback.php      ← tegro.money webhook receiver
├── cronjob30s.php       ← every 30s
├── cronjob1h.php        ← every hour
├── cronjob24h.php       ← every 24h
├── func_gen.php
├── func_wallet.php
├── func_cheque.php
├── func_staking.php
├── func_exchange.php
├── func_exchange2.php
├── func_lang.php
└── subscribers.json     ← runtime state (gitignored)
```

## Setup

### Prerequisites

- PHP 7.4+ with `mysqli`, `curl`, `openssl`, `bcmath`
- MySQL 5.7+
- A Telegram bot token from [@BotFather](https://t.me/BotFather)
- A TON wallet seed phrase (24 words)
- TON Center API key — get one at [toncenter.com](https://toncenter.com/)
- (Optional) 0xpay merchant account for card acquiring
- (Optional) tegro.money merchant account for RUB acquiring
- Local [`ton-http-api`](https://github.com/toncenter/ton-http-api) on `127.0.0.1:5881`

### Install

```bash
git clone git@github.com:LiberMall/Telegram-Cryptocurrency-Wallet-Libermall.git
cd Telegram-Cryptocurrency-Wallet-Libermall

# Copy the placeholder config and fill in YOUR values
cp botdata_DEMO.php botdata.php
$EDITOR botdata.php

# Recommended: use env vars instead of editing botdata.php
cp .env.example .env
$EDITOR .env
```

### Configure cron

```cron
* * * * *           php /var/www/Telegram-Cryptocurrency-Wallet-Libermall/cronjob30s.php
*/30 * * * *        php /var/www/Telegram-Cryptocurrency-Wallet-Libermall/cronjob30s.php
0 * * * *           php /var/www/Telegram-Cryptocurrency-Wallet-Libermall/cronjob1h.php
0 0 * * *           php /var/www/Telegram-Cryptocurrency-Wallet-Libermall/cronjob24h.php
```

### Configure webhooks

- 0xpay control panel → `Postback URL` → `https://your.domain/_0xpay_postback.php`
- tegro.money control panel → `Postback URL` → `https://your.domain/tm_postback.php`
- Telegram → `setWebhook` → `https://your.domain/tgbot.php`

## Known limitations

This codebase has been in production but ships with rough edges that any deployer **must** address:

- ❗ Mixes `mysqli` with string concatenation in places — open issue [#16](https://github.com/LiberMall/Telegram-Cryptocurrency-Wallet-Libermall/issues/16) — switch to prepared statements before going live.
- ❗ Hot-wallet model — the bot can sign withdrawals directly. Consider an air-gapped signer + per-tx review for any sizeable balance.
- ❗ No rate-limit / brute-force protection on withdrawal commands.
- ❗ Single-tenant — global `$link` / `$tonapikey` style means you can't run two bots from one codebase without a major refactor.
- ❗ `error_reporting(E_ALL & ~E_NOTICE)` — production deploys should surface warnings to a logger, not suppress them.

## Roadmap

This repository is in **security-maintenance only** mode. It receives:

- [x] Credential leak remediation (2026-05-25)
- [ ] SQL injection fixes (issue #16) — community PRs welcome
- [ ] Prepared-statement migration — community PRs welcome
- [ ] Documented `.env`-only configuration path

Active development of the Libermall ecosystem has moved to:

- **[Libermall ID](https://github.com/LiberMall/libermall-id-landing)** — modern identity layer (OIDC / OAuth 2.0 / SAML)
- **[@LibermallIDbot](https://github.com/LiberMall/libermall-id-bot)** — the production Telegram identity bot
- **[Libermall ID Mini App](https://github.com/LiberMall/libermall-id-miniapp)** — Tier-1 Telegram WebApp
- **[PayLibermall](https://pay.libermall.com)** — production payment platform

## Contributing

PRs welcome — especially security hardening. Read [`SECURITY.md`](SECURITY.md) before reporting vulnerabilities.

## License

[MIT](LICENSE) © 2026 Libermall.

The `Libermall` wordmark and the M-shield logo are trademarks of Libermall; see brand guidelines in [`LiberMall/libermall-id-landing`](https://github.com/LiberMall/libermall-id-landing/blob/main/BRANDBOOK.md).

---

<div align="center">

**Part of the [Libermall ecosystem](https://libermall.com).**

[Identity](https://id.libermall.com) · [DEX](https://dex.libermall.com) · [Pay](https://pay.libermall.com) · [Card](https://card.libermall.com) · [Reviews](https://sites.reviews) · [TonChat AI](https://tonchat.ai) · [TON.CEO](https://ton.ceo)

</div>
