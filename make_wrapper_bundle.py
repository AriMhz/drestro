import os
import zipfile

files_to_zip = [
    'src/components/layout/ClientLayoutWrapper.tsx'
]

with zipfile.ZipFile('drestro_wrapper_fix.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    for file in files_to_zip:
        if os.path.exists(file):
            zipf.write(file)
            print(f"Added {file}")
        else:
            print(f"Warning: {file} not found")

print("Created drestro_wrapper_fix.zip successfully.")
