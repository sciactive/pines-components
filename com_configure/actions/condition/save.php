<?php
/**
 * Save changes to a condition.
 *
 * @package Components\configure
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_configure/edit') )
		punt_user(null, pines_url('com_configure', 'list', array('percondition' => '1')));
	$condition = com_configure_condition::factory((int) $_REQUEST['id']);
	if (!isset($condition->guid)) {
		pines_error('Requested condition id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_configure/edit') )
		punt_user(null, pines_url('com_configure', 'list', array('percondition' => '1')));
	$condition = com_configure_condition::factory();
}

$condition->name = $_REQUEST['name'];

// Conditions
$conditions = (array) json_decode($_REQUEST['conditions']);
$condition->conditions = array();
foreach ($conditions as $cur_condition) {
	if (!isset($cur_condition->values[0], $cur_condition->values[1]))
		continue;
	$condition->conditions[$cur_condition->values[0]] = $cur_condition->values[1];
}

if (empty($condition->name)) {
	$condition->print_form();
	pines_notice('Please specify a name.');
	return;
}
if (empty($condition->conditions)) {
	$condition->print_form();
	pines_notice('Please specify conditions.');
	return;
}
$test = $pines->entity_manager->get_entity(array('class' => com_configure_condition, 'skip_ac' => true), array('&', 'tag' => array('com_configure', 'condition'), 'data' => array('name', $condition->name)));
if (isset($test) && $test->guid != $_REQUEST['id']) {
	$condition->print_form();
	pines_notice('There is already a condition with that name. Please choose a different name.');
	return;
}

$condition->ac = (object) array('user' => 3, 'group' => 3, 'other' => 3);

if ($condition->save()) {
	pines_notice('Saved condition ['.$condition->name.']');
} else {
	pines_error('Error saving condition. Do you have permission?');
}

pines_redirect(pines_url('com_configure', 'list', array('percondition' => '1', 'id' => $condition->guid)));

?>