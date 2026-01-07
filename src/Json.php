<?php
namespace Tumik\CMS;

class Json {
    public static function ok($data = []): void {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok'=>true] + $data);
    }
    public static function error(string $msg, int $code = 400): void {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok'=>false,'error'=>$msg]);
    }
}
