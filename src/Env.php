<?php
namespace Tumik\CMS;

class Env {
    private static array $vars = [];

    public static function load(string $path): void {
        if (!is_file($path)) { return; }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) { continue; }
            [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
            self::$vars[trim($key)] = trim($value);
        }
    }

    public static function get(string $key, ?string $default = null): ?string {
        return self::$vars[$key] ?? $default;
    }
}
