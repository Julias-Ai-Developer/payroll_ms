<?php
/**
 * PHPMailer Helper Class
 * Save this as: includes/EmailHelper.php
 * 
 * First, install PHPMailer via Composer:
 * composer require phpmailer/phpmailer
 * 
 * Or download manually from: https://github.com/PHPMailer/PHPMailer
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php'; // If using Composer
// OR if installed manually:
// require_once __DIR__ . '/../phpmailer/src/Exception.php';
// require_once __DIR__ . '/../phpmailer/src/PHPMailer.php';
// require_once __DIR__ . '/../phpmailer/src/SMTP.php';

require_once __DIR__ . '/../config/email_config.php';

class EmailHelper {
    
    /**
     * Send email using PHPMailer
     * 
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $htmlBody HTML body content
     * @param string $recipientName Recipient name (optional)
     * @return bool True on success, false on failure
     */
    public static function sendEmail($to, $subject, $htmlBody, $recipientName = '') {
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USERNAME;
            $mail->Password   = SMTP_PASSWORD;
            $mail->SMTPSecure = SMTP_ENCRYPTION;
            $mail->Port       = SMTP_PORT;

            
            
            // Disable SSL verification for localhost/development (remove in production)
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            // Recipients
            $mail->setFrom(FROM_EMAIL, FROM_NAME);
            $mail->addAddress($to, $recipientName);
            $mail->addReplyTo(REPLY_TO_EMAIL, REPLY_TO_NAME);
            
            // Content
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = strip_tags($htmlBody); // Plain text alternative
            
            $mail->send();
            return true;
            
        } catch (Exception $e) {
            error_log("Email sending failed: {$mail->ErrorInfo}");
            return false;
        }
    }
    
    /**
     * Send password reset email
     */
    public static function sendPasswordResetEmail($to, $fullName, $resetLink) {
        $subject = "Password Reset Request - PayrollPro";
        
        $htmlBody = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(to right, #0284c7, #0369a1); padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .header h1 { color: white; margin: 0; font-size: 28px; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; background: #0284c7; color: white !important; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 20px 0; font-weight: bold; }
                .button:hover { background: #0369a1; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; padding-top: 20px; border-top: 1px solid #ddd; }
                .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 15px 0; border-radius: 5px; }
                .link-box { background: white; padding: 15px; border-radius: 5px; word-break: break-all; border: 1px solid #ddd; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔐 Password Reset Request</h1>
                </div>
                <div class='content'>
                    <p>Hello <strong>" . htmlspecialchars($fullName) . "</strong>,</p>
                    
                    <p>We received a request to reset your password for your PayrollPro account.</p>
                    
                    <p>Click the button below to create a new password:</p>
                    
                    <p style='text-align: center;'>
                        <a href='" . htmlspecialchars($resetLink) . "' class='button'>Reset My Password</a>
                    </p>
                    
                    <p>Or copy and paste this link into your browser:</p>
                    <div class='link-box'>" . htmlspecialchars($resetLink) . "</div>
                    
                    <div class='warning'>
                        <strong>⚠️ Important Security Information:</strong>
                        <ul style='margin: 10px 0; padding-left: 20px;'>
                            <li>This link will expire in <strong>1 hour</strong> for your security</li>
                            <li>If you didn't request this reset, please ignore this email</li>
                            <li>Your password won't change until you create a new one using the link above</li>
                            <li>Never share this link with anyone</li>
                        </ul>
                    </div>
                    
                    <p style='margin-top: 30px;'>If you're having trouble clicking the button, copy and paste the link into your web browser.</p>
                    
                    <p style='margin-top: 20px;'>If you have any questions or didn't request this reset, please contact our support team immediately.</p>
                    
                    <p style='margin-top: 30px;'>Best regards,<br><strong>The PayrollPro Team</strong></p>
                </div>
                <div class='footer'>
                    <p><strong>PayrollPro - Payroll Management System</strong></p>
                    <p>&copy; " . date('Y') . " All Rights Reserved || Group E</p>
                    <p style='margin-top: 10px;'>This is an automated message, please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return self::sendEmail($to, $subject, $htmlBody, $fullName);
    }
    
    /**
     * Send password reset confirmation email
     */
    public static function sendPasswordResetConfirmation($to, $fullName) {
        $subject = "Password Successfully Reset - PayrollPro";
        
        $htmlBody = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(to right, #059669, #047857); padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .header h1 { color: white; margin: 0; font-size: 28px; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .success { background: #d1fae5; border-left: 4px solid #10b981; padding: 15px; margin: 15px 0; border-radius: 5px; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; padding-top: 20px; border-top: 1px solid #ddd; }
                .info-box { background: white; padding: 15px; border-radius: 5px; border: 1px solid #ddd; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>✅ Password Reset Successful</h1>
                </div>
                <div class='content'>
                    <p>Hello <strong>" . htmlspecialchars($fullName) . "</strong>,</p>
                    
                    <div class='success'>
                        <strong>Your password has been successfully reset!</strong>
                    </div>
                    
                    <p>You can now login to your PayrollPro account with your new password.</p>
                    
                    <div class='info-box'>
                        <p style='margin: 0;'><strong>Reset Details:</strong></p>
                        <ul style='margin: 10px 0; padding-left: 20px;'>
                            <li>Date: " . date('F j, Y') . "</li>
                            <li>Time: " . date('g:i A T') . "</li>
                            <li>IP Address: " . $_SERVER['REMOTE_ADDR'] . "</li>
                        </ul>
                    </div>
                    
                    <p style='margin-top: 20px;'><strong>⚠️ Security Alert:</strong></p>
                    <p>If you didn't make this change or believe an unauthorized person has accessed your account, please contact our support team immediately at <a href='mailto:support@payrollpro.com'>support@payrollpro.com</a></p>
                    
                    <p style='margin-top: 30px;'>Thank you for using PayrollPro!</p>
                    
                    <p style='margin-top: 20px;'>Best regards,<br><strong>The PayrollPro Team</strong></p>
                </div>
                <div class='footer'>
                    <p><strong>PayrollPro - Payroll Management System</strong></p>
                    <p>&copy; " . date('Y') . " All Rights Reserved || Group E</p>
                    <p style='margin-top: 10px;'>This is an automated message, please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return self::sendEmail($to, $subject, $htmlBody, $fullName);
    }
}
?>