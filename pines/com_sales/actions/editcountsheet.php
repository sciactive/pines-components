<?php
/**
 * Provide a form to edit a countsheet.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (isset($_REQUEST['id'])) {
	if ( !gatekeeper('com_sales/editcountsheet') )
		punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'editcountsheet', array('id' => $_REQUEST['id'])));
} else {
	if ( !gatekeeper('com_sales/newcountsheet') )
		punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'editcountsheet'));
}

$entity = com_sales_countsheet::factory((int) $_REQUEST['id']);
// Ensure that only one countsheet is done at a time per assignment/location.
if ($_SESSION['user']->group->com_sales_task_countsheet) {
	if ( isset($entity->gid) && $entity->gid != $_SESSION['user']->group->guid ) {
		pines_notice('This countsheet belongs to a different location.');
		$pines->com_sales->list_countsheets();
		return;
	}
	// Look for any countsheets that are waiting to be committed.
	$existing_sheets = $pines->entity_manager->get_entities(array('data' => array('gid' => $_SESSION['user']->group->guid), 'tags' => array('com_sales', 'countsheet'), 'class' => com_sales_countsheet));
	foreach ($existing_sheets as $cur_sheet) {
		if (!$entity->is($cur_sheet) && !$cur_sheet->final) {
			pines_notice('A countsheet is already waiting to be committed for your location.');
			$pines->com_sales->list_countsheets();
			return;
		}
	}
}
$entity->print_form();

?>