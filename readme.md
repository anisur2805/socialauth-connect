=== SocialAuth Connect ===
Contributors: Anisur Rahman
Tags: social login, facebook login, google login, oauth, authentication
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

One-click social login for WordPress. Let users sign in with Google, Facebook, X (Twitter), or GitHub — no passwords required.

== Description ==

**SocialAuth Connect** replaces the default WordPress login form with a modern, one-click social authentication experience. Users click "Continue with Google" or "Continue with Facebook" and they're logged in instantly — no passwords to create, remember, or reset.

= Why SocialAuth Connect? =

* **Reduce Login Friction** — Users authenticate with accounts they already have, eliminating password fatigue
* **Increase Registrations** — Lower barrier to entry means more signups
* **Improve Security** — OAuth 2.0 with CSRF state tokens, rate limiting, and audit logging
* **Zero Dependencies** — Pure WordPress plugin, no external frameworks or services
* **Developer Friendly** — Extensible provider architecture, hooks, filters, and shortcodes

= Supported Providers =

* **Google** — OAuth 2.0 + OpenID Connect. Supports email verification, profile data, and avatar retrieval.
* **Facebook** — OAuth 2.0. Supports login with name and profile picture. Email permission available after Facebook App Review.
* **X (Twitter)** — OAuth 2.0 with PKCE. Supports login with name, username, and profile picture.
* **GitHub** — OAuth 2.0. Supports login with name, username, email, and avatar.

= Key Features =

* **Automatic User Creation** — New social users are automatically registered as WordPress subscribers
* **Account Linking** — Existing users can link their social accounts to their profile
* **CSRF Protection** — One-time, time-limited, IP-bound state tokens prevent cross-site request forgery
* **Rate Limiting** — Configurable attempt limits prevent brute-force and abuse
* **Audit Logging** — All authentication events are logged for security monitoring
* **Email Verification** — Optionally require verified email addresses before granting access
* **Login Redirect** — Customizable post-login destination per user or globally

= Admin Experience =

* **Setup Wizard** — Step-by-step configuration guide with copy-to-clipboard redirect URIs
* **Test Connection** — Verify your Facebook App credentials work before going live
* **Provider Status** — Visual indicators showing which providers are configured and enabled
* **Dashboard Widget** — Shows connected social accounts with profile pictures and a logout button
* **Admin Notices** — Alerts when providers are enabled but misconfigured

= Developer Features =

* **Shortcode** — Place login buttons anywhere: `[socialauth_login providers="google,facebook"]`
* **Filters** — Customize redirect URLs, normalize user data, and control registration
* **Actions** — Hook into login success, user creation, and provider linking events
* **Modular Architecture** — Add new providers by extending `AbstractOAuth2Provider`
* **Full i18n** — Translation-ready with `.pot` file included

= Shortcode Usage =

`[socialauth_login]` — Shows all enabled providers

`[socialauth_login providers="google"]` — Shows only Google

`[socialauth_login providers="google,facebook" redirect="/dashboard"]` — With custom redirect

= Available Filters =

* `socialauth_login_redirect` — Customize post-login redirect URL
* `socialauth_normalize_user` — Modify user data before account creation
* `socialauth_can_register` — Control whether new users can register
* `socialauth_state_check_ip` — Enable/disable IP binding for state tokens

== Installation ==

1. Download the plugin zip file
2. Go to **Plugins → Add New → Upload Plugin**
3. Upload the zip file and click **Install Now**
4. Activate the plugin through the **Plugins** menu
5. Navigate to **Settings → SocialAuth** to configure your providers

== Configuration ==

= Google OAuth Setup =

1. Go to [Google Cloud Console](https://console.cloud.google.com/apis/credentials)
2. Click **Create Credentials** → **OAuth client ID**
3. Select **Web application** as the application type
4. Enter your site name under **Authorized JavaScript origins**
   * Production: `https://yoursite.com`
   * Local: `http://localhost` or `http://localhost:8080`
5. Under **Authorized redirect URIs**, add the exact redirect URI shown on the plugin's Google settings page:
   ```
   https://yoursite.com/?socialauth_provider=google&socialauth_action=callback
   ```
6. Click **Create** — copy the **Client ID** and **Client Secret**
7. In WordPress, go to **Settings → SocialAuth → Google** tab
8. Paste your Client ID and Client Secret
9. Check **Enable Google Login** and save

= Facebook OAuth Setup =

**Step 1: Create a Facebook App**

1. Go to [Facebook Developers](https://developers.facebook.com/apps/)
2. Click **Create App**
3. Select **Business** as the app type
4. Enter your app name (e.g., "Your Site Login") and contact email
5. On the **Use cases** screen, select **"Authenticate and request data from users with Facebook Login"**
6. Click **Next** through the remaining steps and click **Create App**

**Step 2: Configure App Settings**

1. In the left sidebar, go to **App Settings → Basic**
2. Set a **Category** (e.g., "Business and pages")
3. Add your **Privacy Policy URL** (e.g., `https://yoursite.com/privacy-policy`)
4. In the **App Domains** field, add your domain:
   ```
   yoursite.com
   ```
5. Click **Save Changes**

**Step 3: Configure Facebook Login**

1. In the left sidebar, click **Facebook Login** (under your app's products)
2. Click **Settings**
3. In the **Valid OAuth Redirect URIs** field, add:
   ```
   https://yoursite.com/?socialauth_provider=facebook&socialauth_action=callback
   ```
4. Click **Save Changes**

**Step 4: Publish Your App**

1. In the left sidebar, click **App Review**
2. Click **Switch to Live Mode** (or "Create a live version")
3. Confirm when prompted

**Step 5: Configure the Plugin**

1. Copy the **App ID** and **App Secret** from **App Settings → Basic**
2. In WordPress, go to **Settings → SocialAuth → Facebook** tab
3. Paste your App ID and App Secret
4. Check **Enable Facebook Login**
5. Click **Test Connection** to verify your credentials work
6. Click **Save Changes**

= Important Facebook Notes =

* The **Valid OAuth Redirect URI** must match exactly — including `https://`, domain, path, and query parameters
* The `email` permission requires **Facebook App Review** — without it, users can still log in but only their name and profile picture are retrieved
* In development mode, only the app creator can test Facebook login until the app is published
* Facebook requires **HTTPS** for all production sites
* Do **not** use "Facebook Login for Business" — use the standard **Facebook Login** product

= X (Twitter) OAuth Setup =

**Step 1: Create a Developer Account and App**

1. Go to [X Developer Portal](https://developer.x.com/en/portal/dashboard)
2. Sign up for a developer account if you don't have one
3. Create a new app in the dashboard

**Step 2: Configure OAuth 2.0**

1. In your app settings, go to **User authentication settings**
2. Enable **OAuth 2.0**
3. Set **Type of App** to **Web App, Automated App or Bot**
4. Add this URL to **Callback URI / Redirect URL**:
   ```
   https://yoursite.com/?socialauth_provider=x&socialauth_action=callback
   ```
5. Save changes

**Step 3: Configure the Plugin**

1. Copy the **Client ID** and **Client Secret** from your app settings
2. In WordPress, go to **Settings → SocialAuth → X (Twitter)** tab
3. Paste your Client ID and Client Secret
4. Check **Enable X Login** and save

= Important X/Twitter Notes =

* X uses **OAuth 2.0 with PKCE** — no additional configuration needed, the plugin handles it automatically
* X does not always provide email addresses — users can still log in with name and profile picture
* The **Callback URI** must match exactly (including `https://` and query parameters)

= GitHub OAuth Setup =

**Step 1: Create an OAuth App**

1. Go to [GitHub Developer Settings](https://github.com/settings/developers)
2. Click **OAuth Apps** → **New OAuth App**
3. Enter an **Application name** (e.g., your site name)
4. Add this URL to **Authorization callback URL**:
   ```
   https://yoursite.com/?socialauth_provider=github&socialauth_action=callback
   ```
5. Click **Register application**

**Step 2: Configure the Plugin**

1. Copy the **Client ID** from the app page
2. Click **Generate a new client secret** and copy it
3. In WordPress, go to **Settings → SocialAuth → GitHub** tab
4. Paste your Client ID and Client Secret
5. Check **Enable GitHub Login** and save

= Important GitHub Notes =

* GitHub may not return email addresses if the user has their email set to private — users can still log in with name and avatar
* The **Callback URL** must match exactly (including protocol and query parameters)

== Frequently Asked Questions ==

= How long does setup take? =

Google: About 5 minutes. Facebook: About 10–15 minutes (including app creation and publishing). X: About 5 minutes. GitHub: About 3 minutes.

= Do I need to know how to code? =

No. The plugin provides a visual setup wizard with step-by-step instructions. Simply copy and paste your credentials into the settings fields.

= Can existing WordPress users link their social accounts? =

Yes. Once logged in, users can link their social accounts from their profile. Future versions will add a dedicated account linking page.

= What happens after login? =

Users are redirected to the WordPress admin dashboard by default. You can customize this by:
* Setting a **Login Redirect URL** in Settings → SocialAuth → General
* Using the `socialauth_login_redirect` filter for dynamic redirects
* Using the `redirect` attribute in shortcodes: `[socialauth_login redirect="/dashboard"]`

= Can I control who can register? =

Yes. In **Settings → SocialAuth → General**, toggle **Allow Registration** to enable or disable new user registration via social login.

= Is it secure? =

Yes. The plugin implements multiple security layers:
* **CSRF state tokens** — One-time use, time-limited (10 minutes), IP-bound
* **Rate limiting** — Prevents brute-force and abuse attempts
* **Email verification** — Optionally reject unverified email addresses
* **Input sanitization** — All user data is sanitized before storage
* **Audit logging** — All authentication events are logged for monitoring

= Does it work with WooCommerce? =

Yes. The plugin automatically injects social login buttons on WooCommerce login forms.

= Can I use shortcodes? =

Yes. Use `[socialauth_login]` to place login buttons anywhere on your site:
* `[socialauth_login]` — All enabled providers
* `[socialauth_login providers="google"]` — Google only
* `[socialauth_login providers="facebook,x"]` — Facebook and X only
* `[socialauth_login providers="github" redirect="/dashboard"]` — With redirect
* `[socialauth_login show_label="false"]` — Icon only, no text

= Does the plugin collect or store passwords? =

No. Authentication happens entirely through OAuth 2.0 with Google, Facebook, X, or GitHub. The plugin never sees, handles, or stores user passwords.

= Can I add more providers? =

Yes. The plugin uses a modular provider architecture. Developers can add new providers by extending the `AbstractOAuth2Provider` class. Planned providers include X (Twitter), GitHub, and Email Magic Link.

== Screenshots ==

1. Login page with social login buttons
2. Facebook setup wizard with step-by-step instructions
3. Google provider settings with redirect URI
4. Dashboard widget with connected accounts and logout button
5. Test connection success dialog

== Changelog ==

= 1.1.0 =
* X (Twitter) OAuth 2.0 with PKCE authentication
* GitHub OAuth 2.0 authentication
* Setup wizards for X and GitHub providers
* X logo and GitHub logo assets

= 1.0.0 =
* Initial release
* Google OAuth2 + OpenID Connect authentication
* Facebook OAuth2 authentication with setup wizard and test connection
* CSRF state protection (one-time, time-limited, IP-bound tokens)
* Automatic user creation and account linking by email
* Rate limiting (10 attempts per 5 minutes)
* Admin settings UI with provider configuration tabs
* Dashboard widget showing connected social accounts with profile pictures
* Logout button on dashboard widget
* Shortcode support `[socialauth_login]`
* Login button injection on wp-login.php and WooCommerce
* Audit logging of all authentication events
* Full i18n support with .pot file
* Modular provider architecture for future extensions
* Facebook scope separator fix (comma-separated)
* Post-login redirect reads saved option from database
* Placeholder email for users without Facebook email permission

== Upgrade Notice ==

= 1.1.0 =
Adds X (Twitter) and GitHub social login providers.

= 1.0.0 =
Initial release with Google and Facebook social login support.
