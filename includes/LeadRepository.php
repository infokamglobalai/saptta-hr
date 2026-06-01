<?php
declare(strict_types=1);

final class LeadRepository
{
    public static function create(array $data): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO leads (name, email, phone, company, inquiry_type, message, source, ip_address, user_agent)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['phone'] ?? null,
            $data['company'] ?? null,
            $data['inquiry_type'] ?? 'general',
            $data['message'] ?? null,
            $data['source'] ?? 'website',
            $data['ip_address'] ?? null,
            $data['user_agent'] ?? null,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'SELECT l.*, a.name AS assigned_name
             FROM leads l
             LEFT JOIN admins a ON a.id = l.assigned_to
             WHERE l.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function list(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $pdo = Database::connection();
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'l.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(l.name LIKE ? OR l.email LIKE ? OR l.company LIKE ?)';
            $q = '%' . $filters['q'] . '%';
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
        }

        $whereSql = implode(' AND ', $where);
        $offset = max(0, ($page - 1) * $perPage);

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM leads l WHERE $whereSql");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $sql = "SELECT l.*, a.name AS assigned_name
                FROM leads l
                LEFT JOIN admins a ON a.id = l.assigned_to
                WHERE $whereSql
                ORDER BY l.created_at DESC
                LIMIT $perPage OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return [
            'items' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => (int) max(1, ceil($total / $perPage)),
        ];
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::connection();
        $fields = [];
        $params = [];
        foreach (['status', 'priority', 'assigned_to'] as $key) {
            if (array_key_exists($key, $data)) {
                $fields[] = "$key = ?";
                $params[] = $data[$key];
            }
        }
        if ($fields === []) {
            return false;
        }
        $params[] = $id;
        $stmt = $pdo->prepare('UPDATE leads SET ' . implode(', ', $fields) . ' WHERE id = ?');
        return $stmt->execute($params);
    }

    public static function stats(): array
    {
        $pdo = Database::connection();
        $total = (int) $pdo->query('SELECT COUNT(*) FROM leads')->fetchColumn();
        $new = (int) $pdo->query("SELECT COUNT(*) FROM leads WHERE status = 'new'")->fetchColumn();
        $won = (int) $pdo->query("SELECT COUNT(*) FROM leads WHERE status = 'won'")->fetchColumn();
        $week = (int) $pdo->query(
            'SELECT COUNT(*) FROM leads WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)'
        )->fetchColumn();
        $subs = (int) $pdo->query(
            "SELECT COUNT(*) FROM newsletter_subscribers WHERE status = 'active'"
        )->fetchColumn();

        $byStatus = $pdo->query(
            "SELECT status, COUNT(*) AS cnt FROM leads GROUP BY status"
        )->fetchAll();

        return [
            'total_leads' => $total,
            'new_leads' => $new,
            'won_leads' => $won,
            'leads_this_week' => $week,
            'subscribers' => $subs,
            'by_status' => $byStatus,
        ];
    }

    public static function addNote(int $leadId, ?int $adminId, string $note): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO lead_notes (lead_id, admin_id, note) VALUES (?, ?, ?)'
        );
        $stmt->execute([$leadId, $adminId, $note]);
    }

    public static function notes(int $leadId): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'SELECT n.*, a.name AS admin_name
             FROM lead_notes n
             LEFT JOIN admins a ON a.id = n.admin_id
             WHERE n.lead_id = ?
             ORDER BY n.created_at DESC'
        );
        $stmt->execute([$leadId]);
        return $stmt->fetchAll();
    }
}
