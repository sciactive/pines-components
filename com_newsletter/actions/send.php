<?php
/**
 * Send a newsletter.
 *
 * @package Dandelion
 * @subpackage com_newsletter
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('D_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_newsletter/send') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", $config->template->url('com_newsletter', 'list', null, false));
	return;
}

$com_newsletter_send = new module('com_newsletter', 'send', 'content');

function clean_header($header) {
	return str_replace("\n", ' ', $header);
}

if ( !isset($_REQUEST['mail_id']) ) {
	display_error("No mail specified!");
	return false;
}

$mail = new entity;
$mail = $config->entity_manager->get_entity($_REQUEST['mail_id']);
if ( !$mail->has_tag('com_newsletter', 'mail') ) {
	display_error('Invalid mail!');
	return false;
}

@ini_set('max_execution_time',0);
@set_time_limit(0);

$message = $mail->message;
if ( $_REQUEST['include_permalink'] == 'on' ) {
	$mail_id = $_REQUEST['mail_id'];
	$message = "<div style=\"text-align: center; font-size: smaller;\">Having trouble reading this message? <a href=\"".$config->template->url('com_newsletter', 'webview', array('mail_id' => $mail_id), true, true)."\" target=\"_blank\">Click here</a> to view this message in your browser.</div>" . $message;
}

$bcc = '';
foreach ( $_REQUEST['user'] as $cur_user_id) {
	$cur_user_id = intval($cur_user_id);
	$user = $config->user_manager->get_user($cur_user_id);
	if ( !empty($user->email) )
		$bcc = $bcc . (strlen($bcc) ? ', ' : '') . $user->email;
}

$mailer = &new Mailer(clean_header($_REQUEST['from']), 'undisclosed-recipients <noone@example.com>', clean_header($_REQUEST['subject']), $message);
$mailer->addHeader('Reply-To', clean_header($_REQUEST['replyto']));
$mailer->addHeader('Bcc', $bcc);

$attachments = $mail->attachments;
foreach ( $attachments as $cur_attachment ) {
	$mailer->addAttachment($config->setting_upload . 'attachments/' . $cur_attachment);
}

if ( $mailer->send() ) {
	$com_newsletter_send->title = "Success sending \"".$mail->name."\".";
} else {
	$com_newsletter_send->title = "Failed to send \"".$mail->name."\".";
}

$com_newsletter_send->content("<h3>Subject: &quot;".clean_header($_REQUEST['subject'])."&quot;.</h3>");
$com_newsletter_send->content("<div style=\"background: white; border: 2px solid black; padding: 5px; clear: both; overflow: auto;\">$message<br style=\"clear: both;\" /></div><br />");

// com_newsletter::list_mails('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', '');
?>
