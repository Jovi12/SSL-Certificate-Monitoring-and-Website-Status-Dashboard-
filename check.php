<?php
// Require the PHPMailer library
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

// Function to check if the script is running from the task scheduler
// function isScheduledTask() {
//     return php_sapi_name() === 'cli';
// }

// Check if the script is running as a scheduled task
// if (isScheduledTask()) {
    // Database connection setup
    $conn = new mysqli('localhost', 'root', '', 'website_monitoring');

    // Check for a successful connection
    if ($conn->connect_error) {
        die('Database connection failed: ' . $conn->connect_error);
    }

    // Fetch websites with SSL certificates expiring in 5 days or less
    $sslExpirySql = "SELECT user_id, url, email FROM monitored_websites WHERE DATEDIFF(ssl_expiry_date, CURDATE()) <= 28";
    $sslExpiryResult = $conn->query($sslExpirySql);

    if ($sslExpiryResult === false) {
        die('Error executing SSL expiry query: ' . $conn->error);
    }

    if ($sslExpiryResult->num_rows > 0) {
        while ($row = $sslExpiryResult->fetch_assoc()) {
            $to = $row['email'];
            $subject = 'SSL Certificate Expiry Alert';
            $message = 'The SSL certificate for website: ' . $row['url'] . ' will expire in 5 days or less.';
            
            // Use PHPMailer to send the email
            $mail = new PHPMailer(true);
            try {
                // Set to 2 for debugging
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Your SMTP host (update as needed)
                $mail->SMTPAuth = true;
                $mail->Username = 'jovidsilva6@gmail.com'; // SMTP username
                $mail->Password = 'riux vygx aqng okrc'; // Your SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Use 'tls' or 'ssl' as needed
                $mail->Port = 465; // Port number
                
                $mail->setFrom('jovidsilva6@gmail.com', 'Jovi'); // Update sender details
                $mail->addAddress($to);
                
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $message;
                
                $mail->send();
                echo 'SSL Certificate Expiry Email sent successfully to ' . $to . '<br>';
            } catch (Exception $e) {
                echo 'SSL Certificate Expiry Email could not be sent. Mailer Error: ' . $mail->ErrorInfo . '<br>';
            }
        }
    }

    // Fetch websites to monitor for downtime
    $downtimeSql = "SELECT user_id, url, email FROM monitored_websites";
    $downtimeResult = $conn->query($downtimeSql);

    if ($downtimeResult === false) {
        die('Error executing downtime query: ' . $conn->error);
    }

    if ($downtimeResult->num_rows > 0) {
        while ($row = $downtimeResult->fetch_assoc()) {
            $url = $row['url'];
            $userEmail = $row['email'];

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

            // Check if the website is down
            if (!isWebsiteActive($url)) {
                $subject = 'Website Down Notification';
                $message = 'Website: ' . $url . ' is currently down.';
                
                // Use PHPMailer to send the email
                $mail = new PHPMailer(true);
                try {
                    // Set to 2 for debugging
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com'; // Your SMTP host (update as needed)
                    $mail->SMTPAuth = true;
                    $mail->Username = 'jovidsilva6@gmail.com'; // SMTP username
                    $mail->Password = 'riux vygx aqng okrc';  // Your SMTP password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;// Use 'tls' or 'ssl' as needed
                    $mail->Port = 465;// Port number
                    
                    $mail->setFrom('jovidsilva6@gmail.com', 'Jovi'); // Update sender details
                    $mail->addAddress($userEmail);
                    
                    $mail->isHTML(true);
                    $mail->Subject = $subject;
                    $mail->Body = $message;
                    
                    $mail->send();
                    echo 'Website Down Notification Email sent successfully to ' . $userEmail . '<br>';
                } catch (Exception $e) {
                    echo 'Website Down Notification Email could not be sent. Mailer Error: ' . $mail->ErrorInfo . '<br>';
                }
            }
        }
    }

    // Close the database connection
    $conn->close();
// } else {
//     echo 'This script should be executed as a scheduled task.';
// }
?>
