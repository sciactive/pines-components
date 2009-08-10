<?php
/**
 * Delete a newsletter.
 *
 * @package Pines
 * @subpackage com_newsletter
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_newsletter/managemails') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", $config->template->url('com_newsletter', 'list', null, false));
	return;
}

if ( isset($_REQUEST['mail_id']) ) {
	$mail = new entity;
	$mail = $config->entity_manager->get_entity($_REQUEST['mail_id']);
	if ( !$mail->has_tag('com_newsletter', 'mail') ) {
		display_error('Invalid mail!');
		return false;
	}
	$mail->delete();
	display_notice("Successfully deleted mail \"".$mail->name."\".");
} else {
	display_error("No mail specified!");
}

$config->run_newsletter->list_mails();
?>