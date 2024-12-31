<?php
// Add this at the top of `post_listing.php` and `get_listings.php`
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Database connection details
$host = 'localhost:3306';
$dbname = 'justindv_db';
$username = 'justindv';
$password = '50460006';

// Connect to MySQL database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Retrieve all listings from the database
$stmt = $pdo->query("SELECT * FROM listings ORDER BY created_at DESC");
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return listings as JSON
header('Content-Type: application/json');
echo json_encode($listings);
?>