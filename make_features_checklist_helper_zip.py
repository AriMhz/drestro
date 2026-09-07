import zipfile
import os

files_to_zip = [
    'src/app/admin/(dashboard)/pricing/[id]/EditPlanForm.tsx',
    'src/app/admin/(dashboard)/pricing/[id]/page.tsx'
]

zip_name = 'drestro_plan_features_checklist_helper.zip'

with zipfile.ZipFile(zip_name, 'w', zipfile.ZIP_DEFLATED) as zipf:
    for file in files_to_zip:
        if os.path.exists(file):
            zipf.write(file, arcname=file)
            print(f"Added: {file}")
        else:
            print(f"Missing: {file}")

print(f"Created {zip_name} successfully.")
