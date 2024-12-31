<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection parameters
$host = 'localhost:3306';
$db = 'cse442_2024_fall_team_q_db';
$user = 'dhuang44';
$pass = '50452905';

// Create a connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if auth_token cookie exists
if (!isset($_COOKIE['auth_token'])) {
    http_response_code(401); // Unauthorized
    echo "<p>You must be logged in to add properties to the comparison list.</p>";
    exit;
}

$auth_token = $_COOKIE['auth_token'];

// Verify auth_token and get the user's ID
$stmt = $conn->prepare("SELECT id FROM users WHERE auth_token = ?");
$stmt->bind_param('s', $auth_token);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

if (!$user_id) {
    http_response_code(401); // Unauthorized
    echo "<p>Unauthorized: Invalid auth_token.</p>";
    $conn->close();
    exit;
}

// Handle the checkbox state for comparison list
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputRaw = file_get_contents("php://input");
    error_log("Raw input: " . $inputRaw); // Debug raw input
    $input = json_decode($inputRaw, true);

    if ($input === null) {
        echo json_encode(["success" => false, "message" => "Invalid input"]);
        exit();
    }

    $property_id = $input['property_id'];
    $is_checked = isset($input['is_checked']) && $input['is_checked'] == '1';

    if ($is_checked) {
        // Check the number of entries in the 'compare' table for the specific user
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM compare WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($compareCount);
        $stmt->fetch();
        $stmt->close();
        if ($compareCount < 2){
            // Insert property into `apartments` table if checked
            $stmt = $conn->prepare("INSERT IGNORE INTO compare (title, address, sqft, bed,bath, price, amenities, rating, user_id)
                SELECT title, address, sqft, bed, bath, price, amenities, rating, ? FROM apartments WHERE id = ?");
            $stmt->bind_param("ii", $user_id, $property_id);
            $stmt->execute();
            echo json_encode(["success" => true, "message" => "Property added to comparison list."]);
            $stmt->close();
        } else {
            echo json_encode(["success" => false, "message" => "Only two properties can be compared at a time."]);
        }
    } else {
        // Remove property from `compare` table if unchecked
        $stmt = $conn->prepare("DELETE FROM compare WHERE title = (SELECT title FROM apartments WHERE id = ?) AND user_id = ?");
        $stmt->bind_param("ii", $property_id, $user_id);
        $stmt->execute();
        echo json_encode(["success" => true, "message" => "Property removed from comparison list."]);
        $stmt->close();
    }

}

// Retrieve all properties
$properties = [];
$sql = "SELECT * FROM apartments";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $properties[] = $row;
    }
}

// Retrieve compared property names from `apartments` table
$compared_properties = [];
$stmt = $conn->prepare("SELECT title FROM compare WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $compared_properties[] = $row['title'];
}
$stmt->close();

$conn->close();
?>
