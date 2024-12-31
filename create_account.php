<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS, GET");
header("Access-Control-Allow-Headers: Content-Type, X-CSRF-Token");
header('Content-Type: application/json');

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Prevent XSS by escaping output and input
function escape($input) {
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

// Database connection details
$servername = 'localhost';
$username = 'rmurray5';
$password = '50447880';
$dbname = 'cse442_2024_fall_team_q_db';
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

// Send the token to the frontend as JSON
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode(['csrfToken' => $_SESSION['csrf_token']]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit(); // Exit to stop further processing
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $inputRaw = file_get_contents("php://input");
    error_log("Raw input: " . $inputRaw); // Log raw input for debugging
    $input = json_decode($inputRaw, true);
    if ($input === null) {
        echo json_encode(["success" => false, "message" => "Invalid input"]);
        exit();
    }

    $name = trim($input['name'] ?? '');
    $email = trim($input['email'] ?? '');
    $password = trim($input['password'] ?? '');
    $csrfToken = $input['csrfToken'] ?? '';

    // CSRF token validation (if using sessions or custom tokens)
    if (empty($csrfToken) || $csrfToken !== $_SESSION['csrf_token']) {
        echo json_encode(["success" => false, "message" => "Invalid CSRF token."]);
        exit();
    }

    if (empty($name) || empty($email) || empty($password)) {
        echo json_encode(["success" => false, "message" => "All fields are required."]);
        exit();
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "Invalid email format."]);
        exit();
    }

    // Generate a random salt
    $salt = bin2hex(random_bytes(16)); // 16-byte random salt

    // Concatenate the salt with the password
    $saltedPassword = $salt . $password;

    // Hash the salted password
    $hashed_password = password_hash($saltedPassword, PASSWORD_DEFAULT);

    // Prepare SQL statement to insert user details (including the salt)
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, salt) VALUES (?, ?, ?, ?)");

    if ($stmt === false) {
        error_log("Error preparing statement: " . $conn->error);
        echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
        exit();
    }

    // Bind and execute the statement (now storing both the hash and salt)
    $stmt->bind_param("ssss", $name, $email, $hashed_password, $salt);
    if ($stmt->execute()) {
        // Account created successfully, now generate an authentication token
        $authToken = bin2hex(random_bytes(32)); // Generate a random authentication token

        // Update the user record with the auth token
        $userId = $stmt->insert_id; // Get the newly created user's ID
        $updateStmt = $conn->prepare("UPDATE users SET auth_token = ? WHERE id = ?");
        $updateStmt->bind_param("si", $authToken, $userId);

        if ($updateStmt->execute()) {
            // Send auth token back to the frontend
            setcookie('auth_token', $authToken, time() + (86400 * 30), "/", $_SERVER['HTTP_HOST'], true, true); // Expires in 30 days, secure, HTTP only
            echo json_encode([
                "success" => true, 
                "message" => "Account created successfully.",
                "authToken" => $authToken // Send the token to the frontend
            ]);
        } else {
            error_log("Error updating auth token: " . $updateStmt->error);
            echo json_encode(["success" => false, "message" => "Database error: " . $updateStmt->error]);
        }
        $updateStmt->close();
    } else {
        error_log("Error executing statement: " . $stmt->error);
        echo json_encode(["success" => false, "message" => "Database error: " . $stmt->error]);
    }

    $stmt->close();
}
$conn->close();
?>