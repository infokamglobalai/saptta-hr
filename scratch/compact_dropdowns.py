import os

html_dir = r"c:\Users\user\Desktop\hr-services"

target_dropdowns = """<!-- Services Dropdown -->
                <div class="relative group z-50">
                    <a class="font-label-md text-label-md text-on-surface-variant hover:text-secondary transition-colors flex items-center gap-1 cursor-pointer" href="services.html">
                        Services <span class="material-symbols-outlined text-[18px]">keyboard_arrow_down</span>
                    </a>
                    <div class="absolute left-0 top-full mt-4 w-[600px] bg-surface-container-lowest border border-outline-variant/30 rounded-3xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 p-5 grid grid-cols-2 gap-3 transform origin-top group-hover:scale-100 scale-95 border-t-4 border-t-primary">
                        <a href="recruitment.html" class="flex gap-4 p-3 rounded-2xl hover:bg-surface-container/60 transition-all duration-200 group/item hover:translate-x-1 border-l-2 border-l-transparent hover:border-l-blue-500">
                            <span class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[20px]">person_search</span>
                            </span>
                            <div>
                                <h4 class="font-label-md text-primary font-semibold group-hover/item:text-secondary transition-colors">Recruitment Services</h4>
                                <p class="font-body-xs text-on-surface-variant/80 mt-0.5 leading-normal">Find top-tier permanent talent across regions</p>
                            </div>
                        </a>
                        <a href="contract-staffing.html" class="flex gap-4 p-3 rounded-2xl hover:bg-surface-container/60 transition-all duration-200 group/item hover:translate-x-1 border-l-2 border-l-transparent hover:border-l-amber-500">
                            <span class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[20px]">badge</span>
                            </span>
                            <div>
                                <h4 class="font-label-md text-primary font-semibold group-hover/item:text-secondary transition-colors">Contract Staffing</h4>
                                <p class="font-body-xs text-on-surface-variant/80 mt-0.5 leading-normal">Flexible contract staffing for rapid scaling</p>
                            </div>
                        </a>
                        <a href="payroll.html" class="flex gap-4 p-3 rounded-2xl hover:bg-surface-container/60 transition-all duration-200 group/item hover:translate-x-1 border-l-2 border-l-transparent hover:border-l-emerald-500">
                            <span class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[20px]">payments</span>
                            </span>
                            <div>
                                <h4 class="font-label-md text-primary font-semibold group-hover/item:text-secondary transition-colors">Payroll Outsourcing</h4>
                                <p class="font-body-xs text-on-surface-variant/80 mt-0.5 leading-normal">Compliant payroll & benefits administration</p>
                            </div>
                        </a>
                        <a href="hr-advisory.html" class="flex gap-4 p-3 rounded-2xl hover:bg-surface-container/60 transition-all duration-200 group/item hover:translate-x-1 border-l-2 border-l-transparent hover:border-l-purple-500">
                            <span class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-600 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[20px]">partner_exchange</span>
                            </span>
                            <div>
                                <h4 class="font-label-md text-primary font-semibold group-hover/item:text-secondary transition-colors">HR Advisory & Consulting</h4>
                                <p class="font-body-xs text-on-surface-variant/80 mt-0.5 leading-normal">Strategic HR policy & compliance guidance</p>
                            </div>
                        </a>
                        <a href="manpower-bgv.html" class="flex gap-4 p-3 rounded-2xl hover:bg-surface-container/60 transition-all duration-200 group/item hover:translate-x-1 border-l-2 border-l-transparent hover:border-l-teal-500">
                            <span class="w-10 h-10 rounded-xl bg-teal-500/10 text-teal-600 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[20px]">fact_check</span>
                            </span>
                            <div>
                                <h4 class="font-label-md text-primary font-semibold group-hover/item:text-secondary transition-colors">Manpower Consulting & BGV</h4>
                                <p class="font-body-xs text-on-surface-variant/80 mt-0.5 leading-normal">Comprehensive background checks & verification</p>
                            </div>
                        </a>
                        <a href="executive-search.html" class="flex gap-4 p-3 rounded-2xl hover:bg-surface-container/60 transition-all duration-200 group/item hover:translate-x-1 border-l-2 border-l-transparent hover:border-l-indigo-500">
                            <span class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[20px]">diversity_3</span>
                            </span>
                            <div>
                                <h4 class="font-label-md text-primary font-semibold group-hover/item:text-secondary transition-colors">Executive Search</h4>
                                <p class="font-body-xs text-on-surface-variant/80 mt-0.5 leading-normal">Retained search for C-suite & leadership roles</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Industries Dropdown -->
                <div class="relative group">
                    <a class="font-label-md text-label-md text-on-surface-variant hover:text-secondary transition-colors flex items-center gap-1 cursor-pointer" href="industries.html">
                        Industries <span class="material-symbols-outlined text-[18px]">keyboard_arrow_down</span>
                    </a>
                    <div class="absolute right-0 top-full mt-4 w-[600px] bg-surface-container-lowest border border-outline-variant/30 rounded-3xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 p-5 grid grid-cols-2 gap-3 transform origin-top group-hover:scale-100 scale-95 border-t-4 border-t-secondary">
                        <a href="industries.html" class="flex gap-4 p-3 rounded-2xl hover:bg-surface-container/60 transition-all duration-200 group/item hover:translate-x-1 border-l-2 border-l-transparent hover:border-l-violet-500">
                            <span class="w-10 h-10 rounded-xl bg-violet-500/10 text-violet-600 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[20px]">terminal</span>
                            </span>
                            <div>
                                <h4 class="font-label-md text-primary font-semibold group-hover/item:text-secondary transition-colors">IT & Technology</h4>
                                <p class="font-body-xs text-on-surface-variant/80 mt-0.5 leading-normal">Talent for software, cloud, & deep tech startups</p>
                            </div>
                        </a>
                        <a href="industries.html" class="flex gap-4 p-3 rounded-2xl hover:bg-surface-container/60 transition-all duration-200 group/item hover:translate-x-1 border-l-2 border-l-transparent hover:border-l-cyan-500">
                            <span class="w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-600 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[20px]">precision_manufacturing</span>
                            </span>
                            <div>
                                <h4 class="font-label-md text-primary font-semibold group-hover/item:text-secondary transition-colors">Engineering & Manufacturing</h4>
                                <p class="font-body-xs text-on-surface-variant/80 mt-0.5 leading-normal">Technical staffing for industrial & auto operations</p>
                            </div>
                        </a>
                        <a href="industries.html" class="flex gap-4 p-3 rounded-2xl hover:bg-surface-container/60 transition-all duration-200 group/item hover:translate-x-1 border-l-2 border-l-transparent hover:border-l-amber-500">
                            <span class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[20px]">school</span>
                            </span>
                            <div>
                                <h4 class="font-label-md text-primary font-semibold group-hover/item:text-secondary transition-colors">Education & EdTech</h4>
                                <p class="font-body-xs text-on-surface-variant/80 mt-0.5 leading-normal">Recruiting leaders for modern learning institutions</p>
                            </div>
                        </a>
                        <a href="industries.html" class="flex gap-4 p-3 rounded-2xl hover:bg-surface-container/60 transition-all duration-200 group/item hover:translate-x-1 border-l-2 border-l-transparent hover:border-l-rose-500">
                            <span class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-600 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[20px]">medical_services</span>
                            </span>
                            <div>
                                <h4 class="font-label-md text-primary font-semibold group-hover/item:text-secondary transition-colors">Healthcare</h4>
                                <p class="font-body-xs text-on-surface-variant/80 mt-0.5 leading-normal">Fully compliant medical and clinical staff hiring</p>
                            </div>
                        </a>
                        <a href="industries.html" class="flex gap-4 p-3 rounded-2xl hover:bg-surface-container/60 transition-all duration-200 group/item hover:translate-x-1 border-l-2 border-l-transparent hover:border-l-orange-500">
                            <span class="w-10 h-10 rounded-xl bg-orange-500/10 text-orange-600 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[20px]">storefront</span>
                            </span>
                            <div>
                                <h4 class="font-label-md text-primary font-semibold group-hover/item:text-secondary transition-colors">Hospitality & Retail</h4>
                                <p class="font-body-xs text-on-surface-variant/80 mt-0.5 leading-normal">Flexible frontline and service sector operations</p>
                            </div>
                        </a>
                    </div>
                </div>"""

replacement_dropdowns = """<!-- Services Dropdown -->
                <div class="relative group z-50">
                    <a class="font-label-md text-label-md text-on-surface-variant hover:text-secondary transition-colors flex items-center gap-1 cursor-pointer" href="services.html">
                        Services <span class="material-symbols-outlined text-[18px]">keyboard_arrow_down</span>
                    </a>
                    <div class="absolute left-0 top-full mt-4 w-[680px] bg-surface-container-lowest border border-outline-variant/30 rounded-3xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 p-3.5 grid grid-cols-2 gap-2 transform origin-top group-hover:scale-100 scale-95 border-t-4 border-t-primary">
                        <a href="recruitment.html" class="flex gap-3 p-2 rounded-2xl hover:bg-surface-container/60 transition-all duration-200 group/item hover:translate-x-1 border-l-2 border-l-transparent hover:border-l-blue-500">
                            <span class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[20px]">person_search</span>
                            </span>
                            <div>
                                <h4 class="font-label-md text-primary font-semibold group-hover/item:text-secondary transition-colors">Recruitment Services</h4>
                                <p class="font-body-xs text-on-surface-variant/80 mt-0.5 leading-normal">Find top-tier permanent talent across regions</p>
                            </div>
                        </a>
                        <a href="contract-staffing.html" class="flex gap-3 p-2 rounded-2xl hover:bg-surface-container/60 transition-all duration-200 group/item hover:translate-x-1 border-l-2 border-l-transparent hover:border-l-amber-500">
                            <span class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[20px]">badge</span>
                            </span>
                            <div>
                                <h4 class="font-label-md text-primary font-semibold group-hover/item:text-secondary transition-colors">Contract Staffing</h4>
                                <p class="font-body-xs text-on-surface-variant/80 mt-0.5 leading-normal">Flexible contract staffing for rapid scaling</p>
                            </div>
                        </a>
                        <a href="payroll.html" class="flex gap-3 p-2 rounded-2xl hover:bg-surface-container/60 transition-all duration-200 group/item hover:translate-x-1 border-l-2 border-l-transparent hover:border-l-emerald-500">
                            <span class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[20px]">payments</span>
                            </span>
                            <div>
                                <h4 class="font-label-md text-primary font-semibold group-hover/item:text-secondary transition-colors">Payroll Outsourcing</h4>
                                <p class="font-body-xs text-on-surface-variant/80 mt-0.5 leading-normal">Compliant payroll & benefits administration</p>
                            </div>
                        </a>
                        <a href="hr-advisory.html" class="flex gap-3 p-2 rounded-2xl hover:bg-surface-container/60 transition-all duration-200 group/item hover:translate-x-1 border-l-2 border-l-transparent hover:border-l-purple-500">
                            <span class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-600 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[20px]">partner_exchange</span>
                            </span>
                            <div>
                                <h4 class="font-label-md text-primary font-semibold group-hover/item:text-secondary transition-colors">HR Advisory & Consulting</h4>
                                <p class="font-body-xs text-on-surface-variant/80 mt-0.5 leading-normal">Strategic HR policy & compliance guidance</p>
                            </div>
                        </a>
                        <a href="manpower-bgv.html" class="flex gap-3 p-2 rounded-2xl hover:bg-surface-container/60 transition-all duration-200 group/item hover:translate-x-1 border-l-2 border-l-transparent hover:border-l-teal-500">
                            <span class="w-10 h-10 rounded-xl bg-teal-500/10 text-teal-600 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[20px]">fact_check</span>
                            </span>
                            <div>
                                <h4 class="font-label-md text-primary font-semibold group-hover/item:text-secondary transition-colors">Manpower Consulting & BGV</h4>
                                <p class="font-body-xs text-on-surface-variant/80 mt-0.5 leading-normal">Comprehensive background checks & verification</p>
                            </div>
                        </a>
                        <a href="executive-search.html" class="flex gap-3 p-2 rounded-2xl hover:bg-surface-container/60 transition-all duration-200 group/item hover:translate-x-1 border-l-2 border-l-transparent hover:border-l-indigo-500">
                            <span class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[20px]">diversity_3</span>
                            </span>
                            <div>
                                <h4 class="font-label-md text-primary font-semibold group-hover/item:text-secondary transition-colors">Executive Search</h4>
                                <p class="font-body-xs text-on-surface-variant/80 mt-0.5 leading-normal">Retained search for C-suite & leadership roles</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Industries Dropdown -->
                <div class="relative group">
                    <a class="font-label-md text-label-md text-on-surface-variant hover:text-secondary transition-colors flex items-center gap-1 cursor-pointer" href="industries.html">
                        Industries <span class="material-symbols-outlined text-[18px]">keyboard_arrow_down</span>
                    </a>
                    <div class="absolute right-0 top-full mt-4 w-[680px] bg-surface-container-lowest border border-outline-variant/30 rounded-3xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 p-3.5 grid grid-cols-2 gap-2 transform origin-top group-hover:scale-100 scale-95 border-t-4 border-t-secondary">
                        <a href="industries.html" class="flex gap-3 p-2 rounded-2xl hover:bg-surface-container/60 transition-all duration-200 group/item hover:translate-x-1 border-l-2 border-l-transparent hover:border-l-violet-500">
                            <span class="w-10 h-10 rounded-xl bg-violet-500/10 text-violet-600 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[20px]">terminal</span>
                            </span>
                            <div>
                                <h4 class="font-label-md text-primary font-semibold group-hover/item:text-secondary transition-colors">IT & Technology</h4>
                                <p class="font-body-xs text-on-surface-variant/80 mt-0.5 leading-normal">Talent for software, cloud, & deep tech startups</p>
                            </div>
                        </a>
                        <a href="industries.html" class="flex gap-3 p-2 rounded-2xl hover:bg-surface-container/60 transition-all duration-200 group/item hover:translate-x-1 border-l-2 border-l-transparent hover:border-l-cyan-500">
                            <span class="w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-600 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[20px]">precision_manufacturing</span>
                            </span>
                            <div>
                                <h4 class="font-label-md text-primary font-semibold group-hover/item:text-secondary transition-colors">Engineering & Manufacturing</h4>
                                <p class="font-body-xs text-on-surface-variant/80 mt-0.5 leading-normal">Technical staffing for industrial & auto operations</p>
                            </div>
                        </a>
                        <a href="industries.html" class="flex gap-3 p-2 rounded-2xl hover:bg-surface-container/60 transition-all duration-200 group/item hover:translate-x-1 border-l-2 border-l-transparent hover:border-l-amber-500">
                            <span class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[20px]">school</span>
                            </span>
                            <div>
                                <h4 class="font-label-md text-primary font-semibold group-hover/item:text-secondary transition-colors">Education & EdTech</h4>
                                <p class="font-body-xs text-on-surface-variant/80 mt-0.5 leading-normal">Recruiting leaders for modern learning institutions</p>
                            </div>
                        </a>
                        <a href="industries.html" class="flex gap-3 p-2 rounded-2xl hover:bg-surface-container/60 transition-all duration-200 group/item hover:translate-x-1 border-l-2 border-l-transparent hover:border-l-rose-500">
                            <span class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-600 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[20px]">medical_services</span>
                            </span>
                            <div>
                                <h4 class="font-label-md text-primary font-semibold group-hover/item:text-secondary transition-colors">Healthcare</h4>
                                <p class="font-body-xs text-on-surface-variant/80 mt-0.5 leading-normal">Fully compliant medical and clinical staff hiring</p>
                            </div>
                        </a>
                        <a href="industries.html" class="flex gap-3 p-2 rounded-2xl hover:bg-surface-container/60 transition-all duration-200 group/item hover:translate-x-1 border-l-2 border-l-transparent hover:border-l-orange-500">
                            <span class="w-10 h-10 rounded-xl bg-orange-500/10 text-orange-600 flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[20px]">storefront</span>
                            </span>
                            <div>
                                <h4 class="font-label-md text-primary font-semibold group-hover/item:text-secondary transition-colors">Hospitality & Retail</h4>
                                <p class="font-body-xs text-on-surface-variant/80 mt-0.5 leading-normal">Flexible frontline and service sector operations</p>
                            </div>
                        </a>
                    </div>
                </div>"""

files_updated = 0

for file_name in os.listdir(html_dir):
    if file_name.endswith(".html"):
        file_path = os.path.join(html_dir, file_name)
        with open(file_path, "r", encoding="utf-8") as f:
            content = f.read()
            
        new_content = content.replace(target_dropdowns, replacement_dropdowns)
        
        if new_content != content:
            with open(file_path, "w", encoding="utf-8") as f:
                f.write(new_content)
            print(f"Updated {file_name} successfully.")
            files_updated += 1

print(f"Compact dropdown refactoring complete. {files_updated} files updated.")
