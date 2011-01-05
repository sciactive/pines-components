<?php
/**
 * Save changes to a return checklist.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_sales/editreturnchecklist') )
		punt_user(null, pines_url('com_sales', 'returnchecklist/list'));
	$return_checklist = com_sales_return_checklist::factory((int) $_REQUEST['id']);
	if (!isset($return_checklist->guid)) {
		pines_error('Requested return checklist id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_sales/newreturnchecklist') )
		punt_user(null, pines_url('com_sales', 'returnchecklist/list'));
	$return_checklist = com_sales_return_checklist::factory();
}

$return_checklist->name = $_REQUEST['name'];
$return_checklist->label = $_REQUEST['label'];
$return_checklist->enabled = ($_REQUEST['enabled'] == 'ON');
// Conditions
$return_checklist->conditions = (array) json_decode($_REQUEST['conditions']);
foreach ($return_checklist->conditions as $key => &$cur_condition) {
	$cur_condition = array(
		'condition' => $cur_condition->values[0],
		'type' => $cur_condition->values[1] == 'flat_rate' ? 'flat_rate' : 'percentage',
		'amount' => (float) $cur_condition->values[2],
		'always' => $cur_condition->values[3] == 'Yes'
	);
}
unset($cur_condition);

if (empty($return_checklist->name)) {
	$return_checklist->print_form();
	pines_notice('Please specify a name.');
	return;
}
$test = $pines->entity_manager->get_entity(array('class' => com_sales_return_checklist, 'skip_ac' => true), array('&', 'tag' => array('com_sales', 'return_checklist'), 'data' => array('name', $return_checklist->name)));
if (isset($test) && $test->guid != $_REQUEST['id']) {
	$return_checklist->print_form();
	pines_notice('There is already a return checklist with that name. Please choose a different name.');
	return;
}

if ($pines->config->com_sales->global_return_checklists)
	$return_checklist->ac->other = 1;

if ($return_checklist->save()) {
	pines_notice('Saved return checklist ['.$return_checklist->name.']');
} else {
	pines_error('Error saving return checklist. Do you have permission?');
}

redirect(pines_url('com_sales', 'returnchecklist/list'));

?>