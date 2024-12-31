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

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the data from the request body
    $data = json_decode(file_get_contents('php://input'), true);

    // Extract the listing details
    $title = $data['title'] ?? '';
    $description = $data['description'] ?? '';
    $price = $data['price'] ?? 0;
    $location = $data['location'] ?? '';
    $image_url = $data['image_url'] ?? '';

    // Validate the required fields
    if (empty($title) || empty($description) || empty($price) || empty($location)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit();
    }

    // Insert the listing into the database
    $stmt = $pdo->prepare("INSERT INTO listings (title, description, price, location, image_url) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$title, $description, $price, $location, $image_url])) {
        echo json_encode(['status' => 'success', 'message' => 'Listing added successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add listing.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>