<?php
namespace Tumik\CMS;

class Util {
    public static function slug(string $s): string {
        $s = iconv('UTF-8','ASCII//TRANSLIT',$s);
        $s = preg_replace('/[^a-zA-Z0-9]+/','-',strtolower($s));
        $s = trim($s,'-');
        return $s ?: uniqid();
    }
}
