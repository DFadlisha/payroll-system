<?php
/**
 * ============================================
 * MAILER CLASS
 * ============================================
 * Handles sending email notifications.
 * Can configured to use PHP mail() or SMTP later.
 * ============================================
 */

class Mailer
{
    private $fromName = "MI-NES Payroll";
    private $fromEmail = "noreply@mi-nes-payroll.com";

    /**
     * Send an email
     */
    public function send($to, $subject, $body)
    {
        // Headers
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: {$this->fromName} <{$this->fromEmail}>" . "\r\n";
        $headers .= "Reply-To: {$this->fromEmail}" . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        // Wrap body in a nice HTML template
        $htmlBody = $this->getHtmlTemplate($subject, $body);

        // Try sending
        try {
            $result = mail($to, $subject, $htmlBody, $headers);

            // Log the attempt
            $status = $result ? "SUCCESS" : "FAILED";
            error_log("[MAILER] To: $to | Subject: $subject | Status: $status");

            return $result;
        } catch (Exception $e) {
            error_log("[MAILER ERROR] " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send Leave Status Update
     */
    public function sendLeaveStatusNotification($userEmail, $userName, $status, $leaveType, $startDate, $endDate)
    {
        $subject = "Leave Request " . ucfirst($status);
        $color = ($status == 'approved') ? '#10B981' : '#EF4444';

        $body = "
            <h2 style='color: #1F2937;'>Leave Request Update</h2>
            <p>Dear $userName,</p>
            <p>Your <strong>" . ucfirst($leaveType) . "</strong> leave request from <strong>$startDate</strong> to <strong>$endDate</strong> has been:</p>
            <div style='background-color: {$color}; color: white; padding: 10px 20px; border-radius: 5px; display: inline-block; font-weight: bold; text-transform: uppercase; margin: 10px 0;'>
                $status
            </div>
            <p>Please log in to the portal for more details.</p>
        ";

        return $this->send($userEmail, $subject, $body);
    }

    /**
     * Send Payslip Notification
     */
    public function sendPayslipNotification($userEmail, $userName, $month, $year, $netPay)
    {
        $monthName = date("F", mktime(0, 0, 0, $month, 10));
        $subject = "Payslip Available: $monthName $year";

        $body = "
            <h2 style='color: #1F2937;'>Payslip Ready</h2>
            <p>Dear $userName,</p>
            <p>Your payslip for <strong>$monthName $year</strong> has been generated and is ready for viewing.</p>
            <p style='font-size: 1.1em;'>Net Pay: <strong>RM " . number_format($netPay, 2) . "</strong></p>
            <p><a href='http://localhost:8000/staff/payslips.php' style='background-color: #4F46E5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>View Payslip</a></p>
        ";

        return $this->send($userEmail, $subject, $body);
    }

    /**
     * HTML Template
     */
    private function getHtmlTemplate($title, $content)
    {
        return "
        <html>
        <head>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e5e7eb; border-radius: 10px; }
                .header { text-align: center; border-bottom: 1px solid #e5e7eb; padding-bottom: 20px; margin-bottom: 20px; }
                .footer { text-align: center; font-size: 0.8em; color: #6b7280; margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1 style='color: #4F46E5; margin: 0;'>MI-NES Payroll</h1>
                </div>
                <div class='content'>
                    $content
                </div>
                <div class='footer'>
                    &copy; " . date('Y') . " MI-NES Solution. All rights reserved.
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
