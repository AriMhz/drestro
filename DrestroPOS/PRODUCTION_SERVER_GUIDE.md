# Production Server Guide for Drestro POS

Drestro POS comes with a convenient `Run Server.cmd` file that uses PHP's built-in development server. While this is great for a single computer, **it is single-threaded on Windows**. This means if the Kitchen Display and a Waiter try to load data at the exact same time, one device will have to wait for the other, which can make the software feel "frozen" or slow on a busy network.

To achieve true concurrency and high performance across multiple devices on your local network, you should use a proper local web server.

## The Recommended Solution: Laragon

We highly recommend using **Laragon** on your main Windows POS computer. Laragon is a fast, lightweight, and incredibly powerful local server environment that will process multiple requests simultaneously.

### How to set it up:

1. **Download Laragon**: Go to [laragon.org/download](https://laragon.org/download/) and download the "Laragon Full" version.
2. **Install**: Run the installer and keep the default settings.
3. **Move Drestro**: Move the entire `drestro` folder into Laragon's `www` directory (usually located at `C:\laragon\www`).
4. **Start Laragon**: Open the Laragon application and click **Start All**.
5. **Access the POS**: Laragon automatically creates a local domain for you. You can access the POS at `http://drestro.test` on the host machine.
6. **Network Access**: To allow iPads, phones, or other computers to connect, simply find your main computer's IP address (e.g., `192.168.1.50`) and visit that IP address on the other devices.

Using this setup ensures your restaurant POS can handle dozens of simultaneous orders without ever slowing down.
