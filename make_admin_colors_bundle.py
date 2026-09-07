import os
import zipfile

directories_to_zip = [
    'src/app/admin',
    'src/components'
]

with zipfile.ZipFile('drestro_admin_colors.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    for d in directories_to_zip:
        for root, dirs, files in os.walk(d):
            for file in files:
                filepath = os.path.join(root, file)
                zipf.write(filepath)
                print(f"Added {filepath}")

print("Created drestro_admin_colors.zip successfully.")
