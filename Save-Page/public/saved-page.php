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
$username = 'rmurray5';
$password = '50447880';
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
$userId = $_COOKIE['user_id'] ?? '';

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Send the CSRF token to the frontend as JSON (for GET requests)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // SQL query to get saved apartments for the logged-in user
    $query = "
        SELECT a.id, a.title, a.address, a.bed, a.bath, a.sqft, a.price, a.rating, a.image
        FROM apartments a
        INNER JOIN user_saved_properties usp ON a.id = usp.property_id
        WHERE usp.user_id = ? AND usp.saved = 1
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Prepare saved apartments data as JSON
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

    $type = $input['type'] ?? '';

    // Check if the request is to update a rating
    if ($type === 'updateRating' && isset($input['propertyId']) && isset($input['rating'])) {
        $propertyId = $input['propertyId'];
        $newRating = $input['rating'];

        // Fetch the current rating and numRates for the property
        $stmt = $conn->prepare("SELECT rating, numRates FROM apartments WHERE id = ?");
        $stmt->bind_param("i", $propertyId);
        $stmt->execute();
        $stmt->bind_result($currentRating, $currentNumRates);
        
        if ($stmt->fetch()) {
            $stmt->close();

            // Calculate the updated rating and numRates
            $updatedNumRates = $currentNumRates + 1;
            $updatedRating = (($currentRating * $currentNumRates) + $newRating) / ($updatedNumRates);
            

            // Update the rating and numRates in the database
            $stmt = $conn->prepare("UPDATE apartments SET rating = ?, numRates = ? WHERE id = ?");
            $stmt->bind_param("dii", $updatedRating, $updatedNumRates, $propertyId);

            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Rating updated successfully"]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to update rating"]);
            }
            $stmt->close();
        } else {
            echo json_encode(["success" => false, "message" => "Property not found."]);
        }
        
        exit();
    }


    // Check if this request is for deleting a saved property
    if ($type === 'removeProperty' && isset($input['propertyId'])) {
        $propertyId = $input['propertyId'];

        $userId = $_COOKIE['user_id'] ?? null;
        // Ensure the user is authenticated
        if (!$userId) {
            echo json_encode(["success" => false, "message" => "User not authenticated."]);
            exit();
        }

        // Remove the property from user_saved_properties table
        $stmt = $conn->prepare("DELETE FROM user_saved_properties WHERE user_id = ? AND property_id = ?");
        $stmt->bind_param("ii", $userId, $propertyId);
        
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Property removed successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to remove property."]);
        }

        $decrementStmt = $conn->prepare("UPDATE apartments SET interestedCount = interestedCount - 1 WHERE id = ?");
        $decrementStmt->bind_param("i", $propertyId);
        $decrementStmt->execute();

        exit();
    }
}
$conn->close();
?>
