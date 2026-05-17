// Global JavaScript for KAM Global AI

document.addEventListener('DOMContentLoaded', () => {
    // Initialize AOS (Animate On Scroll)
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 50
        });
    }

    // Mobile menu toggle logic
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');

        if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }

    // Scroll Spy for Process Section (Sticky Images)
    const processSteps = document.querySelectorAll('.process-step');
    const processImages = [
        document.getElementById('process-img-1'),
        document.getElementById('process-img-2'),
        document.getElementById('process-img-3'),
        document.getElementById('process-img-4')
    ];

    if (processSteps.length > 0 && processImages[0]) {
        const observerOptions = {
            root: null,
            rootMargin: '-30% 0px -40% 0px',
            threshold: 0
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const stepIndex = parseInt(entry.target.getAttribute('data-step')) - 1;
                    
                    // Fade out all images
                    processImages.forEach(img => {
                        if(img) img.style.opacity = '0';
                    });
                    
                    // Fade in current image
                    if (processImages[stepIndex]) {
                        processImages[stepIndex].style.opacity = '1';
                    }
                }
            });
        }, observerOptions);

        processSteps.forEach(step => observer.observe(step));
    }
});

    // 3D Tilt Effect for cards
    const tiltCards = document.querySelectorAll(".tilt-card");
    tiltCards.forEach(card => {
        card.addEventListener("mousemove", e => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const rotateX = ((y - centerY) / centerY) * -10; // Max 10 deg
            const rotateY = ((x - centerX) / centerX) * 10;
            
            card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)`;
        });
        
        card.addEventListener("mouseleave", () => {
            card.style.transform = `perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)`;
        });
    });
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
                muteBtn.innerHTML = automationVideo.muted ? '<span class="material-symbols-outlined text-xl">volume_off</span>' : '<span class="material-symbols-outlined text-xl">volume_up</span>';
            });
        }

        automationVideo.addEventListener('timeupdate', () => {
            const percentage = (automationVideo.currentTime / automationVideo.duration) * 100;
            if (videoProgress) videoProgress.style.width = percentage + '%';
        });
    }

    // Dynamic Counter Animation Engine
    const counterElements = document.querySelectorAll('.count-up');
    if (counterElements.length > 0) {
        const counterObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const target = entry.target;
                    const finalValue = parseFloat(target.getAttribute('data-count')) || 0;
                    const isDecimal = target.getAttribute('data-count').includes('.');
                    const duration = 2500; // 2.5 seconds
                    const frameRate = 1000 / 60;
                    const totalFrames = Math.round(duration / frameRate);
                    let currentFrame = 0;

                    const updateCounter = () => {
                        currentFrame++;
                        const progress = currentFrame / totalFrames;
                        // Ease out quad
                        const current = finalValue * (1 - Math.pow(1 - progress, 3));

                        if (isDecimal) {
                            target.innerText = current.toFixed(1);
                        } else {
                            target.innerText = Math.round(current);
                        }

                        if (currentFrame < totalFrames) {
                            requestAnimationFrame(updateCounter);
                        } else {
                            target.innerText = finalValue;
                        }
                    };

                    updateCounter();
                    observer.unobserve(target);
                }
            });
        }, { threshold: 0.3 });

        counterElements.forEach(el => counterObserver.observe(el));
    }
