<?php
/**
 * Send a message from a contact form.
 *
 * @package Components\contact
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

$name = str_replace("\n", '', $_REQUEST['author_name']);
$phone = $_REQUEST['author_phone'];
$email = $_REQUEST['author_email'];
$title = str_replace("\n", '', $_REQUEST['subject']);
$message = $_REQUEST['message'];
$send_to = $pines->config->com_contact->contact_email;
if (empty($name) || empty($phone) || empty($email) || empty($title) || empty($message)) {
	pines_notice('Please complete all fields of the contact form.');
	return;
}

$subject = "$title - $name";
$msg = $message;
$msg .= "\r\n\r\n";
$msg .= "Customer Name:	$name\r\n";
$msg .= "Phone:			$phone\r\n";
$msg .= "E-mail:		$email";
$mail = com_mailer_mail::factory($pines->config->com_mailer->from_address, $send_to, $subject, $msg);
if ($mail->send()) {
	pines_notice('Your message has been sent.');
	pines_redirect(pines_url('com_contact', 'thanks'));
} else {
	pines_error('Your message was not sent successfuly.');
	return;
}
?>