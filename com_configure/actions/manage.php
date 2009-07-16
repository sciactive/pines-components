<?php
/**
 * Manage Dandelion configuration.
 *
 * @package Dandelion
 * @subpackage com_configure
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('D_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_configure/manage') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", $config->template->url('com_configure', 'manage', null, false));
	return;
}

display_notice("Not implemented yet.");
//TODO: finish this configurator manager code
?>
