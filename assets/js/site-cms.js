/**
 * Hydrates static HTML from api/cms.php (footer, offices, insights, case studies, home blocks).
 */
(function () {
    'use strict';

    var CMS_API = (function () {
        try {
            return new URL('api/cms.php', window.location.href).href;
        } catch (e) {
            return 'api/cms.php';
        }
    })();

    function esc(s) {
        if (s == null) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function fetchCms(scope) {
        return fetch(CMS_API + '?scope=' + encodeURIComponent(scope), {
            headers: { Accept: 'application/json' },
        }).then(function (r) {
            if (!r.ok) throw new Error('CMS fetch failed');
            return r.json();
        });
    }

    function setting(settings, key, fallback) {
        return settings && settings[key] != null && settings[key] !== ''
            ? settings[key]
            : fallback;
    }

    var INSIGHT_IMG_FALLBACK = 'assets/img/about-team.png';

    function categoryLabel(cat) {
        var labels = {
            recruitment: 'Recruitment',
            'hr-advisory': 'HR Advisory',
            payroll: 'Payroll',
            'executive-search': 'Executive Search',
            'industry-reports': 'Industry Reports',
            general: 'General',
        };
        return labels[cat] || cat;
    }

    function insightImageSrc(item) {
        return item && item.image_url ? item.image_url : INSIGHT_IMG_FALLBACK;
    }

    function insightCardMedia(item) {
        return '<div class="insights-card__media">' +
            '<img src="' + esc(insightImageSrc(item)) + '" alt="' + esc(item.title || 'Insight') + '" loading="lazy" decoding="async"/>' +
            '</div>';
    }

    function caseCardHtml(c, idx, compact) {
        var delay = idx * 50;
        if (compact) {
            return '<a class="home-case-card" href="case-studies.html" data-aos="fade-up" data-aos-delay="' + delay + '">' +
                '<span class="home-case-card__badge">' + esc(c.industry) + '</span>' +
                '<h3 class="home-case-card__title">' + esc(c.title) + '</h3>' +
                '<p class="home-case-card__excerpt">' + esc(c.summary || c.outcome || '') + '</p>' +
                '<span class="home-case-card__cta">Read story <span class="material-symbols-outlined text-[16px]">arrow_forward</span></span></a>';
        }
        return '<article class="cs-card" data-aos="fade-up" data-aos-delay="' + delay + '">' +
            '<span class="cs-badge">' + esc(c.industry) + '</span>' +
            '<h3>' + esc(c.title) + '</h3>' +
            (c.summary ? '<p class="cs-card__lead">' + esc(c.summary) + '</p>' : '') +
            '<dl class="cs-card__details">' +
            '<div><dt>Challenge</dt><dd>' + esc(c.challenge) + '</dd></div>' +
            '<div><dt>Solution</dt><dd>' + esc(c.solution) + '</dd></div>' +
            '<div><dt>Outcome</dt><dd>' + esc(c.outcome) + '</dd></div></dl>' +
            '<a href="contact.html" class="cs-card__link">Discuss a similar project <span class="material-symbols-outlined">arrow_forward</span></a></article>';
    }

    function applyTrustBand(settings) {
        var band = document.querySelector('[data-cms="trust-band-stats"]');
        if (!band || !settings) return;

        var items = [
            { icon: 'history', value: setting(settings, 'stat_years_value', '22+'), label: setting(settings, 'stat_years_label', 'Years experience') },
            { icon: 'handshake', value: setting(settings, 'stat_clients_value', '100+'), label: setting(settings, 'stat_clients_label', 'Client relationships') },
            { icon: 'public', value: setting(settings, 'stat_countries_value', '7+'), label: setting(settings, 'stat_countries_label', 'Countries served') },
            { icon: 'domain', value: setting(settings, 'stat_industries_value', '5+'), label: setting(settings, 'stat_industries_label', 'Industries covered') },
        ];

        band.innerHTML = items.map(function (item) {
            return '<div class="trust-stat">' +
                '<span class="trust-stat__icon material-symbols-outlined" aria-hidden="true">' + esc(item.icon) + '</span>' +
                '<strong>' + esc(item.value) + '</strong>' +
                '<span>' + esc(item.label) + '</span></div>';
        }).join('');
        band.classList.add('cms-loaded');
    }

    function applySettings(settings) {
        if (!settings) return;

        var email = setting(settings, 'contact_email', 'info@kamgroups.com');
        var phone = setting(settings, 'contact_phone', '');

        document.querySelectorAll('[data-cms="contact-email"]').forEach(function (el) {
            el.textContent = email;
            if (el.tagName === 'A') el.setAttribute('href', 'mailto:' + email);
        });
        document.querySelectorAll('[data-cms="contact-email-href"]').forEach(function (el) {
            el.setAttribute('href', 'mailto:' + email);
            if (!el.textContent.trim()) el.textContent = email;
        });

        document.querySelectorAll('[data-cms="contact-phone"]').forEach(function (el) {
            if (!phone) return;
            el.textContent = phone;
            var tel = phone.replace(/[^\d+]/g, '');
            if (el.tagName === 'A') el.setAttribute('href', 'tel:' + tel);
        });
        document.querySelectorAll('[data-cms="contact-phone-href"]').forEach(function (el) {
            if (!phone) return;
            var tel = phone.replace(/[^\d+]/g, '');
            el.setAttribute('href', 'tel:' + tel);
            if (!el.textContent.trim()) el.textContent = phone;
        });

        document.querySelectorAll('[data-cms="site-tagline"]').forEach(function (el) {
            var tagline = setting(settings, 'site_tagline', '');
            if (tagline) el.textContent = tagline;
        });

        document.querySelectorAll('[data-cms="copyright"]').forEach(function (el) {
            var t = setting(settings, 'copyright_text', el.textContent);
            if (t) el.textContent = t;
        });

        var socialMap = {
            linkedin: 'social_linkedin',
            facebook: 'social_facebook',
            x: 'social_x',
            youtube: 'social_youtube',
        };
        Object.keys(socialMap).forEach(function (network) {
            var url = setting(settings, socialMap[network], '');
            if (!url || url === '#') return;
            document.querySelectorAll('[data-cms-social="' + network + '"]').forEach(function (el) {
                el.setAttribute('href', url);
            });
        });

        var trust = document.querySelector('[data-cms="footer-trust"]');
        if (trust) {
            var items = [
                { icon: 'engineering', value: setting(settings, 'stat_years_value', '22+'), label: setting(settings, 'stat_years_label', 'Workforce Excellence') },
                { icon: 'work', value: setting(settings, 'stat_clients_value', '100+'), label: setting(settings, 'stat_clients_label', 'Client Relationships') },
                { icon: 'public', value: setting(settings, 'stat_countries_value', '7+ Countries'), label: setting(settings, 'stat_countries_label', 'Global Presence') },
                { icon: 'verified_user', value: setting(settings, 'stat_partner_value', 'Trusted Partner'), label: setting(settings, 'stat_partner_label', 'Compliance & Quality Driven') },
            ];
            trust.innerHTML = items.map(function (item) {
                return '<div class="site-footer__trust-item">' +
                    '<span class="site-footer__trust-icon material-symbols-outlined" aria-hidden="true">' + esc(item.icon) + '</span>' +
                    '<span class="site-footer__trust-text"><strong>' + esc(item.value) + '</strong><span>' + esc(item.label) + '</span></span></div>';
            }).join('');
        }

        applyTrustBand(settings);
    }

    function renderOffices(offices) {
        var container = document.querySelector('[data-cms="offices"]');
        if (!container || !offices || !offices.length) return;

        container.innerHTML = offices.map(function (o) {
            var href = o.map_url || '#';
            var lines = esc(o.address_line1) + (o.address_line2 ? '<br>' + esc(o.address_line2) : '');
            return '<a class="site-footer__office-card" href="' + esc(href) + '" target="_blank" rel="noopener noreferrer">' +
                '<span class="site-footer__office-pin material-symbols-outlined" aria-hidden="true">location_on</span>' +
                '<span class="site-footer__office-body">' +
                '<span class="site-footer__office-title">' + esc(o.title) + '</span>' +
                '<span class="site-footer__office-lines">' + lines + '</span></span></a>';
        }).join('');

        var contactOffices = document.querySelector('[data-cms="contact-offices"]');
        if (contactOffices) {
            contactOffices.innerHTML = offices.map(function (o) {
                var href = o.map_url || '#';
                var lines = esc(o.address_line1) + (o.address_line2 ? '<br/>' + esc(o.address_line2) : '');
                return '<a class="contact-hubs__office" href="' + esc(href) + '" target="_blank" rel="noopener noreferrer">' +
                    '<span class="contact-hubs__office-city">' + esc(o.title) + '</span>' +
                    '<span class="contact-hubs__office-lines">' + lines + '</span>' +
                    '<span class="contact-hubs__office-cta">View on map <span class="material-symbols-outlined" aria-hidden="true">open_in_new</span></span></a>';
            }).join('');
        }
    }

    function renderInsights(insights) {
        if (!insights || !insights.length) return;

        var articles = insights.filter(function (i) { return i.content_type === 'article'; });
        var reports = insights.filter(function (i) { return i.content_type === 'report'; });
        var featured = articles.find(function (i) { return Number(i.is_featured) === 1; }) || articles[0];

        if (featured) {
            var feat = document.querySelector('[data-cms="insight-featured"]');
            if (feat) {
                feat.classList.add('cms-loaded');
                var img = feat.querySelector('.insights-featured__visual img');
                if (img) {
                    img.setAttribute('src', insightImageSrc(featured));
                    img.setAttribute('alt', featured.title);
                }
                var title = feat.querySelector('.insights-featured__title');
                if (title) title.textContent = featured.title;
                var desc = feat.querySelector('.insights-featured__desc');
                if (desc) desc.textContent = featured.excerpt || '';
                var link = feat.querySelector('.insights-featured__copy a.page-btn');
                if (link) {
                    link.setAttribute('href', 'insight.php?slug=' + encodeURIComponent(featured.slug));
                    link.innerHTML = 'Read Full Article <span class="material-symbols-outlined text-[18px]">arrow_forward</span>';
                }
                var badge = feat.querySelector('.insights-badge--featured');
                if (badge) badge.textContent = categoryLabel(featured.category);
            }
        }

        var grid = document.querySelector('[data-cms="insights-articles"]');
        if (grid && articles.length) {
            grid.classList.add('cms-loaded');
            grid.innerHTML = articles.map(function (a, idx) {
                return '<article class="insights-article-card insights-filterable" data-category="' + esc(a.category) + '" data-aos="fade-up" data-aos-delay="' + (idx * 50) + '">' +
                    insightCardMedia(a) +
                    '<div class="insights-card__body">' +
                    '<span class="insights-badge">' + esc(categoryLabel(a.category)) + '</span>' +
                    '<h3>' + esc(a.title) + '</h3>' +
                    '<p>' + esc(a.excerpt || '') + '</p>' +
                    '<a href="insight.php?slug=' + encodeURIComponent(a.slug) + '" class="insights-article-card__link">Read article <span class="material-symbols-outlined">arrow_forward</span></a></div></article>';
            }).join('');
        }

        var reportsGrid = document.querySelector('[data-cms="insights-reports"]');
        if (reportsGrid && reports.length) {
            reportsGrid.classList.add('cms-loaded');
            reportsGrid.innerHTML = reports.map(function (r, idx) {
                var href = r.download_url || ('insight.php?slug=' + encodeURIComponent(r.slug));
                return '<article class="insights-report-card insights-filterable" data-category="' + esc(r.category) + '" data-aos="fade-up" data-aos-delay="' + (idx * 50) + '">' +
                    insightCardMedia(r) +
                    '<div class="insights-card__body">' +
                    '<span class="insights-badge insights-badge--report">Report</span>' +
                    '<h3>' + esc(r.title) + '</h3>' +
                    '<p>' + esc(r.excerpt || '') + '</p>' +
                    '<a href="' + esc(href) + '" class="insights-report-card__btn page-btn page-btn--ghost">Download <span class="material-symbols-outlined text-[16px]">download</span></a></div></article>';
            }).join('');
        }

        renderHomeInsights(articles);
    }

    function renderHomeInsights(articles) {
        var grid = document.querySelector('[data-cms="home-insights"]');
        if (!grid || !articles || !articles.length) return;

        var picked = articles.slice(0, 3);
        grid.classList.add('cms-loaded');
        grid.innerHTML = picked.map(function (a, idx) {
            return '<a class="home-insight-card" href="insight.php?slug=' + encodeURIComponent(a.slug) + '" data-aos="fade-up" data-aos-delay="' + (idx * 60) + '">' +
                '<div class="home-insight-card__media"><img src="' + esc(insightImageSrc(a)) + '" alt="' + esc(a.title) + '" loading="lazy" decoding="async"/></div>' +
                '<span class="home-insight-card__badge">' + esc(categoryLabel(a.category)) + '</span>' +
                '<h3 class="home-insight-card__title">' + esc(a.title) + '</h3>' +
                '<p class="home-insight-card__excerpt">' + esc(a.excerpt || '') + '</p>' +
                '<span class="home-insight-card__cta">Read article <span class="material-symbols-outlined text-[16px]">arrow_forward</span></span></a>';
        }).join('');
    }

    function renderCaseStudies(cases) {
        var grid = document.querySelector('[data-cms="case-studies-grid"]');
        if (!grid || !cases || !cases.length) return;

        grid.classList.add('cms-loaded');
        grid.innerHTML = cases.map(function (c, idx) {
            return caseCardHtml(c, idx, false);
        }).join('');

        renderHomeCaseStudies(cases);
    }

    function renderHomeCaseStudies(cases) {
        var grid = document.querySelector('[data-cms="home-case-studies"]');
        if (!grid || !cases || !cases.length) return;

        var sorted = cases.slice().sort(function (a, b) {
            return (Number(b.is_featured) || 0) - (Number(a.is_featured) || 0);
        });
        var picked = sorted.slice(0, 3);
        grid.classList.add('cms-loaded');
        grid.innerHTML = picked.map(function (c, idx) {
            return caseCardHtml(c, idx, true);
        }).join('');
    }

    function renderTestimonials(items) {
        var el = document.querySelector('[data-cms="testimonial-quote"]');
        if (!el || !items || !items.length) return;

        var t = items[0];
        el.classList.add('cms-loaded');
        var quote = el.querySelector('blockquote, .home-testimonial__quote, .quote-spotlight__text, p');
        if (quote) quote.textContent = t.quote;
        var cite = el.querySelector('cite, .home-testimonial__cite, .quote-spotlight__cite, .quote-spotlight__meta');
        if (cite) {
            cite.textContent = (t.name || '') +
                (t.role_title ? ' — ' + t.role_title : '') +
                (t.company ? ', ' + t.company : '');
        }
    }

    function init() {
        fetchCms('all').then(function (data) {
            if (!data.ok) return;
            applySettings(data.settings);
            renderOffices(data.offices);
            renderInsights(data.insights);
            renderCaseStudies(data.case_studies);
            renderTestimonials(data.testimonials);
            document.dispatchEvent(new CustomEvent('cms:hydrated'));
        }).catch(function () {
            /* Keep static HTML fallback */
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
