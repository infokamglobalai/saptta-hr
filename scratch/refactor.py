import os
import re
import glob

# The new HTML header to inject for global assets
NEW_ASSETS = """
    <!-- External Libraries -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Global Assets -->
    <script src="assets/js/tailwind-config.js"></script>
    <link href="assets/css/global.css" rel="stylesheet"/>
</head>
"""

# The new Navbar
NEW_NAVBAR = """
    <!-- TopNavBar -->
    <header class="fixed top-0 w-full z-50 bg-surface/90 backdrop-blur-xl border-b border-outline-variant/20 shadow-sm h-20 transition-all duration-300">
        <div class="flex justify-between items-center h-full px-margin-mobile md:px-margin-tablet lg:px-margin-desktop max-w-container-max mx-auto">
            <a href="index.html" class="font-headline-md text-headline-md font-bold text-primary flex items-center gap-2">
                KAM Global AI
            </a>
            
            <nav class="hidden md:flex items-center gap-8">
                <a class="font-label-md text-label-md text-on-surface-variant hover:text-secondary transition-colors" href="index.html">Home</a>
                <a class="font-label-md text-label-md text-on-surface-variant hover:text-secondary transition-colors" href="about.html">Company</a>
                
                <!-- Services Dropdown -->
                <div class="relative group">
                    <a class="font-label-md text-label-md text-on-surface-variant hover:text-secondary transition-colors flex items-center gap-1 cursor-pointer" href="services.html">
                        Services <span class="material-symbols-outlined text-[18px]">keyboard_arrow_down</span>
                    </a>
                    <div class="absolute left-0 top-full mt-2 w-72 bg-surface-container-lowest border border-outline-variant/30 rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 p-2 transform origin-top-left group-hover:scale-100 scale-95">
                        <a href="services.html" class="block px-4 py-2 font-body-sm hover:bg-surface-container hover:text-secondary rounded-lg transition-colors">Recruitment Services</a>
                        <a href="hr-talent.html" class="block px-4 py-2 font-body-sm hover:bg-surface-container hover:text-secondary rounded-lg transition-colors">Contract Staffing</a>
                        <a href="payroll.html" class="block px-4 py-2 font-body-sm hover:bg-surface-container hover:text-secondary rounded-lg transition-colors">Payroll Outsourcing</a>
                        <a href="hr-talent.html" class="block px-4 py-2 font-body-sm hover:bg-surface-container hover:text-secondary rounded-lg transition-colors">HR Advisory & Consulting</a>
                        <a href="services.html" class="block px-4 py-2 font-body-sm hover:bg-surface-container hover:text-secondary rounded-lg transition-colors">Manpower Consulting & BGV</a>
                        <a href="services.html" class="block px-4 py-2 font-body-sm hover:bg-surface-container hover:text-secondary rounded-lg transition-colors">Executive Search</a>
                    </div>
                </div>

                <!-- Industries Dropdown -->
                <div class="relative group">
                    <a class="font-label-md text-label-md text-on-surface-variant hover:text-secondary transition-colors flex items-center gap-1 cursor-pointer" href="industries.html">
                        Industries <span class="material-symbols-outlined text-[18px]">keyboard_arrow_down</span>
                    </a>
                    <div class="absolute left-0 top-full mt-2 w-64 bg-surface-container-lowest border border-outline-variant/30 rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 p-2 transform origin-top-left group-hover:scale-100 scale-95">
                        <a href="industries.html" class="block px-4 py-2 font-body-sm hover:bg-surface-container hover:text-secondary rounded-lg transition-colors">IT & Technology</a>
                        <a href="industries.html" class="block px-4 py-2 font-body-sm hover:bg-surface-container hover:text-secondary rounded-lg transition-colors">Engineering & Manufacturing</a>
                        <a href="industries.html" class="block px-4 py-2 font-body-sm hover:bg-surface-container hover:text-secondary rounded-lg transition-colors">Education & EdTech</a>
                        <a href="industries.html" class="block px-4 py-2 font-body-sm hover:bg-surface-container hover:text-secondary rounded-lg transition-colors">Healthcare</a>
                        <a href="industries.html" class="block px-4 py-2 font-body-sm hover:bg-surface-container hover:text-secondary rounded-lg transition-colors">Hospitality & Retail</a>
                    </div>
                </div>

                <a class="font-label-md text-label-md text-on-surface-variant hover:text-secondary transition-colors" href="insights.html">Insights</a>
            </nav>

            <div class="flex items-center gap-4">
                <a href="demo.html" class="bg-primary text-on-primary px-6 py-2.5 rounded-lg font-label-md text-label-md hover:bg-secondary transition-all shadow-md hover:shadow-lg active:scale-95">Request Demo</a>
            </div>
        </div>
    </header>
"""

# Footer scripts
FOOTER_SCRIPTS = """
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="assets/js/main.js"></script>
</body>
"""

URL_MAP = {
    "1st.html": "about.html",
    "2nd.html": "industries.html",
    "3rd.html": "payroll.html",
    "4th.html": "hr-talent.html",
    "5th.html": "services.html",
    "6th.html": "partners.html",
    "7th.html": "contact.html",
    "8th.html": "demo.html",
    "9th.html": "insights.html"
}

files = glob.glob('*.html')

for file in files:
    if file == 'index.html':
        continue
    
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()

    # Replace old URLs with new ones
    for old_url, new_url in URL_MAP.items():
        content = content.replace(old_url, new_url)

    # 1. Remove tailwind script and inline styles and replace with external
    # We find <script src="https://cdn.tailwindcss.com..."></script> and everything up to </head>
    head_pattern = re.compile(r'<script src="https://cdn\.tailwindcss\.com\?plugins=forms,container-queries"></script>.*?</head>', re.DOTALL)
    content = re.sub(head_pattern, NEW_ASSETS.strip() + '\n</head>', content)

    # 2. Replace Header/Navbar
    # Some files use <header class="...">, some use <nav class="fixed...">
    header_pattern = re.compile(r'<(?:header|nav)\s+class="(?:fixed\s+top-0|absolute\s+w-full)[^>]*>.*?</(?:header|nav)>', re.DOTALL)
    
    if header_pattern.search(content):
        content = re.sub(header_pattern, NEW_NAVBAR.strip(), content)
    else:
        print(f"WARNING: Could not find header in {file}")

    # 3. Add AOS to sections and glass-cards
    # Let's just blindly add data-aos="fade-up" to glass-cards if not present
    content = re.sub(r'class="([^"]*glass-card[^"]*)"', r'class="\1" data-aos="fade-up"', content)
    
    # Let's add data-aos="fade-up" to section headers (h2, h3)
    content = re.sub(r'<h2 class="([^"]*)"', r'<h2 class="\1" data-aos="fade-up"', content)
    
    # 4. Inject AOS init scripts before </body>
    if "aos.js" not in content:
        content = content.replace("</body>", FOOTER_SCRIPTS.strip() + '\n</body>')

    with open(file, 'w', encoding='utf-8') as f:
        f.write(content)
        
print("Refactoring complete.")
