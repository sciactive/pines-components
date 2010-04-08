<?php
/**
 * Save changes to a widget.
 *
 * @package Pines
 * @subpackage com_example
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_example/editwidget') )
		punt_user('You don\'t have necessary permission.', pines_url('com_example', 'listwidgets'));
	$widget = com_example_widget::factory((int) $_REQUEST['id']);
	if (is_null($widget->guid)) {
		pines_error('Requested widget id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_example/newwidget') )
		punt_user('You don\'t have necessary permission.', pines_url('com_example', 'listwidgets'));
	$widget = com_example_widget::factory();
}

// General
$widget->name = $_REQUEST['name'];
$widget->enabled = ($_REQUEST['enabled'] == 'ON');
$widget->description = $_REQUEST['description'];
$widget->short_description = $_REQUEST['short_description'];

// Attributes
$widget->attributes = (array) json_decode($_REQUEST['attributes']);
foreach ($widget->attributes as &$cur_attribute) {
	$array = array(
		'name' => $cur_attribute->values[0],
		'value' => $cur_attribute->values[1]
	);
	$cur_attribute = $array;
}
unset($cur_attribute);

if (empty($widget->name)) {
	$widget->print_form();
	pines_notice('Please specify a name.');
	return;
}
$test = $pines->entity_manager->get_entity(array('data' => array('name' => $widget->name), 'tags' => array('com_example', 'widget'), 'class' => com_example_widget));
if (isset($test) && $test->guid != $_REQUEST['id']) {
	$widget->print_form();
	pines_notice('There is already a widget with that name. Please choose a different name.');
	return;
}

if ($pines->config->com_example->global_widgets)
	$widget->ac->other = 1;

if ($widget->save()) {
	pines_notice('Saved widget ['.$widget->name.']');
} else {
	pines_error('Error saving widget. Do you have permission?');
}

$pines->com_example->list_widgets();
?>