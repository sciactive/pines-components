<?php
/**
 * Add menu entries.
 *
 * @package Pines
 * @subpackage com_newsletter
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( gatekeeper('com_newsletter/listmail') || gatekeeper('com_newsletter/listgroups') || gatekeeper('com_newsletter/send') ) {
	$com_newsletter_menu_id = $pines->page->main_menu->add('Newsletter');
	if ( gatekeeper('com_newsletter/listmail') || gatekeeper('com_newsletter/send') ) {
		$pines->page->main_menu->add('Mails Index', pines_url('com_newsletter', 'list'), $com_newsletter_menu_id);
		$pines->page->main_menu->add('New Mail', pines_url('com_newsletter', 'new'), $com_newsletter_menu_id);
	}
}

?>