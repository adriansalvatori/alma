---
name: alma-blocks
description: >-
  Builds and reuses alma's ACF Composer blocks and pattern pages. Use when creating or changing a
  block (app/Blocks), composing or editing a page pattern (patterns/*.php), migrating a design or
  an existing page into blocks, or when the user mentions blocks, sections, patterns, a landing
  page, a hero, or page layout in an alma project.
---

# Alma blocks and pattern pages

Reference: `web/app/themes/alma/docs/blocks.md` (the contract) and `docs/design-system.md` (the
rules). Run commands from the repository root; theme paths below are relative to
`web/app/themes/alma`.

## 1. Reuse before you build

Forking the design system is the most expensive mistake: a fix to a block never reaches a
hand-written copy. Before writing any markup:

1. Read `docs/block-inventory.md` (generated: every block, what it renders, its fields, where
   it's used).
2. Confirm in source: `grep -rn "acf/" patterns/` shows how real pages compose blocks.
3. A variant of an existing block is an **option** on that block, defaulting to current
   behaviour, never a second block.
4. Only then create a block. Re-check per section, not per page.

## 2. Making a block

```bash
wp acorn acf:block "Pricing Table"      # stubs already follow the contract
```

The contract (enforced by `tests/Feature/Conventions`; the build fails without it):

- Extend `App\Blocks\Block`. Fallback copy lives **only** in `public const DEFAULTS`; each field's
  `default_value` points at it, and `with()` reads through `$this->value('key')`.
- Fallbacks are explicit (`null`/`''`/`false` → default). Never `?:`, which turns `0` into the default.
- Links are `addText` (relative paths and `#anchors` are valid; `addUrl` rejects them).
- Numeric choice keys use pair form: `[['3' => 'Three across'], ['2' => 'Two']]`.
- Repeaters with preset rows use `repeaterDefaults()` (`HasRepeaterDefaults`), so the sidebar
  shows the rows the page renders.
- `$example` holds preview data (acf-composer wraps it into `attributes.data` itself).
- The view `resources/views/blocks/<slug>.blade.php` opens with a one-line Blade comment saying
  what it renders (the inventory reads it). Use the canonical container (`container-page`),
  design tokens, and `<img>` with width and height.

Then regenerate the inventory: `wp acorn alma:blocks:inventory`.

## 3. Pages are patterns

A marketing page is `patterns/<slug>.php` (header: Title, Slug `alma/<slug>`, Categories,
Inserter: no). The page's content is one line: `<!-- wp:pattern {"slug":"alma/<slug>"} /-->`.

- Build block markup with `App\Support\BlockMarkup` (it writes ACF field keys and repeater
  encoding correctly; a raw array silently falls back to presets).
- Inline markup in a pattern is a last resort and needs `// @bespoke: <blocks checked, why none
  fit>` above it; the reuse test fails otherwise.
- Shared partials that aren't patterns go in `resources/patterns/` (core rejects headerless files
  in `patterns/`).
- Pattern pages are **code-owned**: the editor shows a notice, and the save guard keeps the
  reference. To edit a page in wp-admin, use **Detach from pattern** (it becomes database content).
- List pages that must exist in `config/content.php`; `alma:deploy` creates missing ones.

## 4. Verify

```bash
cd web/app/themes/alma
npm run build                     # Tailwind only compiles classes it can see in the @source globs
vendor/bin/pest tests/Feature/Conventions tests/Feature/Content
```

Then open the page on the local site at desktop and mobile widths. Check that a section is
actually visible, and sanity-check its geometry, not just the screenshot.
