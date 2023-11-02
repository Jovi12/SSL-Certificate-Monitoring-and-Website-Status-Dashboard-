<!-- <?php
// session_start();

// if (!isset($_SESSION['user_id'])) {
//     header('Location: login.php');
//     exit;
// }

// $conn = new mysqli('localhost', 'root', '', 'website_monitoring');
// $user_id = $_SESSION['user_id'];

// // Fetch monitored websites for the logged-in user
// $sql = "SELECT id, url, last_check_date, ssl_expiry_date FROM monitored_websites WHERE user_id = $user_id";
// $result = $conn->query($sql);

?> -->
<?php
session_start();
$email = "";

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Add code to establish a database connection and retrieve the user's email
    $conn = new mysqli('localhost', 'root', '', 'website_monitoring');

    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }

    $sql = "SELECT email FROM users WHERE id = $user_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $email = $row['email'];
    }

    $conn->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $url = $_POST['url'];
    $user_id = $_SESSION['user_id'];

 
    $conn = new mysqli('localhost', 'root', '', 'website_monitoring');

    // Check if the connection was successful
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }

    // Add the website to the database
    $sql = "INSERT INTO monitored_websites (user_id, email, url) VALUES ($user_id, '$email', '$url')";

    if ($conn->query($sql) === TRUE) {
        echo 'Website added successfully.';
    } else {
        echo 'Error adding website: ' . $conn->error;
    }

    $conn->close();
}

// Function to check website status using cURL
function isWebsiteActive($url) {
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return $httpCode == 200;
}


function getSSLCertificateExpiryDays($url) {
    $parsedUrl = parse_url($url);
    $host = $parsedUrl['host'];
    $context = stream_context_create(["ssl" => ["capture_peer_cert" => true]]);
    $client = stream_socket_client("ssl://" . $host . ":443", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);

    if ($client) {
        $params = stream_context_get_params($client);
        $certificate = openssl_x509_parse($params["options"]["ssl"]["peer_certificate"]);

        $expiryTimestamp = $certificate["validTo_time_t"];
        $currentTime = time();

        $expiryDate = date('Y-m-d H:i:s', $expiryTimestamp);
        $daysLeft = floor(($expiryTimestamp - $currentTime) / (60 * 60 * 24));

        return $daysLeft;
    }

    return -1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitored Websites</title>
    <!-- Add your CSS styles here -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            background: linear-gradient(to left,#00ff,green);
        }

        h1 {
            text-align: center;
            margin-top: 20px;
        }

        a {
            text-decoration: none;
            color: #007bff;
        }

        a:hover {
            text-decoration: underline;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        li {
            background-color: #ffffff;
            border: 1px solid #dddddd;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #dddddd;
            border-radius: 5px;
        }

        button[type="submit"] {
            background-color: #007bff;
            color: #ffffff;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
        }

        .dashboard {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border: 1px solid #dddddd;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .card {
            background-color: #ffffff;
            border: 1px solid #dddddd;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h1>Monitored Websites</h1>
    <a href="dashboard2.php">Back to Dashboard</a>
    <ul>
        <?php
        use PHPMailer\PHPMailer\PHPMailer;
        use PHPMailer\PHPMailer\SMTP;
        use PHPMailer\PHPMailer\Exception;

        // Load Composer's autoloader
        require 'vendor/autoload.php';

        // Fetch and display monitored websites
        $conn = new mysqli('localhost', 'root', '', 'website_monitoring');
        $user_id = $_SESSION['user_id'];
        $sql = "SELECT id, url, last_check_date, ssl_expiry_date FROM monitored_websites WHERE user_id = $user_id";
        $result = $conn->query($sql);
       if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $url = $row['url'];
            $lastCheckDate = $row['last_check_date'];
            $sslExpiryDate = $row['ssl_expiry_date'];

            // Check website status
            $status = isWebsiteActive($url);

            // Check SSL certificate expiration days
            $sslExpiryDays = getSSLCertificateExpiryDays($url);

            // Update last check date and SSL expiry date in the database
            $updateSql = "UPDATE monitored_websites SET last_check_date = NOW(), ssl_expiry_date = DATE_ADD(NOW(), INTERVAL $sslExpiryDays DAY) WHERE url = '$url'";
            $conn->query($updateSql);

            // echo '<li>';
            // echo 'URL: ' . $url . '<br>';
            // echo 'Status: ' . ($status ? 'Active' : 'Down') . '<br>';
            // echo 'SSL Certificate Expires in ' . $sslExpiryDays . ' days<br>';

            // echo '</li>';
            echo '<div class="card">';
            echo '<label>URL:</label> ' . $url . '<br>';
            echo '<label>Status:</label> <span class="card-status ' . ($status ? 'active">Active' : 'down">Down') . '</span><br>';
            echo '<label>SSL Certificate Expires in:</label> ' . $sslExpiryDays . ' days<br>';
            echo '</div>';
        }
        } else {
            echo 'No websites are currently monitored.';
        }
        ?>
    </ul>
</body>
</html>
