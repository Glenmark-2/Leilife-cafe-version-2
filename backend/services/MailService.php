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

    public function sendVerification($toEmail, $token) {
        $mail = $this->getMailer();
        if (!$mail) return false;

        try {
            $mail->addAddress($toEmail);     

            // Content
            $mail->isHTML(true);                                  
            $mail->Subject = 'Verify your Leilife Account';
            
            $verifyLink = "http://localhost/Leilife_2nd/public/index.php?page=verify&token=" . $token;
            
            $body = "
                <h1>Welcome to Leilife!</h1>
                <p>Thank you for signing up. Please click the link below to verify your email address:</p>
                <p><a href='$verifyLink' style='padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>Verify Email</a></p>
                <p>Or copy this link: $verifyLink</p>
                <p>This link will expire in 24 hours.</p>
            ";

            $mail->Body    = $body;
            $mail->AltBody = "Please verify your email by visiting: $verifyLink";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
}
