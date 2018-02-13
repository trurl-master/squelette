<?php

namespace Squelette;

class Email
{

	public static function send($to, $subject, $msg)
	{
		//Create a new PHPMailer instance
		$mail = new \PHPMailer;

		//Tell PHPMailer to use SMTP
		$mail->isSMTP();

		//Enable SMTP debugging
		// 0 = off (for production use)
		// 1 = client messages
		// 2 = client and server messages
		$mail->SMTPDebug = 0;

		//Ask for HTML-friendly debug output
		$mail->Debugoutput = 'html';

		//Set the hostname of the mail server
		$mail->Host = '';
		// use
		// $mail->Host = gethostbyname('smtp.gmail.com');
		// if your network does not support SMTP over IPv6
		//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
		$mail->Port = ;

		//Set the encryption system to use - ssl (deprecated) or tls
		$mail->SMTPSecure = '';

		//Whether to use SMTP authentication
		$mail->SMTPAuth = true;

		//Username to use for SMTP authentication - use full email address for gmail
		$mail->Username = "";

		//Password to use for SMTP authentication
		$mail->Password = "";

		//Set who the message is to be sent from
		$mail->setFrom('', '');

		//Set an alternative reply-to address
		// $mail->addReplyTo('replyto@example.com', 'First Last');

		//Set who the message is to be sent to
		$mail->addAddress($to);

		//
		$mail->isHTML(true);
		$mail->CharSet = 'UTF-8';

		// Set the subject line
		$mail->Subject = $subject;


		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body
		// $mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));

		//
		$mail->Body    = $msg;

		// Replace the plain text body with one created manually

		$mail->AltBody = strip_tags($msg);

		//Attach an image file
		// $mail->addAttachment('images/phpmailer_mini.png');

		//send the message, check for errors
		if (!$mail->send()) {
			// echo "Mailer Error: " . $mail->ErrorInfo;
			return false;

		} else {

			// echo "Message sent!";
			//Section 2: IMAP
			//Uncomment these to save your message in the 'Sent Mail' folder.
			#if (self::save($mail)) {
			#    echo "Message saved!";
			#}

			return true;
		}
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
