# Commerce (WooCommerce)

The Commerce module is **optional**. It registers only when WooCommerce is active
(`CommerceServiceProvider::enabled()` checks `class_exists('WooCommerce')`), so a site without a
shop carries none of it.

## Switching it on

WooCommerce is not a dependency of the boilerplate. To add a shop to a project:

```bash
composer require wpackagist-plugin/woocommerce   # from the repo root
wp plugin activate woocommerce
```

The module then registers on the next request. To keep WooCommerce installed but the theme
integration off, add `commerce` to `ALMA_DISABLED_MODULES`.

## What the module does

| Piece | File | Purpose |
| :--- | :--- | :--- |
| Theme setup | `app/Domains/Commerce/Hooks/WooCommerceSetup.php` | Declares `woocommerce` support (plus gallery zoom, lightbox and slider). Keeps WooCommerce on the classic template hierarchy (`woocommerce_has_block_template` → false), so the Blade overrides apply. Removes WooCommerce's stylesheets, because the shop is styled with Tailwind. |
| Template hooks | `app/Domains/Commerce/Hooks/RemoveTemplateHooks.php`, `config/commerce.php` | Unhooks the WooCommerce template hooks listed under `remove_hooks` once the request is parsed. By default these are the page wrapper and sidebar (the Blade layout provides both) and the generator tag. Add any `[hook, callback, priority]` from WooCommerce's `includes/wc-template-hooks.php` to strip more. This list replaces Sage's `app/wc-template-hooks.php`. |
| Favorites | `app/Domains/Commerce/Services/FavoritesService.php` | Favorite products for guests and users (see below). |
| Livewire | `resources/views/components/commerce/` | `<livewire:commerce.cart />`, `<livewire:commerce.favorites />`, `<livewire:commerce.search />` and `<livewire:commerce.favorite-toggle :product-id="$id" />`. |

## Template overrides

Blade overrides live in `resources/views/woocommerce/`, mirroring WooCommerce's `templates/`
folder. `generoi/sage-woocommerce` wires them up. It returns early unless `WC_ABSPATH` is defined,
so it is inert on sites without WooCommerce.

To override another template, copy it from `web/app/plugins/woocommerce/templates/` into
`resources/views/woocommerce/` and rename it `.blade.php`. Keep the `@version` header and compare
it against WooCommerce's after each update. WooCommerce → Status lists outdated overrides.

The product templates never show invented content. The single-product "Highlights" column
shows the first gallery image and the best genuine, approved review rated 4 or more stars, or
nothing at all.

## Favorites

- **Logged-in users:** stored in user meta (`alma_favorites`).
- **Guests:** stored in the `alma_favorites` cookie as `12,40,7.<hmac>`, signed with `APP_KEY`.
  - A cookie with a wrong signature, non-numeric content or an oversized value reads as an empty list.
  - The list is capped at `COMMERCE_FAVORITES_MAX` (default 50).
  - When a guest logs in, their cookie list is merged into their account and the cookie is cleared.
- **Why a raw cookie:** favorites are read on WordPress-rendered pages, where Laravel's cookie
  middleware never runs, so the cookie is written with `setcookie()` (through the `CookieJar`
  contract) and excluded from `EncryptCookies`.

## Configuration

| Variable | Default | Purpose |
| :--- | :--- | :--- |
| `COMMERCE_FAVORITES_MAX` | `50` | Maximum number of favorites kept per shopper. |

## Payments at the WooCommerce checkout

WooCommerce payment plugins work as usual. To offer a gateway built for the Payment module at
the WooCommerce checkout as well, see [payments.md](payments.md#woocommerce-bridge).
