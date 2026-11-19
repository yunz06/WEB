<?php
declare(strict_types=1);

/**
 * Resolves publisher attribution from the editorial source block in an article.
 * Only known HTTPS publisher hosts are rendered as external links.
 */
final class EditorialSource
{
    private const PUBLISHERS = [
        'vnexpress.net' => ['name' => 'VnExpress', 'mark' => 'Vn'],
        'e.vnexpress.net' => ['name' => 'VnExpress', 'mark' => 'Vn'],
        'tuoitre.vn' => ['name' => 'Tuổi Trẻ Online', 'mark' => 'TT'],
        'vietnamplus.vn' => ['name' => 'VietnamPlus', 'mark' => 'V+'],
        'www.vietnamplus.vn' => ['name' => 'VietnamPlus', 'mark' => 'V+'],
        'baochinhphu.vn' => ['name' => 'Báo Điện tử Chính phủ', 'mark' => 'CP'],
    ];

    public static function fromContent(string $content): ?array
    {
        if (!preg_match('/<blockquote\b[^>]*>(.*?)<\/blockquote>/is', $content, $block)) {
            return null;
        }

        if (!preg_match('/<a\b[^>]*href=(["\'])(https:\/\/[^"\']+)\1[^>]*>(.*?)<\/a>/is', $block[1], $match)) {
            return null;
        }

        $url = html_entity_decode($match[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if (!isset(self::PUBLISHERS[$host])) {
            return null;
        }

        $label = trim(html_entity_decode(strip_tags($match[3]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($label === '') {
            return null;
        }

        $publisher = self::PUBLISHERS[$host];

        return [
            'publisher' => $publisher['name'],
            'mark' => $publisher['mark'],
            'label' => $label,
            'url' => $url,
        ];
    }
}
