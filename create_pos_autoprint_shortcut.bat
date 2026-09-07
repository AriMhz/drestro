@echo off
title DRestro POS - Auto Print Setup
echo ========================================================
echo       DRestro POS - 1-Click Silent Auto-Print Setup
echo ========================================================
echo.

:: Detect Brave, Chrome, or Edge
set "BROWSER_PATH="
if exist "%ProgramFiles%\BraveSoftware\Brave-Browser\Application\brave.exe" set "BROWSER_PATH=%ProgramFiles%\BraveSoftware\Brave-Browser\Application\brave.exe"
if exist "%LocalAppData%\BraveSoftware\Brave-Browser\Application\brave.exe" set "BROWSER_PATH=%LocalAppData%\BraveSoftware\Brave-Browser\Application\brave.exe"
if exist "%ProgramFiles(x86)%\BraveSoftware\Brave-Browser\Application\brave.exe" set "BROWSER_PATH=%ProgramFiles(x86)%\BraveSoftware\Brave-Browser\Application\brave.exe"

if "%BROWSER_PATH%"=="" (
    if exist "%ProgramFiles%\Google\Chrome\Application\chrome.exe" set "BROWSER_PATH=%ProgramFiles%\Google\Chrome\Application\chrome.exe"
    if exist "%ProgramFiles(x86)%\Google\Chrome\Application\chrome.exe" set "BROWSER_PATH=%ProgramFiles(x86)%\Google\Chrome\Application\chrome.exe"
    if exist "%LocalAppData%\Google\Chrome\Application\chrome.exe" set "BROWSER_PATH=%LocalAppData%\Google\Chrome\Application\chrome.exe"
)

if "%BROWSER_PATH%"=="" (
    if exist "%ProgramFiles(x86)%\Microsoft\Edge\Application\msedge.exe" set "BROWSER_PATH=%ProgramFiles(x86)%\Microsoft\Edge\Application\msedge.exe"
    if exist "%ProgramFiles%\Microsoft\Edge\Application\msedge.exe" set "BROWSER_PATH=%ProgramFiles%\Microsoft\Edge\Application\msedge.exe"
)

if "%BROWSER_PATH%"=="" (
    echo [ERROR] Could not find Brave, Google Chrome, or Microsoft Edge.
    pause
    exit /b
)

echo Found Browser: %BROWSER_PATH%
echo Creating Desktop Shortcut...
echo.

powershell -NoProfile -Command "$ws = New-Object -ComObject WScript.Shell; $s = $ws.CreateShortcut($env:USERPROFILE + '\Desktop\DRestro POS.lnk'); $s.TargetPath = '%BROWSER_PATH%'; $s.Arguments = '--kiosk-printing --user-data-dir=' + $env:LOCALAPPDATA + '\DRestroPOSProfile --app=https://portal.drestro.com'; $s.Save()"

if exist "%USERPROFILE%\Desktop\DRestro POS.lnk" (
    echo ========================================================
    echo  SUCCESS: DRestro POS Shortcut Created on Desktop!
    echo ========================================================
    echo.
    echo IMPORTANT:
    echo 1. Set your Thermal Receipt Printer as DEFAULT in Windows.
    echo    (If destination is Print to PDF, Windows asks where to save file).
    echo.
    echo 2. Launch DRestro POS from your Desktop icon.
    echo.
    echo 3. Click Print ONCE with your thermal printer selected.
    echo    From then on, all KOT, BOT, and Receipts print AUTOMATICALLY!
    echo.
) else (
    echo ERROR: Failed to create desktop shortcut.
)

pause
