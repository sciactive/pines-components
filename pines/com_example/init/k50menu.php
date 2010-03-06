<?php
/**
 * Add menu entries.
 *
 * @package Pines
 * @subpackage com_example
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( gatekeeper('com_example/listwidgets') || gatekeeper('com_example/newwidget') ||
	gatekeeper('com_example/content') ) {
	$com_example_menu_id = $pines->page->main_menu->add('Example');
	if ( gatekeeper('com_example/listwidgets') )
		$pines->page->main_menu->add('Widgets', pines_url('com_example', 'listwidgets'), $com_example_menu_id);
	if ( gatekeeper('com_example/newwidget') )
		$pines->page->main_menu->add('New Widget', pines_url('com_example', 'editwidget'), $com_example_menu_id);
	if ( gatekeeper('com_example/content') )
		$pines->page->main_menu->add('Example Content', pines_url('com_example', 'content'), $com_example_menu_id);
}

?>