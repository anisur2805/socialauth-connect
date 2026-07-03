# SocialAuth Connect — Feature Tracker

Status of every planned feature (source of truth: `plan.md` roadmap).
Update this file whenever a feature lands or a bug is fixed.

## Phase 1 — MVP (Google)

| Feature | Status |
|---|---|
| Plugin scaffold + PSR-4 autoloader | ✅ Done |
| Google OAuth2 + OpenID Connect login | ✅ Done (verified 2026-07-03) |
| CSRF state protection (one-time, 10 min, IP-bound) | ✅ Done |
| User creation + account linking by email | ✅ Done |
| Admin settings UI (Settings → SocialAuth) | ✅ Done |
| Login page button injection (`login_form`, WooCommerce) | ✅ Done |
| Shortcode `[socialauth_login]` | ✅ Done |
| Rate limiting (10 attempts / 5 min / IP) | ✅ Done |
| Audit logging (`wp_socialauth_log` table) | ✅ Done |
| Token storage (user meta) | ✅ Done |
| i18n loading + .pot file | ✅ Done |

## Phase 2 — Extended Providers (Pipeline)

| Feature | Status |
|---|---|
| Facebook (Graph API OAuth2) | ✅ Done (2026-07-03) |
| X / Twitter (OAuth 2.0 PKCE) | ⏳ Pipeline (placeholder class exists) |
| GitHub OAuth2 | ⏳ Pipeline |
| Email Magic Link (passwordless) | ⏳ Pipeline |
| Encrypted token storage (Sodium) | ⏳ Pipeline |

## Phase 3 — Advanced (Pipeline)

| Feature | Status |
|---|---|
| WooCommerce My Account integration | ⏳ Pipeline |
| Account linking / unlink UI on profile page | ⏳ Pipeline |
| REST API endpoints (headless/mobile) | ⏳ Pipeline |
| Two-factor as second step | ⏳ Pipeline |
| Gutenberg login block (basic class exists) | ⏳ Pipeline |
| Multisite support | ⏳ Pipeline |
| PHPUnit test suite (`tests/` scaffolded, no tests yet) | ⏳ Pipeline |

## Fix Log

### 2026-07-03 — Facebook OAuth2 integration complete
- Implemented `get_user_profile()` to fetch from Facebook Graph API v18.0
- Proper field mapping: id, email, name, first_name, last_name, picture (avatar)
- `normalize_user()` handles Facebook's nested picture object format
- Added admin settings panel with App ID / App Secret fields
- Added Facebook tab to settings page with redirect URI instruction
- Registered provider in Core.php; auto-enables when credentials provided
- Ready for production: users can now "Continue with Facebook" on login page

### 2026-07-03 — Google login "can't proceed" fixed
- **Root cause:** `get_auth_url()` built the Google authorization URL with
  `add_query_arg()`, which does not urlencode values. The `&` inside
  `redirect_uri` truncated it, so Google received a wrong redirect URI
  (`redirect_uri_mismatch`). Now built with `http_build_query()` (RFC 3986).
- Restored `class-provider-google.php` (file had been overwritten with a
  stray code snippet — fatal on load) and `readme.txt` from git.
- Moved OAuth handlers from `template_redirect` to `init` so the callback
  also works on `wp-login.php` and runs before canonical redirects.
- Verified end-to-end: login URL 302s to Google with correctly encoded
  redirect_uri; bogus callback state rejected with 403; button renders on
  wp-login.php.

**Google Cloud Console — required Authorized redirect URI (exact):**
`http://localhost:10078/?socialauth_provider=google&socialauth_action=callback`
(shown on the plugin settings page; changes with the site URL)
