=== 4WP Account ===
Contributors: 4wpdev
Tags: social login, oauth, gmail, facebook, instagram
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 8.0
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Social login for WordPress — Gmail, Facebook, Instagram, and TikTok OAuth with shortcodes and optional WooCommerce integration.

== Description ==

**4WP Account** lets visitors sign in or register with social accounts via OAuth 2.0. Credentials stay in WordPress options; the plugin creates or matches users by email and logs them in securely.

A plugin by [4wp.dev](https://4wp.dev/). **4WP** is our project brand; the letters "WP" appear only as part of that brand name, not as a reference to WordPress. This plugin is not affiliated with, endorsed, or sponsored by WordPress.

Source code: [github.com/4wpdev/4wp-account](https://github.com/4wpdev/4wp-account)

= Key features =

* **Gmail, Facebook, Instagram** OAuth login (TikTok settings UI; provider roadmap)
* **Shortcodes** — `[forwp_auth_login provider="gmail"]` and `[forwp_auth_buttons]`
* **REST API** — `/wp-json/forwp-auth/v1/auth/{provider}` and OAuth callbacks
* **WooCommerce** — optional buttons on My Account login/register forms
* **Subscriber options** — hide admin bar, redirect from wp-admin

= Privacy =

OAuth tokens are exchanged server-side. User email and profile fields from the provider are stored in WordPress user records. No data is sent to 4wp.dev.

= Development =

Run tests: `composer install && composer run lint && composer run test`

== External services ==

This plugin connects to third-party OAuth providers when a visitor clicks a social login button and when an administrator saves API credentials.

= Google (Gmail) =

* **When:** User initiates Gmail login; server exchanges authorization code for tokens and reads profile email.
* **Terms:** [Google API Terms of Service](https://developers.google.com/terms)
* **Privacy:** [Google Privacy Policy](https://policies.google.com/privacy)

= Meta (Facebook / Instagram) =

* **When:** User initiates Facebook or Instagram login; server exchanges code and reads basic profile data.
* **Terms:** [Meta Platform Terms](https://developers.facebook.com/terms/)
* **Privacy:** [Meta Privacy Policy](https://www.facebook.com/privacy/policy/)

= TikTok =

* **When:** Planned — settings UI only until provider is enabled in a future release.
* **Terms:** [TikTok Developer Terms](https://developers.tiktok.com/doc/terms-and-conditions)
* **Privacy:** [TikTok Privacy Policy](https://www.tiktok.com/legal/privacy-policy)

== Installation ==

1. Upload the plugin to `/wp-content/plugins/4wp-account/` or install from the Plugins screen.
2. Activate **4WP Account**.
3. Open **4WP Account** in wp-admin → enable providers and paste OAuth credentials.
4. Add redirect URIs shown in settings to each provider console.
5. Use shortcode `[forwp_auth_login provider="gmail"]` on a page.

== Frequently Asked Questions ==

= Which providers work in 1.0.2? =

Gmail, Facebook, and Instagram are implemented. TikTok credentials can be saved; login flow is not enabled yet.

= Where is the OAuth callback URL? =

In **4WP Account → Social Networks** — copy the Redirect URI for each provider into Google Cloud or Meta Developers.

== Screenshots ==

1. Admin — social network credentials and redirect URIs.
2. Front-end — social login buttons via shortcode.
3. WooCommerce My Account — optional social login buttons.

== Changelog ==

= 1.0.2 =
* WordPress.org packaging: readme, GPL license, text domain `4wp-account`, quality toolchain.
* Provider enable toggle respected before login.
* Admin menu slug `forwp-account`.

= 1.0.1 =
* Gmail and Facebook OAuth, shortcodes, WooCommerce integration.

== Upgrade Notice ==

= 1.0.2 =
WordPress.org release prep — update main plugin file to `4wp-account.php` if upgrading from a dev copy named `4wp-auth.php`.
