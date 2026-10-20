# Design system

The rules every block, pattern and template follows. Tokens live in `resources/css/tokens.css`
(`@theme`), imported by the site (`app.css`, so the editor too) and by wp-admin
(`resources/css/admin/`); the editor palette in `theme.json` mirrors them, and
`tests/Feature/Conventions/DesignTokensTest` keeps the two in step.

## Rules

1. **One container.** Section content sits in `container-page`: `max-width: var(--container-page)`
   (80rem), with 1rem / 1.5rem / 2rem side padding at base / sm / lg. Never measure a container
   width off a design comp and hard-code it. Running copy uses the reading measure,
   `--container-content` (48rem).
2. **One section rhythm.** Vertical section padding is `py-section` (a fluid clamp, 4rem to
   7rem). A band that needs to be tighter gets an option on its block, not a one-off class in a
   pattern.
3. **A named type scale.** `text-display` (hero headline), `text-heading` (section headings),
   `text-title` (card and sub-headings), `text-lead` (intro paragraphs). A heading is the same
   size in every block. Don't reach for `text-5xl` in a new block.
4. **One brand hue.** `brand-50` … `brand-950`. Change the project's colour in one place
   (`tokens.css`): Flux takes its accent from `--color-accent`, which points at the brand scale,
   and wp-admin follows too. Neutrals are
   Tailwind's `zinc`. Every surface pairs a light and a `dark:` value.
5. **Weights stop at bold (700).** Nothing heavier.
6. **A variant is an option, not a fork.** A design that differs from an existing block gets a
   field on that block, defaulted to current behaviour (see [`blocks.md`](blocks.md)).
7. **Reuse before build, and justify inline markup.** Pattern markup that isn't a block needs
   `// @bespoke: <reason>` (enforced).
8. **Classes must be compilable.** Tailwind scans `app/`, `patterns/`, `resources/patterns/`,
   `resources/views/` and `resources/js/` (`@source` in `app.css`). A class typed into the
   database, or written in a file outside those paths, doesn't exist at runtime.
9. **Editor colours are a short palette.** `brand`, `ink`, `muted`, `white` (theme.json). Each
   has `.has-*-color` / `.has-*-background-color` rules in `app.css`, because core's
   global-styles stylesheet is dequeued.

## Core block styles

The block library CSS and global styles are dequeued (`FrontEndCleanup`): the theme styles
blocks with Tailwind, and core's stylesheet would fight it. `app.css` re-implements only what the
theme relies on:

- **Content flow** (`.entry-content`): core blocks sit in the reading measure, `alignwide` in the
  container, and `alignfull` (every section block) runs edge to edge.
- **Column stacking**: `.wp-block-columns` stacks on small screens.
- **Palette classes**, as above.
- **Core buttons** render as Flux buttons on the page (`CoreButtons`) and are styled to match in
  the editor (`editor.css`).

## Performance defaults

These are wired into `layouts/app.blade.php` and the Content module:

- **Head order**: the stylesheet first, then the LCP preload slot (`@push('preload')`), then
  font preloads, then `wp_head()`. Push one preload with one URL, with no media-query pairs,
  because the preload scanner evaluates `media` against its default viewport.
- **Fonts**: a system font stack by default, so nothing is downloaded. To use a font:
  self-host the woff2 in `resources/fonts` (subset it, split it by `unicode-range`, trim
  variable axes to the weights you use), declare it with `@font-face` in `app.css`, set
  `--font-sans` in `tokens.css` (wp-admin uses it as well), and list the one or two files used above the fold in `config/content.php` →
  `fonts.preload`. Never use Google Fonts.
- **Images**:
  - Every `<img>` declares width and height (enforced).
  - The hero image is eager with `fetchpriority="high"`; everything else is lazy and async.
  - Theme art is built to WebP at build time (`vite/theme-images.js`); uploads get WebP
    sub-sizes (`MediaDefaults`).
  - Never eager-load an image inside `hidden lg:block`: Chrome still fetches it.
- **JavaScript**: one module script at the end of `<body>`, `modulePreload: false`, no emoji
  script. Removing a third-party script removes its cost; deferring it only moves the cost.
- **CSS**: most of `app.css` is Flux's component styles (~44KB gzipped). Rarely used CSS
  belongs in its own Vite entry, loaded only where it's needed.
- **Measuring**: take the median of three mobile Lighthouse runs. Before a screenshot
  comparison, force images to eager and `await img.decode()`, or lazy images read as missing.

## Dark mode

`@fluxAppearance` sets the `dark` class before first paint, so there's no white flash, and the
header toggle flips it. Every block pairs light and `dark:` classes. Check both.

## wire:navigate (opt-in)

`WIRE_NAVIGATE=true` turns internal links into SPA-style page transitions, with a short fade
(`@view-transition` in `app.css`). It's off by default because scripts then run once per visit
instead of once per page, so analytics and third-party widgets must listen for
`livewire:navigated`. In views:

- plain links use `@navigate`;
- Flux components take `:attributes="\App\Support\Navigate::attributes()"`.

## Front-end kit

Components and motion for marketing pages, ported from Movidagráfica's site. Everything is
opt-in: a page that uses none of it pays only for `gsap` in the bundle.

**Type.** `kit.css` adds an editorial scale beside the `--text-*` tokens: `type-hero`,
`type-display`, `type-menu`, `type-title`, `type-lead`, `type-eyebrow`, `type-body`. Their sizes
are variables (`--type-hero`, `--type-display`, …); `--type-display-case` (`none`, or `uppercase`
for a brand that shouts) and `--type-display-weight` style the three display sizes. Override them
in `tokens.css`. Layout uses the existing `container-page` and `py-section`.

**Components** (`resources/views/components/kit`). Links follow `wire:navigate` when it's on.

| Component | What it is |
| :--- | :--- |
| `<x-kit.pill href variant size>` | The button: `solid`, `brand` or `outline`; `md` or `lg`; an arrow unless `:arrow="false"`. |
| `<x-kit.arrow-link href>` | A link whose arrow slides in on hover, scaled to its text. |
| `<x-kit.marquee :items reverse>` | Words in hero type sliding sideways forever; still with reduced motion; hidden from assistive tech. |
| `<x-kit.carousel inset width width-mobile>` + `<x-kit.carousel-item>` | One row of fixed-width cards you drag; they ease and squash with speed. `inset` lines the first card up with the container while dragged cards run to the screen edge. |
| `<x-kit.menu :links>` | A full-screen menu (slots: `brand`, `aside`). Open it with `onclick="toggleKitMenu()"` and `aria-controls="kit-menu"`; Escape closes it; inert while closed. |
| `<x-kit.media-card href title meta image video>` | A 4:3 panel and a title. With `video`, it plays only on hover or focus. It's also the source of the card morph. |
| `<x-kit.document title :sections>` | A page of numbered text sections: a method, payments, terms. |

**Motion** (`resources/js/kit`, started by `app.js`). Each module sets up what's on the page,
again after `wire:navigate`, and setting up twice is harmless.

- Reveals: `data-reveal` rises in, `data-reveal-text` letter by letter, after `data-reveal-delay`
  milliseconds. What's already on screen plays at once.
- Videos: `<video data-lazy-src>` loads near the viewport; `[data-hover-video]` cards play on hover.
- Page transitions (`PAGE_TRANSITIONS=true`, with `WIRE_NAVIGATE=true`): following a
  `<x-kit.media-card>` grows its panel into the next page and dissolves into the page's
  `[data-morph-target]` image; any other link draws `--kit-curtain` as a circle from the click and
  collapses it into `[data-curtain-anchor]` (or the top-right corner). Back and forward are left to
  Livewire. Set `--kit-curtain` to a colour, a gradient or an image.

Reduced motion turns the marquee, the menu's stagger, the reveals and the hover videos off, and
page transitions become a short fade.

## Admin

wp-admin is the shadcn/ui zinc console Remote Leverage's admin was built with, ported as-is:
monochrome chrome and controls, with the brand in the mark. A project rebrands its admin through
`config/admin.php` (name, mark) and `tokens.css` (`--color-brand-600` paints the generated mark),
never by editing admin CSS.

### The skin (`ADMIN_THEME`, on by default)

`App\Infrastructure\WordPress\Admin\AdminTheme` restyles wp-admin, the toolbar (front end
included) and wp-login.php: a 180px black sidebar with pill items and the current section's
submenu inline, floating flyouts for the others, a black toolbar, zinc surfaces, black primary
buttons, 32px controls, rounded list tables and 12px boxes. Sheets in `resources/css/admin/`:

| Sheet | Loads on |
| :--- | :--- |
| `skin.css` | every wp-admin screen. Imports `reset.css`, `_toolbar.css`, `console.css`, in that order |
| `toolbar.css` | the front end, for logged-in users (imports `_toolbar.css`) |
| `login.css` | wp-login.php |
| `_tokens.css` | Tailwind's theme variables plus `tokens.css`, and the `--alma-admin-*` map the screens use |

Rules for changing them:

- Nearly every rule is `!important`, as in the original, so where two tie on specificity the later
  one wins. **The import order in `skin.css` is load-bearing**; a test pins it.
- The skin paints every link in the content area dark and underlined at (1,1,1). An anchor drawn
  as a control (a button, a tab, a linked tile) needs an id in its selector to beat that:
  `screens.css` ends with those rules (`#wpbody-content .alma-admin a.alma-btn…`). A test checks.
- Core adds a `php-error` body class (and a 26px band under the toolbar) whenever an error was
  suppressed with `@`; `console.css` zeroes it.

**The toolbar** keeps one brand menu (Admin console, View live website, and each module's entry:
Leads adds "Leads & submissions") and drops core's menus that repeat the sidebar. Its bell opens a
**notifications drawer** (`resources/views/admin/_notifications.blade.php`): wp-admin's notices
are moved out of the page into it, classified by severity and counted on the bell, dismissible
for the browser session, with "Restore inline" to put them back. A notice that must stay on the
page carries `alma-keep-notice` (the stalled-cron notice does). `.notice.inline` is never moved.

The brand (`config/admin.php`): the name defaults to the site name; the mark is a theme-relative
image (`ADMIN_BRAND_MARK`, default `resources/images/brand/mark.svg`, ideally a square SVG). Until
that file exists, the name's initial on a brand-coloured square stands in. The footer reads
"<Brand> Admin Console" and how the health checks stood the last time the dashboard ran them (it
never runs one itself), and the live release (`APP_VERSION`). ACF's field-group screens are hidden
in production (`ADMIN_ACF_UI`), because field groups are code. Without a build (`public/build`)
there is simply no skin, never an error.

### The dashboard

`App\Infrastructure\WordPress\Admin\Dashboard\Dashboard` removes core's blog widgets and splits
the dashboard into two columns. Modules put their business widgets on the left (Leads: KPIs,
hourly trend, channels, latest leads; see [lead.md](lead.md#wp-admin)); Core puts the platform on
the right:

| Widget | Shows |
| :--- | :--- |
| Modules & Services Status | Every module in `config/alma.php`: active, disabled (`ALMA_DISABLED_MODULES`) or missing its prerequisite; its provider's summary; what it declares (hooks, events, commands, deploy tasks, routes, tables) and any live figures it adds |
| Platform & Infrastructure Health | The environment and release, every health check with its evidence behind "Details", each host the site called in the last 7 days judged from the integration log (down after 3 failures in a row, degraded after 1 or at a 20% failure rate), and the PHP runtime. Managers only; cached 60 seconds |
| Shortcuts & Quick Actions | Core's (view site, add a page, integration log, Site Health), then each module's |

A module contributes through two filters, from a hook class of its own:

```php
add_filter('alma/dashboard/modules', function (array $modules) {
    $modules['quotes']['chips'] = [sprintf('Open: %d', $open)];  // live figures on its card

    return $modules;
});

add_filter('alma/dashboard/shortcuts', fn (array $shortcuts) => [...$shortcuts, [
    'title' => 'Quotes', 'description' => 'Requests waiting for a price',
    'url' => admin_url('admin.php?page=quotes'), 'icon' => '<path d="…"/>',  // Lucide inner SVG
]]);
```

and its own widgets with `wp_add_dashboard_widget(…, 'normal', 'high'|'default')` on
`wp_dashboard_setup` at 999. Widget markup uses the `alma-dash-*` classes in `dashboard.css` (KPI
tiles, funnel, bar charts, stacked channel bar, tables, health rows, shortcut cards), which load
with the screens' sheet, so widgets keep their look with the skin off.

### Screens: the admin design system

alma's own screens (Leads, Redirects, Integration log, AI Access) are Blade views built from
`<x-admin::…>` components (`resources/views/admin/components`), styled by
`resources/css/admin/screens.css` with the console's values. Everything is scoped under
`.alma-admin`, the wrapper `<x-admin::page>` renders, so it loads on every admin screen without
touching core ones, and it works with the skin off.

| Component | For |
| :--- | :--- |
| `<x-admin::page :title :description>` + `actions` slot, or a `heading` slot | Every screen. Renders `.wrap.alma-admin`, the title row (or a record's own header) and the line core moves notices under |
| `<x-admin::tabs :tabs active>` | A section's screens as segmented tabs; `tabs` is key => [label, url] |
| `<x-admin::stat :label :value :hint href active>` + `icon` slot | A KPI tile (in an `.alma-stats-grid`). With `href` it links to the list it counts; `active` marks the filter in use |
| `<x-admin::card :title :description flush>` + `actions` / `footer` slots | A section. `flush` lets a table run edge to edge; the footer holds form buttons |
| `<x-admin::field :label for :hint locked>` | A form row; `locked="ENV_VAR"` notes a value pinned by `.env` |
| `<x-admin::button href variant size type>` | `secondary` (default), `primary`, `danger`, `ghost`; `size="sm"` |
| `<x-admin::badge tone dot>` | `neutral`, `success`, `warning`, `danger`, `info`, `brand`; `dot` draws a status (white, outlined, a dot in the tone) |
| `<x-admin::avatar :initials :tint :logo :size>` | A person: tinted initials, a logo laid over them that removes itself if it fails (see `LeadAvatar`) |
| `<x-admin::pagination :paginator>` | Page n of m with Previous / Next |
| `<x-admin::table>` | A plain data table; inside a `flush` card it loses its own frame |
| `<x-admin::empty :title :description>` | An empty list |
| `<x-admin::notice tone>` | A callout that stays put (core's `.notice` goes to the notifications drawer) |

Classes for what has no component:

- Lists: `.alma-filter-bar` > `.alma-filter-form` with `.alma-search` (`__icon`, `__input`),
  `select.alma-select` and `.alma-count-badge`; `.alma-table-container` > `.alma-data-table` (the
  record list: `__date`, `__age`, `__empty`, `.alma-col-attr` with `.alma-attr__source` /
  `__campaign`); `.alma-contact` (`__info`, `__name`, `__email`, `__sub`, `__company`);
  `.alma-pill` (`--dark`, `--indigo`); `.alma-audit-pill`.
- Records: `.alma-record-header` (`__who`, `__name`, `__meta`); `.alma-detail-grid` (details,
  then `.alma-timeline-col`, which scrolls on its own); `.alma-detail-card` > `.alma-detail-title`;
  `.alma-kv` (`--compact`, `.alma-kv__group`); `.alma-timeline` > `__item` > `__dot--{outcome}` +
  `__content` (`__meta`, `__actor`, `__time`, `__title`); `.alma-json`.
- Forms and layout: `.alma-input` (`--short`, `--code`), `.alma-check`, `.alma-form-grid`,
  `.alma-grid`, `.alma-split`, `.alma-stack`, `.alma-pre`, `.alma-status-dot--{success,warning,danger}`,
  `.alma-muted`, `.alma-faint`.

A paginated list in wp-admin uses `paged`, never Laravel's `page` (that is the screen's slug):
see `LeadsAdmin::paginate()`.

A new module screen:

```blade
{{-- Tools → Quotes: what this screen is for. --}}
<x-admin::page :title="__('Quotes', 'alma')" :description="__('Requests waiting for a price.', 'alma')">
  <x-slot:actions>
    <x-admin::button :href="$exportUrl">{{ __('Export CSV', 'alma') }}</x-admin::button>
  </x-slot:actions>

  <div class="alma-stats-grid">
    <x-admin::stat :label="__('Open', 'alma')" :value="$open" :href="$openUrl" />
  </div>

  <x-admin::card :title="__('Latest', 'alma')" flush>
    <x-admin::table>…</x-admin::table>
  </x-admin::card>
</x-admin::page>
```

`tests/Feature/Admin/AdminScreensTest` fails a screen view that doesn't start from
`<x-admin::page>`, and any PHP that opens its own `.wrap`. Two Blade traps: don't put literal
double quotes inside a component attribute (`:description="__('… "x" …')"`), or Blade skips the
whole tag; and don't print a nonce with `@php(wp_nonce_field(…))` in a view that has an
`@php … @endphp` block after it, because Blade's block extraction runs from the inline `@php` to
that `@endphp`. Use `{!! wp_nonce_field($action, '_wpnonce', true, false) !!}`. The view compile
test catches both.
