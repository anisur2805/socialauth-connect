=== SocialAuth Connect ===
Contributors: yourname
Tags: authentication, social login, google, oauth
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

Modular social authentication with Google OAuth2/OIDC. Extensible to Facebook, X, Email, and more.

== Description ==

SocialAuth Connect provides OAuth 2.0 / OpenID Connect authentication via Google (Phase 1), with a modular architecture designed for easy extension to Facebook, X, GitHub, Email Magic Link, and more.

== Installation ==

1. Upload the `socialauth-connect` folder to `/wp-content/plugins/`
2. Activate through the Plugins menu
3. Go to Settings > SocialAuth and configure Google credentials

== Changelog ==

= 1.0.0 =
* Initial release
* Google OAuth2 + OpenID Connect
* CSRF state protection
* User creation and account linking
* Rate limiting
* Admin settings UI
* Shortcode support
