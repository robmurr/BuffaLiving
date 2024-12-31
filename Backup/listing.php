<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS, GET");
header("Access-Control-Allow-Headers: Content-Type, X-CSRF-Token");
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Prevent XSS by escaping input and output
function escape($input) {
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

// Database connection details
$servername = 'localhost';
$username = 'hrlin';
$password = '50429551';
$dbname = 'cse442_2024_fall_team_q_db';
//$dbname = 'rmurray5_db';
$port = 3306;

// Establish a database connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit();
}

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Send the CSRF token to the frontend as JSON (for GET requests)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // Generate CSRF token if it doesn't exist
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $query = "SELECT * FROM apartments LIMIT 4";  // Query to get 4 apartments
    $result = $conn->query($query);

    if ($result === false) {
        error_log("Error executing query: " . $conn->error);
        echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
        exit();
    }

    $apartments = [];
    while ($row = $result->fetch_assoc()) {
        $apartments[] = [
            'id' => $row['id'],
            'title' => escape($row['title']),
            'address' => escape($row['address']),
            'bed' => (int)$row['bed'],
            'bath' => (float)$row['bath'],
            'sqft' => (int)$row['sqft'],
            'price' => (int)$row['price'],
            'rating' => (float)$row['rating'],
            'image' => $row['image'],
            'interestedCount' => (int)$row['interestedCount'],
            'user_id' => (int)$row['user_id']
        ];
    }
    $result->free();

    echo json_encode([
        'csrfToken' => $_SESSION['csrf_token'],
        'apartments' => $apartments,
        'success' => true
    
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit(); // Stop further processing
}

// Handle POST request for search/filter
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $inputRaw = file_get_contents("php://input");
    error_log("Raw input: " . $inputRaw); // Debug raw input
    $input = json_decode($inputRaw, true);

    if ($input === null) {
        echo json_encode(["success" => false, "message" => "Invalid input"]);
        exit();
    }

    $csrfToken = $input['csrfToken'] ?? '';

    // CSRF token validation
    if (empty($csrfToken) || $csrfToken !== $_SESSION['csrf_token']) {
        echo json_encode(["success" => false, "message" => "Invalid CSRF token."]);
        exit();
    }
    error_log("Received CSRF Token: $csrfToken"); // Log the received CSRF token

    $action = $input['action'] ?? '';
    if($action === 'save'){
        $userId = $_SESSION['user_id']; // Assuming the user ID is stored in session
        $propertyId = isset($input['propertyId']) ? (int)$input['propertyId'] : null;

        if ($userId && $propertyId) {
            // Check if the property is already saved
            $stmt = $conn->prepare("SELECT * FROM user_saved_properties WHERE user_id = ? AND property_id = ?");
            $stmt->bind_param("ii", $userId, $propertyId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Toggle the saved status if it exists
                $row = $result->fetch_assoc();
                $newStatus = !$row['saved'];
                $updateStmt = $conn->prepare("UPDATE user_saved_properties SET saved = ? WHERE user_id = ? AND property_id = ?");
                $updateStmt->bind_param("iii", $newStatus, $userId, $propertyId);
                $updateStmt->execute();

                $incrementStmt = $conn->prepare("UPDATE apartments SET interestedCount = interestedCount + 1 WHERE id = ?");
                $incrementStmt->bind_param("i", $propertyId);
                $incrementStmt->execute();
            } else {
                // Insert a new row if not already saved
                $saved = 1; // true
                $insertStmt = $conn->prepare("INSERT INTO user_saved_properties (user_id, property_id, saved) VALUES (?, ?, ?)");
                $insertStmt->bind_param("iii", $userId, $propertyId, $saved);
                $insertStmt->execute();

                $incrementStmt = $conn->prepare("UPDATE apartments SET interestedCount = interestedCount + 1 WHERE id = ?");
                $incrementStmt->bind_param("i", $propertyId);
                $incrementStmt->execute();
            }

            echo json_encode(["success" => true, "message" => "Saved status updated."]);
        } else {
            echo json_encode(["success" => false, "message" => "Invalid user or property ID."]);
        }
        exit();
    }

    // Extract search and filter parameters
    $search = trim($input['search'] ?? '');
    $bed = isset($input['bed']) ? (int)$input['bed'] : null;    
    $maxPrice = isset($input['max_price']) ? (int)$input['max_price'] : null;

    // Build dynamic SQL query with filters
    $query = "SELECT * FROM apartments WHERE 1=1";
    if (empty($search) && empty($bed) && empty($maxPrice)) {
        // Return default set of properties or a message
        $query = "SELECT * FROM apartments LIMIT 4";
    } else {
        if (!empty($search)) {
            $escapedSearch = $conn->real_escape_string($search);
            $query = "SELECT * FROM apartments WHERE title LIKE '%$escapedSearch%'";
        }
    
        if (!empty($bed)) {
            $query .= " AND bed = $bed";
        }
    
        if (!empty($maxPrice)) {
            $query .= " AND price <= $maxPrice";
        }
    }
    
    $result = $conn->query($query);
    error_log("Query: $query"); // Log the query being executed

    // Check for database errors
    if ($result === false) {
        error_log("Error executing query: " . $conn->error);
        echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
        exit();
    }

    // Check if apartments are fetched successfully
    if ($result->num_rows === 0) {
        error_log("No properties found.");
    }

    // Prepare property data as JSON
    $apartments = [];
    while ($row = $result->fetch_assoc()) {
        $apartments[] = [
            'id' => $row['id'],
            'title' => escape($row['title']),
            'address' => escape($row['address']),
            'bed' => (int)$row['bed'],
            'bath' => (float)$row['bath'],
            'sqft' => (int)$row['sqft'],
            'price' => (int)$row['price'],
            'rating' => (float)$row['rating'],
            'image' => $row['image']
        ];
    }

    // Return the properties as a JSON response
    echo json_encode(["success" => true, "apartments" => $apartments]);

    $result->free();
}
$conn->close();
?>
