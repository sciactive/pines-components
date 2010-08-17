<?php
/**
 * Save changes to a module.
 *
 * @package Pines
 * @subpackage com_modules
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_modules/editmodule') )
		punt_user('You don\'t have necessary permission.', pines_url('com_modules', 'module/list'));
	$module = com_modules_module::factory((int) $_REQUEST['id']);
	if (!isset($module->guid)) {
		pines_error('Requested module id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_modules/newmodule') )
		punt_user('You don\'t have necessary permission.', pines_url('com_modules', 'module/list'));
	$module = com_modules_module::factory();
}

// General
$module->name = $_REQUEST['name'];
$module->enabled = ($_REQUEST['enabled'] == 'ON');
$module->position = $_REQUEST['position'];
$module->type = $_REQUEST['type'];

// Options
$module->options = (array) json_decode($_REQUEST['options']);
foreach ($module->options as &$cur_option) {
	$array = array(
		'name' => $cur_option->values[0],
		'value' => $cur_option->values[1]
	);
	$cur_option = $array;
}
unset($cur_option);

// Conditions
$conditions = (array) json_decode($_REQUEST['conditions']);
$module->conditions = array();
foreach ($conditions as $cur_condition) {
	if (!isset($cur_condition->values[0], $cur_condition->values[1]))
		continue;
	$module->conditions[$cur_condition->values[0]] = $cur_condition->values[1];
}

if (empty($module->name)) {
	$module->print_form();
	pines_notice('Please specify a name.');
	return;
}

if ($pines->config->com_modules->global_modules)
	$module->ac->other = 1;

if ($module->save()) {
	pines_notice('Saved module ['.$module->name.']');
} else {
	pines_error('Error saving module. Do you have permission?');
}

redirect(pines_url('com_modules', 'module/list'));

?>