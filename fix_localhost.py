import os

directory = 'DrestroPOS'

for root, _, files in os.walk(directory):
    for file in files:
        if file.endswith(('.php', '.blade.php')):
            filepath = os.path.join(root, file)
            try:
                with open(filepath, 'r', encoding='utf-8') as f:
                    content = f.read()
                
                if 'http://localhost:3000' in content:
                    content = content.replace('http://localhost:3000', 'https://drestro.com')
                    with open(filepath, 'w', encoding='utf-8') as f:
                        f.write(content)
                    print(f"Fixed {filepath}")
            except Exception as e:
                pass

print("Done fixing localhost links.")
