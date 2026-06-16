<?php

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer(true);

try {

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;

    $mail->Username = 'akunhooh07@gmail.com';
    $mail->Password = 'migiypfdoavmwkzu';

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->CharSet = 'UTF-8';

    $mail->setFrom(
        'akunhooh07@gmail.com',
        'StudyFlow'
    );

    $mail->addAddress(
        'akunhooh07@gmail.com'
    );

    $mail->Subject = 'Tes Email';

    $mail->Body = 'halo ini adalah tes email';

    $mail->SMTPDebug = 2;

    $mail->send();

    echo "Berhasil";

} catch(Exception $e) {

    echo $mail->ErrorInfo;
}