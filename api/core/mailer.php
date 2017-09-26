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
function mailer_sendEmail($info){

	$headers = sprintf("From: %s \r\n" .
		"Reply-To: %s \r\n" .
		'X-Mailer: PHP/ %s',
		conf_get('defaultMail', 'core', 'info@nneus.com'),
		$info['replyTo'] != '' ? $info['replyTo'] : conf_get('defaultMail', 'core', 'info@nneus.com'),
		phpversion());

	return mail($info['to'], $info['subject'], $info['message'], $headers);

}

