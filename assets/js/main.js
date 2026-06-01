// Global JavaScript for Kam Global HR

document.addEventListener('DOMContentLoaded', () => {
    // Initialize AOS (Animate On Scroll)
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 750,
            easing: 'ease-out-cubic',
            once: true,
            offset: 60,
            delay: 0,
            anchorPlacement: 'top-bottom'
        });
    }

    // Site header: scroll state + mobile menu
    const siteHeader = document.getElementById('site-header');
    if (siteHeader) {
        const onScroll = () => {
            siteHeader.classList.toggle('site-header--scrolled', window.scrollY > 16);
        };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');

    if (mobileMenuBtn && mobileMenu) {
        const icon = mobileMenuBtn.querySelector('.material-symbols-outlined');
        const openMenu = () => {
            mobileMenu.classList.remove('hidden');
            mobileMenu.setAttribute('aria-hidden', 'false');
            mobileMenuBtn.classList.add('is-open');
            mobileMenuBtn.setAttribute('aria-expanded', 'true');
            if (icon) icon.textContent = 'close';
            document.body.style.overflow = 'hidden';
        };
        const closeMenu = () => {
            mobileMenu.classList.add('hidden');
            mobileMenu.setAttribute('aria-hidden', 'true');
            mobileMenuBtn.classList.remove('is-open');
            mobileMenuBtn.setAttribute('aria-expanded', 'false');
            if (icon) icon.textContent = 'menu';
            document.body.style.overflow = '';
        };
        mobileMenuBtn.addEventListener('click', () => {
            if (mobileMenu.classList.contains('hidden')) openMenu();
            else closeMenu();
        });
        mobileMenu.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', closeMenu);
        });
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) closeMenu();
        });
    }

    // Hero hub: highlight connector on chip hover
    const heroHubStage = document.querySelector('.hero-hub__stage');
    if (heroHubStage) {
        heroHubStage.querySelectorAll('.hero-hub__chip').forEach((chip) => {
            const service = chip.getAttribute('data-service');
            if (!service) return;
            chip.addEventListener('mouseenter', () => {
                heroHubStage.classList.add(`is-hover-${service}`);
            });
            chip.addEventListener('mouseleave', () => {
                heroHubStage.classList.remove(`is-hover-${service}`);
            });
        });
    }

    // Scroll Spy for Process Section (Sticky Images)
    const processSteps = document.querySelectorAll('.process-step');
    const processImages = Array.from(document.querySelectorAll('.process-section__sticky-img'));

    if (processSteps.length > 0 && processImages.length > 0) {
        const observerOptions = {
            root: null,
            rootMargin: '-30% 0px -40% 0px',
            threshold: 0
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const stepIndex = parseInt(entry.target.getAttribute('data-step'), 10) - 1;

                    processImages.forEach(img => {
                        if (img) img.style.opacity = '0';
                    });

                    if (processImages[stepIndex]) {
                        processImages[stepIndex].style.opacity = '1';
                    }
                }
            });
        }, observerOptions);

        processSteps.forEach(step => observer.observe(step));
    }

    // 3D Tilt Effect for cards
    const tiltCards = document.querySelectorAll('.tilt-card');
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!prefersReducedMotion) {
        tiltCards.forEach(card => {
            card.addEventListener('mousemove', e => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                const centerX = rect.width / 2;
                const centerY = rect.height / 2;

                const rotateX = ((y - centerY) / centerY) * -8;
                const rotateY = ((x - centerX) / centerX) * 8;

                card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)`;
            });

            card.addEventListener('mouseleave', () => {
                card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)';
            });
        });
    }

    // Interactive Video Player Logic
    const automationVideo = document.getElementById('automation-video');
    const playOverlay = document.getElementById('play-overlay');
    const muteBtn = document.getElementById('mute-btn');
    const videoProgress = document.getElementById('video-progress');

    if (automationVideo && playOverlay) {
        playOverlay.addEventListener('click', () => {
            if (automationVideo.paused) {
                automationVideo.play().then(() => {
                    playOverlay.style.opacity = '0';
                    playOverlay.style.pointerEvents = 'none';
                }).catch(err => console.log('Video play error:', err));
            }
        });

        automationVideo.addEventListener('click', () => {
            if (!automationVideo.paused) {
                automationVideo.pause();
                playOverlay.style.opacity = '1';
                playOverlay.style.pointerEvents = 'auto';
            }
        });

        if (muteBtn) {
            muteBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                automationVideo.muted = !automationVideo.muted;
                muteBtn.innerHTML = automationVideo.muted
                    ? '<span class="material-symbols-outlined text-xl">volume_off</span>'
                    : '<span class="material-symbols-outlined text-xl">volume_up</span>';
            });
        }

        automationVideo.addEventListener('timeupdate', () => {
            const percentage = (automationVideo.currentTime / automationVideo.duration) * 100;
            if (videoProgress) videoProgress.style.width = percentage + '%';
        });
    }

    // Dynamic Counter Animation Engine
    const counterElements = document.querySelectorAll('.count-up');
    if (counterElements.length > 0 && !prefersReducedMotion) {
        const counterObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const target = entry.target;
                    const raw = target.getAttribute('data-count') || '0';
                    const finalValue = parseFloat(raw) || 0;
                    const isDecimal = raw.includes('.');
                    const duration = 2200;
                    const frameRate = 1000 / 60;
                    const totalFrames = Math.round(duration / frameRate);
                    let currentFrame = 0;

                    const updateCounter = () => {
                        currentFrame++;
                        const progress = currentFrame / totalFrames;
                        const current = finalValue * (1 - Math.pow(1 - progress, 3));

                        if (isDecimal) {
                            target.innerText = current.toFixed(1);
                        } else {
                            target.innerText = Math.round(current);
                        }

                        if (currentFrame < totalFrames) {
                            requestAnimationFrame(updateCounter);
                        } else {
                            target.innerText = raw;
                        }
                    };

                    updateCounter();
                    observer.unobserve(target);
                }
            });
        }, { threshold: 0.35 });

        counterElements.forEach(el => counterObserver.observe(el));
    }

    // Image reveal on scroll
    const imgRevealBlocks = document.querySelectorAll('.img-reveal');
    if (imgRevealBlocks.length > 0) {
        const imgObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

        imgRevealBlocks.forEach(el => imgObserver.observe(el));
    }

    // Quote spotlight — subtle entrance (text stays visible; no empty band)
    const quoteSpotlight = document.querySelector('.quote-spotlight');
    if (quoteSpotlight) {
        quoteSpotlight.classList.add('is-visible');
    }

    // Lead forms: CRM API first, FormSubmit fallback when PHP/DB unavailable
    const FORM_FALLBACK = 'https://formsubmit.co/ajax/info@kamgroups.com';
    const CRM_LEAD_API = new URL('api/submit-lead.php', window.location.href).href;
    const CRM_NEWSLETTER_API = new URL('api/newsletter.php', window.location.href).href;

    function showFormMessage(form, type, message) {
        let box = form.querySelector('.form-message');
        if (!box) {
            box = document.createElement('p');
            box.className = 'form-message';
            box.setAttribute('role', 'status');
            box.setAttribute('aria-live', 'polite');
            form.appendChild(box);
        }
        box.className = `form-message form-message--${type}`;
        box.textContent = message;
    }

    function validateLeadForm(form) {
        const required = form.querySelectorAll('[required]');
        let valid = true;
        required.forEach((field) => {
            field.classList.remove('is-invalid');
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                valid = false;
            }
        });
        const email = form.querySelector('input[type="email"]');
        if (email && email.value.trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
            email.classList.add('is-invalid');
            valid = false;
        }
        return valid;
    }

    async function postCrm(endpoint, data) {
        const res = await fetch(endpoint, {
            method: 'POST',
            body: data,
            headers: { Accept: 'application/json' }
        });
        const json = await res.json().catch(() => ({}));
        if (!res.ok || json.ok === false) {
            throw new Error(json.error || 'submit failed');
        }
        return json;
    }

    async function postFallback(data) {
        const res = await fetch(FORM_FALLBACK, {
            method: 'POST',
            body: data,
            headers: { Accept: 'application/json' }
        });
        if (!res.ok) throw new Error('submit failed');
    }

    async function submitLeadForm(form) {
        const submitBtn = form.querySelector('[type="submit"]');
        const originalLabel = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.setAttribute('aria-busy', 'true');
        }

        const isNewsletter = form.classList.contains('insights-newsletter__form');
        const crmEndpoint = isNewsletter ? CRM_NEWSLETTER_API : CRM_LEAD_API;

        const crmData = new FormData(form);
        if (!isNewsletter) {
            crmData.set('source', 'website');
        }

        const payload = isNewsletter
            ? (() => {
                const fd = new FormData();
                fd.set('email', form.querySelector('[type="email"]')?.value || '');
                fd.set('source', 'insights');
                return fd;
            })()
            : crmData;

        const fallbackData = new FormData(form);
        fallbackData.set('_subject', isNewsletter
            ? 'KAM Global HR newsletter subscription'
            : 'KAM Global HR website inquiry');
        fallbackData.set('_captcha', 'false');

        const successMsg = isNewsletter
            ? 'Thank you. You are subscribed to workforce insights.'
            : 'Thank you. Your message has been sent. Our team will respond shortly.';

        try {
            if (window.location.protocol === 'file:') {
                throw new Error('file protocol');
            }
            await postCrm(crmEndpoint, payload);
        } catch {
            try {
                await postFallback(fallbackData);
            } catch {
                showFormMessage(form, 'error', 'We could not send your message. Please email info@kamgroups.com or try again.');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.removeAttribute('aria-busy');
                    submitBtn.innerHTML = originalLabel;
                }
                return;
            }
        }

        form.reset();
        showFormMessage(form, 'success', successMsg);
        if (form.classList.contains('contact-form')) {
            window.setTimeout(() => {
                window.location.href = 'thank-you.html';
            }, 1200);
        }

        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.removeAttribute('aria-busy');
            submitBtn.innerHTML = originalLabel;
        }
    }

    document.querySelectorAll('form.contact-form, form.insights-newsletter__form').forEach((form) => {
        form.setAttribute('novalidate', 'novalidate');
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            if (!validateLeadForm(form)) {
                showFormMessage(form, 'error', 'Please complete all required fields.');
                return;
            }
            submitLeadForm(form);
        });
    });
});
