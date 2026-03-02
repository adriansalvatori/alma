# Native Single-File Block Guidelines

This project uses a custom, high-performance system for building WordPress Gutenberg blocks using a single `.blade.php` file. This eliminates the dependency on ACF Pro and streamlines development.

## 🛠 Creating a New Block

Use the provided Artisan/WP-CLI command to scaffold a new block:

```bash
wp acorn make:block MyNewBlock
```

This creates a file at `resources/views/blocks/my-new-block.blade.php`.

## 📁 File Structure

A block is defined by a single Blade file. It consists of three parts:

### 1. JSON Frontmatter (The Config)
At the very top of the file, inside a Blade comment `{{-- ... --}}`, define the block metadata and attributes.

```blade
{{--
{
    "name": "alma/my-block",
    "title": "My Block",
    "category": "alma",
    "icon": "star-filled",
    "attributes": {
        "title": {
            "type": "string",
            "control": "TextControl",
            "label": "Block Title"
        }
    }
}
--}}
```

**Supported Controls:**
- `TextControl` (Simple text)
- `TextareaControl` (Multi-line text)
- `ToggleControl` (Boolean switch)
- `SelectControl` (Dropdown, requires `options` array)
- `ImageControl` (Native WP Media Library selector)

### 2. PHP Initialization
Immediately after the comment, use a `@php` block to extract your attributes for cleaner usage in the template.

```blade
@php
    $title = $attributes['title'] ?? 'Default Title';
@endphp
```

### 3. HTML/Blade Template
Write your standard Blade/Tailwind markup. The block is rendered natively in both the Editor (via ServerSideRender) and the Frontend.

## 🚀 Build Process

When you create or modify a block's frontmatter, you **must** run the build system to generate the corresponding `block.json`:

```bash
npm run dev   # For local development (watches for changes)
npm run build # For production
```

## ⚠️ Important Rules
1. **No manual `block.json`**: These are auto-generated in `resources/.blocks/`.
2. **No `edit.jsx` needed**: Sidebar controls are dynamically generated from your frontmatter.
3. **Namespace**: Always use the `alma/` prefix for block names.
