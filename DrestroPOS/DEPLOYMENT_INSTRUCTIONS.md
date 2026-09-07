# Drestro POS - Deployment & Installation Guide

This document outlines how to safely package Drestro POS and install it on a brand new client's computer.

## Part 1: Preparing the Files (Your Job)

Before copying the software to a USB drive or sending it to a client, you need to package it cleanly:

1. **Obfuscate the Code (Optional but Recommended)**
   - If using IonCube or SourceGuardian, you should encrypt the `app/` and `routes/` folders.
   - **Will the "Run Server.cmd" still work?** Yes! `php artisan serve` doesn't care if the code is obfuscated. PHP will decrypt it in real-time as long as the client has the IonCube Loader installed in their PHP extensions.

2. **What files to ZIP**
   - You need to zip the **entire `drestro` folder**. 
   - Ensure the `.env` file is included in the zip (it's hidden by default on some systems, so double-check).
   - Ensure `database/database.sqlite` is included. I have already cleared all test orders for you, but your Menu, Tables, and Admin user are still there!

## Part 2: Installing on a Client's PC from Scratch

When you arrive at the client's restaurant, follow these exact steps:

1. **Install Prerequisites**
   - Install PHP (make sure it's added to the Windows PATH).
   - If you obfuscated the code, make sure to add the IonCube/SourceGuardian loader extension to their `php.ini`.

2. **Copy the Files**
   - Extract the `drestro` zip file to a permanent location (e.g., `C:\DrestroPOS`).

3. **Run Initial Setup (CRITICAL)**
   - Double-click the **`Install.cmd`** file.
   - *Why?* When you move a Laravel app to a new computer, the hard drive paths change. This script automatically links the image folders and optimizes the code for their specific computer's path to make it run extremely fast. You only need to do this **once**.

4. **Network & Firewall**
   - Double-click **`Open Firewall.cmd`**.
   - This opens port `8000` so that waiters' phones and kitchen tablets can connect to the main PC.

5. **Start the Server**
   - Double-click **`Run Server.cmd`**.
   - A black window will open and tell you the IP Address (e.g., `http://192.168.1.100:8000`).
   - Keep this window open at all times! (You can minimize it).

## Part 3: Connecting Tablets & Phones

1. Go to the Admin Dashboard (`http://localhost:8000/admin`).
2. Navigate to **Settings** -> **QR Codes**.
3. You will see automatically generated QR codes for the Kitchen, Waiter, and Menu.
4. Have the staff scan their respective QR codes with their mobile devices to instantly log in!
