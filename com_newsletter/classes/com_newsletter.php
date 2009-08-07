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
	function delete_attachment(&$mail, $name) {
		global $config;
		if ( unlink($config->setting_upload . 'attachments/' . clean_filename($name)) ) {
			if ( in_array($name, $mail->attachments) )
				unset($mail->attachments[array_search($name, $mail->attachments)]);
			return true;
		} else {
			display_error('File removal failed!');
			return false;
		}
	}

	function edit_mail($heading = '', $mail = NULL, $new_option = '', $new_action = '', $close_option = "com_newsletter", $close_action = "list") {
		global $config, $page;

		if ( !is_null($mail) ) {
			if ( !$mail->has_tag('com_newsletter', 'mail') ) {
				display_error('Invalid mail!');
				return false;
			}
		} else {
			$mail = new entity;
		}

		$module = new module('com_newsletter', 'edit_mail', 'content');
		$module->title = $heading;
        $module->mail = $mail;
        $module->new_option = $new_option;
        $module->new_action = $new_action;
        $module->close_option = $close_option;
        $module->close_action = $close_action;
	}

	function list_mails() {
		global $config;
		$entities = array();
		$entity = new entity;

		$entities = $config->entity_manager->get_entities_by_tags('com_newsletter', 'mail');

		$module = new module('com_newsletter', 'list_mails', 'content');
		$module->title = "Mails";
        $module->mails = $entities;

		if ( empty($entities) )
			display_notice("There are no mails.");
	}
}

?>