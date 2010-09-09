<?php
/**
 * Manage the system groups.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_user/listgroups') )
	punt_user(null, pines_url('com_user', 'listgroups'));

$pines->user_manager->list_groups($_REQUEST['enabled'] != 'false');
?>