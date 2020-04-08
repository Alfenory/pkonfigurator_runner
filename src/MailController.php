<?php

namespace PKonfigurator\Runner;

//use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class MailController {
    public static function sendmail(
        $mailconfig, 
        $mail_from, $mail_from_name, 
        $mail_to, $mail_to_name, 
        $mail_bcc,
        $mail_cc,
        $subject, $text) {
        try {

            //Achtung hier muss geklärt werden, dass eventuell auch eine Email versandt wird, wenn kein Passwort hinterlegt ist.

            /*
            $transport = (new Swift_SmtpTransport($mailconfig->host, 25))->setUsername($mailconfig->username)->setPassword($mailconfig->password);
            

            $mail = new PHPMailer(true);
            $mail->SMTPDebug = 0;                                       //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
            $mail->isSMTP();                                            // Send using SMTP
            $mail->Host       = $mailconfig->host;                      // Set the SMTP server to send through
            
            //echo "try to send to ".$mailconfig->host."<br>\n";
            
            if ($mailconfig->username !== "") {
                $mail->SMTPAuth   = true;                               // Enable SMTP authentication
                $mail->Username   = $mailconfig->username;              // SMTP username
                $mail->Password   = $mailconfig->password;              // SMTP password
                //echo "SMTPAUTH<br>\n";
            } else {
                $mail->SMTPAuth   = false;                                  
                //echo "no SMTPAUTH<br>\n";
            }
            
            $mail->SMTPSecure = $mailconfig->ssl*1 === 1 ? PHPMailer::ENCRYPTION_STARTTLS : '';         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
            $mail->Port       = $mailconfig->port;                      // TCP port to connect to
        
            $mail->SMTPOptions = array (
                'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true)
            );

            if ($mailconfig->ssl*1 === 0) {
                $mail->SMTPAutoTLS = false;
                //echo "No SSL / No TLS<br>\n";
            } else {
                //echo "SSL<br>\n";
            }

            //Recipients
            $mail->setFrom($mail_from, \utf8_decode($mail_from_name));  
            $mail->addAddress($mail_to, \utf8_decode($mail_to_name));   // Name is optional
            
            $mail_bcc_list = explode(";", $mail_bcc);
            for ($i=0; $i<count($mail_bcc_list); $i++) {
                if ($mail_bcc_list[$i] !== "") {
                    $mail->addBCC($mail_bcc_list[$i]);
                }
            }

            $mail_cc_list = explode(";", $mail_cc);
            for ($i=0; $i<count($mail_cc_list); $i++) {
                if ($mail_cc_list[$i] !== "") {
                    $mail->addCC($mail_cc_list[$i]);
                }
            }
            
            // Attachments
            //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
            //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
            
            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = \utf8_decode($subject);
            $mail->Body    = \utf8_decode($text);

            $mail->send();
            */
            return true;
        } catch (\Exception $e) {
            echo("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }

    }
}