import os
import subprocess

user_profile = os.environ.get('USERPROFILE', '')
local_app_data = os.environ.get('LOCALAPPDATA', '')
desktop = os.path.join(user_profile, 'Desktop')
shortcut_path = os.path.join(desktop, 'DRestro POS (Auto-Print).lnk')
profile_dir = os.path.join(local_app_data, 'DRestroPOSProfile')

# Detect browser
browsers = [
    r"C:\Program Files\BraveSoftware\Brave-Browser\Application\brave.exe",
    os.path.join(local_app_data, r"BraveSoftware\Brave-Browser\Application\brave.exe"),
    r"C:\Program Files (x86)\BraveSoftware\Brave-Browser\Application\brave.exe",
    r"C:\Program Files\Google\Chrome\Application\chrome.exe",
    r"C:\Program Files (x86)\Google\Chrome\Application\chrome.exe",
    os.path.join(local_app_data, r"Google\Chrome\Application\chrome.exe"),
    r"C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe",
    r"C:\Program Files\Microsoft\Edge\Application\msedge.exe"
]

target_browser = None
for b in browsers:
    if os.path.exists(b):
        target_browser = b
        break

if not target_browser:
    print("[ERROR] No supported browser found (Brave, Chrome, or Edge).")
else:
    print(f"[FOUND] Browser: {target_browser}")
    args = f'--kiosk-printing --user-data-dir="{profile_dir}" --app=https://portal.drestro.com'
    ps_command = f'''
    $ws = New-Object -ComObject WScript.Shell
    $s = $ws.CreateShortcut("{shortcut_path}")
    $s.TargetPath = "{target_browser}"
    $s.Arguments = '{args}'
    $s.Description = "DRestro POS Instant Auto-Print"
    $s.Save()
    '''
    subprocess.run(["powershell", "-NoProfile", "-Command", ps_command], check=True)
    if os.path.exists(shortcut_path):
        print(f"[SUCCESS] Created shortcut: {shortcut_path}")
    else:
        print("[ERROR] Shortcut was not created.")
