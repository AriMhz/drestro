# Drestro POS - macOS Packaging & Deployment Guide

This guide describes how to package Drestro POS into a standalone, cashier-friendly `.app` package and distribute it as a premium **one-click macOS installer (`.dmg`)**—exactly like your Windows Inno Setup installer!

---

## Part 1: How macOS Standalone Apps Work
On macOS, a `.app` file (like `DrestroPOS.app`) is actually a folder (a "bundle") that contains:
1. **Your Codebase**: The entire Laravel + SQLite codebase.
2. **macOS PHP engine**: A precompiled macOS-compatible version of PHP.
3. **A Launcher Script**: A native AppleScript or shell launcher that starts the server in the background and opens the POS window automatically.

---

## Part 2: Creating a One-Click `.app` with Automator (No Code Needed!)
macOS has a built-in tool called **Automator** that lets you turn shell scripts into clickable applications with custom icons in seconds:

1. Open **Automator** on a Mac (press `Cmd + Space` and search for "Automator").
2. Choose **Application** as the document type.
3. In the search box on the left, type **"Run Shell Script"** and drag it to the main window on the right.
4. Set the Shell dropdown to `/bin/bash` and paste this startup command:
   ```bash
   cd "$(dirname "$0")/../Resources/DrestroPOS"
   ./"START SERVER.sh" &
   sleep 2
   open "http://127.0.0.1:8000"
   ```
5. Add another action: **"Run AppleScript"** to handle graceful shutdowns. Drag it below and paste:
   ```applescript
   on quit
       do shell script "killall php"
       continue quit
   end quit
   ```
6. Save the Automator flow as **`DrestroPOS.app`**.
7. Right-click your new `DrestroPOS.app`, select **Get Info**, and drag your Drestro logo icon (`.icns` file) onto the top-left icon box to style it beautifully!

---

## Part 3: Packaging into a `.dmg` Installer (Like Inno Setup!)
To package your folder and the `.app` bundle into a professional `.dmg` Disk Image for your clients:

### Method A: DMG Canvas (Visual Tool - Highly Recommended)
1. Download a free app called **DMG Canvas** or **Disk Drill's DMG maker** on a Mac.
2. Drag your new `DrestroPOS.app` into the design window.
3. Drag a link to the macOS **`Applications`** folder inside.
4. Set a custom branded background image (Drestro logo/colors).
5. Click **Build** to output a perfect **`DrestroPOS_Setup.dmg`**!

### Method B: appdmg (Terminal-Based)
If you prefer a script-based approach like Inno Setup:
1. Create a file named `appdmg.json` in your build directory:
   ```json
   {
     "title": "Drestro POS Installer",
     "icon": "icon.icns",
     "background": "installer_bg.png",
     "icon-size": 80,
     "contents": [
       { "x": 192, "y": 244, "type": "file", "path": "DrestroPOS.app" },
       { "x": 448, "y": 244, "type": "link", "path": "/Applications" }
     ]
   }
   ```
2. Run this command in terminal:
   ```bash
   npm install -g appdmg
   appdmg appdmg.json DrestroPOS_Setup.dmg
   ```

---

## Part 4: Running on the Client's Mac
When your client receives the `.dmg` file:
1. They double-click it.
2. They drag the **Drestro POS** icon onto the **Applications** shortcut.
3. They open their Launchpad or Applications folder and click **Drestro POS** to start running!
