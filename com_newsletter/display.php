<?php
/**
 * com_newsletter's display control.
 *
 * @package XROOM
 * @subpackage com_newsletter
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('X_RUN') or die('Direct access prohibited');

if ( gatekeeper('com_newsletter/managemails') || gatekeeper('com_newsletter/managegroups') || gatekeeper('com_newsletter/send') ) {
	$com_newsletter_menu_id = $page->main_menu->add('Newsletter');
	if ( gatekeeper('com_newsletter/managemails') || gatekeeper('com_newsletter/send') ) {
		$page->main_menu->add('Mails Index', $config->template->url('com_newsletter', 'list'), $com_newsletter_menu_id);
		$page->main_menu->add('New Mail', $config->template->url('com_newsletter', 'new'), $com_newsletter_menu_id);
	}
}

?>