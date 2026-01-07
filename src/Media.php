<?php
namespace Tumik\CMS;

class Media {
    public static function uploadDir(): string {
        $dir = Env::get('UPLOAD_DIR', 'public/uploads');
        return rtrim($dir, '/');
    }

    public static function uploadAbsPath(): string {
        return realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . str_replace(['\\','/'], DIRECTORY_SEPARATOR, self::uploadDir());
    }

    public static function webBase(): string {
        $dir = self::uploadDir();
        if (str_starts_with($dir, 'public/')) {
            return '/' . substr($dir, strlen('public/'));
        }
        return '/' . trim($dir, '/');
    }

    public static function saveUpload(array $file): array {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('upload_error');
        }
        $tmp = $file['tmp_name'];
        $name = $file['name'] ?? 'file';
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmp);
        finfo_close($finfo);
        $allowed = ['image/jpeg','image/png','image/gif','image/webp','application/pdf'];
        if (!in_array($mime, $allowed, true)) {
            throw new \RuntimeException('mime_not_allowed');
        }
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION)) ?: self::extFromMime($mime);
        $base = preg_replace('/[^a-zA-Z0-9_-]+/','-', pathinfo($name, PATHINFO_FILENAME));
        $unique = date('YmdHis') . '-' . bin2hex(random_bytes(4));
        $filename = ($base ?: 'file') . '-' . $unique . '.' . $ext;
        $abs = self::uploadAbsPath();
        if (!is_dir($abs)) { @mkdir($abs, 0755, true); }
        $dest = $abs . DIRECTORY_SEPARATOR . $filename;
        if (!move_uploaded_file($tmp, $dest)) {
            throw new \RuntimeException('move_failed');
        }
        $size = filesize($dest) ?: 0;
        $webPath = rtrim(self::webBase(), '/') . '/' . $filename;
        return ['filename'=>$filename,'path'=>$webPath,'mime'=>$mime,'size'=>$size];
    }

    private static function extFromMime(string $mime): string {
        return match($mime){
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'application/pdf' => 'pdf',
            default => 'bin'
        };
    }

    public static function fsPathFromWeb(string $webPath): string {
        $rel = ltrim($webPath, '/');
        $parts = explode('/', $rel);
        if ($parts[0] === 'public') { array_shift($parts); }
        $abs = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts);
        return $abs;
    }
}
