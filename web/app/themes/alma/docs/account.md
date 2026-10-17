# Account module

Front-end accounts on top of WordPress users: sign-in, registration, two-factor authentication
(TOTP plus recovery codes), and a small account area. Code lives in `app/Domains/Account`, the
Livewire components in `resources/views/components/account`, and the pages in
`resources/views/auth`, `dashboard.blade.php` and `settings.blade.php`.

WordPress stays the identity provider. There is no Laravel auth guard and no separate users
table: "logged in" means WordPress accepted the auth cookie, and every check calls
`is_user_logged_in()` / `wp_get_current_user()`.

## Routes

| Route | Name | Access |
| :--- | :--- | :--- |
| `/login` | `login` | guests (a logged-in user is sent to the dashboard) |
| `/register` | `register` | guests, and only while registration is open (404 otherwise) |
| `/two-factor-challenge` | `two-factor.challenge` | a browser with a pending login (see below) |
| `/dashboard` | `dashboard` | logged-in users |
| `/settings` | `settings` | logged-in users: profile and security panel |

Every account route sends `X-Robots-Tag: noindex, nofollow`. It is a header rather than a meta
tag, so it holds whatever the SEO plugin prints.

The routes are in `app/Domains/Account/routes/web.php` and reference their middleware by class.
Laravel's default `auth` and `guest` aliases point at Laravel guards, which know nothing about
WordPress users.

**Logging out** is WordPress' own nonce-protected flow: link to `wp_logout_url(home_url('/'))`.
There is deliberately no `GET /logout` route. A plain link would let any page on the internet
log your visitors out with an `<img src>`.

**Forgotten passwords** use core's flow at `wp_lostpassword_url()`, linked from the login form.

## How sign-in works

`AttemptLogin` calls `wp_authenticate()`, so every `authenticate` filter applies: the two-factor
gate, and any security plugin a project adds later. Only `CompleteLogin` issues the auth cookie,
and it is reached only after every factor the user has enabled has passed.

- **Rate limiting.** 5 failed attempts per login name and IP, then a 60-second lockout (Laravel
  `RateLimiter`, on Acorn's file cache in production).
- **One error message** for a wrong password and for an unknown account, so the form never
  reveals which accounts exist.

## Two-factor authentication

### Enforced at authentication, not by a session flag

`TwoFactorGate` hooks WordPress' `authenticate` filter at priority 100. When the user has 2FA on,
a correct password does not log them in:

1. it starts a pending challenge;
2. the login is refused with the `alma_two_factor_required` error;
3. on `wp-login.php` the browser is redirected to `/two-factor-challenge`, and whatever
   `redirect_to` wp-login.php had (usually wp-admin) is kept for afterwards.

So no auth cookie exists before the second factor, and wp-admin, the theme's login form and
anything else that signs in with a password are all covered by the same code.

The previous design set a `two_factor_verified` flag in the Laravel session and checked it in
route middleware. That protected the theme's own routes and nothing else: wp-admin accepted the
password alone. It also could not work at all, because the challenge route required being logged
in, which the 2FA flow had not done yet.

### The pending challenge

`PendingChallenge` stores the in-between state:

- **In the browser:** a random 64-character token in the `alma_2fa` cookie (HttpOnly, Secure on
  HTTPS, SameSite=Lax), valid 10 minutes.
- **On the server:** a transient keyed by a SHA-256 hash of the token, holding the user id, the
  remember flag, the attempt count and the original `redirect_to`.

The browser cannot change whose login it is completing. After 5 wrong codes the challenge is
deleted and the user has to enter their password again.

The cookie is a raw cookie, not Laravel's encrypted one, because it is set on `wp-login.php`,
which never passes through Laravel's middleware.

### Codes

- **TOTP** via `pragmarx/google2fa`, with one timestep of drift allowed either side.
- **Replay protection.** Each accepted code stores its timestep in user meta, and a code from
  that timestep or an earlier one is refused.
- **Secrets are encrypted** with `APP_KEY`. If the key changes, existing secrets cannot be
  decrypted and those users read as "2FA off" rather than being locked out. An admin should ask
  them to set it up again.
- **Recovery codes.** 8 single-use codes, stored as keyed hashes (HMAC with WordPress' auth
  salt), shown once when generated or regenerated.

### Setup (Settings → Two-factor authentication)

1. **Set up.** A secret is generated and held as pending, and a QR code (plus the secret as
   text) is shown.
2. **Confirm.** The user enters a code from their app. Only then is 2FA switched on and the
   recovery codes shown.
3. **Turning off, or regenerating recovery codes,** needs the current password.

### Application Passwords bypass 2FA, by design

Application Passwords (REST API, XML-RPC, MCP clients) are a separate credential: long, random,
named and individually revocable. Scripts cannot answer a TOTP prompt, so the gate lets a request
through when core reports `application_password_did_authenticate`. Revoke an application
password from the user's profile in wp-admin if it leaks.

### Resetting a locked-out user

Delete the user's `alma_2fa_*` user meta (for example
`wp user meta delete <id> alma_2fa_enabled`) or disable 2FA from code with
`app(TwoFactorService::class)->disable($id)`.

## Registration

Registration follows WordPress' own setting: **Settings → General → Membership → "Anyone can
register"**, with new users given the **New User Default Role**. Turn the setting off and
`/register` returns 404 and the login page stops linking to it.

The form has:

- **Spam protection.** A honeypot field, a minimum of 3 seconds between rendering and
  submitting, and 5 registrations per IP per hour.
- **Login names** derived from the email: `jane.doe+news@x.com` becomes `jane.doe`, then
  `jane.doe-2` if taken.
- **Core's `register_new_user` action,** fired so the usual notification emails go out.
- **The same message for a taken email** as for any other failure.

New users are signed in straight away. They have no 2FA yet, so the gate does not apply.

## Profile

Name and email are edited on the settings page. Changing the email needs the current password:
password resets go to that address, so changing it from a hijacked session would hand over the
account for good.

## Tests

```bash
vendor/bin/pest tests/Feature/Account tests/Unit/Account
```

Helpers in `tests/Support/WordPress/account.php` create a user (`account_user()`, password
`correct-horse-battery-1`), turn 2FA on (`account_enable_two_factor()`), produce codes
(`account_totp()`) and start a challenge (`account_start_challenge()`).
