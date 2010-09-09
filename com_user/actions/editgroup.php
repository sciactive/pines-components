<?php
/**
 * Edit a group.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (isset($_REQUEST['id'])) {
	if ( !gatekeeper('com_user/editgroup') )
		punt_user(null, pines_url('com_user', 'listgroups'));
} else {
	if ( !gatekeeper('com_user/newgroup') )
		punt_user(null, pines_url('com_user', 'listgroups'));
}

$group = group::factory((int) $_REQUEST['id']);
$group->print_form();

?>