<?php

class EmailHelper {
    
    /**
     * Send email verification message
     */
    public static function sendVerificationEmail($email, $firstName, $verificationToken, $verificationLink) {
        $subject = "Verify Your Email - Library Management System";
        
        $message = "
        <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #845Ec2 0%, #4B4453 100%); color: white; padding: 30px; border-radius: 5px; text-align: center; }
                    .content { padding: 20px; background: #f5f4f7; border-radius: 5px; }
                    .button { display: inline-block; background: linear-gradient(135deg, #C34A36 0%, #FF8066 100%); color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                    .footer { text-align: center; color: #999; font-size: 12px; margin-top: 20px; }
                </style>
            </head>
            <body>
                <div class=\"container\">
                    <div class=\"header\">
                        <h1>Verify Your Email</h1>
                    </div>
                    <div class=\"content\">
                        <p>Hi {$firstName},</p>
                        <p>Welcome to the Library Management System! Please verify your email address to complete your registration.</p>
                        <p>Click the button below to verify your email:</p>
                        <a href=\"{$verificationLink}\" class=\"button\">Verify Email</a>
                        <p>Or copy this link in your browser:</p>
                        <p style=\"word-break: break-all; color: #845Ec2;\">{$verificationLink}</p>
                        <p><strong>This link will expire in 24 hours.</strong></p>
                        <p>If you didn't create this account, you can ignore this email.</p>
                    </div>
                    <div class=\"footer\">
                        <p>Library Management System | Do not reply to this email</p>
                    </div>
                </div>
            </body>
        </html>
        ";
        
        return self::sendEmail($email, $subject, $message);
    }
    
    /**
     * Send welcome email after verification
     */
    public static function sendWelcomeEmail($email, $firstName) {
        $subject = "Welcome to Library Management System!";
        
        $message = "
        <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #845Ec2 0%, #4B4453 100%); color: white; padding: 30px; border-radius: 5px; text-align: center; }
                    .content { padding: 20px; background: #f5f4f7; border-radius: 5px; }
                    .features { margin: 20px 0; }
                    .feature { padding: 10px; margin: 10px 0; background: white; border-left: 4px solid #845Ec2; }
                    .footer { text-align: center; color: #999; font-size: 12px; margin-top: 20px; }
                </style>
            </head>
            <body>
                <div class=\"container\">
                    <div class=\"header\">
                        <h1>Welcome, {$firstName}!</h1>
                    </div>
                    <div class=\"content\">
                        <p>Your email has been verified successfully. Your account is now active!</p>
                        <h3>What you can do now:</h3>
                        <div class=\"features\">
                            <div class=\"feature\">📚 Browse our library catalog</div>
                            <div class=\"feature\">📋 Manage your borrowing records</div>
                            <div class=\"feature\">⏰ View due dates and renewals</div>
                            <div class=\"feature\">👤 Update your profile information</div>
                        </div>
                        <p>You can now login with your email and password to access the system.</p>
                    </div>
                    <div class=\"footer\">
                        <p>Library Management System | Do not reply to this email</p>
                    </div>
                </div>
            </body>
        </html>
        ";
        
        return self::sendEmail($email, $subject, $message);
    }
    
    /**
     * Generic email sending function
     */
    private static function sendEmail($to, $subject, $message) {
        // Email headers
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "From: noreply@lms-system.com" . "\r\n";
        $headers .= "Reply-To: support@lms-system.com" . "\r\n";
        
        // Send email
        $mailSent = mail($to, $subject, $message, $headers);
        
        return $mailSent;
    }
    
    /**
     * Generate a secure verification token
     */
    public static function generateToken() {
        return bin2hex(random_bytes(32));
    }
}
?>
