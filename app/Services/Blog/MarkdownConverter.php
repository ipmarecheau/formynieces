<?php

namespace App\Services\Blog;

use Illuminate\Support\Str;

/**
 * A small, dependency-free Markdown-to-HTML converter, scoped to the subset used
 * by the blog articles: ATX headings (##/###), paragraphs, unordered and ordered
 * lists, blockquotes, horizontal rules, bold, italic, inline code and links.
 *
 * The project has no league/commonmark; adding a dependency was avoided per the
 * "don't change dependencies without approval" convention. This handles the
 * authored article grammar exactly and escapes everything else as text.
 */
class MarkdownConverter
{
    public function toHtml(string $markdown): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $markdown) ?: [];
        $html = [];
        $paragraph = [];
        /** @var array<int, string> $listItems */
        $listItems = [];
        $listType = null; // 'ul' | 'ol'
        $quoteLines = [];

        $flushParagraph = function () use (&$paragraph, &$html): void {
            if ($paragraph !== []) {
                $html[] = '<p>'.$this->inline(implode(' ', $paragraph)).'</p>';
                $paragraph = [];
            }
        };

        $flushList = function () use (&$listItems, &$listType, &$html): void {
            if ($listItems !== []) {
                $items = array_map(fn (string $item): string => '<li>'.$this->inline($item).'</li>', $listItems);
                $html[] = '<'.$listType.'>'.implode('', $items).'</'.$listType.'>';
                $listItems = [];
                $listType = null;
            }
        };

        $flushQuote = function () use (&$quoteLines, &$html): void {
            if ($quoteLines !== []) {
                $html[] = '<blockquote>'.$this->inline(implode(' ', $quoteLines)).'</blockquote>';
                $quoteLines = [];
            }
        };

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                $flushParagraph();
                $flushList();
                $flushQuote();

                continue;
            }

            if (preg_match('/^(#{2,4})\s+(.*)$/', $trimmed, $m)) {
                $flushParagraph();
                $flushList();
                $flushQuote();
                $level = strlen($m[1]);
                $id = Str::slug($m[2]);
                $html[] = "<h{$level} id=\"{$id}\">".$this->inline($m[2])."</h{$level}>";

                continue;
            }

            if (preg_match('/^(-{3,}|\*{3,})$/', $trimmed)) {
                $flushParagraph();
                $flushList();
                $flushQuote();
                $html[] = '<hr>';

                continue;
            }

            if (preg_match('/^[-*]\s+(.*)$/', $trimmed, $m)) {
                $flushParagraph();
                $flushQuote();
                if ($listType === 'ol') {
                    $flushList();
                }
                $listType = 'ul';
                $listItems[] = $m[1];

                continue;
            }

            if (preg_match('/^\d+\.\s+(.*)$/', $trimmed, $m)) {
                $flushParagraph();
                $flushQuote();
                if ($listType === 'ul') {
                    $flushList();
                }
                $listType = 'ol';
                $listItems[] = $m[1];

                continue;
            }

            if (preg_match('/^>\s?(.*)$/', $trimmed, $m)) {
                $flushParagraph();
                $flushList();
                $quoteLines[] = $m[1];

                continue;
            }

            $flushList();
            $flushQuote();
            $paragraph[] = $trimmed;
        }

        $flushParagraph();
        $flushList();
        $flushQuote();

        return implode("\n", $html);
    }

    /**
     * Inline formatting: escape first, then apply links, bold, italic and code so
     * generated tags survive the escape pass.
     */
    private function inline(string $text): string
    {
        $text = e($text);

        // Links: [label](https://url)
        $text = preg_replace_callback(
            '/\[([^\]]+)\]\(([^)\s]+)\)/',
            function (array $m): string {
                $url = $m[2];
                $external = Str::startsWith($url, ['http://', 'https://']);
                $rel = $external ? ' rel="noopener"' : '';

                return '<a href="'.$url.'"'.$rel.'>'.$m[1].'</a>';
            },
            $text
        ) ?? $text;

        // Bold then italic.
        $text = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $text) ?? $text;
        $text = preg_replace('/(?<!\*)\*([^*]+)\*(?!\*)/', '<em>$1</em>', $text) ?? $text;

        // Inline code.
        $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text) ?? $text;

        return $text;
    }
}
