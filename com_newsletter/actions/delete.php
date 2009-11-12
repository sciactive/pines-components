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
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_newsletter', 'list', null, false));
	return;
}

$list = explode(',', $_REQUEST['mail_id']);
foreach ($list as $cur_mail) {
	$mail = new entity;
	$mail = $config->entity_manager->get_entity($cur_mail);
	if ( is_null($mail) || !$mail->has_tag('com_newsletter', 'mail') ) {
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_mail;
	}
	$mail->delete();
}
if (empty($failed_deletes)) {
	display_notice('Selected mail(s) deleted successfully.');
} else {
	display_error('Could not delete mails with given IDs: '.$failed_deletes);
}

$config->run_newsletter->list_mails();
?>