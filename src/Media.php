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

    public static function thumbDirAbs(): string {
        return self::uploadAbsPath() . DIRECTORY_SEPARATOR . 'thumbs';
    }

    public static function thumbWebBase(): string {
        return rtrim(self::webBase(), '/') . '/thumbs';
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
        $cfgAllowed = Settings::get('media_allowed_mime','image/jpeg,image/png,image/gif,image/webp,application/pdf');
        $allowed = array_map('trim', explode(',', $cfgAllowed));
        if (!in_array($mime, $allowed, true)) {
            throw new \RuntimeException('mime_not_allowed');
        }
        $maxMb = (int)(Settings::get('media_max_upload_mb','10'));
        $maxBytes = $maxMb > 0 ? $maxMb * 1024 * 1024 : 0;
        if ($maxBytes && filesize($tmp) > $maxBytes) {
            throw new \RuntimeException('file_too_large');
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
        if (str_starts_with($mime, 'image/')) {
            self::makeThumb($dest, $mime, 320);
        }
        $webPath = rtrim(self::webBase(), '/') . '/' . $filename;
        return ['filename'=>$filename,'path'=>$webPath,'mime'=>$mime,'size'=>$size];
    }

    public static function saveUploadTo(array $file, string $subdir, bool $imagesOnly = false): array {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('upload_error');
        }
        $tmp = $file['tmp_name'];
        $name = $file['name'] ?? 'file';
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmp);
        finfo_close($finfo);
        if ($imagesOnly && !str_starts_with($mime, 'image/')) {
            throw new \RuntimeException('mime_not_image');
        }
        $cfgAllowed = Settings::get('media_allowed_mime','image/jpeg,image/png,image/gif,image/webp,application/pdf');
        $allowed = array_map('trim', explode(',', $cfgAllowed));
        if (!in_array($mime, $allowed, true)) {
            throw new \RuntimeException('mime_not_allowed');
        }
        $maxMb = (int)(Settings::get('media_max_upload_mb','10'));
        $maxBytes = $maxMb > 0 ? $maxMb * 1024 * 1024 : 0;
        if ($maxBytes && filesize($tmp) > $maxBytes) {
            throw new \RuntimeException('file_too_large');
        }
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION)) ?: self::extFromMime($mime);
        $base = preg_replace('/[^a-zA-Z0-9_-]+/','-', pathinfo($name, PATHINFO_FILENAME));
        $unique = date('YmdHis') . '-' . bin2hex(random_bytes(4));
        $filename = ($base ?: 'file') . '-' . $unique . '.' . $ext;
        $absBase = self::uploadAbsPath();
        $abs = rtrim($absBase, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . trim($subdir, DIRECTORY_SEPARATOR);
        if (!is_dir($abs)) { @mkdir($abs, 0755, true); }
        $dest = $abs . DIRECTORY_SEPARATOR . $filename;
        if (!move_uploaded_file($tmp, $dest)) {
            throw new \RuntimeException('move_failed');
        }
        $size = filesize($dest) ?: 0;
        if (str_starts_with($mime, 'image/')) {
            // thumbs under uploads/thumbs/avatars
            @mkdir(self::thumbDirAbs() . DIRECTORY_SEPARATOR . trim($subdir, DIRECTORY_SEPARATOR),0755,true);
            self::makeThumb($dest, $mime, 160);
        }
        $webBase = rtrim(self::webBase(), '/') . '/' . trim($subdir, '/');
        $webPath = rtrim($webBase, '/') . '/' . $filename;
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

    public static function thumbPathFor(string $filename): string {
        return rtrim(self::thumbWebBase(), '/') . '/' . $filename;
    }

    public static function thumbExists(string $filename): bool {
        $abs = self::thumbDirAbs() . DIRECTORY_SEPARATOR . $filename;
        return is_file($abs);
    }

    private static function makeThumb(string $srcAbs, string $mime, int $maxW): void {
        if (!is_file($srcAbs)) { return; }
        $info = getimagesize($srcAbs);
        if (!$info) { return; }
        [$w,$h] = [$info[0],$info[1]];
        if ($w <= $maxW) { $destAbs = self::thumbDirAbs() . DIRECTORY_SEPARATOR . basename($srcAbs); @mkdir(dirname($destAbs),0755,true); @copy($srcAbs,$destAbs); return; }
        $ratio = $h / $w; $tw = $maxW; $th = (int)round($tw * $ratio);
        $src = match($mime){
            'image/jpeg' => imagecreatefromjpeg($srcAbs),
            'image/png' => imagecreatefrompng($srcAbs),
            'image/gif' => imagecreatefromgif($srcAbs),
            'image/webp' => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($srcAbs) : null,
            default => null
        };
        if (!$src) { return; }
        $dst = imagecreatetruecolor($tw,$th);
        imagecopyresampled($dst,$src,0,0,0,0,$tw,$th,$w,$h);
        $destAbs = self::thumbDirAbs() . DIRECTORY_SEPARATOR . basename($srcAbs);
        @mkdir(dirname($destAbs),0755,true);
        switch ($mime){
            case 'image/jpeg': imagejpeg($dst,$destAbs,85); break;
            case 'image/png': imagepng($dst,$destAbs,6); break;
            case 'image/gif': imagegif($dst,$destAbs); break;
            case 'image/webp': if (function_exists('imagewebp')) imagewebp($dst,$destAbs,80); break;
        }
        imagedestroy($src); imagedestroy($dst);
    }
}
