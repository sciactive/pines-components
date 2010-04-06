<?php
/**
 * Edit a newsletter.
 *
 * @package Pines
 * @subpackage com_newsletter
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_newsletter/listmail') )
	punt_user('You don\'t have necessary permission.', pines_url('com_newsletter', 'list'));

if ( !empty($_REQUEST['mail_id']) ) {
	$mail = $pines->entity_manager->get_entity(array('guid' => $_REQUEST['mail_id'], 'tags' => array('com_newsletter', 'mail')));
	if ( is_null($mail) ) {
		pines_error('Invalid mail!');
		return false;
	}
} else {
	$mail = new entity('com_newsletter', 'mail');
	$mail->attachments = array();
}

if ( $_REQUEST['update'] == 'yes' ) {
	$mail->name = $_REQUEST['name'];
	$mail->subject = $_REQUEST['subject'];
	$mail->message = $_REQUEST['data'];
	foreach ( $mail->attachments as $key => $cur_attachment ) {
		if ( $_REQUEST["attach_".clean_checkbox($cur_attachment)] != 'on' ) {
			if ( $pines->com_newsletter->delete_attachment($mail, $cur_attachment) )
				pines_notice("Attachment $cur_attachment removed.");
		}
	}
	if ( !$_FILES['attachment']['error'] ) {
		$upload_file = stripslashes(basename($_FILES['attachment']['name']));
		if ( move_uploaded_file($_FILES['attachment']['tmp_name'], $pines->config->setting_upload.'attachments/'.$upload_file) ) {
			array_push($mail->attachments, $upload_file);
		} else {
			pines_error("Possible file upload attack! Upload failed! D:\n");
		}
	}

	$mail->save();

	pines_notice("Saved mail [$mail->name]");
}

$pines->com_newsletter->edit_mail($mail, 'com_newsletter', 'edit');
?>