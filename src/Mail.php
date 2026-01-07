<?php
namespace Tumik\CMS;

class Mail {
    public static function send(string $to, string $subject, string $html): bool {
        $from = Env::get('MAIL_FROM', 'noreply@localhost');
        $name = Env::get('MAIL_FROM_NAME', 'Tumik CMS');
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=UTF-8';
        $headers[] = 'From: ' . $name . ' <' . $from . '>';
        return mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $html, implode("\r\n", $headers));
    }
}
