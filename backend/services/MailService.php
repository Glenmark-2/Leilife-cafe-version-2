<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/../helpers/EnvLoader.php';

class MailService {
    
    public function __construct() {
        // Ensure Env is loaded
        EnvLoader::load(__DIR__ . '/../../.env');
    }

    private function getMailer() {
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER;  // Enable verbose debug output
            $mail->isSMTP();                                            
            $mail->Host       = getenv('SMTP_HOST');                     
            $mail->SMTPAuth   = true;                                   
            $mail->Username   = getenv('SMTP_USER');                     
            $mail->Password   = getenv('SMTP_PASS');                               
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            
            $mail->Port       = getenv('SMTP_PORT');                                    

            // Recipients
            $mail->setFrom(getenv('SMTP_FROM_EMAIL'), getenv('SMTP_FROM_NAME'));
            
            return $mail;
        } catch (Exception $e) {
            error_log("Mailer Error: " . $e->getMessage());
            return null;
        }
    }

    public function sendVerification($toEmail, $token, $otpCode = null, $source = 'web') {
        $mail = $this->getMailer();
        if (!$mail) return false;

        try {
            $mail->addAddress($toEmail);     

            // Content
            $mail->isHTML(true);                                  
            $mail->Subject = 'Verify your Leilife Account';
            
            require_once __DIR__ . '/../helpers/UrlHelper.php';
            $verifyLink = UrlHelper::getFullUrl("public/index.php?page=verify&token=" . $token);
            
            if ($source === 'mobile') {
                $body = "
                    <h1>Welcome to Leilife!</h1>
                    <p>Thank you for signing up. Please enter this code in the app to verify your account:</p>
                    <div style='margin: 20px 0; padding: 20px; background-color: #f9f9f9; text-align: center; border: 1px dashed #ccc;'>
                        <h2 style='margin: 10px 0; font-size: 32px; letter-spacing: 5px; color: #333;'>$otpCode</h2>
                    </div>
                    <p>This code will expire in 24 hours.</p>
                ";
                $altBody = "Your verification code is: $otpCode";
            } else {
                $body = "
                    <h1>Welcome to Leilife!</h1>
                    <p>Thank you for signing up. Please click the button below to verify your email address:</p>
                    <p><a href='$verifyLink' style='padding: 10px 20px; background-color: #d4a373; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>Verify Email</a></p>
                    <p style='font-size: 12px; color: #999; margin-top: 30px;'>
                        If the button doesn't work, copy this link: $verifyLink<br>
                        This link will expire in 24 hours.
                    </p>
                ";
                $altBody = "Please verify your email by visiting: $verifyLink";
            }

            $mail->Body    = $body;
            $mail->AltBody = $altBody;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
}
