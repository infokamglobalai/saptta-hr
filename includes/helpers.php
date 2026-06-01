<?php
declare(strict_types=1);

function kam_h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function kam_redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function kam_json(array $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function kam_client_ip(): ?string
{
    return $_SERVER['REMOTE_ADDR'] ?? null;
}

function kam_log_activity(?int $adminId, string $entityType, ?int $entityId, string $action, ?array $meta = null): void
{
    try {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO activity_log (admin_id, entity_type, entity_id, action, meta) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $adminId,
            $entityType,
            $entityId,
            $action,
            $meta ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
        ]);
    } catch (Throwable) {
        // Non-blocking
    }
}

function kam_lead_statuses(): array
{
    return ['new', 'contacted', 'qualified', 'proposal', 'won', 'lost'];
}

function kam_status_label(string $status): string
{
    return ucfirst(str_replace('_', ' ', $status));
}

function kam_inquiry_types(): array
{
    return [
        'recruitment' => 'Recruitment Services',
        'contract-staffing' => 'Contract Staffing',
        'payroll' => 'Payroll Outsourcing',
        'hr-advisory' => 'HR Advisory',
        'executive-search' => 'Executive Search',
        'bgv' => 'Background Verification',
        'hrms' => 'HRMS Software',
        'ats' => 'ATS Software',
        'payroll-software' => 'Payroll Software',
        'partnership' => 'Partnership Inquiry',
        'general' => 'General Inquiry',
    ];
}
