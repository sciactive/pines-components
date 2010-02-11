<?php
/**
 * com_newsletter class.
 *
 * @package Pines
 * @subpackage com_newsletter
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_newsletter main class.
 *
 * Manages newsletters to send out to users.
 *
 * @package Pines
 * @subpackage com_newsletter
 */
class com_newsletter extends component {
	/**
	 * Delete an attachment from a mailing.
	 *
	 * @param entity &$mail The mailing.
	 * @param string $name The name of the attachment to delete.
	 * @return bool True on success, false on failure.
	 */
	function delete_attachment(&$mail, $name) {
		global $pines;
		if ( unlink($pines->config->setting_upload . 'attachments/' . clean_filename($name)) ) {
			if ( in_array($name, $mail->attachments) )
				unset($mail->attachments[array_search($name, $mail->attachments)]);
			pines_log("Removed attachment $name from mail $mail->name.", 'notice');
			return true;
		} else {
			display_error('File removal failed!');
			return false;
		}
	}

	/**
	 * Provide a form for the user to edit a mailing.
	 *
	 * @param entity|null $mail The mailing to edit. If null, a new one is created.
	 * @param string $new_option The option to route to when saved.
	 * @param string $new_action The action to route to when saved.
	 * @param string $close_option The option to route to when closed.
	 * @param string $close_action The action to route to when closed.
	 * @return bool True on success, false on failure.
	 */
	function edit_mail($mail = NULL, $new_option = '', $new_action = '', $close_option = "com_newsletter", $close_action = "list") {
		global $pines;

		if ( !is_null($mail) ) {
			if ( !$mail->has_tag('com_newsletter', 'mail') ) {
				display_error('Invalid mail!');
				return false;
			}
		} else {
			$mail = new entity('com_newsletter', 'mail');
		}

		$module = new module('com_newsletter', 'edit_mail', 'content');
		$module->entity = $mail;
		$module->new_option = $new_option;
		$module->new_action = $new_action;
		$module->close_option = $close_option;
		$module->close_action = $close_action;

		return true;
	}

	/**
	 * Provides a list of mailings.
	 */
	function list_mails() {
		global $pines;

		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;

		$module = new module('com_newsletter', 'list_mails', 'content');
		$module->mails = $pines->entity_manager->get_entities(array('tags' => array('com_newsletter', 'mail')));
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_newsletter/list_mails'];

		if ( empty($module->mails) ) {
			$pgrid->detach();
			$module->detach();
			display_notice("There are no mails.");
		}
	}
}

?>