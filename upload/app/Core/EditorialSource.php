<?php

declare(strict_types=1);

/** Publisher attribution extracted from an editorial source block. */
final class EditorialSource
{
    private const PUBLISHERS = [
        'vnexpress.net' => ['name' => 'VnExpress', 'mark' => 'VNEXPRESS'],
        'e.vnexpress.net' => ['name' => 'VnExpress International', 'mark' => 'VNEXPRESS'],
        'tuoitre.vn' => ['name' => 'Tuổi Trẻ Online', 'mark' => 'TUỔI TRẺ'],
        'vietnamplus.vn' => ['name' => 'VietnamPlus', 'mark' => 'VNA'],
        'www.vietnamplus.vn' => ['name' => 'VietnamPlus', 'mark' => 'VNA'],
        'en.vietnamplus.vn' => ['name' => 'VietnamPlus', 'mark' => 'VNA'],
        'baochinhphu.vn' => ['name' => 'Báo Điện tử Chính phủ', 'mark' => 'CHÍNH PHỦ'],
        'en.baochinhphu.vn' => ['name' => 'Báo Điện tử Chính phủ', 'mark' => 'CHÍNH PHỦ'],
        'vov.vn' => ['name' => 'VOV', 'mark' => 'VOV'],
        'english.vov.vn' => ['name' => 'VOV World', 'mark' => 'VOV'],
        'nhandan.vn' => ['name' => 'Nhân Dân', 'mark' => 'NHÂN DÂN'],
        'en.nhandan.vn' => ['name' => 'Nhân Dân', 'mark' => 'NHÂN DÂN'],
        'reuters.com' => ['name' => 'Reuters', 'mark' => 'REUTERS'],
        'www.reuters.com' => ['name' => 'Reuters', 'mark' => 'REUTERS'],
        'vietnam.vnanet.vn' => ['name' => 'Thông tấn xã Việt Nam', 'mark' => 'VNA'],
        'vietnamnews.vn' => ['name' => 'Viet Nam News', 'mark' => 'VNS'],
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
