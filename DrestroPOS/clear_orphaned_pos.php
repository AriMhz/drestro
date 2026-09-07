<?php
// clear_orphaned_pos.php
// Place this in the Laravel POS folder: /home/drestro/htdocs/drestro.com/DrestroPOS/

$dbPath = __DIR__ . '/database/database.sqlite';
if (!file_exists($dbPath)) {
    $dbPath = '/home/drestro/htdocs/portal.drestro.com/database/database.sqlite';
}
if (!file_exists($dbPath)) {
    $dbPath = '/home/drestro/htdocs/portal.drestro.com/DrestroPOS/database/database.sqlite';
}

if (!file_exists($dbPath)) {
    die("Error: SQLite database file not found. Please run find_db.php first to locate it.\n");
}

$allowedSlugs = [
    'demo',
    '84b32dec-a0ff-4ef9-be2f-8559f4f3cabc'
];

try {
    $pdo = new PDO("sqlite:" . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Find all restaurants
    $stmt = $pdo->query("SELECT id, name, slug FROM restaurants");
    $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($restaurants) . " restaurants in POS database.\n";
    
    $deletedCount = 0;
    
    foreach ($restaurants as $r) {
        $slug = $r['slug'];
        $restaurantId = $r['id'];
        
        if (!in_array($slug, $allowedSlugs)) {
            echo "Deleting orphaned restaurant: {$r['name']} (Slug: {$slug}, ID: {$restaurantId})\n";
            
            // Delete associated data cascadingly using raw SQL statements
            $queries = [
                "DELETE FROM orders WHERE restaurant_id = :id",
                "DELETE FROM menu_items WHERE restaurant_id = :id",
                "DELETE FROM menu_categories WHERE restaurant_id = :id",
                "DELETE FROM tables WHERE restaurant_id = :id",
                "DELETE FROM users WHERE restaurant_id = :id",
                "DELETE FROM restaurants WHERE id = :id"
            ];
            
            // Handle optional tables that might not exist in old schemas
            $optionalTables = ['inventory_logs', 'invoices', 'inventory_items'];
            foreach ($optionalTables as $tbl) {
                // Check if table exists
                $tableCheck = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='$tbl'")->fetch();
                if ($tableCheck) {
                    $queries[] = "DELETE FROM $tbl WHERE restaurant_id = :id";
                }
            }
            
            // Execute deletes
            foreach ($queries as $sql) {
                $delStmt = $pdo->prepare($sql);
                $delStmt->execute([':id' => $restaurantId]);
            }
            
            $deletedCount++;
        }
    }
    
    echo "Successfully deleted {$deletedCount} orphaned restaurants and their data.\n";
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}
