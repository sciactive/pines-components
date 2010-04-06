<?php
/**
 * Delete a newsletter.
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

$list = explode(',', $_REQUEST['mail_id']);
foreach ($list as $cur_mail) {
	$mail = $pines->entity_manager->get_entity(array('guid' => $cur_mail, 'tags' => array('com_newsletter', 'mail')));
	if ( is_null($mail) ) {
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_mail;
	}
	$mail->delete();
}
if (empty($failed_deletes)) {
	pines_notice('Selected mail(s) deleted successfully.');
} else {
	pines_error('Could not delete mails with given IDs: '.$failed_deletes);
}

$pines->com_newsletter->list_mails();
?>