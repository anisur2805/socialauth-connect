=== SocialAuth Connect ===
Contributors: Anisur Rahman
Tags: authentication, social login, google, oauth
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

Modular social authentication with Google OAuth2/OIDC. Extensible to Facebook, X, Email, and more.

== Description ==

SocialAuth Connect solves the friction of password-based WordPress authentication by providing frictionless OAuth 2.0 / OpenID Connect social login with Google. Users can sign in with a single click using their Google account, eliminating the need to remember passwords and reducing registration barriers.

**Problems Solved:**

- **Registration Friction** — No password form means faster user onboarding
- **Password Management Burden** — Users authenticate via trusted provider (Google); no secrets to remember or reset
- **Account Linking** — Existing WordPress users can link their Google account to their profile
- **Security** — CSRF state tokens, rate limiting, audit logging, and email verification checks
- **Extensibility** — Modular provider pattern (Facebook, X, GitHub, Email Magic Link Phase 2+)
- **WordPress Native** — Zero external framework dependencies; uses WordPress standards, hooks, and coding conventions

**Features:**

- Google OAuth2 + OpenID Connect authentication
- Automatic user creation and account linking
- CSRF state protection (one-time, time-limited, IP-bound)
- Rate limiting (prevents brute-force attacks)
- Audit logging of login events
- Admin settings panel to configure Google credentials
- Login button injection on wp-login.php and WooCommerce login forms
- Shortcode support `[socialauth_login]` for custom placement
- Full i18n support
- Extensible provider architecture for future integrations

== Installation ==

1. Upload the `socialauth-connect` folder to `/wp-content/plugins/`
2. Activate through the Plugins menu
3. Go to Settings > SocialAuth and configure Google credentials

== Configuration ==

**Google OAuth Setup:**

1. Visit [Google Cloud Console](https://console.cloud.google.com/apis/credentials)
2. Create a new OAuth 2.0 Client ID (Web application type)
3. Add your site URL to "Authorized JavaScript origins" (e.g., `http://localhost:10078` or `https://yoursite.com`)
4. Add the exact redirect URI shown on the plugin settings page to "Authorized redirect URIs"
5. Copy the Client ID and Client Secret into the plugin settings
6. Check "Enable Google Login" and save

**Redirect URI Format:**
The plugin automatically generates the correct redirect URI: `https://yoursite.com/?socialauth_provider=google&socialauth_action=callback`

This URI must be registered exactly in Google Cloud Console for the login flow to work.

== Frequently Asked Questions ==

= How do I get Google Client ID and Secret? =
Visit [Google Cloud Console](https://console.cloud.google.com/apis/credentials), create a new OAuth 2.0 Client ID (Web application), and copy your credentials.

= Can existing users link their Google account? =
Yes. Existing WordPress users can link their Google account by logging in normally first. Future versions will add a profile page UI for this.

= What happens after login? =
Users are redirected to the WordPress admin dashboard by default. You can customize this with the `socialauth_login_redirect` filter.

= Is it secure? =
Yes. The plugin includes CSRF state validation, rate limiting, email verification checks, and audit logging of all authentication events.

= Can I use this with WooCommerce? =
Yes. The plugin automatically injects login buttons on WooCommerce login forms.

== Changelog ==

= 1.0.0 =
* Initial release
* Google OAuth2 + OpenID Connect authentication
* CSRF state protection (one-time, time-limited, IP-bound tokens)
* User creation and account linking by email
* Rate limiting (10 attempts per 5 minutes)
* Admin settings UI with Google credentials configuration
* Shortcode support `[socialauth_login]`
* Login button injection on wp-login.php and WooCommerce
* Audit logging of all authentication events
* Full i18n support with .pot file
* Modular provider architecture for future extensions
* Bug fixes: Authorization URL properly RFC 3986 encoded; OAuth handlers on `init` hook for wp-login.php support

== Requirements ==

- WordPress 6.0 or later
- PHP 8.1 or later
- cURL enabled (for OAuth token exchange)
