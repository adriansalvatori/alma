<?php

namespace App\Domains\Ai\Support;

/**
 * Turns the plain text or light Markdown an agent writes into core block markup, so a drafted
 * post opens in the block editor as real blocks instead of one Classic block.
 *
 * Deliberately small: headings, paragraphs, bulleted and numbered lists, bold, italic, inline
 * code and links. Everything is HTML-escaped first, so an agent cannot inject markup through it.
 */
class MarkdownBlocks
{
    public function convert(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", trim($text));

        if ($text === '') {
            return '';
        }

        $blocks = [];

        foreach (preg_split('/\n\s*\n/', $text) as $chunk) {
            $lines = array_values(array_filter(array_map('rtrim', explode("\n", trim($chunk))), fn ($line) => $line !== ''));

            if ($lines === []) {
                continue;
            }

            $blocks[] = match (true) {
                count($lines) === 1 && preg_match('/^(#{1,6})\s+(.+)$/', $lines[0], $heading) === 1 => $this->heading(strlen($heading[1]), $heading[2]),
                $this->allMatch($lines, '/^[-*]\s+/') => $this->list($lines, '/^[-*]\s+/', false),
                $this->allMatch($lines, '/^\d+[.)]\s+/') => $this->list($lines, '/^\d+[.)]\s+/', true),
                default => $this->paragraph(implode(' ', array_map('trim', $lines))),
            };
        }

        return implode("\n\n", $blocks);
    }

    public function inline(string $text): string
    {
        $html = esc_html($text);

        // Links first, so the URL is not mangled by the emphasis rules.
        $html = preg_replace_callback('/\[([^\]]+)\]\(([^)\s]+)\)/', function (array $match) {
            $url = html_entity_decode($match[2]);

            if (! preg_match('#^(https?://|mailto:|/|\#)#i', $url)) {
                return $match[0];
            }

            return '<a href="'.esc_url($url).'">'.$match[1].'</a>';
        }, $html);

        $html = preg_replace('/`([^`]+)`/', '<code>$1</code>', $html);
        $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);
        $html = preg_replace('/(?<![*\w])\*(?!\s)(.+?)(?<!\s)\*(?![*\w])/', '<em>$1</em>', $html);

        return (string) preg_replace('/(?<![_\w])_(?!\s)(.+?)(?<!\s)_(?![_\w])/', '<em>$1</em>', $html);
    }

    private function heading(int $level, string $text): string
    {
        // Core's heading block defaults to h2, and omits the attribute when the level is the default.
        $attributes = $level === 2 ? '' : ' {"level":'.$level.'}';

        return "<!-- wp:heading{$attributes} -->\n<h{$level} class=\"wp-block-heading\">".$this->inline($text)."</h{$level}>\n<!-- /wp:heading -->";
    }

    private function paragraph(string $text): string
    {
        return "<!-- wp:paragraph -->\n<p>".$this->inline($text)."</p>\n<!-- /wp:paragraph -->";
    }

    /**
     * @param  list<string>  $lines
     */
    private function list(array $lines, string $marker, bool $ordered): string
    {
        $tag = $ordered ? 'ol' : 'ul';
        $attributes = $ordered ? ' {"ordered":true}' : '';
        $items = array_map(
            fn (string $line) => "<!-- wp:list-item -->\n<li>".$this->inline((string) preg_replace($marker, '', $line))."</li>\n<!-- /wp:list-item -->",
            $lines,
        );

        return "<!-- wp:list{$attributes} -->\n<{$tag} class=\"wp-block-list\">".implode("\n", $items)."</{$tag}>\n<!-- /wp:list -->";
    }

    /**
     * @param  list<string>  $lines
     */
    private function allMatch(array $lines, string $pattern): bool
    {
        foreach ($lines as $line) {
            if (preg_match($pattern, $line) !== 1) {
                return false;
            }
        }

        return true;
    }
}
