-- KAM Global HR CRM schema
-- Run: mysql -u root -p < database/schema.sql
-- Or import via phpMyAdmin

CREATE DATABASE IF NOT EXISTS u879823199_kamglobal_hr
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE u879823199_kamglobal_hr;

CREATE TABLE IF NOT EXISTS admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('super_admin', 'admin', 'sales') NOT NULL DEFAULT 'admin',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default Admin User (Email: admin@kamglobalhr.com | Password: admin123)
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

CREATE TABLE IF NOT EXISTS leads (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(40) NULL,
  company VARCHAR(190) NULL,
  inquiry_type VARCHAR(80) NOT NULL DEFAULT 'general',
  message TEXT NULL,
  source VARCHAR(60) NOT NULL DEFAULT 'website',
  status ENUM('new', 'contacted', 'qualified', 'proposal', 'won', 'lost') NOT NULL DEFAULT 'new',
  priority ENUM('low', 'normal', 'high') NOT NULL DEFAULT 'normal',
  assigned_to INT UNSIGNED NULL,
  ip_address VARCHAR(45) NULL,
  user_agent VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_leads_status (status),
  INDEX idx_leads_email (email),
  INDEX idx_leads_created (created_at),
  CONSTRAINT fk_leads_assigned FOREIGN KEY (assigned_to) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS lead_notes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  lead_id INT UNSIGNED NOT NULL,
  admin_id INT UNSIGNED NULL,
  note TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_notes_lead (lead_id),
  CONSTRAINT fk_notes_lead FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
  CONSTRAINT fk_notes_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS newsletter_subscribers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  status ENUM('active', 'unsubscribed') NOT NULL DEFAULT 'active',
  source VARCHAR(60) NOT NULL DEFAULT 'insights',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_subscribers_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS activity_log (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id INT UNSIGNED NULL,
  entity_type VARCHAR(40) NOT NULL,
  entity_id INT UNSIGNED NULL,
  action VARCHAR(80) NOT NULL,
  meta JSON NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_activity_created (created_at),
  CONSTRAINT fk_activity_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Site CMS (managed from admin)
CREATE TABLE IF NOT EXISTS site_settings (
  setting_key VARCHAR(80) NOT NULL PRIMARY KEY,
  setting_value TEXT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS offices (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(120) NOT NULL,
  address_line1 VARCHAR(190) NOT NULL,
  address_line2 VARCHAR(190) NULL,
  phone VARCHAR(40) NULL,
  email VARCHAR(190) NULL,
  map_url VARCHAR(500) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_offices_sort (sort_order)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS insights (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(220) NOT NULL,
  slug VARCHAR(220) NOT NULL UNIQUE,
  excerpt TEXT NULL,
  body_html MEDIUMTEXT NULL,
  category VARCHAR(60) NOT NULL DEFAULT 'general',
  content_type ENUM('article', 'report') NOT NULL DEFAULT 'article',
  image_url VARCHAR(500) NULL,
  download_url VARCHAR(500) NULL,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
  sort_order INT NOT NULL DEFAULT 0,
  published_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_insights_status (status),
  INDEX idx_insights_featured (is_featured),
  INDEX idx_insights_category (category)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS case_studies (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(220) NOT NULL,
  slug VARCHAR(220) NOT NULL UNIQUE,
  industry VARCHAR(120) NOT NULL DEFAULT 'General',
  summary VARCHAR(500) NULL,
  challenge TEXT NOT NULL,
  solution TEXT NOT NULL,
  outcome TEXT NOT NULL,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
  sort_order INT NOT NULL DEFAULT 0,
  published_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_cases_status (status),
  INDEX idx_cases_featured (is_featured)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS testimonials (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  role_title VARCHAR(160) NULL,
  company VARCHAR(160) NULL,
  quote TEXT NOT NULL,
  image_url VARCHAR(500) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Jobs / Career Openings
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

-- Candidate Job Applications (Candidate Registration Form)
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

-- Application Notes
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

-- Default Job Openings Seed Data
INSERT INTO jobs (id, title, slug, category, job_type, location, image_url, experience_required, salary_range, vacancies, summary, description, requirements, status, sort_order)
VALUES
(1, 'Fresh ITI Diploma Holders (Electrician / Plumber / HVAC / MEP)', 'fresh-iti-diploma-dubai', 'Technical & ITI', 'Full-time / Overseas', 'Dubai, UAE', 'assets/img/jobs/iti-dubai.png', 'Fresher / 0 - 2 Years', 'Attractive Gulf Scale + Accommodation', 25, 'Urgent requirement for Fresh ITI Diploma Holders for a Reputed Construction Company in Dubai. Roles open for Electrician, Plumber, HVAC / AC Technician, and MEP Technician.', 'We are looking for Fresh ITI Diploma Holders for a Reputed Construction Company in Dubai.\n\nOpen Roles:\n• Electrician\n• Plumber\n• HVAC / AC Technician\n• MEP Technician\n\nInterviews in: Bangalore & Mumbai\nEligibility: Only for Male Candidates\nContact / WhatsApp: +91 8050766464, +91 9187227450, +91 9686171617', '• ITI / Diploma Holder in Electrical, RAC/HVAC, Plumbing, or MEP Trades\n• Fresher or up to 2 years experience\n• Only for Male Candidates with valid Passport\n• In-person / Client interviews in Bangalore & Mumbai\n• Willingness to relocate and start an international career in Dubai, UAE', 'active', 1),
(2, 'Fresh Civil Engineers (Diploma / B.E. / B.Tech)', 'fresh-civil-engineers-dubai', 'Engineering', 'Full-time / Overseas', 'Dubai, UAE', 'assets/img/jobs/civil-dubai.png', 'Fresher / 0 - 2 Years', 'Attractive Package + Visa + Accommodation', 15, 'Urgent opening for Fresh Civil Engineers (Diploma / B.E. / B.Tech) for a Reputed Construction Company in Dubai. Build your career abroad with large-scale projects.', 'Looking for Fresh Civil Engineers to start their career abroad with a Reputed Construction Company in Dubai.\n\nKey Highlights:\n• Work with a Reputed Construction Group\n• Gain High-value International Exposure\n• Build a Strong Engineering Career Foundation\n• Be Part of Exciting Commercial & Infrastructure Projects\n\nInterviews in: Bangalore & Mumbai\nEligibility: Only for Male Candidates\nContact / WhatsApp: +91 8050766464, +91 9187227450, +91 9686171617', '• Diploma / B.E. / B.Tech in Civil Engineering\n• Fresher or 0 - 2 Years Experience\n• Only for Male Candidates with valid Passport\n• Basic understanding of structural drawings, site supervision, and AutoCAD\n• Client interviews in Bangalore & Mumbai', 'active', 2),
(3, 'Mechanical Site Engineer (MEP)', 'mechanical-site-engineer-mep', 'Engineering', 'Full-time / Overseas', 'Kuwait / GCC', 'assets/img/industry_engineering_mfg.png', '3 - 6 Years', 'Competitive + Accommodation', 5, 'Looking for experienced Mechanical Engineers with Diploma/B.E. for HVAC and plumbing installations in commercial projects.', 'Oversee installation, testing, and commissioning of MEP systems. Coordinate with consultants, site supervisors, and subcontractors to ensure compliance with project specifications.', '• Diploma / B.E. in Mechanical Engineering\n• 3+ years experience in MEP / HVAC execution\n• GCC experience & valid driving license preferred\n• Proficiency in AutoCAD / Revit is an advantage', 'active', 3),
(4, 'Electrical Supervisor / Wireman', 'electrical-supervisor-wireman', 'Technical & ITI', 'Full-time', 'Bengaluru, India', 'assets/img/contract_staffing.png', '2 - 5 Years', '₹25,000 - ₹40,000 / month', 8, 'ITI / Diploma Electrical professionals required for industrial wiring, panel installation, and facility maintenance.', 'Responsible for cable laying, LT/HT panel erection, transformer maintenance, and safety inspections at industrial sites.', '• ITI (Electrician) / Diploma in Electrical Engineering\n• Valid Wireman / Supervisor license\n• Knowledge of industrial electrical safety norms', 'active', 4),
(5, 'QA / QC Inspector (Welding & Piping)', 'qa-qc-inspector-welding-piping', 'Manufacturing', 'Contract', 'Saudi Arabia / UAE', 'assets/img/executive_leadership_boardroom.png', '4 - 8 Years', 'Attractive Tax-Free Package', 4, 'Certified QA/QC Inspectors needed for oil & gas and heavy fabrication projects in the Gulf region.', 'Conduct non-destructive testing (NDT), weld visual inspection, hydrostatic test witness, and maintain inspection documentation as per ISO standards.', '• Diploma / Degree in Mechanical / Metallurgy\n• CSWIP 3.1 or AWS-CWI certification mandatory\n• NDT Level II (UT, MT, PT, RT)\n• Minimum 4 years in fabrication or EPC projects', 'active', 5)
ON DUPLICATE KEY UPDATE title = VALUES(title), category = VALUES(category), location = VALUES(location), image_url = VALUES(image_url), experience_required = VALUES(experience_required), summary = VALUES(summary);


