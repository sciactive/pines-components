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
		punt_user(null, pines_url('com_sales', 'cashcount/edit', array('id' => $_REQUEST['id'])));
} else {
	if ( !gatekeeper('com_sales/newcashcount') )
		punt_user(null, pines_url('com_sales', 'cashcount/edit'));
}

$entity = com_sales_cashcount::factory((int) $_REQUEST['id']);

$pending_count = false;
// Ensure that only one cashcount is done at a time per assignment/location.
if ($_SESSION['user']->group->com_sales_task_cashcount) {
	if ( isset($entity->group->guid) && !$entity->group->is($_SESSION['user']->group) ) {
		pines_notice('This cash count belongs to a different location.');
		$pines->com_sales->list_cashcounts();
		return;
	}
	if (!isset($entity->guid) && isset($_SESSION['user']->group)) {
		// Look for any cashcounts that are waiting to be committed.
		$existing_counts = $pines->entity_manager->get_entities(
				array('class' => com_sales_cashcount),
				array('&',
					'tag' => array('com_sales', 'cashcount'),
					'ref' => array('group', $_SESSION['user']->group)
				)
			);
		foreach ($existing_counts as $cur_count) {
			if ( !$entity->is($cur_count) && !$cur_count->final) {
				pines_notice('This cash count is already waiting to be committed for your location.');
				$cur_count->print_form();
				return;
			}
			// Mark that there is a pending, commited cash count, so if there is
			// no open one, the user is pushed back to the cash count list.
			if (!in_array($cur_count->status, array('closed', 'flagged')))
				$pending_count = true;
		}
	}
}

if ($pending_count) {
	pines_notice('There is a cash count pending for your location, it must be approved or declined.');
	$pines->com_sales->list_cashcounts();
	return;
}

$entity->print_form();

?>