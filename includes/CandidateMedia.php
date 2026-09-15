<?php
declare(strict_types=1);

final class CandidateMedia
{
    private const MAX_BYTES = 10_485_760; // 10 MB per file

    private const ALLOWED_DOCS = [
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public static function uploadDir(): string
    {
        return KAM_ROOT . '/assets/uploads/candidates';
    }

    /**
     * @param array $file $_FILES item
     * @param string $prefix
     * @return string|null Relative file path or null
     */
    public static function uploadDocument(array $file, string $prefix = 'doc'): ?string
    {
        if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new RuntimeException('Document exceeds maximum allowed upload size (10 MB).');
                case UPLOAD_ERR_PARTIAL:
                    throw new RuntimeException('Document upload was interrupted by network. Please try again.');
                case UPLOAD_ERR_NO_TMP_DIR:
                case UPLOAD_ERR_CANT_WRITE:
                    throw new RuntimeException('Server storage temporarily unavailable. Please try again.');
                default:
                    throw new RuntimeException('Document upload failed (error code ' . $file['error'] . ').');
            }
        }
        if (($file['size'] ?? 0) > self::MAX_BYTES) {
            throw new RuntimeException('Each document must be 10 MB or smaller.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name'] ?? '') ?: '';
        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        
        $fallbackExts = [
            'pdf' => 'pdf',
            'doc' => 'doc',
            'docx' => 'docx',
            'jpg' => 'jpg',
            'jpeg' => 'jpg',
            'png' => 'png',
            'webp' => 'webp',
        ];

        if (isset(self::ALLOWED_DOCS[$mime])) {
            $finalExt = self::ALLOWED_DOCS[$mime];
        } elseif (isset($fallbackExts[$ext])) {
            $finalExt = $fallbackExts[$ext];
        } else {
            throw new RuntimeException('Allowed document formats: PDF, DOC, DOCX, JPG, PNG, WebP.');
        }

        $dir = self::uploadDir();
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('Could not create candidates upload folder.');
        }

        $safePrefix = preg_replace('/[^a-zA-Z0-9_-]/', '', $prefix) ?: 'doc';
        $name = $safePrefix . '-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $finalExt;
        $dest = $dir . '/' . $name;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new RuntimeException('Could not save uploaded document.');
        }

        return 'assets/uploads/candidates/' . $name;
    }

    public static function deleteFile(?string $relativePath): void
    {
        if ($relativePath === null || $relativePath === '') {
            return;
        }
        if (!str_starts_with($relativePath, 'assets/uploads/candidates/')) {
            return;
        }
        $full = KAM_ROOT . '/' . str_replace(['../', '..\\'], '', $relativePath);
        if (is_file($full)) {
            unlink($full);
        }
    }
}
