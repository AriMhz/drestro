[Setup]
; App Information
AppName=Drestro POS
AppVersion=1.1.0
AppPublisher=Drestro
AppPublisherURL=https://www.drestro.com
AppSupportURL=https://www.drestro.com
AppUpdatesURL=https://www.drestro.com

; Important: Install directly to C:\DrestroPOS to avoid Windows 'Program Files' permission issues with SQLite/PHP
DefaultDirName=C:\DrestroPOS
DisableProgramGroupPage=yes
PrivilegesRequired=admin
OutputDir=Output
OutputBaseFilename=Drestro_POS_Installer
Compression=lzma
SolidCompression=yes
SetupIconFile=app_icon.ico
UninstallDisplayIcon={app}\app_icon.ico

[Files]
; Main Files (Excludes dev SQLite databases to prevent overwriting client data or shipping developer test data)
Source: "*"; DestDir: "{app}"; Flags: ignoreversion recursesubdirs createallsubdirs; Excludes: "node_modules\*,\Drestro_Keygen\*,\Output\*,tests\*,.git\*,storage\logs\*,phpunit.xml,*.iss,license_debug.txt,bootstrap\cache\*.php,storage\framework\views\*.php,\database\database.sqlite,\database\database_empty.sqlite"

; Package fresh, clean database template (Renamed to database.sqlite, only installed on new installations, never deleted on uninstall)
Source: "database\database_empty.sqlite"; DestName: "database.sqlite"; DestDir: "{app}\database"; Flags: onlyifdoesntexist uninsneveruninstall

; Microsoft Visual C++ Redistributable (Required for PHP on new machines)
Source: "VC_redist.x64.exe"; DestDir: "{tmp}"; Flags: deleteafterinstall

[Icons]
; Start Menu Shortcuts
Name: "{autoprograms}\Drestro POS\Start Server (Background)"; Filename: "{app}\start-hidden.vbs"; IconFilename: "{app}\app_icon.ico"
Name: "{autoprograms}\Drestro POS\Stop Server"; Filename: "{app}\stop-server.cmd"; IconFilename: "{app}\app_icon.ico"
Name: "{autoprograms}\Drestro POS\Open Admin Dashboard"; Filename: "{app}\Open - Admin Dashboard.cmd"; IconFilename: "{app}\app_icon.ico"
Name: "{autoprograms}\Drestro POS\Open Waiter App"; Filename: "{app}\Open - Waiter Dashboard.cmd"; IconFilename: "{app}\app_icon.ico"
Name: "{autoprograms}\Drestro POS\Open Cashier"; Filename: "{app}\Open - Cashier Dashboard.cmd"; IconFilename: "{app}\app_icon.ico"

; Desktop Shortcuts
Name: "{autodesktop}\Drestro POS Server"; Filename: "{app}\START SERVER.cmd"; IconFilename: "{app}\app_icon.ico"; Tasks: desktopicon
Name: "{autodesktop}\Drestro Admin"; Filename: "{app}\Open - Admin Dashboard.cmd"; IconFilename: "{app}\app_icon.ico"; Tasks: desktopicon

; Auto-Start in Background (Startup Folder)
Name: "{userstartup}\Drestro POS Server"; Filename: "{app}\start-hidden.vbs"; WorkingDir: "{app}"; Tasks: startonstartup

[Tasks]
Name: "desktopicon"; Description: "Create Desktop Shortcuts"; GroupDescription: "Additional icons:"; Flags: unchecked
Name: "startonstartup"; Description: "Run Server automatically in the background on Windows startup"; GroupDescription: "Auto-Start:"; Flags: checkedonce

[Dirs]
Name: "{app}\storage"
Name: "{app}\storage\app"
Name: "{app}\storage\app\public"
Name: "{app}\storage\app\public\categories"
Name: "{app}\storage\app\public\menu_items"
Name: "{app}\storage\app\public\restaurants"
Name: "{app}\storage\framework"
Name: "{app}\storage\framework\cache"
Name: "{app}\storage\framework\cache\data"
Name: "{app}\storage\framework\sessions"
Name: "{app}\storage\framework\views"
Name: "{app}\storage\framework\testing"
Name: "{app}\storage\logs"

[Run]
; Install VC Redist silently
Filename: "{tmp}\VC_redist.x64.exe"; Parameters: "/install /quiet /norestart"; StatusMsg: "Installing Microsoft Visual C++ Redistributable (Required for Server)..."

; Open Firewall port 8000 automatically on install
Filename: "netsh"; Parameters: "advfirewall firewall add rule name=""Drestro POS Server"" dir=in action=allow protocol=TCP localport=8000"; Flags: runhidden; StatusMsg: "Configuring Windows Firewall..."

; Create storage link (just in case)
Filename: "cmd.exe"; Parameters: "/c cd ""{app}"" && (if exist php\php.exe (php\php.exe artisan storage:link) else (php artisan storage:link))"; Flags: runhidden; StatusMsg: "Linking Storage..."

; Start the hidden server immediately after install
Filename: "{app}\start-hidden.vbs"; Flags: shellexec nowait postinstall skipifsilent; Description: "Start Drestro POS Server in background"
