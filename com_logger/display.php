<?php
/**
 * com_logger's display control.
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
	$com_logger_menu_id = $page->main_menu->add('Log Manager');
    if ( gatekeeper('com_logger/view') )
        $page->main_menu->add('View', $config->template->url('com_logger', 'view'), $com_logger_menu_id);
    if ( gatekeeper('com_logger/clear') )
        $page->main_menu->add('Clear', $config->template->url('com_logger', 'clear'), $com_logger_menu_id);
}

?>