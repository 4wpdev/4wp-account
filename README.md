# 4WP Account

[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg?style=flat-square)](https://www.gnu.org/licenses/gpl-2.0.html)
[![WordPress 6.4+](https://img.shields.io/badge/WordPress-6.4%2B-blue.svg?style=flat-square)](https://wordpress.org/)
[![PHP 8.0+](https://img.shields.io/badge/PHP-8.0%2B-777bb4.svg?style=flat-square)](https://www.php.net/)

**Membership user account hub** for WordPress: **user registration**, a front-end **user profile** cabinet, email/password sign-in, and **social login** (Google, GitHub, TikTok). Native **Gutenberg** blocks — each with a matching **shortcode**.

A plugin by **[4WP](https://4wp.dev/)** · Contributors: **4wpdev**, **anatolikkk** · Source: **[github.com/4wpdev/4wp-account](https://github.com/4wpdev/4wp-account)** · Directory: [`readme.txt`](readme.txt)

Requires WordPress 6.4+ · Tested up to **7.1** · PHP 8.0+ · **Stable: 1.1.0**

## Features

- **Membership / user account** — one page for guests (sign-in) and members (cabinet)
- **User registration** — follows **Settings → General → Anyone can register**
- **User profile** — configurable account menu (dashboard, WooCommerce, extras)
- **Social login** — Google, GitHub, TikTok (live); Facebook and X (coming soon)
- **Email/password** — first-class provider, no WooCommerce required
- **Gutenberg blocks + shortcodes** — every block has a shortcode equivalent
- **Appearance** — guest card background, border, radius; native / solid / icon social buttons
- **wp-login.php** — optional replace, only if the account page already hosts a login form
- REST API: `forwp-account/v1`
- Optional WooCommerce social buttons on My Account forms

## Installation

1. Upload the plugin to `wp-content/plugins/4wp-account/` and activate **4WP Account**.
2. Open **4WP Account → Auth / Register**. Enable email/password and any social networks; paste OAuth credentials.
3. Copy each **Redirect URI** into the Google, GitHub, or TikTok app.
4. Create a page, add the **Account** block (`forwp/account`) or `[forwp_account]`.
5. Select that page under **Settings → Pages**. Optionally replace `wp-login.php` once the form is on the page.
6. Add **Account Menu** to the header. Style the guest card under **Appearance**.

## Gutenberg blocks and shortcodes

| Use case | Block | Shortcode |
|----------|--------|-----------|
| Account page (sign-in or cabinet) | `forwp/account` | `[forwp_account]` |
| Header icon + account dropdown | `forwp/account-menu` | `[forwp_account_menu]` |
| Header/menu account link | `forwp/account-link` | `[forwp_account_link]` |
| Social sign-in buttons | `forwp/auth-buttons` | `[forwp_account_signin_buttons providers="gmail,github"]` |
| Single social button | — | `[forwp_account_login provider="gmail"]` |

## REST API

Base URL: `/wp-json/forwp-account/v1/`

| Route | Description |
|-------|-------------|
| `GET /auth/{provider}` | Get OAuth authorization URL |
| `GET /callback/{provider}` | OAuth callback handler |

See [API.md](API.md) for details.

## OAuth redirect URIs

Register these in your provider console (copy the exact value from **Auth / Register**):

- Google: `{site}/wp-json/forwp-account/v1/callback/gmail`
- GitHub: `{site}/wp-json/forwp-account/v1/callback/github`
- TikTok: `{site}/wp-json/forwp-account/v1/callback/tiktok`

## FAQ

### Is this a membership plugin for a user account?

Yes. It is a **membership** layer: one front-end **user account** for sign-in, **user registration**, and a logged-in **user profile** cabinet. It does not sell plans or paywalls.

### Does it support user registration?

**User registration** follows WordPress **Settings → General → Anyone can register**. Social networks can also create a subscriber on first login.

### Where is the user profile?

The **user profile** cabinet is the account page after login. Set the page in **Settings → Pages**; menu sections in **Settings → Account Menu**.

### Can members sign in with social networks?

Yes. Google, GitHub, and TikTok are live when enabled. Facebook and X are coming soon. Buttons can use native brand colors and icons.

### Are there Gutenberg blocks and shortcodes?

Yes. Every block listed above has a shortcode. See **Settings → Blocks / Shortcodes**.

### Can I hide wp-login.php?

Only when a published account page already contains `forwp/account`, `forwp/auth-buttons`, `[forwp_account]`, `[forwp_account_signin_buttons]`, or `[woocommerce_my_account]`. Logout and password-reset URLs on `wp-login.php` stay intact.

### Do I need WooCommerce?

No. Membership, user account, registration, social login, and blocks work without it.

## Changelog

### 1.1.0

- Stable membership release: user account, user registration, user profile cabinet
- Settings → Pages: replace `wp-login.php` only when the page already has a login block or shortcode
- Behavior (toolbar, subscriber redirect) moved next to the account page
- Gutenberg blocks and shortcodes documented under Settings → Blocks / Shortcodes
- Tested up to WordPress 7.1

### 1.0.7

- Appearance tab: guest card tokens, live preview, two-field layout
- Social buttons: native / solid / icons
- Auth / Register method cards; email/password as a first-class provider
- TikTok live; Facebook and X coming soon
- Forms tab removed

## License

GPL-2.0-or-later
