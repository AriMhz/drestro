import re

file_path = 'src/app/admin/(dashboard)/clients/ClientsClient.tsx'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update presets in manage plan modal (web users)
presets_regex = r"""                  \{\[
                    \{ label: '\+14 Days', days: 14 \},
                    \{ label: '\+1 Month', days: 30 \},
                    \{ label: '\+6 Months', days: 180 \},
                    \{ label: '\+1 Year', days: 365 \},
                    \{ label: 'Lifetime', days: 36500 \},
                  \]\.map\(preset => \("""

new_presets = """                  {[
                    { label: '+14 Days (Trial)', days: 14 },
                    { label: '+1 Year', days: 365 },
                    { label: '+2 Years', days: 730 },
                    { label: '+3 Years', days: 1095 },
                    { label: 'Lifetime', days: 36500 },
                  ].map(preset => ("""

content = re.sub(presets_regex, new_presets, content)

# 2. Update offline pos duration select options
offline_regex = r"""                    <option value="14">14 Days \(Free Trial\)</option>
                    <option value="30">1 Month</option>
                    <option value="90">3 Months</option>
                    <option value="180">6 Months</option>
                    <option value="365">1 Year</option>
                    <option value="730">2 Years</option>
                    <option value="36500">Lifetime \(100 Years\)</option>"""

new_offline = """                    <option value="14">14 Days (Free Trial)</option>
                    <option value="30">1 Month</option>
                    <option value="90">3 Months</option>
                    <option value="365">1 Year</option>
                    <option value="730">2 Years</option>
                    <option value="1095">3 Years</option>
                    <option value="36500">Lifetime (100 Years)</option>"""

content = re.sub(offline_regex, new_offline, content)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
print("Updated ClientsClient.tsx")
