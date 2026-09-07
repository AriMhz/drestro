import os

def check_files():
    target_dirs = [
        r'c:\Users\AZN\Documents\drestro-web\src\app',
        r'c:\Users\AZN\Documents\drestro-web\src\components'
    ]
    
    found_issues = []
    
    for target_dir in target_dirs:
        for root, _, files in os.walk(target_dir):
            for file in files:
                if file.endswith('.tsx'):
                    filepath = os.path.join(root, file)
                    try:
                        with open(filepath, 'r', encoding='utf-8') as f:
                            content = f.read()
                        
                        # If the file has onClick but does not have "use client"
                        if 'onClick' in content and '"use client"' not in content and "'use client'" not in content:
                            found_issues.append((filepath, 'onClick found without "use client"'))
                    except Exception as e:
                        print(f"Error reading {filepath}: {e}")
                        
    if found_issues:
        print("FOUND ISSUES:")
        for path, issue in found_issues:
            print(f"- {path}: {issue}")
    else:
        print("No files with onClick missing 'use client' found.")

if __name__ == '__main__':
    check_files()
