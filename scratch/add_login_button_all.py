import os

html_dir = r"c:\Users\user\Desktop\hr-services"

target_str = """            <div class="flex items-center gap-4">
                <a href="demo.html" class="bg-gradient-to-r from-primary to-secondary text-white px-7 py-3 rounded-xl font-label-md hover:from-secondary hover:to-primary hover:shadow-lg transition-all duration-300 hover:scale-105 active:scale-95 shadow-md">Request Demo</a>
            </div>"""

replacement_str = """            <div class="flex items-center gap-4">
                <a href="login.html" class="font-label-md text-label-md text-on-surface-variant hover:text-secondary transition-colors mr-2">Login</a>
                <a href="demo.html" class="bg-gradient-to-r from-primary to-secondary text-white px-7 py-3 rounded-xl font-label-md hover:from-secondary hover:to-primary hover:shadow-lg transition-all duration-300 hover:scale-105 active:scale-95 shadow-md">Request Demo</a>
            </div>"""

files_updated = 0

for file_name in os.listdir(html_dir):
    if file_name.endswith(".html") and file_name not in ["login.html", "register.html"]:
        file_path = os.path.join(html_dir, file_name)
        with open(file_path, "r", encoding="utf-8") as f:
            content = f.read()
            
        new_content = content.replace(target_str, replacement_str)
        
        if new_content != content:
            with open(file_path, "w", encoding="utf-8") as f:
                f.write(new_content)
            print(f"Added Login button to {file_name} successfully.")
            files_updated += 1

print(f"All files Login button deployment complete. {files_updated} files updated.")
