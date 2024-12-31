<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

function escape($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

$host = 'localhost';
$db = 'cse442_2024_fall_team_q_db';
$user = 'tauhidur';
$pass = '50432803';
$port = 3306;

$conn = new mysqli($host, $user, $pass, $db, $port);

function isAuthenticated($conn) {
    if (!isset($_COOKIE['auth_token'])) {
        return false;
    }
    
    $authToken = $_COOKIE['auth_token'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE auth_token = ?");
    $stmt->bind_param("s", $authToken);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows === 1;
}

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed. Please try again later."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(204);
    exit();
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode(['csrfToken' => $_SESSION['csrf_token']]);
    exit();
}

$response = ["success" => false, "message" => ""];

if (isAuthenticated($conn)) {
    echo json_encode(["success" => true, "message" => "You are already logged in."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputRaw = file_get_contents("php://input");
    $data = json_decode($inputRaw, true);

    if ($data === null) {
        echo json_encode(["success" => false, "message" => "Invalid input."]);
        exit();
    }

    $email = trim($data['email'] ?? '');
    $password = trim($data['password'] ?? '');
    $csrfToken = trim($data['csrfToken'] ?? '');

    if (empty($csrfToken) || $csrfToken !== $_SESSION['csrf_token']) {
        echo json_encode(["success" => false, "message" => "Invalid CSRF token."]);
        exit();
    }

    if (empty($email) || empty($password)) {
        $response["message"] = "Email and password are required.";
    } else {
        // Fetch the password hash and salt from the database
        $stmt = $conn->prepare("SELECT id, name, password, salt FROM users WHERE email = ?");
        if ($stmt === false) {
            $response["message"] = "Database error. Please try again later.";
            echo json_encode($response);
            exit();
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            // Concatenate the retrieved salt with the input password
            $saltedPassword = $row['salt'] . $password;

            // Verify the concatenated salted password with the stored hashed password
            if (password_verify($saltedPassword, $row['password'])) {
                $authToken = bin2hex(random_bytes(32));
                $updateStmt = $conn->prepare("UPDATE users SET auth_token = ? WHERE id = ?");
                $updateStmt->bind_param("si", $authToken, $row['id']);
                $updateStmt->execute();

                setcookie('auth_token', $authToken, [
                    'expires' => time() + 3600,
                    'path' => '/',
                    'domain' => $_SERVER['HTTP_HOST'],
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Strict',
                ]);

                $response["success"] = true;
                $response["auth_token"] = $authToken;
                $response["message"] = "Login successful! Welcome, " . escape($row['name']) . ".";
                echo json_encode($response);
                exit();
            } else {
                $response["message"] = "Password is incorrect. Please try again.";
            }
        } else {
            $response["message"] = "The email address does not exist in the system.";
        }

        $stmt->close();
    }
} else {
    $response["message"] = "Invalid request method.";
}

$conn->close();
echo json_encode($response);

?>