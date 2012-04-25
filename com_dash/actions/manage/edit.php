<?php
/**
 * Provide a form to edit a dashboard.
 *
 * @package Components\dash
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_dash/manage') )
	punt_user(null, pines_url('com_dash', 'dashboard/edit', array('id' => $_REQUEST['id'])));

$entity = com_dash_dashboard::factory((int) $_REQUEST['id']);
if (!isset($entity->guid)) {
	pines_error('Requested dashboard id is not accessible.');
	return;
}

$entity->print_form();

?>