<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'mail_config.php';

function sendMail($email, $subject, $message)
{
    // Creating a new PHPMailer object.
    $mail = new PHPMailer(true);

    //Using the SMTP protocol to send the email
    $mail->isSMTP();

    //Setting the SMTPAuth property to true, so Gmail login detals can be used to send the email
    $mail->SMTPAuth = true;

    //Set the Host porperty to the MAILHOST value defined in the mail_config.php file
    $mail->Host = MAILHOST;

    $mail->Username = USERNAME;

    $mail->Password = PASSWORD;

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

    $mail->Port = 587;

    $mail->setFrom(SEND_FROM, SEND_FROM_NAME);

    $mail->addAddress($email);

    $mail->addReplyTo(REPLY_TO, REPLY_TO_NAME);

    $mail->isHTML(true);

    $mail->Subject = $subject;

    $mail->Body = $message;

    $mail->AltBody = $message;

    if (!$mail->send()) {
        return "Email not Sent. Please try again";
    } else {
        return "success";
    }
}
