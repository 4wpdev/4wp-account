=== 4WP Account ===
Contributors: 4wpdev, anatolikkk
Tags: membership, user-profile, user-account, user-registration
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Membership user account hub: registration, user profile cabinet, and social login with Gutenberg blocks and shortcodes.

== Description ==

**4WP Account** is a membership-ready **user account** hub: **user registration**, a front-end **user profile** cabinet, email/password sign-in, and **social network** login (OAuth). Native **Gutenberg** blocks each have a matching **shortcode**. WooCommerce is optional.

A plugin by [4wp.dev](https://4wp.dev/). **4WP** is our project brand; the letters "WP" appear only as part of that brand name, not as a reference to WordPress. This plugin is not affiliated with, endorsed by, or sponsored by WordPress.

Source code: [github.com/4wpdev/4wp-account](https://github.com/4wpdev/4wp-account)

= Membership and user account =

* One **account page** for guests (sign-in / registration) and members (cabinet)
* **User profile** shell with a configurable account menu (dashboard, WooCommerce, extras)
* **User registration** follows **Settings → General → Anyone can register**
* Email/password works **without WooCommerce**
* Optional replacement of `wp-login.php` — only when the chosen page already has a login block or shortcode

= Social login =

Enable providers under **4WP Account → Auth / Register**:

* **Live:** email/password, Google, GitHub, TikTok
* **Coming soon:** Facebook, X

Buttons can use native brand colors and icons, a solid fill, or icon-only.

= Gutenberg blocks and shortcodes =

Every block has a shortcode equivalent:

* `forwp/account` — `[forwp_account]` — account page (sign-in or cabinet)
* `forwp/account-menu` — `[forwp_account_menu]` — header user menu
* `forwp/account-link` — `[forwp_account_link]` — header/menu account link
* `forwp/auth-buttons` — `[forwp_account_signin_buttons]` — social sign-in buttons
* `[forwp_account_login provider="gmail"]` — single provider button (shortcode only)

Place them in the Site Editor or on a page. Sign-in methods are configured in **Auth / Register**. Appearance of the guest card is under **Appearance**. The account page is selected in **Settings → Pages**.

= Also included =

* REST API — `/wp-json/forwp-account/v1/auth/{provider}` and OAuth callbacks
* Optional social buttons on WooCommerce login/register forms
* Hide the admin bar for subscribers; redirect subscribers away from `/wp-admin/`

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

= TikTok =

* **When:** User clicks TikTok sign-in; server exchanges the Login Kit code (enable in Auth / Register).
* **Terms:** [TikTok Terms of Service](https://www.tiktok.com/legal/terms-of-service)
* **Privacy:** [TikTok Privacy Policy](https://www.tiktok.com/legal/privacy-policy)

= Meta (Facebook) — planned =

* **When:** Not enabled in this release. Listed in admin as *coming soon*.
* **Terms:** [Meta Platform Terms](https://developers.facebook.com/terms/)
* **Privacy:** [Meta Privacy Policy](https://www.facebook.com/privacy/policy/)

== Installation ==

1. Upload the plugin to `/wp-content/plugins/4wp-account/` or install from the Plugins screen.
2. Activate **4WP Account**.
3. Open **4WP Account → Auth / Register** — enable email/password and any social networks, then paste OAuth credentials.
4. Copy each **Redirect URI** from settings into the Google, GitHub, or TikTok app.
5. Create a page, add the **Account** block (`forwp/account`) or `[forwp_account]`, then select that page under **Settings → Pages**.
6. Optional: add **Account Menu** to the header; style the guest card under **Appearance**.

== Frequently Asked Questions ==

= Is this a membership plugin for a user account? =

Yes. 4WP Account is a **membership** hub: one front-end **user account** for sign-in, **user registration**, and a logged-in **user profile** cabinet. It does not sell plans or paywalls — it is the account layer those membership sites sit on.

= Does it support user registration? =

**User registration** uses the native WordPress membership setting: **Settings → General → Anyone can register**. When that is on, the account page shows Create account next to email/password sign-in. Social networks can also create a subscriber on first login.

= Where is the user profile? =

The **user profile** (cabinet) is the same account page after login: left menu plus section body. Configure the page in **Settings → Pages** and menu items in **Settings → Account Menu**. Guests see sign-in; members see the account.

= Can members sign in with social networks? =

Yes. **Social login** is first-class next to email/password: Google, GitHub, and TikTok are live when you enable them under **Auth / Register**. Facebook and X are listed as coming soon. Buttons can use native brand colors and icons.

= Are there Gutenberg blocks and shortcodes? =

Yes. Native **Gutenberg** blocks: `forwp/account`, `forwp/account-menu`, `forwp/account-link`, `forwp/auth-buttons`. Each has a **shortcode**: `[forwp_account]`, `[forwp_account_menu]`, `[forwp_account_link]`, `[forwp_account_signin_buttons]`. There is also `[forwp_account_login provider="gmail"]` for a single social button. See **Settings → Blocks / Shortcodes**.

= Can I hide the default WordPress login screen? =

Only if a real entry point exists. Under **Settings → Pages**, pick a published page that already contains the Account block or a login shortcode, then enable **Use the account page instead of wp-login.php**. If the block is removed later, `wp-login.php` stays available. Logout and password-reset links on `wp-login.php` are not blocked.

= Do I need WooCommerce? =

No. Email/password, social login, the account page, and Gutenberg blocks work without WooCommerce. If WooCommerce is active, you can show social buttons on My Account forms and fall back to My Account when no account page is selected.

= Where is the OAuth callback URL? =

**4WP Account → Auth / Register** — open a live provider and copy the Redirect URI (built with `rest_url()`, compatible with custom REST prefixes).

== Screenshots ==

1. Admin — Auth / Register methods (email and social networks).
2. Front-end — membership account page sign-in (email/password and social login).
3. Header — account menu block dropdown (user profile).
4. Admin — Appearance of the guest sign-in card.
5. Admin — Settings → Pages (account page and wp-login replacement).

== Changelog ==

= 1.1.0 =
* Stable membership release: user account page, user registration, user profile cabinet.
* Settings → Pages: replace `wp-login.php` only when the chosen page has an Account block or login shortcode.
* Move toolbar / subscriber redirect Behavior next to the account page setting.
* Document every Gutenberg block and its shortcode under Settings → Blocks / Shortcodes.
* Tested up to WordPress 7.1.

= 1.0.7 =
* Appearance tab: guest card background, border color/width/radius, live preview (two fields side by side; stack when narrow).
* Social buttons: native brand colors and icons, solid fill, or icon-only.
* Auth / Register method cards: email/password, Google, GitHub, TikTok (live); Facebook and X (coming soon).
* Email/password as a first-class provider; registration follows WordPress “Anyone can register”; WooCommerce not required.
* Remove the extra Forms tab.

= 1.0.4 =
* Remove unused `register_setting()` calls (automated Plugin Check).
* Plugin Check warnings: input sanitization, migration SQL `%i`, distignore moved to docs.

= 1.0.3 =
* Review fixes: required OAuth `state` validation, `rest_url()` for callback URLs, readme aligned with active providers.
* Account blocks and GitHub provider (from ongoing development).

= 1.0.2 =
* WordPress.org packaging: readme, GPL license, text domain `4wp-account`, quality toolchain.
* Provider enable toggle respected before login.

= 1.0.1 =
* Gmail OAuth, shortcodes, WooCommerce integration.

== Upgrade Notice ==

= 1.1.0 =
Stable account hub: set the account page in Settings → Pages, add the Account block or [forwp_account], then optionally replace wp-login.php. Appearance and social button styles are under Appearance.

= 1.0.4 =
Automated scan and Plugin Check fixes — includes T1 review items from 1.0.3.

= 1.0.3 =
Review resubmit — OAuth state required on callback; use Redirect URIs from Auth settings after update.
