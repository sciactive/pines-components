<?php
/**
 * Save changes to a replacement.
 *
 * @package Pines
 * @subpackage com_replace
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_replace/editreplacement') )
		punt_user(null, pines_url('com_replace', 'replacement/list'));
	$replacement = com_replace_replacement::factory((int) $_REQUEST['id']);
	if (!isset($replacement->guid)) {
		pines_error('Requested replacement id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_replace/newreplacement') )
		punt_user(null, pines_url('com_replace', 'replacement/list'));
	$replacement = com_replace_replacement::factory();
}

// General
$replacement->name = $_REQUEST['name'];
$replacement->enabled = ($_REQUEST['enabled'] == 'ON');
$replacement->strings = (array) json_decode($_REQUEST['strings']);
foreach ($replacement->strings as &$cur_string) {
	$array = array(
		'search' => $cur_string->values[1],
		'replace' => $cur_string->values[2],
		'macros' => ($cur_string->values[3] == 'Yes' ? true : false)
	);
	$cur_string = $array;
}
unset($cur_string);

// Conditions
$conditions = (array) json_decode($_REQUEST['conditions']);
$replacement->conditions = array();
foreach ($conditions as $cur_condition) {
	if (!isset($cur_condition->values[0], $cur_condition->values[1]))
		continue;
	$replacement->conditions[$cur_condition->values[0]] = $cur_condition->values[1];
}

if (empty($replacement->name)) {
	$replacement->print_form();
	pines_notice('Please specify a name.');
	return;
}
$test = $pines->entity_manager->get_entity(array('class' => com_replace_replacement, 'skip_ac' => true), array('&', 'tag' => array('com_replace', 'replacement'), 'data' => array('name', $replacement->name)));
if (isset($test) && $test->guid != $_REQUEST['id']) {
	$replacement->print_form();
	pines_notice('There is already a replacement with that name. Please choose a different name.');
	return;
}

$replacement->ac->other = 1;

if ($replacement->save()) {
	pines_notice('Saved replacement ['.$replacement->name.']');
} else {
	pines_error('Error saving replacement. Do you have permission?');
}

pines_redirect(pines_url('com_replace', 'replacement/list'));

?>