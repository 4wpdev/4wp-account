# 4WP Account

User account hub for WordPress — OAuth sign-in, account page, cabinet menu, and header blocks.

## Features

- OAuth providers: Google (Gmail), GitHub, Facebook
- Account page with sidebar navigation (Dashboard, Favorites, Notifications, WooCommerce, …)
- Gutenberg blocks: Account, Account Menu, Account Link, Sign-in Buttons
- REST API: `forwp-account/v1`
- WooCommerce checkout sign-in integration

## Installation

1. Upload the plugin to `wp-content/plugins/4wp-account/`
2. Activate **4WP Account**
3. Go to **4WP Account → Auth** and configure OAuth credentials
4. Create an account page and add the **4WP Account** block
5. Add **4WP Account Menu** to your site header

## Shortcodes

```
[forwp_account]
[forwp_account_link]
[forwp_account_menu]
[forwp_account_login provider="gmail"]
[forwp_account_signin_buttons providers="gmail,github"]
```

## REST API

Base URL: `/wp-json/forwp-account/v1/`

| Route | Description |
|-------|-------------|
| `GET /auth/{provider}` | Get OAuth authorization URL |
| `GET /callback/{provider}` | OAuth callback handler |

See [API.md](API.md) for details.

## OAuth redirect URIs

Register these in your provider console:

- Google: `{site}/wp-json/forwp-account/v1/callback/gmail`
- GitHub: `{site}/wp-json/forwp-account/v1/callback/github`

## License

MIT / GPL-2.0-or-later
