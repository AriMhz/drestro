import sqlite3

conn = sqlite3.connect('dev.db')
cursor = conn.cursor()

print("Clients:")
for row in cursor.execute("SELECT id, restaurantName, licenseKey, status FROM Client"):
    print(row)

print("\nRestaurants:")
for row in cursor.execute("SELECT id, name, phone, offlineLicenseKey FROM Restaurant"):
    print(row)

print("\nSupport Tickets:")
for row in cursor.execute("SELECT id, title, status, restaurantId FROM SupportTicket"):
    print(row)
