# Blocks and pattern pages

How pages are built in Alma: **pages are patterns, patterns are made of blocks, blocks are ACF
Composer classes with Blade views.** Everything here is enforced by the tests in
`tests/Feature/Conventions`, so a mistake fails CI rather than reaching an editor.

## Reuse before you build

Before writing markup, check [`block-inventory.md`](block-inventory.md). It's generated from the
code and lists every block, what it renders, its fields and which patterns use it. Check it
**per section**, not per project: a block that was wrong for one page is often right for the next.

A design that is a *variant* of an existing block is not grounds for a second block. Add an
option to the existing one and default it to the current behaviour (`Hero` has four `layout`s;
`FeaturesGrid` has `columns` and `feature_first`).

Regenerate the inventory after adding or changing a block or pattern:

```bash
wp acorn alma:blocks:inventory          # writes docs/block-inventory.md
wp acorn alma:blocks:inventory --check  # what CI runs
```

## Making a block

```bash
wp acorn acf:block PricingTable
```

The generator uses `stubs/acf-composer/`, so the class and view it writes already follow the
contract below and pass the convention tests. Fill in the fields, write the view, regenerate the
inventory.

### The block contract

| Rule | Why | Enforced by |
| :--- | :--- | :--- |
| Extend `App\Blocks\Block` | Shared defaults (category, full-width alignment, anchors) and the `value()` / `rows()` helpers | — |
| `DEFAULTS` holds every piece of fallback copy, and each field's `default_value` points at it | The sidebar shows what the block renders. An untouched block and an empty field render the same thing | `BlockFieldDefaultsTest` |
| Read fields with `$this->value('key')` and `$this->rows('key')`, never `get_field() ?: ...` | `?:` also swallows `0` and `'0'`: on Remote Leverage that rendered a 0% progress bar as 51% | review |
| Repeaters that render preset rows use `HasRepeaterDefaults` and declare them in `repeaterDefaults()` | ACF has no repeater default. Without this the sidebar is empty while the page shows three cards | `BlockRepeaterDefaultsTest` |
| An emptied repeater stays empty | `rows()` only falls back when nothing was ever saved (`null`) | `BlockRepeaterDefaultsTest` |
| Links are `addText`, not `addUrl` | ACF's URL field rejects `/pricing/` and `#contact` | `BlocksTest` |
| Numeric choice keys use pair form: `[['3' => 'Three']]` | acf-builder drops plain numeric keys and stores the label | `BlockFieldDefaultsTest` |
| The field group is `Builder::make('{slug_with_underscores}_block')` | `BlockMarkup` reads field keys from it to write pattern data | `BlocksTest` |
| `$example` holds preview data | The inserter preview | `BlocksTest` |
| The view opens with `{{-- Name: what it renders --}}` | The inventory's "What it renders" column | `BlocksTest` |
| Images are `return_format => 'id'`, rendered with `ResponsiveImage::html()` | srcset, sizes, width and height come from core; WebP sub-sizes from `MediaDefaults` | `ViewsTest` (img dimensions) |
| Sections use `container-page` and `py-section` | One container width and one section rhythm site-wide | `docs/design-system.md` |

## Pages are patterns

A page's content in the database is one line:

```html
<!-- wp:pattern {"slug":"alma/home"} /-->
```

The markup lives in `patterns/home.php`, in git. A fix to a pattern reaches every page using it on
the next deploy, and a database refresh never loses a page.

```php
<?php
/**
 * Title: Home
 * Slug: alma/home
 * Categories: alma-pages
 * Inserter: no
 */

use App\Blocks\Hero;
use App\Support\BlockMarkup;
?>
<?= BlockMarkup::block(Hero::class, ['headline' => 'Hello']) ?>
<?= BlockMarkup::block('lead-form', [], ['anchor' => 'contact']) ?>
```

- **Only pass what differs from the block's presets.** A block renders its `DEFAULTS` for
  everything else.
- **`BlockMarkup` writes ACF's storage format for you.** That means a field-key reference beside
  every value, and repeaters flattened. ACF silently ignores values without keys, which is how a
  hand-written page ships the presets while looking wired up. An unknown field name throws.
- **Categories:** `alma-pages` for whole pages (never offered in the inserter: inserting one copies
  it, and the copy stops getting fixes). `alma-sections` for groups of blocks an editor may insert.
- **Shared partials** that aren't patterns go in `resources/patterns/`. WordPress scans
  `patterns/` and rejects files without a header.
- **A broken pattern file doesn't take the site down.** `ThemePatterns` loads each file on its
  own, logs and skips one that throws, and the rest still register. CI also runs `php -l` over
  `patterns/`.
- **Inline markup in a pattern is the last resort.** Its copy can't be edited, and it never gets
  a fix made to the block it imitates. A loop that hand-builds cards (an image and a heading per
  item) must carry a `// @bespoke: <blocks checked, why none fit>` comment in the 12 lines above
  it (`PatternsTest`).

### Creating pattern pages

List them in `config/content.php` → `pages`. The `EnsurePatternPages` deploy task creates any
that are missing. It never overwrites an existing page, and it sets the front page only when
none is set. `bin/setup.sh` creates Home the same way on a fresh install.

### Editing a pattern page in wp-admin

Until the page-copy overlay is ported (planned as an optional module), pattern pages are
**code-owned**:

- The editor shows a notice naming the pattern file. Title, slug, featured image and SEO fields
  save normally. Changes to the blocks don't: `PatternPageEditing` keeps the reference on every
  block-editor save, because core expands the pattern on open and the next save would otherwise
  write the expanded blocks back for good.
- **Detach from pattern and edit here** turns the page into normal database content, and records
  which pattern it came from (`_alma_detached_from_pattern`). Later changes to the pattern no
  longer reach it. To bring it back, move its content into a pattern file and set the page's
  content to the reference again.
- Server-side writers (WP-CLI, MCP abilities) aren't guarded. They may expand a page on purpose.

## The editor on pages

`Editor\PageChrome` offers every `acf/*` block plus the core blocks listed in
`config/content.php` → `page_inserter`. Posts keep the full inserter. Blocks are hidden from the
inserter, not disallowed: disallowing a block type stops the editor from recovering an invalid
block of that type.

## Rendering helpers

| Helper | Use |
| :--- | :--- |
| `BlockMarkup::block($classOrSlug, $data, $attributes)` | Block markup for patterns |
| `Pattern::render('alma/slug')` | Render a registered pattern from Blade |
| `ResponsiveImage::html($id, $size, $attrs)` | Media-library image with srcset/sizes/dimensions; pass `['loading' => 'eager', 'fetchpriority' => 'high']` for the LCP image |
| `Assets::img('page/file.jpg', $alt)` | Theme image built from `resources/images/pages`, with its WebP sibling and real dimensions |
| `DocumentOutline::build($html)` | Heading anchors and a table of contents |
| `ReadingTime::minutes($content)` | Estimated reading time |
