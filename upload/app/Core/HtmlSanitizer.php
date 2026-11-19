<?php

declare(strict_types=1);

final class HtmlSanitizer
{
    private const ALLOWED_TAGS = [
        'a', 'b', 'blockquote', 'br', 'code', 'del', 'em', 'h2', 'h3', 'h4',
        'i', 'li', 'ol', 'p', 'pre', 's', 'strong', 'u', 'ul',
    ];

    private const DROP_CONTENT_TAGS = [
        'audio', 'button', 'canvas', 'embed', 'form', 'iframe', 'input', 'math',
        'object', 'script', 'select', 'style', 'svg', 'template', 'textarea',
        'video',
    ];

    public static function sanitizeArticleContent(string $content): string
    {
        if (trim($content) === '') {
            return '';
        }

        if (!class_exists(DOMDocument::class)) {
            return self::escapeAsText($content);
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previousErrorMode = libxml_use_internal_errors(true);

        try {
            $html = '<!doctype html><html><head><meta charset="utf-8"></head><body>'
                . '<div id="news-hub-article-content">' . $content . '</div>'
                . '</body></html>';

            if (!$document->loadHTML($html, LIBXML_NONET)) {
                return self::escapeAsText($content);
            }

            $container = $document->getElementsByTagName('div')->item(0);
            if (!$container instanceof DOMElement) {
                return self::escapeAsText($content);
            }

            self::sanitizeChildren($container);

            $safeHtml = '';
            foreach ($container->childNodes as $child) {
                $serialized = $document->saveHTML($child);
                if (is_string($serialized)) {
                    $safeHtml .= $serialized;
                }
            }

            return $safeHtml;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorMode);
        }
    }

    private static function sanitizeChildren(DOMNode $parent): void
    {
        $children = [];
        foreach ($parent->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            if ($child instanceof DOMComment || $child instanceof DOMProcessingInstruction) {
                $parent->removeChild($child);
                continue;
            }

            if (!$child instanceof DOMElement) {
                continue;
            }

            $tagName = strtolower($child->tagName);
            if (in_array($tagName, self::DROP_CONTENT_TAGS, true)) {
                $parent->removeChild($child);
                continue;
            }

            self::sanitizeChildren($child);

            if (!in_array($tagName, self::ALLOWED_TAGS, true)) {
                while ($child->firstChild !== null) {
                    $parent->insertBefore($child->firstChild, $child);
                }
                $parent->removeChild($child);
                continue;
            }

            self::sanitizeAttributes($child, $tagName);
        }
    }

    private static function sanitizeAttributes(DOMElement $element, string $tagName): void
    {
        $href = $tagName === 'a' && $element->hasAttribute('href')
            ? trim($element->getAttribute('href'))
            : null;
        $title = $tagName === 'a' && $element->hasAttribute('title')
            ? $element->getAttribute('title')
            : null;

        while ($element->attributes->length > 0) {
            $attribute = $element->attributes->item(0);
            if ($attribute === null) {
                break;
            }
            $element->removeAttributeNode($attribute);
        }

        if ($href !== null && self::isSafeHref($href)) {
            $element->setAttribute('href', $href);
        }

        if ($title !== null) {
            $element->setAttribute('title', $title);
        }
    }

    private static function isSafeHref(string $href): bool
    {
        if ($href === ''
            || str_starts_with($href, '//')
            || preg_match('/[\x00-\x20\x7f\\\\]/', $href)) {
            return false;
        }

        $scheme = parse_url($href, PHP_URL_SCHEME);
        return $scheme === null
            || in_array(strtolower($scheme), ['http', 'https', 'mailto'], true);
    }

    private static function escapeAsText(string $content): string
    {
        return nl2br(htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), false);
    }
}
