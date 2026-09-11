/**
 * KAM Global HR - Unified Site Navigation & Layout Component
 * Standardizes Header and Footer across all website pages.
 */
(function () {
    const rawPath = window.location.pathname.split('/').pop() || 'index';
    const currentPath = rawPath.replace(/\.(html|php)$/, '') || 'index';

    function getActiveKey() {
        if (currentPath === '' || currentPath === 'index') return 'home';
        if (currentPath === 'about') return 'about';
        if (['services', 'recruitment', 'contract-staffing', 'payroll', 'hr-advisory', 'executive-search', 'manpower-bgv'].includes(currentPath)) return 'services';
        if (currentPath === 'industries') return 'industries';
        if (['careers', 'jobs', 'find-a-job'].includes(currentPath)) return 'careers';
        if (currentPath === 'candidate-registration') return 'candidate-registration';
        if (['insights', 'case-studies', 'partners', 'insight'].includes(currentPath)) return 'resources';
        if (['ats', 'hr-talent', 'payroll-software'].includes(currentPath)) return 'software';
        if (currentPath === 'contact') return 'contact';
        return '';
    }

    const activeKey = getActiveKey();

    const headerHTML = `
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20 sm:h-24">
            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center">
                <a href="index" class="flex items-center gap-3 group" aria-label="KAM Global HR Home">
                    <img src="assets/img/logo.png" alt="KAM Global HR" class="h-12 sm:h-14 md:h-16 w-auto object-contain transition-transform duration-300 group-hover:scale-105" width="220" height="60"/>
                </a>
            </div>

            <!-- Desktop Nav -->
            <nav class="hidden lg:flex items-center gap-6 xl:gap-7" aria-label="Main Navigation">
                <a href="index" class="text-sm font-medium transition-colors ${activeKey === 'home' ? 'text-sky-600 font-bold' : 'text-slate-700 hover:text-sky-600'}">Home</a>
                <a href="about" class="text-sm font-medium transition-colors ${activeKey === 'about' ? 'text-sky-600 font-bold' : 'text-slate-700 hover:text-sky-600'}">About Us</a>

                <!-- Services Dropdown -->
                <div class="relative group">
                    <a href="services" class="flex items-center gap-1 text-sm font-medium transition-colors py-2 ${activeKey === 'services' ? 'text-sky-600 font-bold' : 'text-slate-700 group-hover:text-sky-600'}">
                        Services <span class="material-symbols-outlined text-[18px] text-slate-400 group-hover:text-sky-600 transition-transform group-hover:rotate-180">expand_more</span>
                    </a>
                    <div class="absolute top-full left-0 hidden group-hover:block w-64 bg-white rounded-2xl shadow-xl border border-slate-100 py-3 z-50 transition-all animate-fadeIn">
                        <a href="recruitment" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-sky-50 hover:text-sky-600 transition-colors">
                            <span class="material-symbols-outlined text-sky-600 text-[20px]">person_search</span> Recruitment
                        </a>
                        <a href="contract-staffing" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-sky-50 hover:text-sky-600 transition-colors">
                            <span class="material-symbols-outlined text-orange-500 text-[20px]">work_history</span> Contract Staffing
                        </a>
                        <a href="payroll" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-sky-50 hover:text-sky-600 transition-colors">
                            <span class="material-symbols-outlined text-emerald-600 text-[20px]">payments</span> Payroll Outsourcing
                        </a>
                        <a href="hr-advisory" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-sky-50 hover:text-sky-600 transition-colors">
                            <span class="material-symbols-outlined text-purple-600 text-[20px]">psychology</span> HR Advisory
                        </a>
                        <a href="executive-search" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-sky-50 hover:text-sky-600 transition-colors">
                            <span class="material-symbols-outlined text-rose-500 text-[20px]">manage_accounts</span> Executive Search
                        </a>
                        <a href="manpower-bgv" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-sky-50 hover:text-sky-600 transition-colors">
                            <span class="material-symbols-outlined text-cyan-600 text-[20px]">verified_user</span> Background Verification
                        </a>
                    </div>
                </div>

                <a href="industries" class="text-sm font-medium transition-colors ${activeKey === 'industries' ? 'text-sky-600 font-bold' : 'text-slate-700 hover:text-sky-600'}">Industries</a>
                <a href="careers" class="text-sm font-medium transition-colors ${activeKey === 'careers' ? 'text-sky-600 font-bold' : 'text-slate-700 hover:text-sky-600'}">Find a Job</a>

                <!-- Software Dropdown -->
                <div class="relative group">
                    <a href="ats" class="flex items-center gap-1 text-sm font-medium transition-colors py-2 ${activeKey === 'software' ? 'text-sky-600 font-bold' : 'text-slate-700 group-hover:text-sky-600'}">
                        Software <span class="material-symbols-outlined text-[18px] text-slate-400 group-hover:text-sky-600 transition-transform group-hover:rotate-180">expand_more</span>
                    </a>
                    <div class="absolute top-full left-0 hidden group-hover:block w-52 bg-white rounded-2xl shadow-xl border border-slate-100 py-3 z-50 transition-all animate-fadeIn">
                        <a href="ats" class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-sky-50 hover:text-sky-600">ATS Platform</a>
                        <a href="hr-talent" class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-sky-50 hover:text-sky-600">HRMS Software</a>
                        <a href="payroll-software" class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-sky-50 hover:text-sky-600">Payroll Software</a>
                    </div>
                </div>

                <!-- Resources Dropdown -->
                <div class="relative group">
                    <a href="insights" class="flex items-center gap-1 text-sm font-medium transition-colors py-2 ${activeKey === 'resources' ? 'text-sky-600 font-bold' : 'text-slate-700 group-hover:text-sky-600'}">
                        Resources <span class="material-symbols-outlined text-[18px] text-slate-400 group-hover:text-sky-600 transition-transform group-hover:rotate-180">expand_more</span>
                    </a>
                    <div class="absolute top-full left-0 hidden group-hover:block w-60 bg-white rounded-2xl shadow-xl border border-slate-100 py-3 z-50 transition-all animate-fadeIn">
                        <a href="insights" class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-sky-50 hover:text-sky-600">Insights &amp; Articles</a>
                        <a href="case-studies" class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-sky-50 hover:text-sky-600">Client Case Studies</a>
                        <a href="partners" class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-sky-50 hover:text-sky-600">Partner With Us</a>
                    </div>
                </div>

                <a href="contact" class="text-sm font-medium transition-colors ${activeKey === 'contact' ? 'text-sky-600 font-bold' : 'text-slate-700 hover:text-sky-600'}">Contact</a>
            </nav>

            <!-- Actions -->
            <div class="hidden lg:flex items-center gap-3">
                <a href="candidate-registration" class="text-xs font-bold text-sky-700 hover:text-sky-800 px-3.5 py-2.5 rounded-full border border-sky-200 bg-sky-50 hover:bg-sky-100 transition-all flex items-center gap-1.5 shadow-sm">
                    <span class="material-symbols-outlined text-[16px]">description</span> Candidate Form
                </a>
                <a href="contact" class="hr-btn-primary text-xs py-2.5 px-5">
                    Book Consultation
                    <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </a>
            </div>

            <!-- Mobile Menu Button -->
            <div class="flex lg:hidden items-center gap-2.5">
                <a href="candidate-registration" class="hr-btn-secondary text-xs px-3 py-2">
                    Candidate Form
                </a>
                <button type="button" id="mobile-menu-btn" class="p-2 rounded-xl text-slate-700 hover:text-slate-900 hover:bg-slate-100 focus:outline-none" aria-label="Toggle menu">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Drawer -->
    <div id="mobile-menu" class="hidden lg:hidden bg-white border-b border-slate-200 px-6 py-6 space-y-4 max-h-[85vh] overflow-y-auto shadow-xl">
        <a href="index" class="block text-slate-800 hover:text-sky-600 font-medium ${activeKey === 'home' ? 'text-sky-600 font-bold' : ''}">Home</a>
        <a href="about" class="block text-slate-800 hover:text-sky-600 font-medium ${activeKey === 'about' ? 'text-sky-600 font-bold' : ''}">About Us</a>
        
        <div class="pt-2 border-t border-slate-100">
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Services</span>
            <div class="grid grid-cols-2 gap-2 mt-2">
                <a href="recruitment" class="text-xs text-slate-700 hover:text-sky-600 py-1">Recruitment</a>
                <a href="contract-staffing" class="text-xs text-slate-700 hover:text-sky-600 py-1">Contract Staffing</a>
                <a href="payroll" class="text-xs text-slate-700 hover:text-sky-600 py-1">Payroll</a>
                <a href="hr-advisory" class="text-xs text-slate-700 hover:text-sky-600 py-1">HR Advisory</a>
                <a href="executive-search" class="text-xs text-slate-700 hover:text-sky-600 py-1">Executive Search</a>
                <a href="manpower-bgv" class="text-xs text-slate-700 hover:text-sky-600 py-1">BGV Verification</a>
            </div>
        </div>

        <div class="pt-2 border-t border-slate-100">
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Careers &amp; Candidate</span>
            <div class="space-y-2 mt-2">
                <a href="careers" class="block text-sm font-semibold ${activeKey === 'careers' ? 'text-sky-600' : 'text-slate-800 hover:text-sky-600'}">Find a Job / Openings</a>
                <a href="candidate-registration" class="block text-sm font-bold ${activeKey === 'candidate-registration' ? 'text-sky-700' : 'text-sky-600 hover:text-sky-700'}">Candidate Registration Form</a>
            </div>
        </div>

        <div class="pt-2 border-t border-slate-100">
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Software Solutions</span>
            <div class="grid grid-cols-2 gap-2 mt-2">
                <a href="ats" class="text-xs text-slate-700 hover:text-sky-600 py-1">ATS Software</a>
                <a href="hr-talent" class="text-xs text-slate-700 hover:text-sky-600 py-1">HRMS Software</a>
                <a href="payroll-software" class="text-xs text-slate-700 hover:text-sky-600 py-1">Payroll Software</a>
            </div>
        </div>

        <div class="pt-2 border-t border-slate-100">
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Company &amp; Resources</span>
            <div class="grid grid-cols-2 gap-2 mt-2">
                <a href="industries" class="text-xs text-slate-700 hover:text-sky-600 py-1">Industries</a>
                <a href="insights" class="text-xs text-slate-700 hover:text-sky-600 py-1">Insights &amp; Blog</a>
                <a href="case-studies" class="text-xs text-slate-700 hover:text-sky-600 py-1">Case Studies</a>
                <a href="partners" class="text-xs text-slate-700 hover:text-sky-600 py-1">Partner With Us</a>
            </div>
        </div>

        <div class="pt-2 border-t border-slate-100">
            <a href="contact" class="block text-slate-800 hover:text-sky-600 font-medium mb-3 ${activeKey === 'contact' ? 'text-sky-600 font-bold' : ''}">Contact Us</a>
            <a href="contact" class="hr-btn-primary w-full justify-center text-xs py-3">
                Book Consultation <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </a>
        </div>
    </div>
    `;

    const footerHTML = `
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-8 mb-16">
            
            <!-- Brand Column -->
            <div class="lg:col-span-4 space-y-6">
                <a href="index" class="inline-block group">
                    <img src="assets/img/logo.png" alt="KAM Global HR" class="h-14 sm:h-16 md:h-20 w-auto object-contain transition-transform duration-300 group-hover:scale-105" width="260" height="72"/>
                </a>
                <p class="text-sm text-slate-600 leading-relaxed max-w-sm">
                    Empowering organizations worldwide with specialized recruitment excellence, overseas manpower solutions, payroll outsourcing, and end-to-end workforce strategies.
                </p>
                <div class="space-y-2 text-xs text-slate-600">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-sky-600 text-[18px]">location_on</span>
                        <span>RT Nagar, Bengaluru, Karnataka, India</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-sky-600 text-[18px]">mail</span>
                        <a href="mailto:recruitment@kamglobalai.com" class="hover:text-sky-600 transition-colors">recruitment@kamglobalai.com</a>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-sky-600 text-[18px]">call</span>
                        <a href="tel:+919900007072" class="hover:text-sky-600 transition-colors">+91 9900007072</a>
                        <span>/</span>
                        <a href="tel:+919900007073" class="hover:text-sky-600 transition-colors">+91 9900007073</a>
                    </div>
                </div>
            </div>

            <!-- Solutions Column -->
            <div class="lg:col-span-2">
                <h4 class="text-sm font-bold text-slate-900 mb-4 uppercase tracking-wider">Solutions</h4>
                <ul class="space-y-2.5 text-sm text-slate-600">
                    <li><a href="recruitment" class="hover:text-sky-600 transition-colors">Recruitment</a></li>
                    <li><a href="contract-staffing" class="hover:text-sky-600 transition-colors">Contract Staffing</a></li>
                    <li><a href="payroll" class="hover:text-sky-600 transition-colors">Payroll Outsourcing</a></li>
                    <li><a href="executive-search" class="hover:text-sky-600 transition-colors">Executive Search</a></li>
                    <li><a href="hr-advisory" class="hover:text-sky-600 transition-colors">HR Advisory</a></li>
                    <li><a href="manpower-bgv" class="hover:text-sky-600 transition-colors">BGV &amp; Manpower</a></li>
                </ul>
            </div>

            <!-- Careers & Candidate Column -->
            <div class="lg:col-span-3">
                <h4 class="text-sm font-bold text-slate-900 mb-4 uppercase tracking-wider">Careers &amp; Jobs</h4>
                <ul class="space-y-2.5 text-sm text-slate-600">
                    <li><a href="careers" class="hover:text-sky-600 transition-colors font-semibold text-sky-600">Find a Job / Openings</a></li>
                    <li><a href="candidate-registration" class="hover:text-sky-600 transition-colors font-bold text-sky-700">Candidate Registration Form</a></li>
                    <li><a href="industries" class="hover:text-sky-600 transition-colors">Sectors We Serve</a></li>
                    <li><a href="ats" class="hover:text-sky-600 transition-colors">ATS &amp; HR Software</a></li>
                    <li><a href="case-studies" class="hover:text-sky-600 transition-colors">Client Case Studies</a></li>
                </ul>
            </div>

            <!-- Consultation / Newsletter Column -->
            <div class="lg:col-span-3">
                <h4 class="text-sm font-bold text-slate-900 mb-4 uppercase tracking-wider">Stay Connected</h4>
                <p class="text-xs text-slate-600 leading-relaxed mb-4">
                    Subscribe for industry hiring trends, GCC mobilization updates, and HR insights.
                </p>
                <form class="relative flex items-center mb-5" action="api/newsletter.php" method="post">
                    <div style="display:none;" aria-hidden="true"><input type="text" name="_gotcha" tabindex="-1"/></div>
                    <input type="email" name="email" placeholder="Your work email address" required class="w-full bg-slate-50 border border-slate-300 rounded-full pl-3.5 pr-10 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-sky-500"/>
                    <button type="submit" class="absolute right-1.5 w-8 h-8 rounded-full bg-sky-600 text-white flex items-center justify-center hover:bg-sky-700 transition-colors" aria-label="Subscribe">
                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </button>
                </form>
                <a href="contact" class="hr-btn-primary text-xs py-2.5 px-5 w-full justify-center">
                    Book Consultation <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </a>
            </div>

        </div>

        <!-- Bottom Bar -->
        <div class="pt-8 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
            <div>
                &copy; 2025 KAM Global HR. All rights reserved.
            </div>
            <div class="flex items-center gap-6">
                <a href="privacy" class="hover:text-slate-800 transition-colors">Privacy Policy</a>
                <span>|</span>
                <a href="terms" class="hover:text-slate-800 transition-colors">Terms of Service</a>
                <span>|</span>
                <a href="candidate-registration" class="hover:text-sky-600 font-semibold transition-colors">Candidate Form</a>
            </div>
            <div class="flex items-center gap-2 px-3 py-1 rounded-full border border-slate-200 bg-slate-50 text-slate-700 font-medium">
                <span class="material-symbols-outlined text-[16px] text-slate-500">public</span>
                <span>India &amp; GCC</span>
            </div>
        </div>
    </div>
    `;

    function initSiteLayout() {
        // Render Header
        let headerEl = document.getElementById('site-header');
        if (!headerEl) {
            headerEl = document.querySelector('header');
        }
        if (headerEl) {
            headerEl.className = 'site-header-clean sticky top-0 w-full z-50 bg-white/95 backdrop-blur-md border-b border-slate-200/80 transition-all duration-300';
            headerEl.innerHTML = headerHTML;
        }

        // Render Footer
        let footerEl = document.getElementById('site-footer');
        if (!footerEl) {
            footerEl = document.querySelector('footer');
        }
        if (footerEl) {
            footerEl.className = 'bg-white border-t border-slate-200 pt-16 pb-12 mt-16 text-slate-900 font-sans';
            footerEl.innerHTML = footerHTML;
        }

        // Scroll elevation effect
        const onScroll = () => {
            if (headerEl) {
                if (window.scrollY > 20) {
                    headerEl.classList.add('shadow-md');
                } else {
                    headerEl.classList.remove('shadow-md');
                }
            }
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();

        // Attach Mobile Menu Listeners
        const mobileBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        if (mobileBtn && mobileMenu) {
            mobileBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                mobileMenu.classList.toggle('hidden');
            });

            document.addEventListener('click', (e) => {
                if (!mobileMenu.contains(e.target) && !mobileBtn.contains(e.target)) {
                    mobileMenu.classList.add('hidden');
                }
            });

            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !mobileMenu.classList.contains('hidden')) {
                    mobileMenu.classList.add('hidden');
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSiteLayout);
    } else {
        initSiteLayout();
    }
})();
