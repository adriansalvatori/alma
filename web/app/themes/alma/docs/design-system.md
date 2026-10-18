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

## Admin

wp-admin follows the same tokens: a project rebrands its admin by changing `tokens.css` (brand
scale, font) and `config/admin.php` (name, mark), never by editing admin CSS.

### The skin (`ADMIN_THEME`, on by default)

`App\Infrastructure\WordPress\Admin\AdminTheme` restyles wp-admin, the toolbar (front end
included) and wp-login.php: a dark sidebar and toolbar, neutral surfaces, the brand on primary
buttons, focus rings, the current menu item and the login page. Sheets in `resources/css/admin/`:

| Sheet | Loads on |
| :--- | :--- |
| `skin.css` (imports `_toolbar.css`) | every wp-admin screen |
| `toolbar.css` | the front end, for logged-in users |
| `login.css` | wp-login.php |
| `_tokens.css` | imported by all of them: Tailwind's theme variables plus `tokens.css`, mapped to `--alma-admin-*` |

Rules for changing them:

- Colour goes through core's own properties (`--wp-admin-theme-color*`, set from the brand), so
  core's buttons, links and focus rings follow without being restyled.
- Every other selector is core's own with `body.wp-admin` (or `html`) in front. That outranks core
  and every admin colour scheme regardless of load order. **No `!important`**: a test fails on it.
- Geometry stays core's (menu widths, folded states, breakpoints), so a WordPress update can't
  strand the layout. Change colour, type, radius and surfaces only.
- Content-area rules skip the block editor (`:not(.block-editor-page)`), which has its own design
  system.

The brand (`config/admin.php`): the name defaults to the site name; the mark is a theme-relative
image (`ADMIN_BRAND_MARK`, default `resources/images/brand/mark.svg`, ideally a square SVG). Until
that file exists, the name's initial on a brand-coloured square stands in. The footer shows the
live release (`APP_VERSION`). ACF's field-group screens are hidden in production (`ADMIN_ACF_UI`),
because field groups are code. Without a build (`public/build`) there is simply no skin, never an
error.

### Screens: the admin design system

alma's own screens (Leads, Redirects, Integration log, AI Access) are Blade views built from
`<x-admin::…>` components (`resources/views/admin/components`), styled by
`resources/css/admin/screens.css`. Everything is scoped under `.alma-admin`, the wrapper
`<x-admin::page>` renders, so it loads on every admin screen without touching core ones, and it
works with the skin off.

| Component | For |
| :--- | :--- |
| `<x-admin::page :title :description>` + `<x-slot:actions>` | Every screen. Renders `.wrap.alma-admin`, the title row and the line core moves notices under |
| `<x-admin::card :title :description flush>` + `actions` / `footer` slots | A section. `flush` lets a table run edge to edge; the footer holds form buttons |
| `<x-admin::field :label for :hint locked>` | A form row; `locked="ENV_VAR"` notes a value pinned by `.env` |
| `<x-admin::button href variant size type>` | `secondary` (default), `primary`, `danger`, `ghost`; `size="sm"` |
| `<x-admin::badge tone>` | `neutral`, `success`, `warning`, `danger`, `info`, `brand` |
| `<x-admin::stat :label :value :hint>` | A KPI tile (in an `.alma-grid`) |
| `<x-admin::table>` | A plain data table; inside a `flush` card it loses its own frame |
| `<x-admin::empty :title :description>` | An empty list |
| `<x-admin::notice tone>` | A callout that stays put (core's `.notice` is moved under the title) |

Classes for what has no component: `.alma-input` (`--short`, `--code`), `.alma-check`,
`.alma-form-grid`, `.alma-kv` (a key-value table), `.alma-grid`, `.alma-split` (main + aside),
`.alma-stack`, `.alma-pre`, `.alma-status-dot--{success,warning,danger}`. `WP_List_Table`
inside `.alma-admin` gets the card look automatically.

A new module screen:

```blade
{{-- Tools → Quotes: what this screen is for. --}}
<x-admin::page :title="__('Quotes', 'alma')" :description="__('Requests waiting for a price.', 'alma')">
  <x-slot:actions>
    <x-admin::button :href="$exportUrl">{{ __('Export CSV', 'alma') }}</x-admin::button>
  </x-slot:actions>

  <div class="alma-grid">
    <x-admin::stat :label="__('Open', 'alma')" :value="$open" />
  </div>

  <x-admin::card :title="__('Latest', 'alma')" flush>
    <x-admin::table>…</x-admin::table>
  </x-admin::card>
</x-admin::page>
```

`tests/Feature/Admin/AdminScreensTest` fails a screen view that doesn't start from
`<x-admin::page>`, and any PHP that opens its own `.wrap`. Don't put literal double quotes inside
a component attribute (`:description="__('… "x" …')"`): Blade then skips the whole tag. The view
compile test catches it.

