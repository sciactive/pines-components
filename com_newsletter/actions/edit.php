<?php
/**
 * Edit a newsletter.
 *
 * @package Pines
 * @subpackage com_newsletter
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_newsletter/listmail') )
	punt_user(null, pines_url('com_newsletter', 'list'));

if ( !empty($_REQUEST['mail_id']) ) {
	$mail = $pines->entity_manager->get_entity(array(), array('&', 'guid' => (int) $_REQUEST['mail_id'], 'tag' => array('com_newsletter', 'mail')));
	if ( !isset($mail) ) {
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
		if ( $_REQUEST["attach_".clean_checkbox($cur_attachment)] != 'on' )
			unset($mail->attachments[$key]);
	}
	if (!empty($_REQUEST['attachment'])) {
		if (!in_array($_REQUEST['attachment'], $mail->attachments) && $pines->uploader->check($_REQUEST['attachment'])) {
			$mail->attachments[] = $_REQUEST['attachment'];
		} else {
			pines_error("File attachment failed.\n");
		}
	}

	$mail->save();

	pines_notice("Saved mail [$mail->name]");
}

$pines->com_newsletter->edit_mail($mail, 'com_newsletter', 'edit');
?>