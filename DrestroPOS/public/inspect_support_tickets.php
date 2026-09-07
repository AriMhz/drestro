<?php
$file = __DIR__ . '/../app/Livewire/Admin/SupportTickets.php';
if (file_exists($file)) {
    echo "<h1>File found!</h1>";
    echo "<pre>" . htmlspecialchars(file_get_contents($file)) . "</pre>";
} else {
    echo "<h1>File not found!</h1>";
}
