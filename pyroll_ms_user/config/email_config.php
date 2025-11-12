<?php
/**
 * Email Configuration File
 * Save this as: config/email_config.php
 */

// Email Configuration
// === SMTP Configuration ===
define('SMTP_ENABLED', true);
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'muyambijulias@gmail.com'); // your Gmail address
define('SMTP_PASSWORD', 'mrdelhmwvvlkxthl'); // your Gmail App Password
define('SMTP_ENCRYPTION', 'tls');

// Sender Information
define('FROM_EMAIL', 'noreply@payrollpro.com');
define('FROM_NAME', 'PayrollPro');
define('REPLY_TO_EMAIL', 'support@payrollpro.com');
define('REPLY_TO_NAME', 'PayrollPro Support');

/**
 * IMPORTANT: For Gmail users
 * 
 * 1. Enable 2-Step Verification in your Google Account
 * 2. Generate an App Password:
 *    - Go to: https://myaccount.google.com/apppasswords
 *    - Select "Mail" and "Other (Custom name)"
 *    - Enter "PayrollPro" as the name
 *    - Copy the 16-character password
 *    - Use it as SMTP_PASSWORD above
 * 
 * Alternative SMTP Services:
 * - Gmail: smtp.gmail.com (Port 587/465)
 * - Outlook: smtp-mail.outlook.com (Port 587)
 * - Yahoo: smtp.mail.yahoo.com (Port 587)
 * - SendGrid: smtp.sendgrid.net (Port 587)
 * - Mailgun: smtp.mailgun.org (Port 587)
 */
?>