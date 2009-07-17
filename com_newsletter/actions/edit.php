<?php
/**
 * Edit a newsletter.
 *
 * @package Dandelion
 * @subpackage com_newsletter
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('D_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_newsletter/managemails') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", $config->template->url('com_newsletter', 'list', null, false));
	return;
}

$mail = new entity;
if ( !empty($_REQUEST['mail_id']) ) {
	$mail = $config->entity_manager->get_entity($_REQUEST['mail_id']);
	if ( !$mail->has_tag('com_newsletter', 'mail') ) {
		display_error('Invalid mail!');
		return false;
	}
} else {
	$mail->add_tag('com_newsletter', 'mail');
	$mail->attachments = array();
}

if ( $_REQUEST['update'] == 'yes' ) {
	$mail->name = $_REQUEST['name'];
	$mail->subject = $_REQUEST['subject'];
	$mail->message = stripslashes($_REQUEST['data']);
	foreach ( $mail->attachments as $key => $cur_attachment ) {
		if ( $_REQUEST["attach_".clean_checkbox($cur_attachment)] != 'on' ) {
			if ( $config->com_newsletter->delete_attachment($mail, $cur_attachment) )
				display_notice("Attachment $cur_attachment removed.");
		}
	}
	if ( !$_FILES['attachment']['error'] ) {
		$upload_file = stripslashes(basename($_FILES['attachment']['name']));
		if ( move_uploaded_file($_FILES['attachment']['tmp_name'], $config->setting_upload.'attachments/'.$upload_file) ) {
			array_push($mail->attachments, $upload_file);
			//var_dump($mail->attachments);
		} else {
			display_error("Possible file upload attack! Upload failed! D:\n");
		}
	}

	$mail->save();

	display_notice('Saved "'.$mail->name.'"');
}

$config->com_newsletter->edit_mail("Editing mail \"" . $mail->name . "\".", $mail, 'com_newsletter', 'edit');
?>