<?php

declare(strict_types=1);

/**
 * One canonical resolver for article thumbnails and gallery images.
 * Database values are filenames only; image bytes live in public/uploads/articles/.
 */
final class ArticleImage
{
    private const UPLOAD_RELATIVE_DIR = '/public/uploads/articles/';
    private const ALLOWED_PATTERN = '/\A[A-Za-z0-9][A-Za-z0-9._-]{0,249}\.(?:jpe?g|png|webp|gif)\z/i';

    public static function filename(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $filename = trim($value);
        if ($filename === '' || basename($filename) !== $filename || !preg_match(self::ALLOWED_PATTERN, $filename)) {
            return null;
        }

        return $filename;
    }

    public static function absolutePath(mixed $value): ?string
    {
        $filename = self::filename($value);
        if ($filename === null) {
            return null;
        }

        $path = dirname(__DIR__, 2) . self::UPLOAD_RELATIVE_DIR . $filename;
        $realDirectory = realpath(dirname($path));
        $realPath = realpath($path);
        if ($realDirectory === false || $realPath === false || !is_file($realPath)) {
            return null;
        }

        $prefix = rtrim($realDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (strncmp($realPath, $prefix, strlen($prefix)) !== 0) {
            return null;
        }

        return $realPath;
    }

    /** Return an escaped-by-caller public URL, or null when the file is unavailable. */
    public static function url(mixed $value, string $baseUrl = ''): ?string
    {
        $filename = self::filename($value);
        $path = self::absolutePath($filename);
        if ($filename === null || $path === null) {
            return null;
        }

        $version = @filemtime($path);
        $url = rtrim($baseUrl, '/') . self::UPLOAD_RELATIVE_DIR . rawurlencode($filename);
        return $url . ($version !== false ? '?v=' . $version : '');
    }

    public static function exists(mixed $value): bool
    {
        return self::absolutePath($value) !== null;
    }
}
