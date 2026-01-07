<?php
namespace Tumik\CMS;

require_once __DIR__ . '/Settings.php';

class Theme {
    public static function injectHead(): void {
        $font = Settings::get('ui_font_family', 'Inter');
        $size = Settings::get('ui_font_size', '16px');
        $weight = Settings::get('ui_font_weight', '300');
        echo "\n<style>\n:root{--ui-font-family:".htmlspecialchars($font,ENT_QUOTES).", ui-sans-serif, system-ui;--ui-font-size:".htmlspecialchars($size,ENT_QUOTES).";--ui-font-weight:".htmlspecialchars($weight,ENT_QUOTES).";}\nhtml:not(.dark) body{color:#0f172a;}\nhtml.dark body{color:#e5e7eb;}\nbody{font-family:var(--ui-font-family);font-size:var(--ui-font-size);font-weight:var(--ui-font-weight);}\ninput,textarea,select{color:inherit;}\nhtml.dark input,html.dark textarea,html.dark select{background-color:#0f172a;color:#e5e7eb;}\n</style>\n<script>(function(){var t=localStorage.getItem('theme');document.documentElement.classList.toggle('dark', t==='dark');})();</script>\n";
    }
}
