<?php
/**
 * com_newsletter class.
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
	 * Provide a form for the user to edit a mailing.
	 *
	 * @param entity|null $mail The mailing to edit. If null, a new one is created.
	 * @param string $new_option The option to route to when saved.
	 * @param string $new_action The action to route to when saved.
	 * @param string $close_option The option to route to when closed.
	 * @param string $close_action The action to route to when closed.
	 * @return bool True on success, false on failure.
	 */
	function edit_mail($mail = null, $new_option = '', $new_action = '', $close_option = 'com_newsletter', $close_action = "list") {
		global $pines;

		if ( isset($mail) ) {
			if ( !$mail->has_tag('com_newsletter', 'mail') ) {
				pines_error('Invalid mail!');
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
	 * @return module The module.
	 */
	function list_mails() {
		global $pines;

		$module = new module('com_newsletter', 'list_mails', 'content');
		$module->mails = $pines->entity_manager->get_entities(array(), array('&', 'tag' => array('com_newsletter', 'mail')));

		if ( empty($module->mails) ) {
			//$module->detach();
			pines_notice('There are no mails.');
		}

		return $module;
	}
}

?>