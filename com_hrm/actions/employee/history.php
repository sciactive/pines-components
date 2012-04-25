<?php
/**
 * Provide a form to edit an employee.
 *
 * @package Components\hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (gatekeeper('com_hrm/listemployees') && !empty($_REQUEST['id'])) {
	$entity = com_hrm_employee::factory((int) $_REQUEST['id']);
	$entity->print_history();
} elseif (gatekeeper('com_hrm/viewownhistory') && $_SESSION['user']->employee) {
	$entity = com_hrm_employee::factory((int) $_SESSION['user']->guid);
	$entity->print_history();
} else {
	punt_user(null, pines_url('com_hrm', 'employee/history', array('id' => $_REQUEST['id'])));
}

?>