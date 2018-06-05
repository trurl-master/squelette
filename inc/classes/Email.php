<?php

namespace Squelette;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Email
{

	private static function setupSmtp($mail)
	{

		$smtp = \App::cfg('smtp');

		// Tell PHPMailer to use SMTP
		$mail->isSMTP();

		// Enable SMTP debugging
		// 0 = off (for production use)
		// 1 = client messages
		// 2 = client and server messages
		$mail->SMTPDebug = 0;

		// Ask for HTML-friendly debug output
		$mail->Debugoutput = 'html';

		// Set the hostname of the mail server
		$mail->Host = $smtp['host'];

		// use
		// $mail->Host = gethostbyname('smtp.gmail.com');
		// if your network does not support SMTP over IPv6
		// Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
		$mail->Port = $smtp['port'];

		// Set the encryption system to use - ssl (deprecated) or tls
		$mail->SMTPSecure = $smtp['encryption'];

		if (isset($smtp['auth'])) {
			//Whether to use SMTP authentication
			$mail->SMTPAuth = true;

			//Username to use for SMTP authentication - use full email address for gmail
			$mail->Username = $smtp['auth']['username'];

			//Password to use for SMTP authentication
			$mail->Password = $smtp['auth']['password'];
		}


	}

	public static function sendNoreplyPlain($to, $subject, $msg)
	{

		$mail = new PHPMailer;

		//
		if (\App::cfg('smtp', false) !== false) {
			self::setupSmtp($mail);
		}

		//
		$mail->isHTML(false);
		$mail->CharSet = 'UTF-8';

		// Set who the message is to be sent from
		$noreply = \App::cfg('emails')['noreply'];

		$mail->setFrom($noreply['address'], $noreply['title']);

		// Set who the message is to be sent to
		$mail->addAddress($to);

		// Set the subject line
		$mail->Subject = $subject;
		$mail->Body = $msg;

		//
		return $mail->send();
	}

	public static function send($from, $to, $subject, $body, $options = [])
	{

		$mail = new PHPMailer;
		$is_html = isset($options['is_html']) ? $options['is_html'] : true;

		if (\App::cfg('smtp', false) !== false) {
			self::setupSmtp($mail);
		}

		//
		$mail->isHTML($is_html);
		$mail->CharSet = 'UTF-8';

		// Set who the message is to be sent from
		$from = \App::cfg('emails')[$from];

		$mail->setFrom($from['address'], $from['title']);

		// Set who the message is to be sent to
		$mail->addAddress($to);

		// Set the subject line
		$mail->Subject = $subject;
		$mail->Body = $body;

		if ($is_html) {
			$mail->AltBody = strip_tags($body);
		}

		//
		return $mail->send();
	}

	public static function sendTemplate($from, $to, $subject, $templateName, $templateParams = [])
	{

		ob_start();

		\App::renderTemplate('emails/' . $templateName . '/index', $templateParams);

		$body = ob_get_clean();

		\Squelette\Email::send(
            $from,
            $to,
            $subject,
            $body,
            [
                'is_html' => true
            ]
        );
	}

	protected static function save($mail) {
	    //You can change 'Sent Mail' to any other folder or tag
	    $path = "{imap.gmail.com:993/imap/ssl}[Gmail]/Sent Mail";
	    //Tell your server to open an IMAP connection using the same username and password as you used for SMTP
	    $imapStream = imap_open($path, $mail->Username, $mail->Password);
	    $result = imap_append($imapStream, $path, $mail->getSentMIMEMessage());
	    imap_close($imapStream);
	    return $result;
	}

}
