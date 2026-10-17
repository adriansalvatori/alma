# Design system

The rules every block, pattern and template follows. Tokens live in `resources/css/app.css`
(`@theme`); the editor palette in `theme.json` mirrors them, and
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
4. **One brand hue.** `brand-50` … `brand-950`. Change the project's colour in one place:
   Flux takes its accent from `--color-accent`, which points at the brand scale. Neutrals are
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
  `--font-sans`, and list the one or two files used above the fold in `config/content.php` →
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
