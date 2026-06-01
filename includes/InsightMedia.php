<?php
declare(strict_types=1);

final class InsightMedia
{
    private const MAX_BYTES = 5_242_880; // 5 MB

    private const ALLOWED = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    public static function uploadDir(): string
    {
        return KAM_ROOT . '/assets/uploads/insights';
    }

    public static function uploadFromRequest(array $file): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Image upload failed. Please try again.');
        }
        if (($file['size'] ?? 0) > self::MAX_BYTES) {
            throw new RuntimeException('Image must be 5 MB or smaller.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name'] ?? '') ?: '';
        if (!isset(self::ALLOWED[$mime])) {
            throw new RuntimeException('Use JPG, PNG, WebP, or GIF only.');
        }

        $dir = self::uploadDir();
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('Could not create upload folder.');
        }

        $name = 'insight-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . self::ALLOWED[$mime];
        $dest = $dir . '/' . $name;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new RuntimeException('Could not save uploaded image.');
        }

        return 'assets/uploads/insights/' . $name;
    }

    public static function deleteFile(?string $relativePath): void
    {
        if ($relativePath === null || $relativePath === '') {
            return;
        }
        if (!str_starts_with($relativePath, 'assets/uploads/insights/')) {
            return;
        }
        $full = KAM_ROOT . '/' . str_replace(['../', '..\\'], '', $relativePath);
        if (is_file($full)) {
            unlink($full);
        }
    }
}
