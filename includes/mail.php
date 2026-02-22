<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

function sendOTP($toEmail, $userName, $otp) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'laxmi011000110@gmail.com';
        $mail->Password   = 'xyfspapjynpkdbnk'; // Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        $mail->setFrom('laxmi011000110@gmail.com', 'CookEasy');
        $mail->addAddress($toEmail);

        $mail->isHTML(true);
        $mail->Subject = 'CookEasy OTP Verification';
        $mail->Body = "
            <div style='font-family:Arial'>
                <h3>Hello $userName</h3>
                <p>Your OTP is:</p>
                <h2>$otp</h2>
                <p>Valid for 10 minutes</p>
            </div>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        return false;
    }
}
?>
