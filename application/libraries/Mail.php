<?php 
// defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter Mail Library
 *
 * @package			CodeIgniter
 * @subpackage		Libraries
 * @category		Libraries
 * @link			https://github.com/PHPMailer/PHPMailer
 *
 */
// 
require_once(dirname(__FILE__) . '/../../vendor/autoload.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mail extends CI_Model
{
    /* public */
    public $inst;

    /* private */
    private $mail_host, $mail_user, $mail_pasw, $mail_port;

    public function __construct()
	{
        $this->inst = new PHPMailer(true);

        $this->inst->isSMTP();
        $this->inst->SMTPAuth   = true; 
        $this->inst->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

        // settings
        //SMTP::DEBUG_OFF = off (for production use)
        //SMTP::DEBUG_CLIENT = client messages
        //SMTP::DEBUG_SERVER = client and server messages
        $this->inst->SMTPDebug = SMTP::DEBUG_OFF;

        // pull from config
        $this->inst->Host       = $this->config->item('smtp_host');
        $this->inst->Username   = $this->config->item('smtp_user');
        $this->inst->Password   = $this->config->item('smtp_pasw');
        $this->inst->Port       = $this->config->item('smtp_port');

        // from
        $this->inst->setFrom($this->config->item('mail_from_email'), $this->config->item('mail_from_user'));

        // lets keep things simple and just send text
        $this->inst->isHTML(false);
    }

    public function attach_file(string $file)
    {
        if (file_exists($file))
        {
            $this->inst->addAttachment($file);
        }
    }

    public function send(string $recp, string $subject, string $body, bool $bcc = true)
    {
        $this->inst->addAddress($recp);
        $this->inst->addReplyTo($this->config->item('mail_reply_to'));
        
        if ($bcc) {
            $this->inst->addBCC($this->config->item('mail_bcc'));
        }

        $this->inst->Subject = $subject;
        $this->inst->Body = $body;

        try {
            $this->inst->send();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}