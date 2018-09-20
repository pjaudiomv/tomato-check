<?php
include 'config.php';
require_once 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
date_default_timezone_set("America/New_York");

$tomatoStatusURL =  file_get_contents("https://tomato.na-bmlt.org/rest/v1/rootservers/?format=json");
$tomatoStatus = json_decode($tomatoStatusURL,true);
$message = "";
$failedImports = "";



foreach($tomatoStatus as $tomato) {
    $eightHoursAgo = strtotime("-8 hours");
    $lastImport = strtotime($tomato['last_successful_import']);
    
    if ($eightHoursAgo > $lastImport) {
        $failedImports .= "<strong>Root Server: </strong>" .$tomato['root_server_url']. "<br />";
        $failedImports .= "<strong>Last Import: </strong>" . date("M d, o g:iA", $lastImport). "<br /><br />";
    } 
    else {
        $allgood = 'good';
    }
}


if ($allgood != 'good') {
    $message .= "<strong>The following root servers have missed a tomato import</strong><br /><br />";
    $message .= $failedImports;
    
    //Send Email
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $smtp_host;
    $mail->SMTPAuth = true;
    $mail->Username = $smtp_username;
    $mail->Password = $smtp_password;
    $mail->SMTPSecure = $smtp_secure;
    if ($smtp_alt_port) {
        $mail->Port = $smtp_alt_port;
    } elseif ($smtp_secure == 'tls') {
        $mail->Port = 587;
    } elseif ($smtp_secure == 'ssl') {
        $mail->Port = 465;
    }
    $mail->setFrom($smtp_from_address, $smtp_from_name);
    $mail->isHTML(true);
    $mail->addAddress($smtp_to_address, $smtp_to_name);
    $mail->Body = $message;
    $mail->Subject = $smtp_email_subject;
    if (!$mail->send()) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        echo 'Mailer Error';
    } else {
       echo 'Message sent!';
    }
}
