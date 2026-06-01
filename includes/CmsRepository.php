<?php
declare(strict_types=1);

final class CmsRepository
{
    public static function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
        return trim($text, '-') ?: 'item';
    }

    /* ---------- Settings ---------- */

    public static function settingsAll(): array
    {
        $pdo = Database::connection();
        $rows = $pdo->query('SELECT setting_key, setting_value FROM site_settings')->fetchAll();
        $out = [];
        foreach ($rows as $row) {
            $out[$row['setting_key']] = $row['setting_value'];
        }
        return $out;
    }

    public static function setting(string $key, ?string $default = null): ?string
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT setting_value FROM site_settings WHERE setting_key = ?');
        $stmt->execute([$key]);
        $v = $stmt->fetchColumn();
        return $v !== false ? (string) $v : $default;
    }

    public static function settingSet(string $key, ?string $value): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );
        $stmt->execute([$key, $value]);
    }

    public static function settingsSave(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            self::settingSet((string) $key, $value === '' ? null : (string) $value);
        }
    }

    /* ---------- Offices ---------- */

    public static function officesPublic(): array
    {
        $pdo = Database::connection();
        return $pdo->query(
            'SELECT id, title, address_line1, address_line2, phone, email, map_url
             FROM offices WHERE is_active = 1 ORDER BY sort_order ASC, id ASC'
        )->fetchAll();
    }

    public static function officesAll(): array
    {
        $pdo = Database::connection();
        return $pdo->query('SELECT * FROM offices ORDER BY sort_order ASC, id ASC')->fetchAll();
    }

    public static function officeFind(int $id): ?array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM offices WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function officeSave(?int $id, array $data): int
    {
        $pdo = Database::connection();
        if ($id) {
            $stmt = $pdo->prepare(
                'UPDATE offices SET title=?, address_line1=?, address_line2=?, phone=?, email=?, map_url=?, sort_order=?, is_active=? WHERE id=?'
            );
            $stmt->execute([
                $data['title'], $data['address_line1'], $data['address_line2'] ?? null,
                $data['phone'] ?? null, $data['email'] ?? null, $data['map_url'] ?? null,
                (int) ($data['sort_order'] ?? 0), (int) ($data['is_active'] ?? 1), $id,
            ]);
            return $id;
        }
        $stmt = $pdo->prepare(
            'INSERT INTO offices (title, address_line1, address_line2, phone, email, map_url, sort_order, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['title'], $data['address_line1'], $data['address_line2'] ?? null,
            $data['phone'] ?? null, $data['email'] ?? null, $data['map_url'] ?? null,
            (int) ($data['sort_order'] ?? 0), (int) ($data['is_active'] ?? 1),
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function officeDelete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM offices WHERE id = ?')->execute([$id]);
    }

    /* ---------- Insights ---------- */

    public static function insightsPublic(?string $type = null, int $limit = 50): array
    {
        $pdo = Database::connection();
        $where = "status = 'published'";
        $params = [];
        if ($type === 'article' || $type === 'report') {
            $where .= ' AND content_type = ?';
            $params[] = $type;
        }
        $sql = "SELECT id, title, slug, excerpt, category, content_type, image_url, download_url,
                       is_featured, published_at
                FROM insights WHERE $where
                ORDER BY is_featured DESC, sort_order ASC, published_at DESC, id DESC
                LIMIT " . (int) $limit;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function insightBySlug(string $slug): ?array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            "SELECT * FROM insights WHERE slug = ? AND status = 'published' LIMIT 1"
        );
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function insightsAll(): array
    {
        $pdo = Database::connection();
        return $pdo->query('SELECT * FROM insights ORDER BY sort_order ASC, id DESC')->fetchAll();
    }

    public static function insightFind(int $id): ?array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM insights WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function insightSave(?int $id, array $data): int
    {
        $pdo = Database::connection();
        $slug = $data['slug'] ?: self::slugify($data['title']);
        $publishedAt = ($data['status'] ?? '') === 'published'
            ? ($data['published_at'] ?? date('Y-m-d H:i:s'))
            : null;

        if ($id) {
            $stmt = $pdo->prepare(
                'UPDATE insights SET title=?, slug=?, excerpt=?, body_html=?, category=?, content_type=?,
                 image_url=?, download_url=?, is_featured=?, status=?, sort_order=?, published_at=? WHERE id=?'
            );
            $stmt->execute([
                $data['title'], $slug, $data['excerpt'] ?? null, $data['body_html'] ?? null,
                $data['category'] ?? 'general', $data['content_type'] ?? 'article',
                $data['image_url'] ?? null, $data['download_url'] ?? null,
                (int) ($data['is_featured'] ?? 0), $data['status'] ?? 'draft',
                (int) ($data['sort_order'] ?? 0), $publishedAt, $id,
            ]);
            return $id;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO insights (title, slug, excerpt, body_html, category, content_type, image_url, download_url,
             is_featured, status, sort_order, published_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['title'], $slug, $data['excerpt'] ?? null, $data['body_html'] ?? null,
            $data['category'] ?? 'general', $data['content_type'] ?? 'article',
            $data['image_url'] ?? null, $data['download_url'] ?? null,
            (int) ($data['is_featured'] ?? 0), $data['status'] ?? 'draft',
            (int) ($data['sort_order'] ?? 0), $publishedAt,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function insightDelete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM insights WHERE id = ?')->execute([$id]);
    }

    /* ---------- Case studies ---------- */

    public static function casesPublic(int $limit = 50): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->query(
            "SELECT id, title, slug, industry, summary, challenge, solution, outcome, is_featured, published_at
             FROM case_studies WHERE status = 'published'
             ORDER BY is_featured DESC, sort_order ASC, published_at DESC, id DESC
             LIMIT " . (int) $limit
        );
        return $stmt->fetchAll();
    }

    public static function casesAll(): array
    {
        $pdo = Database::connection();
        return $pdo->query('SELECT * FROM case_studies ORDER BY sort_order ASC, id DESC')->fetchAll();
    }

    public static function caseFind(int $id): ?array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM case_studies WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function caseSave(?int $id, array $data): int
    {
        $pdo = Database::connection();
        $slug = $data['slug'] ?: self::slugify($data['title']);
        $publishedAt = ($data['status'] ?? '') === 'published'
            ? ($data['published_at'] ?? date('Y-m-d H:i:s'))
            : null;

        if ($id) {
            $stmt = $pdo->prepare(
                'UPDATE case_studies SET title=?, slug=?, industry=?, summary=?, challenge=?, solution=?, outcome=?,
                 is_featured=?, status=?, sort_order=?, published_at=? WHERE id=?'
            );
            $stmt->execute([
                $data['title'], $slug, $data['industry'] ?? 'General', $data['summary'] ?? null,
                $data['challenge'], $data['solution'], $data['outcome'],
                (int) ($data['is_featured'] ?? 0), $data['status'] ?? 'draft',
                (int) ($data['sort_order'] ?? 0), $publishedAt, $id,
            ]);
            return $id;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO case_studies (title, slug, industry, summary, challenge, solution, outcome,
             is_featured, status, sort_order, published_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['title'], $slug, $data['industry'] ?? 'General', $data['summary'] ?? null,
            $data['challenge'], $data['solution'], $data['outcome'],
            (int) ($data['is_featured'] ?? 0), $data['status'] ?? 'draft',
            (int) ($data['sort_order'] ?? 0), $publishedAt,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function caseDelete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM case_studies WHERE id = ?')->execute([$id]);
    }

    /* ---------- Testimonials ---------- */

    public static function testimonialsPublic(): array
    {
        $pdo = Database::connection();
        return $pdo->query(
            'SELECT id, name, role_title, company, quote, image_url
             FROM testimonials WHERE is_active = 1 ORDER BY sort_order ASC, id ASC'
        )->fetchAll();
    }

    public static function testimonialsAll(): array
    {
        $pdo = Database::connection();
        return $pdo->query('SELECT * FROM testimonials ORDER BY sort_order ASC, id ASC')->fetchAll();
    }

    public static function testimonialFind(int $id): ?array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM testimonials WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function testimonialSave(?int $id, array $data): int
    {
        $pdo = Database::connection();
        if ($id) {
            $stmt = $pdo->prepare(
                'UPDATE testimonials SET name=?, role_title=?, company=?, quote=?, image_url=?, sort_order=?, is_active=? WHERE id=?'
            );
            $stmt->execute([
                $data['name'], $data['role_title'] ?? null, $data['company'] ?? null,
                $data['quote'], $data['image_url'] ?? null,
                (int) ($data['sort_order'] ?? 0), (int) ($data['is_active'] ?? 1), $id,
            ]);
            return $id;
        }
        $stmt = $pdo->prepare(
            'INSERT INTO testimonials (name, role_title, company, quote, image_url, sort_order, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['name'], $data['role_title'] ?? null, $data['company'] ?? null,
            $data['quote'], $data['image_url'] ?? null,
            (int) ($data['sort_order'] ?? 0), (int) ($data['is_active'] ?? 1),
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function testimonialDelete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM testimonials WHERE id = ?')->execute([$id]);
    }

    public static function publicBundle(): array
    {
        return [
            'settings' => self::settingsAll(),
            'offices' => self::officesPublic(),
            'insights' => self::insightsPublic(null, 100),
            'case_studies' => self::casesPublic(100),
            'testimonials' => self::testimonialsPublic(),
        ];
    }
}
