<?php

declare(strict_types=1);

namespace ExtensionBuilder\ExtensionBuilderTypo3\Tools;

/**
 * Lightweight Markdown-to-HTML renderer.
 *
 * Replaces league/commonmark for internal documentation rendering.
 * Supports: headings (h1–h6), bold, italic, inline code, code blocks,
 * blockquotes, unordered/ordered lists, horizontal rules, links, images,
 * GFM tables, and heading-permalink anchors.
 *
 * Migration:
 * - Target: ExtensionBuilder Core 1.x
 * - Status: legacy
 *
 * @extensionbuilderCoreMajorVersion 0
 * @extensionbuilderMigrationStatus legacy
 *
 * @since 0.14
 */

final class MarkdownRenderer
{
    private bool $headingPermalinks;
    private string $headingHtmlClass;

    /**
     * @since 0.14
     */
    public function __construct(
        array $options = [],
    ) {
        $this->headingPermalinks = (bool)($options['heading_permalink'] ?? true);
        $this->headingHtmlClass  = (string)($options['heading_html_class'] ?? 'documentation-heading-permalink');
    }

    // -------------------------------------------------------------------------
    //  Public API (mirrors league/commonmark MarkdownConverter::convert())
    // -------------------------------------------------------------------------

    /**
     * @since 0.14
     */
    public function convert(
        string $markdown,
    ): string {
        $html = $this->parse($markdown);
        return $html;
    }

    // -------------------------------------------------------------------------
    //  Parsing pipeline
    // -------------------------------------------------------------------------

    /**
     * @since 0.14
     */
    private function parse(
        string $markdown,
    ): string {
        // Normalise line endings
        $text = str_replace(["\r\n", "\r"], "\n", $markdown);

        // Strip unsafe HTML (mirrors html_input: strip)
        $text = strip_tags(
            $text,
            '<p><br><em><strong><code><pre><a><img><table><thead><tbody><tr><th><td><blockquote><ul><ol><li><hr>'
        );

        // Split into blocks and process
        $blocks = $this->splitBlocks($text);
        $output = '';
        foreach ($blocks as $block) {
            $output .= $this->renderBlock($block);
        }
        return $output;
    }

    /**
     * Split markdown into top-level block units (fenced code, tables, blank-
     * separated paragraphs, lists, blockquotes, headings, hr).
     *
     * @since 0.14
     */
    private function splitBlocks(
        string $text,
    ): array {
        $lines  = explode("\n", $text);
        $blocks = [];
        $buffer = [];

        $flush = function () use (&$buffer, &$blocks): void {
            if ($buffer !== []) {
                $blocks[] = implode("\n", $buffer);
                $buffer   = [];
            }
        };

        $inFence = false;
        $fence   = '';

        foreach ($lines as $line) {
            // Fenced code block toggle
            if (!$inFence && preg_match('/^(`{3,}|~{3,})/', $line, $m)) {
                $flush();
                $inFence = true;
                $fence   = $m[1];
                $buffer[] = $line;
                continue;
            }
            if ($inFence) {
                $buffer[] = $line;
                if (str_starts_with(rtrim($line), $fence)) {
                    $inFence = false;
                    $flush();
                }
                continue;
            }

            // Blank line = block separator
            if (trim($line) === '') {
                $flush();
                continue;
            }

            $buffer[] = $line;
        }
        $flush();

        return $blocks;
    }

    /**
     * @since 0.14
     */
    private function renderBlock(
        string $block,
    ): string {
        $lines = explode("\n", rtrim($block));
        $first = $lines[0];

        // ── Fenced code block ──────────────────────────────────────────────
        if (preg_match('/^(`{3,}|~{3,})(\w*)/', $first, $m)) {
            $lang    = htmlspecialchars($m[2], ENT_QUOTES);
            $content = implode("\n", array_slice($lines, 1));
            // Remove closing fence
            $content = preg_replace('/\n?(`{3,}|~{3,})\s*$/', '', $content);
            $attr    = $lang ? " class=\"language-{$lang}\"" : '';
            return "<pre><code{$attr}>" . htmlspecialchars($content, ENT_QUOTES) . "</code></pre>\n";
        }

        // ── ATX Heading ───────────────────────────────────────────────────
        if (preg_match('/^(#{1,6})\s+(.+)$/', $first, $m)) {
            $level   = strlen($m[1]);
            $text    = $this->renderInline($m[2]);
            $id      = $this->slugify(strip_tags($text));
            $anchor  = $this->headingPermalinks
                ? "<a id=\"{$id}\" class=\"{$this->headingHtmlClass}\"></a>"
                : '';
            return "<h{$level}>{$anchor}{$text}</h{$level}>\n";
        }

        // ── Setext Heading (underline style) ──────────────────────────────
        if (count($lines) >= 2) {
            $under = $lines[1];
            if (preg_match('/^=+\s*$/', $under)) {
                $text = $this->renderInline($lines[0]);
                $id   = $this->slugify(strip_tags($text));
                $a    = $this->headingPermalinks ? "<a id=\"{$id}\" class=\"{$this->headingHtmlClass}\"></a>" : '';
                return "<h1>{$a}{$text}</h1>\n";
            }
            if (preg_match('/^-+\s*$/', $under)) {
                $text = $this->renderInline($lines[0]);
                $id   = $this->slugify(strip_tags($text));
                $a    = $this->headingPermalinks ? "<a id=\"{$id}\" class=\"{$this->headingHtmlClass}\"></a>" : '';
                return "<h2>{$a}{$text}</h2>\n";
            }
        }

        // ── Horizontal rule ───────────────────────────────────────────────
        if (preg_match('/^(\*{3,}|-{3,}|_{3,})\s*$/', $first)) {
            return "<hr />\n";
        }

        // ── Blockquote ────────────────────────────────────────────────────
        if (str_starts_with($first, '>')) {
            $inner = implode("\n", array_map(
                fn(string $l) => preg_replace('/^>\s?/', '', $l),
                $lines
            ));
            return "<blockquote>\n" . $this->parse($inner) . "</blockquote>\n";
        }

        // ── GFM Table ─────────────────────────────────────────────────────
        if (count($lines) >= 2 && preg_match('/^\|/', $first) && preg_match('/^\|[-| :]+\|/', $lines[1])) {
            return $this->renderTable($lines);
        }

        // ── Unordered list ────────────────────────────────────────────────
        if (preg_match('/^[-*+]\s/', $first)) {
            return $this->renderList($lines, false);
        }

        // ── Ordered list ──────────────────────────────────────────────────
        if (preg_match('/^\d+\.\s/', $first)) {
            return $this->renderList($lines, true);
        }

        // ── Paragraph ─────────────────────────────────────────────────────
        $text = implode("\n", $lines);
        return '<p>' . $this->renderInline($text) . "</p>\n";
    }

    // -------------------------------------------------------------------------
    //  Block helpers
    // -------------------------------------------------------------------------

    /**
     * @since 0.14
     */
    private function renderTable(
        array $lines,
    ): string {
        $parseRow = fn(string $line): array => array_map(
            'trim',
            array_slice(explode('|', $line), 1, -1)
        );

        $headers  = $parseRow($lines[0]);
        // $lines[1] is the separator row – skip it
        $bodyRows = array_slice($lines, 2);

        $html = "<table>\n<thead>\n<tr>\n";
        foreach ($headers as $h) {
            $html .= '<th>' . $this->renderInline($h) . "</th>\n";
        }
        $html .= "</tr>\n</thead>\n<tbody>\n";
        foreach ($bodyRows as $row) {
            $cols = $parseRow($row);
            $html .= "<tr>\n";
            foreach ($cols as $c) {
                $html .= '<td>' . $this->renderInline($c) . "</td>\n";
            }
            $html .= "</tr>\n";
        }
        $html .= "</tbody>\n</table>\n";
        return $html;
    }

    /**
     * @since 0.14
     */
    private function renderList(
        array $lines,
        bool $ordered,
    ): string {
        $tag   = $ordered ? 'ol' : 'ul';
        $regex = $ordered ? '/^\d+\.\s+/' : '/^[-*+]\s+/';
        $html  = "<{$tag}>\n";
        foreach ($lines as $line) {
            $content = preg_replace($regex, '', $line);
            $html   .= '<li>' . $this->renderInline((string)$content) . "</li>\n";
        }
        $html .= "</{$tag}>\n";
        return $html;
    }

    // -------------------------------------------------------------------------
    //  Inline rendering
    // -------------------------------------------------------------------------

    /**
     * @since 0.14
     */
    private function renderInline(
        string $text,
    ): string {
        // Inline code (before everything else to protect contents)
        $placeholders = [];
        $text = preg_replace_callback('/`([^`]+)`/', function (array $m) use (&$placeholders): string {
            $key = "\x00CODE" . count($placeholders) . "\x00";
            $placeholders[$key] = '<code>' . htmlspecialchars($m[1], ENT_QUOTES) . '</code>';
            return $key;
        }, $text);

        // Bold + italic  ***text***  or  ___text___
        $text = preg_replace('/(\*{3}|_{3})(.+?)\1/', '<strong><em>$2</em></strong>', $text);
        // Bold  **text**  or  __text__
        $text = preg_replace('/(\*{2}|_{2})(.+?)\1/', '<strong>$2</strong>', $text);
        // Italic  *text*  or  _text_
        $text = preg_replace('/(\*|_)(.+?)\1/', '<em>$2</em>', $text);

        // Images  ![alt](url)
        $text = preg_replace_callback(
            '/!\[([^\]]*)\]\(([^)]+)\)/',
            function (array $m): string {
                $alt = htmlspecialchars($m[1], ENT_QUOTES);
                $src = $this->sanitizeUrl($m[2]);
                return "<img src=\"{$src}\" alt=\"{$alt}\" />";
            },
            $text
        );

        // Links  [label](url)
        $text = preg_replace_callback(
            '/\[([^\]]+)\]\(<([^>]+)>\)/',
            fn(array $m) => '<a href="' . $this->sanitizeUrl($m[2]) . '">' . $m[1] . '</a>',
            $text
        );
        $text = preg_replace_callback(
            '/\[([^\]]+)\]\(([^)]+)\)/',
            fn(array $m) => '<a href="' . $this->sanitizeUrl($m[2]) . '">' . $m[1] . '</a>',
            $text
        );

        // Restore code placeholders
        $text = strtr($text, $placeholders);

        // Hard line break  (two trailing spaces + newline)
        $text = preg_replace('/  \n/', "<br />\n", $text);

        return $text;
    }

    // -------------------------------------------------------------------------
    //  Utilities
    // -------------------------------------------------------------------------

    /**
     * @since 0.14
     */
    private function slugify(
        string $text,
    ): string {
        $slug = strtolower(trim($text));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        return trim((string)$slug, '-');
    }

    /**
     * Sanitise a URL: reject javascript: and data: schemes (mirrors
     * league/commonmark allow_unsafe_links: false).
     *
     * @since 0.14
     */
    private function sanitizeUrl(
        string $url,
    ): string {
        $url = trim($url);
        if (preg_match('/^(javascript|data|vbscript):/i', $url)) {
            return '#';
        }
        return htmlspecialchars($url, ENT_QUOTES);
    }

}