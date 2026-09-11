-- =======================================================================
-- KAM GLOBAL HR - DATABASE UPGRADE / MIGRATION SCRIPT
-- Purpose: Safely update an existing/old database to the latest schema
-- Compatible with MySQL 5.7+, MySQL 8.0+, MariaDB 10.2+
-- =======================================================================

-- 1. Ensure `jobs` table exists
CREATE TABLE IF NOT EXISTS jobs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(220) NOT NULL,
  slug VARCHAR(220) NOT NULL UNIQUE,
  category VARCHAR(80) NOT NULL DEFAULT 'Engineering',
  job_type VARCHAR(60) NOT NULL DEFAULT 'Full-time',
  location VARCHAR(150) NOT NULL DEFAULT 'India / GCC',
  image_url VARCHAR(500) NULL,
  experience_required VARCHAR(100) NULL,
  salary_range VARCHAR(100) NULL,
  vacancies INT NOT NULL DEFAULT 1,
  summary TEXT NULL,
  description MEDIUMTEXT NOT NULL,
  requirements MEDIUMTEXT NULL,
  status ENUM('active', 'closed', 'draft') NOT NULL DEFAULT 'active',
  sort_order INT NOT NULL DEFAULT 0,
  deadline DATE NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_jobs_status (status),
  INDEX idx_jobs_category (category)
) ENGINE=InnoDB;

-- 2. Add `image_url` to `jobs` if the table was created previously without it
SET @exist_img := (
    SELECT COUNT(*) 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'jobs' 
      AND COLUMN_NAME = 'image_url'
);

SET @sql_img = IF(@exist_img = 0, 'ALTER TABLE jobs ADD COLUMN image_url VARCHAR(500) NULL AFTER location', 'SELECT "image_url already exists"');
PREPARE stmt_img FROM @sql_img;
EXECUTE stmt_img;
DEALLOCATE PREPARE stmt_img;

-- 3. Ensure `job_applications` table exists (Candidate Registration Form)
CREATE TABLE IF NOT EXISTS job_applications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  job_id INT UNSIGNED NULL,
  reg_no VARCHAR(60) NULL UNIQUE,
  
  -- 1. Applicant Information
  full_name VARCHAR(190) NOT NULL,
  dob DATE NULL,
  age INT NULL,
  gender VARCHAR(20) NULL,
  mobile_no VARCHAR(40) NOT NULL,
  whatsapp_no VARCHAR(40) NULL,
  email VARCHAR(190) NOT NULL,
  aadhaar_no VARCHAR(40) NULL,
  current_address TEXT NULL,
  city_district VARCHAR(120) NULL,
  state VARCHAR(120) NULL,
  pin_code VARCHAR(20) NULL,
  nationality VARCHAR(80) NOT NULL DEFAULT 'Indian',

  -- 2. Educational / Technical Qualification
  qualification_level VARCHAR(60) NOT NULL DEFAULT 'Diploma',
  trade_branch VARCHAR(160) NULL,
  institute_college VARCHAR(220) NULL,
  year_passed VARCHAR(20) NULL,
  percentage_cgpa VARCHAR(40) NULL,
  additional_certifications TEXT NULL,

  -- 3. Employment / Experience Details
  employment_status ENUM('fresher', 'experienced') NOT NULL DEFAULT 'fresher',
  experience_years INT NOT NULL DEFAULT 0,
  experience_months INT NOT NULL DEFAULT 0,
  current_designation VARCHAR(160) NULL,
  current_company VARCHAR(190) NULL,
  current_location VARCHAR(150) NULL,
  current_salary VARCHAR(100) NULL,
  expected_salary VARCHAR(100) NULL,
  notice_period VARCHAR(60) NULL,
  primary_skills TEXT NULL,
  secondary_skills TEXT NULL,

  -- 4. Passport / Overseas Employment Details
  passport_no VARCHAR(60) NULL,
  passport_expiry DATE NULL,
  place_of_issue VARCHAR(120) NULL,
  ecnr_status VARCHAR(20) NULL,
  gcc_experience VARCHAR(20) NULL,
  gcc_country_employer TEXT NULL,
  gcc_license_id VARCHAR(100) NULL,
  visa_status VARCHAR(100) NULL,

  -- 5. Uploaded Documents (Replaces Checklist)
  resume_path VARCHAR(500) NULL,
  photo_path VARCHAR(500) NULL,
  aadhaar_path VARCHAR(500) NULL,
  qualification_cert_path VARCHAR(500) NULL,
  experience_cert_path VARCHAR(500) NULL,
  passport_copy_path VARCHAR(500) NULL,
  skill_cert_path VARCHAR(500) NULL,
  driving_license_path VARCHAR(500) NULL,
  other_docs_path VARCHAR(500) NULL,

  -- 6. Emergency Contact
  emergency_name VARCHAR(160) NULL,
  emergency_relationship VARCHAR(80) NULL,
  emergency_mobile VARCHAR(40) NULL,
  emergency_alt_mobile VARCHAR(40) NULL,

  -- 7. Status & Tracking
  status ENUM('new', 'reviewing', 'shortlisted', 'interview', 'selected', 'rejected', 'hold') NOT NULL DEFAULT 'new',
  source VARCHAR(60) NOT NULL DEFAULT 'website',
  notes TEXT NULL,
  assigned_to INT UNSIGNED NULL,
  ip_address VARCHAR(45) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  INDEX idx_apps_job (job_id),
  INDEX idx_apps_status (status),
  INDEX idx_apps_email (email),
  INDEX idx_apps_mobile (mobile_no),
  INDEX idx_apps_created (created_at),
  CONSTRAINT fk_apps_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE SET NULL,
  CONSTRAINT fk_apps_admin FOREIGN KEY (assigned_to) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 4. Ensure `job_application_notes` table exists
CREATE TABLE IF NOT EXISTS job_application_notes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  application_id INT UNSIGNED NOT NULL,
  admin_id INT UNSIGNED NULL,
  note TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_app_notes_app (application_id),
  CONSTRAINT fk_app_notes_app FOREIGN KEY (application_id) REFERENCES job_applications(id) ON DELETE CASCADE,
  CONSTRAINT fk_app_notes_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 5. Insert or Update latest active Job Vacancies (with Posters & Helpline numbers)
INSERT INTO jobs (id, title, slug, category, job_type, location, image_url, experience_required, salary_range, vacancies, summary, description, requirements, status, sort_order)
VALUES
(1, 'Fresh ITI Diploma Holders (Electrician / Plumber / HVAC / MEP)', 'fresh-iti-diploma-dubai', 'Technical & ITI', 'Full-time / Overseas', 'Dubai, UAE', 'assets/img/jobs/iti-dubai.png', 'Fresher / 0 - 2 Years', 'Attractive Gulf Scale + Accommodation', 25, 'Urgent requirement for Fresh ITI Diploma Holders for a Reputed Construction Company in Dubai. Roles open for Electrician, Plumber, HVAC / AC Technician, and MEP Technician.', 'We are looking for Fresh ITI Diploma Holders for a Reputed Construction Company in Dubai.\n\nOpen Roles:\n• Electrician\n• Plumber\n• HVAC / AC Technician\n• MEP Technician\n\nInterviews in: Bangalore & Mumbai\nEligibility: Only for Male Candidates\nContact / WhatsApp: +91 8050766464, +91 9187227450, +91 9686171617', '• ITI / Diploma Holder in Electrical, RAC/HVAC, Plumbing, or MEP Trades\n• Fresher or up to 2 years experience\n• Only for Male Candidates with valid Passport\n• In-person / Client interviews in Bangalore & Mumbai\n• Willingness to relocate and start an international career in Dubai, UAE', 'active', 1),
(2, 'Fresh Civil Engineers (Diploma / B.E. / B.Tech)', 'fresh-civil-engineers-dubai', 'Engineering', 'Full-time / Overseas', 'Dubai, UAE', 'assets/img/jobs/civil-dubai.png', 'Fresher / 0 - 2 Years', 'Attractive Package + Visa + Accommodation', 15, 'Urgent opening for Fresh Civil Engineers (Diploma / B.E. / B.Tech) for a Reputed Construction Company in Dubai. Build your career abroad with large-scale projects.', 'Looking for Fresh Civil Engineers to start their career abroad with a Reputed Construction Company in Dubai.\n\nKey Highlights:\n• Work with a Reputed Construction Group\n• Gain High-value International Exposure\n• Build a Strong Engineering Career Foundation\n• Be Part of Exciting Commercial & Infrastructure Projects\n\nInterviews in: Bangalore & Mumbai\nEligibility: Only for Male Candidates\nContact / WhatsApp: +91 8050766464, +91 9187227450, +91 9686171617', '• Diploma / B.E. / B.Tech in Civil Engineering\n• Fresher or 0 - 2 Years Experience\n• Only for Male Candidates with valid Passport\n• Basic understanding of structural drawings, site supervision, and AutoCAD\n• Client interviews in Bangalore & Mumbai', 'active', 2),
(3, 'Mechanical Site Engineer (MEP)', 'mechanical-site-engineer-mep', 'Engineering', 'Full-time / Overseas', 'Kuwait / GCC', 'assets/img/industry_engineering_mfg.png', '3 - 6 Years', 'Competitive + Accommodation', 5, 'Looking for experienced Mechanical Engineers with Diploma/B.E. for HVAC and plumbing installations in commercial projects.', 'Oversee installation, testing, and commissioning of MEP systems. Coordinate with consultants, site supervisors, and subcontractors to ensure compliance with project specifications.', '• Diploma / B.E. in Mechanical Engineering\n• 3+ years experience in MEP / HVAC execution\n• GCC experience & valid driving license preferred\n• Proficiency in AutoCAD / Revit is an advantage', 'active', 3),
(4, 'Electrical Supervisor / Wireman', 'electrical-supervisor-wireman', 'Technical & ITI', 'Full-time', 'Bengaluru, India', 'assets/img/contract_staffing.png', '2 - 5 Years', '₹25,000 - ₹40,000 / month', 8, 'ITI / Diploma Electrical professionals required for industrial wiring, panel installation, and facility maintenance.', 'Responsible for cable laying, LT/HT panel erection, transformer maintenance, and safety inspections at industrial sites.', '• ITI (Electrician) / Diploma in Electrical Engineering\n• Valid Wireman / Supervisor license\n• Knowledge of industrial electrical safety norms', 'active', 4),
(5, 'QA / QC Inspector (Welding & Piping)', 'qa-qc-inspector-welding-piping', 'Manufacturing', 'Contract', 'Saudi Arabia / UAE', 'assets/img/executive_leadership_boardroom.png', '4 - 8 Years', 'Attractive Tax-Free Package', 4, 'Certified QA/QC Inspectors needed for oil & gas and heavy fabrication projects in the Gulf region.', 'Conduct non-destructive testing (NDT), weld visual inspection, hydrostatic test witness, and maintain inspection documentation as per ISO standards.', '• Diploma / Degree in Mechanical / Metallurgy\n• CSWIP 3.1 or AWS-CWI certification mandatory\n• NDT Level II (UT, MT, PT, RT)\n• Minimum 4 years in fabrication or EPC projects', 'active', 5)
ON DUPLICATE KEY UPDATE 
  title = VALUES(title), 
  category = VALUES(category), 
  location = VALUES(location), 
  image_url = VALUES(image_url), 
  experience_required = VALUES(experience_required), 
  salary_range = VALUES(salary_range),
  vacancies = VALUES(vacancies),
  summary = VALUES(summary),
  description = VALUES(description),
  requirements = VALUES(requirements),
  status = VALUES(status);

-- 6. Ensure Super Admin Account exists in database
INSERT INTO admins (id, name, email, password_hash, role, is_active)
VALUES (
  1,
  'KAM Super Admin',
  'admin@kamglobalhr.com',
  '$2y$10$1eZDSyQFLEagjmfeuh3EMei1VOwvrijkxn1lSPnn7.y110pimHQPq',
  'super_admin',
  1
) ON DUPLICATE KEY UPDATE
  password_hash = VALUES(password_hash),
  is_active = 1;
