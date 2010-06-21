<?php
/**
 * Provide a form to edit an employee.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/editemployee') )
	punt_user('You don\'t have necessary permission.', pines_url('com_hrm', 'employee/edit', array('id' => $_REQUEST['id'])));

$entity = com_hrm_employee::factory((int) $_REQUEST['id']);
$entity->print_form();

?>