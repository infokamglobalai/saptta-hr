<?php
declare(strict_types=1);

require_once __DIR__ . '/CmsRepository.php';

function kam_cms_seed_defaults(): void
{
    $pdo = Database::connection();
    $count = (int) $pdo->query('SELECT COUNT(*) FROM site_settings')->fetchColumn();
    if ($count > 0) {
        return;
    }

    CmsRepository::settingsSave([
        'site_tagline' => 'Empowering organizations worldwide with recruitment excellence, workforce solutions, payroll services, and HR expertise to build stronger, future-ready teams.',
        'contact_email' => 'info@kamgroups.com',
        'contact_phone' => '+91 80 0000 0000',
        'social_linkedin' => 'https://www.linkedin.com/company/kamglobalhr',
        'social_facebook' => 'https://www.facebook.com/kamglobalhr',
        'social_x' => 'https://x.com/kamglobalhr',
        'social_youtube' => 'https://www.youtube.com/@kamglobalhr',
        'stat_years_value' => '22+',
        'stat_years_label' => 'Workforce Excellence',
        'stat_clients_value' => '100+',
        'stat_clients_label' => 'Client Relationships',
        'stat_countries_value' => '7+ Countries',
        'stat_countries_label' => 'Global Presence',
        'stat_partner_value' => 'Trusted Partner',
        'stat_partner_label' => 'Compliance & Quality Driven',
        'copyright_text' => '© 2026 KAM Global HR. All rights reserved.',
    ]);

    $offices = [
        [
            'title' => 'India',
            'address_line1' => 'RT Nagar,',
            'address_line2' => 'Bengaluru, KA',
            'map_url' => 'https://www.google.com/maps/place/KAM+GLOBAL+AI/@13.3104691,75.2165459,7z',
            'sort_order' => 1,
        ],
        [
            'title' => 'Kuwait',
            'address_line1' => 'Block 4, Tunisia St.,',
            'address_line2' => 'Hawally',
            'map_url' => 'https://www.google.com/maps/place/KAM+International+Group+Co./@29.3438775,48.0144258,17z',
            'sort_order' => 2,
        ],
    ];
    foreach ($offices as $office) {
        CmsRepository::officeSave(null, array_merge($office, ['is_active' => 1]));
    }

    $articles = [
        [
            'title' => 'The Future of Workforce Management in a Changing Business Landscape',
            'slug' => 'future-of-workforce-management',
            'excerpt' => 'Explore emerging workforce trends, evolving employee expectations, and strategies organizations can adopt to build resilient teams.',
            'body_html' => '<p>Organizations worldwide are rethinking how they attract, retain, and develop talent. This article outlines practical steps for workforce resilience.</p>',
            'category' => 'hr-advisory',
            'content_type' => 'article',
            'image_url' => 'assets/img/about-team.png',
            'is_featured' => 1,
            'status' => 'published',
            'sort_order' => 0,
        ],
        [
            'title' => 'Recruitment Trends Shaping the Future of Hiring',
            'slug' => 'recruitment-trends-future-hiring',
            'excerpt' => 'How organizations are adapting sourcing, assessment, and offer strategies in competitive talent markets.',
            'body_html' => '<p>From skills-based hiring to employer branding, recruitment is evolving rapidly across GCC and India markets.</p>',
            'category' => 'recruitment',
            'content_type' => 'article',
            'status' => 'published',
            'sort_order' => 1,
        ],
        [
            'title' => 'How Organizations Can Improve Employee Retention',
            'slug' => 'improve-employee-retention',
            'excerpt' => 'Practical approaches to engagement, career pathways, and workforce experience that reduce attrition.',
            'body_html' => '<p>Retention starts with clarity on role design, manager capability, and measurable engagement drivers.</p>',
            'category' => 'hr-advisory',
            'content_type' => 'article',
            'status' => 'published',
            'sort_order' => 2,
        ],
        [
            'title' => '2026 Workforce Trends Report',
            'slug' => '2026-workforce-trends-report',
            'excerpt' => 'Annual outlook on hiring demand, workforce mobility, and sector-specific talent dynamics.',
            'body_html' => '<p>Download our summary of workforce trends across manufacturing, healthcare, and technology sectors.</p>',
            'category' => 'industry-reports',
            'content_type' => 'report',
            'status' => 'published',
            'sort_order' => 10,
        ],
    ];
    foreach ($articles as $article) {
        CmsRepository::insightSave(null, $article);
    }

    $cases = [
        [
            'title' => 'Scaling Contract Workforce for a Regional Logistics Operator',
            'slug' => 'logistics-contract-workforce',
            'industry' => 'Logistics',
            'summary' => 'Rapid deployment of compliant contract staff across multiple sites.',
            'challenge' => 'Seasonal demand spikes required 200+ contract workers within six weeks while maintaining compliance.',
            'solution' => 'KAM Global HR deployed a structured sourcing, onboarding, and attendance framework with local payroll coordination.',
            'outcome' => '98% fill rate achieved; onboarding time reduced by 35% with zero major compliance findings.',
            'is_featured' => 1,
            'status' => 'published',
            'sort_order' => 0,
        ],
        [
            'title' => 'Healthcare Staffing for Multi-Site Hospital Network',
            'slug' => 'healthcare-multi-site-staffing',
            'industry' => 'Healthcare',
            'summary' => 'Clinical and non-clinical hiring across India and GCC facilities.',
            'challenge' => 'High attrition and credential verification delays impacted patient-facing roles.',
            'solution' => 'Integrated recruitment pipeline with BGV, credential checks, and replacement SLAs.',
            'outcome' => 'Time-to-fill improved by 28%; verified hires increased with stronger retention in critical roles.',
            'status' => 'published',
            'sort_order' => 1,
        ],
        [
            'title' => 'Payroll Harmonization for Manufacturing Group',
            'slug' => 'manufacturing-payroll-harmonization',
            'industry' => 'Manufacturing',
            'summary' => 'Unified payroll operations across three countries.',
            'challenge' => 'Fragmented payroll vendors created reporting gaps and statutory risk.',
            'solution' => 'Centralized payroll outsourcing with standardized controls and monthly governance reviews.',
            'outcome' => 'Single reporting view established; payroll accuracy and audit readiness significantly improved.',
            'status' => 'published',
            'sort_order' => 2,
        ],
    ];
    foreach ($cases as $case) {
        CmsRepository::caseSave(null, $case);
    }

    CmsRepository::testimonialSave(null, [
        'name' => 'Leadership Team',
        'role_title' => 'Enterprise Client',
        'company' => 'Multi-country operations',
        'quote' => 'KAM Global HR brings disciplined workforce delivery with the consulting depth we expect from a strategic HR partner.',
        'sort_order' => 0,
        'is_active' => 1,
    ]);
}
