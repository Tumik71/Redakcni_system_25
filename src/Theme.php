<?php
namespace Tumik\CMS;

require_once __DIR__ . '/Settings.php';

class Theme {
    public static function injectHead(): void {
        $bodyFont = Settings::get('ui_font_family', 'Inter');
        $bodySize = Settings::get('ui_font_size', '16px');
        $bodyWeight = Settings::get('ui_font_weight', '300');

        $headingFont = Settings::get('ui_heading_font_family', $bodyFont);
        $headingWeight = Settings::get('ui_heading_weight', '600');

        $brand = Settings::get('ui_brand_color', '#0ea5e9');
        $link = Settings::get('ui_link_color', $brand);
        $textLight = Settings::get('ui_text_light', '#0f172a');
        $textDark = Settings::get('ui_text_dark', '#e5e7eb');

        $css = "\n<style>\n:root{--ui-body-font:".htmlspecialchars($bodyFont,ENT_QUOTES).", ui-sans-serif, system-ui;--ui-body-size:".htmlspecialchars($bodySize,ENT_QUOTES).";--ui-body-weight:".htmlspecialchars($bodyWeight,ENT_QUOTES).";--ui-heading-font:".htmlspecialchars($headingFont,ENT_QUOTES).", ui-sans-serif, system-ui;--ui-heading-weight:".htmlspecialchars($headingWeight,ENT_QUOTES).";--ui-brand-color:".htmlspecialchars($brand,ENT_QUOTES).";--ui-link-color:".htmlspecialchars($link,ENT_QUOTES).";--ui-text-light:".htmlspecialchars($textLight,ENT_QUOTES).";--ui-text-dark:".htmlspecialchars($textDark,ENT_QUOTES).";}\nhtml:not(.dark) body{color:var(--ui-text-light);}\nhtml.dark body{color:var(--ui-text-dark);}\nbody{font-family:var(--ui-body-font);font-size:var(--ui-body-size);font-weight:var(--ui-body-weight);}\nh1,h2,h3,h4,h5,h6{font-family:var(--ui-heading-font);font-weight:var(--ui-heading-weight);}\na{color:var(--ui-link-color);}\n.text-brand{color:var(--ui-brand-color)!important;}\n.bg-brand{background-color:var(--ui-brand-color)!important;}\n.hover\\:bg-brand:hover{background-color:var(--ui-brand-color)!important;}\ninput,textarea,select{color:inherit;}\nhtml.dark input,html.dark textarea,html.dark select{background-color:#0f172a;color:var(--ui-text-dark);}\n</style>\n";
        $js = "<script>(function(){\n  var t=localStorage.getItem('theme');\n  document.documentElement.classList.toggle('dark', t==='dark');\n  function syncIcons(){\n    var d=document.documentElement.classList.contains('dark');\n    var s=document.getElementById('iconSun');\n    var m=document.getElementById('iconMoon');\n    if(s&&m){ s.classList.toggle('hidden', d); m.classList.toggle('hidden', !d); }\n  }\n  function toggleTheme(){\n    var d=!document.documentElement.classList.contains('dark');\n    document.documentElement.classList.toggle('dark', d);\n    try{ localStorage.setItem('theme', d?'dark':'light'); }catch(e){}\n    syncIcons();\n  }\n  ['click','touchstart','pointerdown','keydown'].forEach(function(ev){\n    document.addEventListener(ev,function(e){\n      var el = e.target.closest('[data-theme-toggle], #themeToggle');\n      if(!el) return;\n      if(ev==='keydown'){ if(e.key!=='Enter' && e.key!==' ') return; }\n      e.preventDefault(); toggleTheme();\n    }, {passive:false});\n  });\n  document.addEventListener('DOMContentLoaded', syncIcons);\n})();</script>\n";
        echo $css.$js;
    }
}
