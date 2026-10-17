# 0006. Marketing pages are block patterns in git, owned by code

- **Status:** accepted (the page-copy overlay is deferred)
- **Date:** 2026-10-17

## Context

Page content stored as expanded block markup in `post_content` drifts from the design system and is lost on a database refresh. RL moved pages into `patterns/<slug>.php`, with the page holding a single `<!-- wp:pattern {"slug":"…"} /-->` reference. The block editor then expanded that reference on open and wrote the expansion back on save. RL measured a non-content save turning the reference into 835 bytes of markup. RL later added a copy overlay (edits stored as page meta laid over the pattern), but that is about 3,000 lines tied to ACF and Gutenberg internals.

## Decision

Pages are patterns in `patterns/`, loaded by a safe loader that skips and logs a broken file instead of failing the site. A save guard keeps a bare pattern reference from being replaced by its expansion. Without the overlay, pattern pages are code-owned: the editor says so, and an explicit **Detach from pattern** turns a page into database content. The copy overlay is a later, optional module.

## Consequences

Layout and copy for pattern pages change through git and deploys; marketing edits a detached page or a database page. When the overlay lands, pattern pages become copy-editable without detaching.
