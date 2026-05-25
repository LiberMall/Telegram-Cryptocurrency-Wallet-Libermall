# Changelog

All notable changes to this repository are documented here.
Format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [2026-05-25] — Security maintenance release

### Security
- **Disclosed credential leak.** Prior commits of `botdata.php` shipped real production credentials including a 24-word wallet seed mnemonic, xPay merchant/private keys, the TON Center API key, and the TegroMoney shop secret. All have been rotated and the funding wallet drained. See [`SECURITY.md`](SECURITY.md) for the full disclosure. **Any credential found in this repository's git history before this commit must be considered compromised.**

### Changed
- `botdata.php` now contains only `XXXXXXX` placeholders + `getenv()` fallbacks. The file is intentionally tracked so the file exists out of the box; replace values locally without committing them.
- Added comprehensive `.gitignore` with explicit entries for `.env`, `*.key`, `*.pem`, `*.jks`, `*.keystore`, and runtime state files.
- README rewritten in Tier-1 format with prominent security warning callout, architecture diagram, setup instructions and explicit "reference implementation, not production-grade" framing.
- `SECURITY.md` now documents the disclosed leak, lists private disclosure channels (security@libermall.com, GitHub Security Advisory, @LibermallIDbot `/security`), defines scope and safe-harbor.

### Roadmap
- This repository is now in **security-maintenance only** mode. Active development of the Libermall ecosystem has moved to [Libermall ID](https://id.libermall.com), [PayLibermall](https://pay.libermall.com), [Libermall Card](https://card.libermall.com), and [Libermall DEX](https://dex.libermall.com).

## [2023-08-11] — Initial open-source release

- Initial commit. Telegram bot offering TON wallet, multi-cheques, fiat invoicing (0xpay + tegro.money), staking, internal swap, NFT discounts.
- Modular PHP layout: `tgbot.php` entry-point, separate `func_*.php` modules per feature area.
- Cron-driven deposit polling (`cronjob30s.php`), hourly rate refresh (`cronjob1h.php`), daily staking accrual (`cronjob24h.php`).
