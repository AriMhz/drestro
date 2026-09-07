import zipfile
import os

files_to_zip = [
    'DrestroPOS/resources/views/components/layouts/app.blade.php',
]

with zipfile.ZipFile('drestropos_alert_fix.zip', 'w', zipfile.ZIP_DEFLATED) as zipf:
    for file in files_to_zip:
        if os.path.exists(file):
            arcname = file.replace('DrestroPOS/', '', 1)
            zipf.write(file, arcname=arcname)

print("Created drestropos_alert_fix.zip")
