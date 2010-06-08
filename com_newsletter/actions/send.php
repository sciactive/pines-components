<?php
/**
 * Send a newsletter.
 *
 * @package Pines
 * @subpackage com_newsletter
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_newsletter/send') )
	punt_user('You don\'t have necessary permission.', pines_url('com_newsletter', 'list'));

$send = new module('com_newsletter', 'send', 'content');

/**
 * Clean a mail header of new line characters.
 *
 * New line characters can be used to inject arbitrary headers into an email, so
 * they should always be removed before the header is inserted into the email.
 *
 * @param string $header The header to be cleaned.
 * @return string The cleaned header.
 */
function com_newsletter__clean_header($header) {
	return str_replace("\n", ' ', $header);
}

if ( !isset($_REQUEST['mail_id']) ) {
	pines_error("No mail specified!");
	return false;
}

$mail = $pines->entity_manager->get_entity(array(), array('&', 'guid' => (int) $_REQUEST['mail_id'], 'tag' => array('com_newsletter', 'mail')));
if ( !isset($mail) ) {
	pines_error('Invalid mail!');
	return false;
}

@ini_set('max_execution_time',0);
@set_time_limit(0);

$message = $mail->message;
if ( $_REQUEST['include_permalink'] == 'on' ) {
	$mail_id = $_REQUEST['mail_id'];
	$message = "<div style=\"text-align: center; font-size: smaller;\">Having trouble reading this message? <a href=\"".htmlentities(pines_url('com_newsletter', 'webview', array('mail_id' => $mail_id), true))."\" target=\"_blank\">Click here</a> to view this message in your browser.</div>" . $message;
}

$location = group::factory((int) $_REQUEST['location']);
if (isset($location->guid)) {
	$addresses = array();
	$group_users = $pines->entity_manager->get_entities(array('class' => user), array('&', 'tag' => array('com_user', 'user')));
	foreach ($group_users as $key => &$cur_user) {
		if (!($cur_user->in_group($location) || $cur_user->is_descendent($location) || empty($cur_user->email))) {
			unset($group_users[$key]);
		} else {
			$addresses[$cur_user->guid] = $cur_user->email;
		}
	}
}
$bcc = implode(', ', $addresses);

$mailer = &new com_mailer_mail(com_newsletter__clean_header($_REQUEST['from']), 'undisclosed-recipients <noone@example.com>', com_newsletter__clean_header($_REQUEST['subject']), $message);
$mailer->addHeader('Reply-To', com_newsletter__clean_header($_REQUEST['replyto']));
$mailer->addHeader('Bcc', $bcc);

$attachments = $mail->attachments;
foreach ( $attachments as $cur_attachment ) {
	$mailer->addAttachment($pines->uploader->real($cur_attachment));
}

if ( $mailer->send() ) {
	pines_log("Successfully sent mail, $mail->name.", 'notice');
	$send->success = true;
} else {
	pines_log("Failed to send mail, $mail->name.", 'notice');
	$send->success = false;
}

$send->name = $mail->name;
$send->subject = com_newsletter__clean_header($_REQUEST['subject']);
$send->message = $message;
?>