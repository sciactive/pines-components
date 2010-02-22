<?php
/**
 * Edit a group.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (isset($_REQUEST['id'])) {
	if ( !gatekeeper('com_user/editgroup') )
		punt_user('You don\'t have necessary permission.', pines_url('com_user', 'listgroups', null, false));
} else {
	if ( !gatekeeper('com_user/newgroup') )
		punt_user('You don\'t have necessary permission.', pines_url('com_user', 'listgroups', null, false));
}

$group = group::factory((int) $_REQUEST['id']);
$group->print_form();

?>