
<?php
header("Access-Control-Allow-Origin: *");
header("Cross-Origin-Opener-Policy: same-origin");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/vendor/autoload.php';
require 'phpmailer/vendor/phpmailer/phpmailer/src/Exception.php';
require 'phpmailer/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'phpmailer/vendor/phpmailer/phpmailer/src/SMTP.php';

$servername = "bom1plzcpnl503931.prod.bom1.secureserver.net";
$username = "DataGateway";
$password = "33zBrmCUqoJ7";
$dbname = "Data_Gateway";

$errors = array();

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? $_POST['email'] : '';

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format. Please enter a valid email address.";
    }

    $agreeTerms = isset($_POST['agreeTerms']) ? $_POST['agreeTerms'] : '';

    if (empty($agreeTerms)) {
        $errors[] = "You must accept the terms in order to proceed.";
    }

    if (empty($errors)) {
        $checkEmailQuery = "SELECT * FROM user WHERE email = '$email'";
        $result = $pdo->query($checkEmailQuery);

        if ($result->rowCount() > 0) {
            http_response_code(400); // Bad Request
            echo '<script>document.getElementById("error-message").innerHTML = "Email already exists. Please use a different email address.";</script>';
            exit;
        } else {
            sendSignupLink($pdo, $email);
        }
    } else {
        foreach ($errors as $error) {
            echo '<script>document.getElementById("error-message").innerHTML = "' . $error . '";</script>';
        }
        exit;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $email = isset($_GET['email']) ? $_GET['email'] : '';
} else {
    http_response_code(405);
    // echo 'Invalid request method';
}

function sendSignupLink($pdo, $email)
{
    $mail = new PHPMailer;
    $mail->isSMTP();
    $mail->Host = 'mail.datagateway.in';
    $mail->Port = 587;
    $mail->SMTPAuth = true;
    $mail->Username = 'notification@datagateway.in';
    $mail->Password = 'uojx?Ss?EOsx';
    $mail->setFrom('notification@datagateway.in', 'Verification');
    $mail->addReplyTo('notification@datagateway.in', 'Verification');

    // Load the HTML template
   $templateContent = '<html>
    <body>
        <p>Hello,</p>
        <p>Click the following link to sign up: <a href="{{signupLink}}">{{signupLink}}</a></p>
    </body>
</html>';

// Replace placeholders with actual values
$signupLink = "https://app.datagateway.in/signup/validate/index.php?email=" . urlencode($email);
$templateContent = str_replace('{{signupLink}}', $signupLink, $templateContent);

// Set email body as HTML content
$mail->Body = $templateContent;
    $mail->isHTML(true);

    // Add recipient and send email
    $mail->addAddress($email);
    $mail->Subject = 'Signup Link';

    if ($mail->send()) {
        $insertUserQuery = "INSERT INTO user (email) VALUES (?)";
        $stmt = $pdo->prepare($insertUserQuery);

        try {
            if ($stmt->execute([$email])) {
                http_response_code(200);
                echo '<script>document.getElementById("success-message").innerHTML = "Signup link sent!";</script>';
            }
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                http_response_code(400);
                // echo '<script>document.getElementById("error-message").innerHTML = "Error: Email already exists. Please use a different email address.";</script>';
            } else {
                http_response_code(500);
                echo '<script>document.getElementById("error-message").innerHTML = "Error inserting user: ' . $e->getMessage() . '";</script>';
            }
        }
    } else {
        http_response_code(500);
        echo '<script>document.getElementById("error-message").innerHTML = "Error: ' . $mail->ErrorInfo . '";</script>';
    }
}
?>

