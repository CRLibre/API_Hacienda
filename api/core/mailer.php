<?php

/**
 * Send emails
 * @params info an array with:
 * to=mail@example.com
 * replyTo=replyTo@example.com (I will use the default mail in config if not)
 * from=from@example.com (I will use the default mail in config if not)
 * subject=the subject
 * message=the message
 */

//## to use the funtion mail() in line 32 you need install sendmail in your unix server
//Steps for Ubuntu linux
//1) install sendmail
//	sudo apt-get install sendmail
//2) config sendmail
//	sudo sendmailconfig
//3) Edit hosts file
//	sudo nano /etc/hosts
//		127.0.0.1 localhost su_dominio.com
//4) Restart apache
//	sudo service apache2 restart


function mailer_sendEmail($info){

	$headers = sprintf("From: %s \r\n" .
		"Reply-To: %s \r\n" .
		'X-Mailer: PHP/ %s',
		conf_get('defaultMail', 'core', 'info@crlibre.org'),
		$info['replyTo'] != '' ? $info['replyTo'] : conf_get('defaultMail', 'core', 'info@crlibre.org'),
		phpversion());

	return mail($info['to'], $info['subject'], $info['message'], $headers);

}

