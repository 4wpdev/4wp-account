=== 4WP Account ===
Contributors: 4wpdev
Tags: social login, oauth, google, github, account
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 8.0
Stable tag: 1.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Social login with Google and GitHub, account page and blocks. Facebook and TikTok are planned for a future release.

== Description ==

**4WP Account** is a user account hub for WordPress: social sign-in (OAuth 2.0), a front-end account page, header account menu block, and optional WooCommerce login buttons.

**Active in this release:** Google and GitHub login when enabled in **4WP Account → Auth**. Facebook and TikTok appear in settings as *coming soon* — they are not available for login yet.

A plugin by [4wp.dev](https://4wp.dev/). **4WP** is our project brand; the letters "WP" appear only as part of that brand name, not as a reference to WordPress. This plugin is not affiliated with, endorsed by, or sponsored by WordPress.

Source code: [github.com/4wpdev/4wp-account](https://github.com/4wpdev/4wp-account)

= Key features =

* **Google and GitHub** OAuth sign-in (enable per provider in wp-admin)
* **Account page** — `[forwp_account]` shortcode or `forwp/account` block (sign-in when logged out; cabinet when logged in)
* **Account menu** — `forwp/account-menu` block or `[forwp_account_menu]` for header dropdown
* **Sign-in buttons** — `[forwp_account_signin_buttons]` or `forwp/auth-buttons` block
* **REST API** — `/wp-json/forwp-account/v1/auth/{provider}` and OAuth callbacks
* **WooCommerce** — optional social buttons on My Account login/register forms
* **Subscriber options** — hide admin bar, redirect subscribers away from wp-admin

= Privacy =

OAuth tokens are exchanged server-side. Profile email and name from the provider are stored in WordPress user records. No data is sent to 4wp.dev.

= Development =

Run tests: `composer install && composer run lint && composer run test`

== External services ==

This plugin connects to third-party OAuth providers when a visitor starts social login and when an administrator saves API credentials.

= Google =

* **When:** User clicks Google sign-in; server exchanges the authorization code and reads profile email.
* **Terms:** [Google API Terms of Service](https://developers.google.com/terms)
* **Privacy:** [Google Privacy Policy](https://policies.google.com/privacy)

= GitHub =

* **When:** User clicks GitHub sign-in; server exchanges the code and reads the primary verified email.
* **Terms:** [GitHub Terms of Service](https://docs.github.com/en/site-policy/github-terms/github-terms-of-service)
* **Privacy:** [GitHub Privacy Statement](https://docs.github.com/en/site-policy/privacy-policies/github-privacy-statement)

= Meta (Facebook) — planned =

* **When:** Not enabled in this release. Listed in admin as *coming soon*.
* **Terms:** [Meta Platform Terms](https://developers.facebook.com/terms/)
* **Privacy:** [Meta Privacy Policy](https://www.facebook.com/privacy/policy/)

= TikTok — planned =

* **When:** Not enabled in this release. Listed in admin as *coming soon*.
* **Terms:** [TikTok Terms of Service](https://www.tiktok.com/legal/terms-of-service)
* **Privacy:** [TikTok Privacy Policy](https://www.tiktok.com/legal/privacy-policy)

== Installation ==

1. Upload the plugin to `/wp-content/plugins/4wp-account/` or install from the Plugins screen.
2. Activate **4WP Account**.
3. Open **4WP Account → Auth** — enable Google and/or GitHub and paste OAuth credentials.
4. Copy each **Redirect URI** from settings into Google Cloud or GitHub OAuth app settings.
5. Create a page with `[forwp_account]` or add the **Account** block.

== Frequently Asked Questions ==

= Which providers work in 1.0.3? =

**Google** and **GitHub** when enabled and configured. **Facebook** and **TikTok** are shown as *coming soon* in admin and cannot be used for login.

= Where is the OAuth callback URL? =

In **4WP Account → Auth** — use the Redirect URI shown for each provider (built with `rest_url()`, compatible with custom REST prefixes).

= Does the plugin create WordPress users? =

Yes. On first social login, a subscriber account is created from the provider email (required). Returning users are matched by email and logged in with WordPress auth cookies after OAuth `state` verification.

== Screenshots ==

1. Admin — Auth tab with Google/GitHub credentials and redirect URIs.
2. Front-end — account page sign-in.
3. Header — account menu block dropdown.

== Changelog ==

= 1.0.3 =
* Review fixes: required OAuth `state` validation, `rest_url()` for callback URLs, readme aligned with active providers.
* Account blocks and GitHub provider (from ongoing development).

= 1.0.2 =
* WordPress.org packaging: readme, GPL license, text domain `4wp-account`, quality toolchain.
* Provider enable toggle respected before login.

= 1.0.1 =
* Gmail OAuth, shortcodes, WooCommerce integration.

== Upgrade Notice ==

= 1.0.3 =
Review resubmit — OAuth state required on callback; use Redirect URIs from Auth settings after update.
