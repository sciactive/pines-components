<?php
/**
 * Show list of configurable components.
 *
 * @package Pines
 * @subpackage com_configure
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_configure/edit') && !gatekeeper('com_configure/view') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", $config->template->url('com_about', null, null, false));
	return;
}

$config->configurator->list_components();

?>