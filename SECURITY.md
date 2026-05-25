# Security Policy

## Disclosed incident — 2026-05-25

**Prior commits of `botdata.php` shipped real production credentials**, including a 24-word wallet seed mnemonic, xPay merchant/private keys, the TON Center API key and the TegroMoney shop secret. They were committed on 2023-08-12 and remained in this **public** repository — plus 5 community forks — for ~21 months before disclosure.

All listed credentials have been **rotated** and the funding wallet (`EQADeT9xqZ4wAam-bCXbTjNhUTN03K33EYQmnL5Q8udk5E2T`) drained. Treat any credential or seed phrase found in this repository's git history before 2026-05-25 as compromised.

Because the leak propagated to public forks, history rewriting on the upstream alone does not fully neutralise the exposure — **rotation was the only effective remedy**. Future contributors must never commit live values to `botdata.php`; use environment variables via `env.php` instead.

## Reporting a vulnerability

If you discover a security issue in this repository **do not open a public GitHub issue**. Use one of these private channels instead:

| Channel | Use it for |
|---|---|
| **Email**: [`security@libermall.com`](mailto:security@libermall.com) | Most reports. PGP key on request. |
| **GitHub Security Advisory** | Private coordinated disclosure via the *Security* tab |
| **Telegram** to [@LibermallIDbot](https://t.me/LibermallIDbot) → `/security` | Quick disclosure with screenshots |

We acknowledge reports within **48 hours**, triage within **5 business days**, and aim to ship a fix within **30 days** for high-severity issues.

## Scope

In scope:

- This source code repository
- The historical credential leak documented above

Out of scope:

- The currently-active Libermall surfaces — [`id.libermall.com`](https://id.libermall.com), [`pay.libermall.com`](https://pay.libermall.com), [`dex.libermall.com`](https://dex.libermall.com), [`card.libermall.com`](https://card.libermall.com). Each has its own SECURITY.md.
- Forks of this repository under other accounts — please report to fork owners directly
- DoS / volumetric attacks

## Safe-harbor

We won't pursue legal action against researchers who:

1. Make good-faith effort to avoid privacy violations and service degradation.
2. Don't exfiltrate data beyond what's needed to prove the issue.
3. Give us reasonable time to remediate before public disclosure (typically 90 days).
4. Don't exploit the issue for personal gain.

## Hall of fame

Researchers who report valid vulnerabilities will be credited (with consent) in [`CHANGELOG.md`](CHANGELOG.md) and on [`id.libermall.com/security.html`](https://id.libermall.com/security.html).

## Supported versions

This repository is in **security-maintenance only** mode (see [`README.md`](README.md#roadmap)). Security fixes land on `main`. There are no release tags.
