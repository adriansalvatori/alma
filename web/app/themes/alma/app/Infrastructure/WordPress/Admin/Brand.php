<?php

namespace App\Infrastructure\WordPress\Admin;

/**
 * The project's name and mark, as the toolbar and the login page show them (config/admin.php).
 *
 * Only identity lives here. Colour and type come from the design tokens, so the generated mark
 * (the name's initial) is a plain element the stylesheets paint with the brand colour.
 */
class Brand
{
    public function name(): string
    {
        $name = trim((string) (config('admin.brand.name') ?: get_bloginfo('name')));

        return $name !== '' ? $name : 'Alma';
    }

    public function initial(): string
    {
        return mb_strtoupper(mb_substr($this->name(), 0, 1));
    }

    /**
     * The configured mark's URL, or null when none is configured or the file doesn't exist.
     * The path must stay inside the theme.
     */
    public function markUrl(): ?string
    {
        $path = ltrim((string) config('admin.brand.mark'), '/');

        if ($path === '' || str_contains($path, '..') || ! is_file(get_theme_file_path($path))) {
            return null;
        }

        return get_theme_file_uri($path);
    }

    /**
     * The mark: the configured image, or the initial on a brand square. Decorative: the name
     * always sits next to it.
     */
    public function markHtml(): string
    {
        $url = $this->markUrl();

        if ($url !== null) {
            return '<span class="alma-brand-mark has-image" aria-hidden="true"><img src="'.esc_url($url).'" alt=""></span>';
        }

        return '<span class="alma-brand-mark" aria-hidden="true">'.esc_html($this->initial()).'</span>';
    }

    /**
     * The mark followed by the name.
     */
    public function html(): string
    {
        return $this->markHtml().'<span class="alma-brand-name">'.esc_html($this->name()).'</span>';
    }
}
