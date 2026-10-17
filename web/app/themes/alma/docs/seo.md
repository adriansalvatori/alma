# SEO

The `seo` module (`app/Domains/Seo`) handles search visibility. It works on its own and integrates
with **The SEO Framework** (TSF, `wpackagist-plugin/autodescription`) when that plugin is active.
TSF owns titles, descriptions, Open Graph, structured data and the sitemap. This module adds what
TSF does not do, and keeps TSF's settings in code.

| Concern | Where | Notes |
| :--- | :--- | :--- |
| robots.txt | `Robots/SiteRobotsTxt` | Generated per host on `robots_txt` (priority 20), after TSF/core |
| Page noindex | `Robots/PageRobots`, `Robots/RobotsMeta` | Marker comments in content or patterns |
| Real 404s | `Routing/MissingPathNotFound` | Unknown paths no longer serve the homepage with a 200 |
| HTTPS | `Routing/HttpsRedirect` | Skipped when `WP_HOME` is http |
| Redirects | `Redirects/*`, `Admin/RedirectsScreen` | `config/redirects.php` + Tools → Redirects |
| Acorn route SEO | `Routing/RouteSeo` | Title, description, canonical, robots for `routes/` pages |
| Sitemap hygiene | `Sitemap/SitemapExclusions` | Drops noindexed and redirected posts |
| TSF settings as code | `Settings/TsfConfigurator`, `config/seo.php` | Applied on every deploy |

## robots.txt

There is no static `robots.txt` file. WordPress serves it, so it is always right for the host being
served. TSF (or core) writes the base rules and the `Sitemap:` line. The theme then appends the
machine endpoints listed in `config/seo.php` → `robots.disallow` (`/api/`, `/livewire/`).

**Never disallow a page in robots.txt.** A disallowed page cannot be crawled, so its `noindex` is
never seen, and it can stay in results as a bare URL. Use a noindex marker instead.

To ask AI training crawlers to stay away, set `SEO_BLOCK_AI_CRAWLERS=true`. TSF then adds the
directives, and they apply on the next deploy.

## Keeping a page out of the index

Put a marker comment in the page content, or in the pattern the page renders:

```html
<!-- alma:noindex -->          <!-- noindex, nofollow -->
<!-- alma:noindex-follow -->   <!-- noindex, follow: thank-you pages, gated downloads -->
```

- The decision travels with the content. A page whose content is a single `<!-- wp:pattern -->`
  reference inherits the pattern's marker.
- To override one page, set its `_alma_robots` post meta to `noindex`, `noindex-follow`, or `index`
  (`index` ignores a marker inherited from a pattern).
- For Acorn routes, call `App\Domains\Seo\Robots\PageRobots::forceNoindex()`.
- The directive is applied to core's `wp_robots` and to TSF's robots meta, and the page is removed
  from the sitemap.

Non-production environments are noindexed as a whole by Bedrock's `DISALLOW_INDEXING`
(`config/environments/*.php`). Production is indexable unless `DISALLOW_INDEXING=true`, which you
can set for a pre-launch site on its final host.

## Real 404s

Some permalink structures (verbose page rules) let WordPress leave the query empty for an unknown
path. An empty query is the front page, so every mistyped URL returned the homepage with a 200: a
soft 404 that search engines index as duplicate content. `MissingPathNotFound` turns that case into
a real 404. It runs last on `parse_request` and ignores the front page, matched rewrite rules,
explicit query vars, WordPress and application endpoints, and Acorn route URIs.

## Redirects

There are two tiers, and code wins when both define the same path.

1. **`config/redirects.php`**: code-reviewed, permanent redirects (a migrated site's old URLs). It is
   shown read-only in the admin.
2. **Tools → Redirects**: editors add and remove redirects without a deploy. They are stored in
   `alma_redirects`.

Matching ignores case and the trailing slash, and the visitor's query string is carried over unless
the target has its own. Redirects run on `template_redirect` priority 0, before core's canonical
redirect. A redirect that carries a query string is sent `Cache-Control: private, no-store`, so a
page cache never replays one visitor's campaign parameters to the next visitor.

The manager refuses:

- the homepage;
- system paths (`/wp`, `/wp-admin`, `/wp-json`, `/api`, `/livewire`, `/.well-known`, …);
- loops and chains of more than 10 hops;
- off-site targets that are not `https://`;
- duplicates, including paths already redirected in code.

Same-site targets are stored as paths, so the table still works after the database is copied to
another domain.

**Performance.** The database redirects are compiled into one autoloaded option
(`alma_redirects_compiled`), so a page view costs no extra query. The option is rebuilt on every
change.

## SEO for Acorn routes

WordPress resolves no post for pages served by Acorn routes, such as `/login` or `/dashboard`. Left
alone, TSF would describe them as the homepage: wrong title, canonical and Open Graph. `RouteSeo`
handles these pages instead:

- it tells TSF the query has no SEO support, so TSF prints nothing;
- it prints the title, description and canonical itself, with the canonical pointing at the
  requested path;
- it noindexes any route behind the `auth` or `guest` middleware automatically.

For a public route, set the metadata in its controller:

```php
use App\Domains\Seo\Routing\RouteSeo;

RouteSeo::set(title: 'Savings calculator', description: 'Work out what you save.');
```

## TSF settings as code

TSF keeps its settings in the `autodescription-site-settings` option. That option drifts between
environments and gets changed by accident. The keys listed under `config/seo.php` →
`tsf.settings` are re-applied:

- by `wp acorn alma:deploy` on every release (set `SEO_APPLY_ON_DEPLOY=false` to stop this);
- on demand with `wp acorn alma:seo:apply` (add `--dry-run` to see what would change).

Rules:

- **Only listed keys are written.** Everything else stays editable in SEO → Settings.
- **Unknown keys are refused and reported, never written.** The deploy task is non-fatal, and a
  test checks every shipped key against TSF's source.
- **It is idempotent.** TSF's `1` and `"1"` compare equal.
- **It does nothing when TSF is not active.**

Add a setting by finding its key in
`web/app/plugins/autodescription/inc/classes/data/plugin/setup.class.php`.

## Launch checklist

- [ ] The SEO Framework is active, and `wp acorn alma:seo:apply --dry-run` reports no unknown keys.
- [ ] Production `.env` does not set `DISALLOW_INDEXING` (unless the site is pre-launch).
- [ ] `https://<domain>/robots.txt` lists the sitemap and the machine endpoints.
- [ ] A made-up URL returns 404, not the homepage.
- [ ] Old URLs from the previous site are in `config/redirects.php`.
- [ ] Thank-you and confirmation pages carry `<!-- alma:noindex-follow -->`.
