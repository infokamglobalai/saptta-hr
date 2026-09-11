<?php
declare(strict_types=1);

final class JobRepository
{
    // -------------------------------------------------------------
    // JOBS CRUD
    // -------------------------------------------------------------

    public static function jobSave(?int $id, array $data): int
    {
        $pdo = Database::connection();
        $title = trim((string) $data['title']);
        $slug = trim((string) ($data['slug'] ?? ''));
        if ($slug === '') {
            $slug = self::slugify($title);
        } else {
            $slug = self::slugify($slug);
        }

        // Ensure unique slug
        $slugBase = $slug;
        $i = 1;
        while (true) {
            $check = $pdo->prepare('SELECT id FROM jobs WHERE slug = ? AND id != ?');
            $check->execute([$slug, $id ?? 0]);
            if (!$check->fetch()) {
                break;
            }
            $slug = $slugBase . '-' . $i++;
        }

        $fields = [
            'title' => $title,
            'slug' => $slug,
            'category' => trim((string) ($data['category'] ?? 'Engineering')),
            'job_type' => trim((string) ($data['job_type'] ?? 'Full-time')),
            'location' => trim((string) ($data['location'] ?? 'India / GCC')),
            'image_url' => !empty($data['image_url']) ? trim((string) $data['image_url']) : null,
            'experience_required' => trim((string) ($data['experience_required'] ?? '')),
            'salary_range' => trim((string) ($data['salary_range'] ?? '')),
            'vacancies' => max(1, (int) ($data['vacancies'] ?? 1)),
            'summary' => trim((string) ($data['summary'] ?? '')),
            'description' => (string) ($data['description'] ?? ''),
            'requirements' => (string) ($data['requirements'] ?? ''),
            'status' => in_array($data['status'] ?? '', ['active', 'closed', 'draft'], true) ? $data['status'] : 'active',
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'deadline' => !empty($data['deadline']) ? $data['deadline'] : null,
        ];

        if ($id && $id > 0) {
            $sets = [];
            $params = [];
            foreach ($fields as $k => $v) {
                $sets[] = "$k = ?";
                $params[] = $v;
            }
            $params[] = $id;
            $stmt = $pdo->prepare('UPDATE jobs SET ' . implode(', ', $sets) . ' WHERE id = ?');
            $stmt->execute($params);
            return $id;
        }

        $cols = array_keys($fields);
        $placeholders = array_fill(0, count($cols), '?');
        $stmt = $pdo->prepare(
            'INSERT INTO jobs (' . implode(', ', $cols) . ') VALUES (' . implode(', ', $placeholders) . ')'
        );
        $stmt->execute(array_values($fields));
        return (int) $pdo->lastInsertId();
    }

    public static function jobFind(int $id): ?array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM jobs WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function jobBySlug(string $slug): ?array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM jobs WHERE slug = ? AND status = "active" LIMIT 1');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function ensureTable(): void
    {
        try {
            $pdo = Database::connection();
            $pdo->exec('CREATE TABLE IF NOT EXISTS jobs (
              id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              title VARCHAR(220) NOT NULL,
              slug VARCHAR(220) NOT NULL UNIQUE,
              category VARCHAR(80) NOT NULL DEFAULT "Engineering",
              job_type VARCHAR(60) NOT NULL DEFAULT "Full-time",
              location VARCHAR(150) NOT NULL DEFAULT "India / GCC",
              image_url VARCHAR(500) NULL,
              experience_required VARCHAR(100) NULL,
              salary_range VARCHAR(100) NULL,
              vacancies INT NOT NULL DEFAULT 1,
              summary TEXT NULL,
              description MEDIUMTEXT NOT NULL,
              requirements MEDIUMTEXT NULL,
              status ENUM("active", "closed", "draft") NOT NULL DEFAULT "active",
              sort_order INT NOT NULL DEFAULT 0,
              deadline DATE NULL,
              created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              INDEX idx_jobs_status (status),
              INDEX idx_jobs_category (category)
            ) ENGINE=InnoDB');

            // Auto-check if image_url exists
            try {
                $check = $pdo->query('SELECT image_url FROM jobs LIMIT 1');
            } catch (Throwable $e) {
                $pdo->exec('ALTER TABLE jobs ADD COLUMN image_url VARCHAR(500) NULL AFTER location');
            }

            // Auto-seed if empty
            $count = (int) $pdo->query('SELECT COUNT(*) FROM jobs')->fetchColumn();
            if ($count === 0) {
                $sampleJobs = self::defaultSeedJobs();
                foreach ($sampleJobs as $j) {
                    self::jobSave(null, $j);
                }
            }
        } catch (Throwable $t) {
            // ignore
        }
    }

    public static function defaultSeedJobs(): array
    {
        return [
            [
                'title' => 'Fresh ITI Diploma Holders (Electrician / Plumber / HVAC / MEP)',
                'slug' => 'fresh-iti-diploma-dubai',
                'category' => 'Technical & ITI',
                'job_type' => 'Full-time / Overseas',
                'location' => 'Dubai, UAE',
                'image_url' => 'assets/img/jobs/iti-dubai.png',
                'experience_required' => 'Fresher / 0 - 2 Years',
                'salary_range' => 'Attractive Gulf Scale + Accommodation',
                'vacancies' => 25,
                'summary' => 'Urgent requirement for Fresh ITI Diploma Holders for a Reputed Construction Company in Dubai. Roles open for Electrician, Plumber, HVAC / AC Technician, and MEP Technician.',
                'description' => "We are looking for Fresh ITI Diploma Holders for a Reputed Construction Company in Dubai.\n\nOpen Roles:\n• Electrician\n• Plumber\n• HVAC / AC Technician\n• MEP Technician\n\nInterviews in: Bangalore & Mumbai\nEligibility: Only for Male Candidates\nContact / WhatsApp: +91 8050766464, +91 9187227450, +91 9686171617",
                'requirements' => "• ITI / Diploma Holder in Electrical, RAC/HVAC, Plumbing, or MEP Trades\n• Fresher or up to 2 years experience\n• Only for Male Candidates with valid Passport\n• In-person / Client interviews in Bangalore & Mumbai\n• Willingness to relocate and start an international career in Dubai, UAE",
                'status' => 'active',
                'sort_order' => 1,
            ],
            [
                'title' => 'Fresh Civil Engineers (Diploma / B.E. / B.Tech)',
                'slug' => 'fresh-civil-engineers-dubai',
                'category' => 'Engineering',
                'job_type' => 'Full-time / Overseas',
                'location' => 'Dubai, UAE',
                'image_url' => 'assets/img/jobs/civil-dubai.png',
                'experience_required' => 'Fresher / 0 - 2 Years',
                'salary_range' => 'Attractive Package + Visa + Accommodation',
                'vacancies' => 15,
                'summary' => 'Urgent opening for Fresh Civil Engineers (Diploma / B.E. / B.Tech) for a Reputed Construction Company in Dubai. Build your career abroad with large-scale projects.',
                'description' => "Looking for Fresh Civil Engineers to start their career abroad with a Reputed Construction Company in Dubai.\n\nKey Highlights:\n• Work with a Reputed Construction Group\n• Gain High-value International Exposure\n• Build a Strong Engineering Career Foundation\n• Be Part of Exciting Commercial & Infrastructure Projects\n\nInterviews in: Bangalore & Mumbai\nEligibility: Only for Male Candidates\nContact / WhatsApp: +91 8050766464, +91 9187227450, +91 9686171617",
                'requirements' => "• Diploma / B.E. / B.Tech in Civil Engineering\n• Fresher or 0 - 2 Years Experience\n• Only for Male Candidates with valid Passport\n• Basic understanding of structural drawings, site supervision, and AutoCAD\n• Client interviews in Bangalore & Mumbai",
                'status' => 'active',
                'sort_order' => 2,
            ],
            [
                'title' => 'Mechanical Site Engineer (MEP)',
                'slug' => 'mechanical-site-engineer-mep',
                'category' => 'Engineering',
                'job_type' => 'Full-time / Overseas',
                'location' => 'Kuwait / GCC',
                'image_url' => 'assets/img/industry_engineering_mfg.png',
                'experience_required' => '3 - 6 Years',
                'salary_range' => 'Competitive + Accommodation',
                'vacancies' => 5,
                'summary' => 'Looking for experienced Mechanical Engineers with Diploma/B.E. for HVAC and plumbing installations in commercial projects.',
                'description' => 'Oversee installation, testing, and commissioning of MEP systems. Coordinate with consultants, site supervisors, and subcontractors to ensure compliance with project specifications.',
                'requirements' => "• Diploma / B.E. in Mechanical Engineering\n• 3+ years experience in MEP / HVAC execution\n• GCC experience & valid driving license preferred\n• Proficiency in AutoCAD / Revit is an advantage",
                'status' => 'active',
                'sort_order' => 3,
            ],
            [
                'title' => 'Electrical Supervisor / Wireman',
                'slug' => 'electrical-supervisor-wireman',
                'category' => 'Technical & ITI',
                'job_type' => 'Full-time',
                'location' => 'Bengaluru, India',
                'image_url' => 'assets/img/contract_staffing.png',
                'experience_required' => '2 - 5 Years',
                'salary_range' => '₹25,000 - ₹40,000 / month',
                'vacancies' => 8,
                'summary' => 'ITI / Diploma Electrical professionals required for industrial wiring, panel installation, and facility maintenance.',
                'description' => 'Responsible for cable laying, LT/HT panel erection, transformer maintenance, and safety inspections at industrial sites.',
                'requirements' => "• ITI (Electrician) / Diploma in Electrical Engineering\n• Valid Wireman / Supervisor license\n• Knowledge of industrial electrical safety norms",
                'status' => 'active',
                'sort_order' => 4,
            ],
            [
                'title' => 'QA / QC Inspector (Welding & Piping)',
                'slug' => 'qa-qc-inspector-welding-piping',
                'category' => 'Manufacturing',
                'job_type' => 'Contract',
                'location' => 'Saudi Arabia / UAE',
                'image_url' => 'assets/img/executive_leadership_boardroom.png',
                'experience_required' => '4 - 8 Years',
                'salary_range' => 'Attractive Tax-Free Package',
                'vacancies' => 4,
                'summary' => 'Certified QA/QC Inspectors needed for oil & gas and heavy fabrication projects in the Gulf region.',
                'description' => 'Conduct non-destructive testing (NDT), weld visual inspection, hydrostatic test witness, and maintain inspection documentation as per ISO standards.',
                'requirements' => "• Diploma / Degree in Mechanical / Metallurgy\n• CSWIP 3.1 or AWS-CWI certification mandatory\n• NDT Level II (UT, MT, PT, RT)\n• Minimum 4 years in fabrication or EPC projects",
                'status' => 'active',
                'sort_order' => 5,
            ],
        ];
    }

    public static function jobsPublic(?string $category = null, ?string $q = null): array
    {
        self::ensureTable();
        $pdo = Database::connection();
        $where = ['status = "active"'];
        $params = [];

        if (!empty($category) && $category !== 'all') {
            $where[] = 'category = ?';
            $params[] = $category;
        }
        if (!empty($q)) {
            $where[] = '(title LIKE ? OR location LIKE ? OR summary LIKE ? OR description LIKE ?)';
            $wild = '%' . $q . '%';
            $params[] = $wild;
            $params[] = $wild;
            $params[] = $wild;
            $params[] = $wild;
        }

        $sql = 'SELECT id, title, slug, category, job_type, location, image_url, experience_required, salary_range, vacancies, summary, deadline, created_at
                FROM jobs
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY sort_order ASC, created_at DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function jobsAll(array $filters = []): array
    {
        $pdo = Database::connection();
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['category'])) {
            $where[] = 'category = ?';
            $params[] = $filters['category'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(title LIKE ? OR location LIKE ?)';
            $wild = '%' . $filters['q'] . '%';
            $params[] = $wild;
            $params[] = $wild;
        }

        $sql = 'SELECT j.*, (SELECT COUNT(*) FROM job_applications a WHERE a.job_id = j.id) AS applications_count
                FROM jobs j
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY j.sort_order ASC, j.created_at DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function jobDelete(int $id): bool
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('DELETE FROM jobs WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function categories(): array
    {
        try {
            $pdo = Database::connection();
            $stmt = $pdo->query('SELECT DISTINCT category FROM jobs WHERE status = "active" AND category IS NOT NULL AND category != "" ORDER BY category ASC');
            $cats = $stmt->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($cats)) {
                $assoc = [];
                foreach ($cats as $c) {
                    $assoc[$c] = $c;
                }
                return $assoc;
            }
        } catch (Throwable $t) {
            // Fallback to defaults
        }

        return [
            'Engineering' => 'Engineering',
            'Technical & ITI' => 'Technical & ITI',
            'Construction & MEP' => 'Construction & MEP',
            'Manufacturing' => 'Manufacturing',
            'IT & Software' => 'IT & Software',
            'Healthcare' => 'Healthcare',
            'Finance & Accounts' => 'Finance & Accounts',
            'Operations & Admin' => 'Operations & Admin',
            'General' => 'General',
        ];
    }

    // -------------------------------------------------------------
    // APPLICATIONS CRUD (Candidate Registration Form)
    // -------------------------------------------------------------

    public static function apply(array $data): int
    {
        $pdo = Database::connection();

        // Generate Registration Number: KAM-YYYY-XXXX
        $year = date('Y');
        $regNo = 'KAM-' . $year . '-' . strtoupper(bin2hex(random_bytes(3)));

        $stmt = $pdo->prepare('
            INSERT INTO job_applications (
                job_id, reg_no, full_name, dob, age, gender, mobile_no, whatsapp_no, email, aadhaar_no,
                current_address, city_district, state, pin_code, nationality,
                qualification_level, trade_branch, institute_college, year_passed, percentage_cgpa, additional_certifications,
                employment_status, experience_years, experience_months, current_designation, current_company, current_location, current_salary, expected_salary, notice_period, primary_skills, secondary_skills,
                passport_no, passport_expiry, place_of_issue, ecnr_status, gcc_experience, gcc_country_employer, gcc_license_id, visa_status,
                resume_path, photo_path, aadhaar_path, qualification_cert_path, experience_cert_path, passport_copy_path, skill_cert_path, driving_license_path, other_docs_path,
                emergency_name, emergency_relationship, emergency_mobile, emergency_alt_mobile,
                status, source, ip_address
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?,
                "new", ?, ?
            )
        ');

        $stmt->execute([
            !empty($data['job_id']) ? (int) $data['job_id'] : null,
            $regNo,
            $data['full_name'],
            !empty($data['dob']) ? $data['dob'] : null,
            !empty($data['age']) ? (int) $data['age'] : null,
            $data['gender'] ?? null,
            $data['mobile_no'],
            $data['whatsapp_no'] ?? null,
            $data['email'],
            $data['aadhaar_no'] ?? null,
            $data['current_address'] ?? null,
            $data['city_district'] ?? null,
            $data['state'] ?? null,
            $data['pin_code'] ?? null,
            $data['nationality'] ?? 'Indian',

            $data['qualification_level'] ?? 'Diploma',
            $data['trade_branch'] ?? null,
            $data['institute_college'] ?? null,
            $data['year_passed'] ?? null,
            $data['percentage_cgpa'] ?? null,
            $data['additional_certifications'] ?? null,

            $data['employment_status'] ?? 'fresher',
            (int) ($data['experience_years'] ?? 0),
            (int) ($data['experience_months'] ?? 0),
            $data['current_designation'] ?? null,
            $data['current_company'] ?? null,
            $data['current_location'] ?? null,
            $data['current_salary'] ?? null,
            $data['expected_salary'] ?? null,
            $data['notice_period'] ?? null,
            $data['primary_skills'] ?? null,
            $data['secondary_skills'] ?? null,

            $data['passport_no'] ?? null,
            !empty($data['passport_expiry']) ? $data['passport_expiry'] : null,
            $data['place_of_issue'] ?? null,
            $data['ecnr_status'] ?? 'NA',
            $data['gcc_experience'] ?? 'No',
            $data['gcc_country_employer'] ?? null,
            $data['gcc_license_id'] ?? null,
            $data['visa_status'] ?? null,

            $data['resume_path'] ?? null,
            $data['photo_path'] ?? null,
            $data['aadhaar_path'] ?? null,
            $data['qualification_cert_path'] ?? null,
            $data['experience_cert_path'] ?? null,
            $data['passport_copy_path'] ?? null,
            $data['skill_cert_path'] ?? null,
            $data['driving_license_path'] ?? null,
            $data['other_docs_path'] ?? null,

            $data['emergency_name'] ?? null,
            $data['emergency_relationship'] ?? null,
            $data['emergency_mobile'] ?? null,
            $data['emergency_alt_mobile'] ?? null,

            $data['source'] ?? 'website',
            $data['ip_address'] ?? null,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function applicationFind(int $id): ?array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('
            SELECT a.*, j.title AS job_title, j.category AS job_category, adm.name AS assigned_name
            FROM job_applications a
            LEFT JOIN jobs j ON j.id = a.job_id
            LEFT JOIN admins adm ON adm.id = a.assigned_to
            WHERE a.id = ?
        ');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function applicationsList(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $pdo = Database::connection();
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'a.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['job_id'])) {
            $where[] = 'a.job_id = ?';
            $params[] = (int) $filters['job_id'];
        }
        if (!empty($filters['qualification'])) {
            $where[] = 'a.qualification_level = ?';
            $params[] = $filters['qualification'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(a.full_name LIKE ? OR a.email LIKE ? OR a.mobile_no LIKE ? OR a.reg_no LIKE ? OR a.trade_branch LIKE ?)';
            $wild = '%' . $filters['q'] . '%';
            $params[] = $wild;
            $params[] = $wild;
            $params[] = $wild;
            $params[] = $wild;
            $params[] = $wild;
        }

        $whereSql = implode(' AND ', $where);
        $offset = max(0, ($page - 1) * $perPage);

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM job_applications a WHERE $whereSql");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $sql = "SELECT a.*, j.title AS job_title
                FROM job_applications a
                LEFT JOIN jobs j ON j.id = a.job_id
                WHERE $whereSql
                ORDER BY a.created_at DESC
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

    public static function applicationUpdateStatus(int $id, string $status, ?int $assignedTo = null): bool
    {
        $pdo = Database::connection();
        $validStatuses = ['new', 'reviewing', 'shortlisted', 'interview', 'selected', 'rejected', 'hold'];
        if (!in_array($status, $validStatuses, true)) {
            $status = 'new';
        }

        $stmt = $pdo->prepare('UPDATE job_applications SET status = ?, assigned_to = ? WHERE id = ?');
        return $stmt->execute([$status, $assignedTo, $id]);
    }

    public static function applicationAddNote(int $applicationId, ?int $adminId, string $note): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('INSERT INTO job_application_notes (application_id, admin_id, note) VALUES (?, ?, ?)');
        $stmt->execute([$applicationId, $adminId, $note]);
    }

    public static function applicationNotes(int $applicationId): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('
            SELECT n.*, a.name AS admin_name
            FROM job_application_notes n
            LEFT JOIN admins a ON a.id = n.admin_id
            WHERE n.application_id = ?
            ORDER BY n.created_at DESC
        ');
        $stmt->execute([$applicationId]);
        return $stmt->fetchAll();
    }

    public static function applicationDelete(int $id): bool
    {
        $pdo = Database::connection();
        // First delete associated uploaded files
        $app = self::applicationFind($id);
        if ($app) {
            require_once __DIR__ . '/CandidateMedia.php';
            foreach ([
                'resume_path', 'photo_path', 'aadhaar_path', 'qualification_cert_path',
                'experience_cert_path', 'passport_copy_path', 'skill_cert_path',
                'driving_license_path', 'other_docs_path'
            ] as $key) {
                if (!empty($app[$key])) {
                    CandidateMedia::deleteFile($app[$key]);
                }
            }
        }
        $stmt = $pdo->prepare('DELETE FROM job_applications WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function stats(): array
    {
        $pdo = Database::connection();
        $totalJobs = 0;
        $activeJobs = 0;
        $totalApplications = 0;
        $newApplications = 0;

        try {
            $totalJobs = (int) $pdo->query('SELECT COUNT(*) FROM jobs')->fetchColumn();
            $activeJobs = (int) $pdo->query('SELECT COUNT(*) FROM jobs WHERE status = "active"')->fetchColumn();
            $totalApplications = (int) $pdo->query('SELECT COUNT(*) FROM job_applications')->fetchColumn();
            $newApplications = (int) $pdo->query('SELECT COUNT(*) FROM job_applications WHERE status = "new"')->fetchColumn();
        } catch (Throwable) {
            // Tables not created yet
        }

        return [
            'total_jobs' => $totalJobs,
            'active_jobs' => $activeJobs,
            'total_applications' => $totalApplications,
            'new_applications' => $newApplications,
        ];
    }

    private static function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/i', '-', $text) ?? '';
        return trim($text, '-') ?: 'job-' . bin2hex(random_bytes(3));
    }
}
