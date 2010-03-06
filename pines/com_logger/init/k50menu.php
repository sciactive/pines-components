<?php
/**
 * Add menu entries.
 *
 * @package Pines
 * @subpackage com_logger
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( gatekeeper('com_logger/view') || gatekeeper('com_logger/clear') ) {
	$com_logger_menu_id = $pines->page->main_menu->add('Logs');
	if ( gatekeeper('com_logger/view') )
		$pines->page->main_menu->add('View', pines_url('com_logger', 'view'), $com_logger_menu_id);
	if ( gatekeeper('com_logger/clear') )
		$pines->page->main_menu->add('Clear', pines_url('com_logger', 'clear'), $com_logger_menu_id);
}

?>