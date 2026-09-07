# Drestro POS - Printer Setup Guide
This guide explains how to connect Thermal Receipt Printers (like ZKTeco, Xprinter, Epson) to Drestro POS using either a USB cable or a LAN (Network) cable.

---

## METHOD 1: USB PRINTER SETUP
*Use this method if the printer is sitting right next to the computer and connected with a USB cable. This is the easiest setup.*

### Part A: On the Computer (Windows Setup)
1. **Plug it in:** Connect the printer to power and plug the USB cable into the computer. Turn the printer ON.
2. **Install the Driver:** 
   - Insert the CD that came with the printer, or download the Windows Driver for your printer model (e.g., "ZKTeco Receipt Printer Driver").
   - Run the installer. When asked for the port, select "USB".
3. **Test the Windows Connection:**
   - Open Windows **Settings** -> **Devices** -> **Printers & Scanners**.
   - Click your new printer and select **Manage** -> **Print a test page**. If it prints, Windows can see the printer.
4. **Share the Printer:**
   - On that same screen, click **Printer Properties**.
   - Go to the **Sharing** tab.
   - Check the box that says **"Share this printer"**.
   - Give it a simple share name with NO SPACES. Example: `ReceiptPrinter`
   - Click **Apply** and **OK**.

### Part B: In Drestro POS Settings
1. Open Drestro POS -> Go to **Admin Dashboard** -> **Settings**.
2. Scroll down to **Hardware & Printers**.
3. Under "Cashier Printer":
   - Change **Connection Type** to: `Windows USB Share`
   - In **Address / Share Name**, type exactly: `smb://localhost/ZKT80` *(Change 'ZKT80' if you used a different share name)*
4. Click **Save Settings**, then click **Test Print**.

---

## METHOD 2: LAN (NETWORK) PRINTER SETUP
*Use this method if you want multiple wireless tablets/phones to print directly to the kitchen or cashier printer without needing a USB cable. The printer must be plugged into your Wi-Fi Router.*

### Part A: Finding the Networks
Your computer and your printer must be on the exact same network (subnet).
1. Open Command Prompt (`cmd`) on your computer and type `ipconfig`.
2. Look at your **IPv4 Address**. 
   - *Example: `192.168.1.64`*
   - This means your network is `192.168.1.X`.
3. Find the printer's default IP. Turn the printer OFF. Hold the **FEED** button, turn it ON, and hold for 3 seconds. It will print a receipt.
   - Look at the receipt for "IP Address". 
   - *Example: `192.168.123.100`*. 
   - Because `.123` does not match `.1`, they cannot talk! You must change the printer's IP.

### Part B: Changing the Printer's IP
1. Temporarily plug the printer into the computer using a **USB Cable**.
2. Open the **Printer Configuration Tool** (found on the CD or downloaded from the manufacturer's website, often called "PrinterSet.exe" or "Xprinter Test Tool").
3. Connect the tool using the **USB** option.
4. Go to the **Advanced** or **Set Net** tab.
5. Change the Printer's IP Address to match your computer's network, but give it a high number so it doesn't conflict with phones. 
   - *Example: Change it from `192.168.123.100` to `192.168.1.200`*.
6. Click **Save** or **Set IP**. The printer will beep.
7. Turn the printer OFF and **unplug the USB cable** permanently.

### Part C: Physical Setup
1. Plug an **Ethernet (LAN) cable** into the back of the printer.
2. Plug the other end of that cable directly into your **Wi-Fi Router**.
3. Turn the printer ON. 

### Part D: In Drestro POS Settings
1. Open Drestro POS -> Go to **Admin Dashboard** -> **Settings**.
2. Scroll down to **Hardware & Printers**.
3. Under "Cashier Printer" (or Kitchen Printer):
   - Change **Connection Type** to: `Network (LAN/Wi-Fi)`
   - In **Address / Share Name**, type the NEW IP address and port: `192.168.1.200:9100`
4. Click **Save Settings**, then click **Test Print**.

---
### Troubleshooting
* **Error "Cannot initialise NetworkPrintConnector":** The IP address is wrong, the printer is off, or the printer is plugged into the wrong router.
* **Prints random symbols via USB:** You installed the wrong driver (e.g., installed a generic text driver instead of the thermal receipt driver).
* **LAN works on PC but not on Waiter Tablets:** Your Windows Firewall is blocking the tablets. Run the `Open Firewall.cmd` file as Administrator.
