<?php
session_start(); 
header('X-Content-Type-Options: nosniff');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <link rel="stylesheet" type="text/css" href="../static/CSS/Style.css">
</head>
<body>
<div class="background">
    <div class="container">
        <div class="reset-box">
            <div class="icon-container">
                <img src="../static/JPG/House_Icon.png" alt="House Icon">
            </div>
            <h2>Password Reset</h2>
            <?php

                $servername = "localhost";
                $username = "hrlin";  
                $password = "50429551";  
                $dbname = "cse442_2024_fall_team_q_db";
                $port = 3306;

                $conn = new mysqli($servername, $username, $password, $dbname, $port);

                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                $message = '';
                $type = '';

                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                        die('CSRF token validation failed');
                    }
                    $email = trim($_POST['email']);
                    $new_password = trim($_POST['new_password']);
                    $confirm_password = trim($_POST['confirm_password']);
                    if (empty($email) || empty($new_password) || empty($confirm_password)) {
                        $message = "All fields are required!";
                        $type = "error-message";
                    } elseif ($new_password !== $confirm_password) {
                        $message = "Passwords do not match!";
                        $type = "error-message";
                    } else {
                        $check_email_stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
                        $check_email_stmt->bind_param("s", $email);
                        $check_email_stmt->execute();
                        $result = $check_email_stmt->get_result();

                        if ($result->num_rows > 0) {
                            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
                            $stmt->bind_param("ss", $hashed_password , $email);

                            if ($stmt->execute()) {
                                $message = "Password reset successful!";
                                $type = "success-message";
                            } else {
                                $message = "Error: " . $stmt->error;
                                $type = "error-message";
                            }

                            $stmt->close();
                        } else {
                            $message = "The email address does not exist in our records!";
                            $type = "error-message";
                        }

                        $check_email_stmt->close();
                    }
                    $conn->close();
                }
                $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
            ?>
             <p id="message" class="<?php echo $type; ?>"><?php echo $message; ?></p>
            <div id="message-container"></div>
            <form id="resetForm" action="PasswordReset.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="input-group">
                    <label for="email"><i class="icon-email"></i></label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="input-group">
                    <label for="new_password"><i class="icon-lock"></i></label>
                    <input type="password" id="new_password" name="new_password" placeholder="New Password" required>
                </div>
                <div class="input-group">
                    <label for="confirm_password"><i class="icon-lock"></i></label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                </div>
                <div class="help-contact-container">
                    <span class="help-text">Need More Help?</span>
                    
                    <span class="contact-text">Contact Us</span>
                </div>
                <button type="submit" id="resetButton">Reset Password</button>
            </form>
        </div>
    </div>
</div>
<script src="../static/JS/WebpageFunction.JS"></script>
</body>
</html>
