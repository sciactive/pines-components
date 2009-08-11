<?php
/**
 * com_configure's display control.
 *
 * @package Pines
 * @subpackage com_configure
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( gatekeeper('com_configure/edit') || gatekeeper('com_configure/view') ) {
	$com_configure_menu_id = $page->main_menu->add('Configuration');
	$page->main_menu->add('Components', $config->template->url('com_configure', 'list'), $com_configure_menu_id);
}

?>