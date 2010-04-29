<?php
/**
 * Provide a form to edit a cash count.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (isset($_REQUEST['id'])) {
	if ( !gatekeeper('com_sales/editcashcount') )
		punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'editcashcount', array('id' => $_REQUEST['id'])));
} else {
	if ( !gatekeeper('com_sales/newcashcount') )
		punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'editcashcount'));
}

$entity = com_sales_cashcount::factory((int) $_REQUEST['id']);
// Ensure that only one cashcount is done at a time per assignment/location.
if ($_SESSION['user']->group->com_sales_task_cashcount) {
	if ( isset($entity->group->guid) && $entity->group->guid != $_SESSION['user']->group->guid ) {
		pines_notice('This cash count belongs to a different location.');
		$pines->com_sales->list_cashcounts();
		return;
	}
	if (!isset($entity->guid) && isset($_SESSION['user']->group)) {
		// Look for any cashcounts that are waiting to be committed.
		$existing_sheets = $pines->entity_manager->get_entities(array('ref' => array('group' => $_SESSION['user']->group), 'tags' => array('com_sales', 'cashcount'), 'class' => com_sales_cashcount));
		foreach ($existing_sheets as $cur_sheet) {
			if (!$entity->is($cur_sheet) && !$cur_sheet->final) {
				pines_notice('This cash count is already waiting to be committed for your location.');
				$cur_sheet->print_form();
				return;
			}
		}
	}
}
$entity->print_form();

?>