<?php
/**
 * Save changes to a template.
 *
 * @package Components\mailer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_mailer/edittemplate') )
		punt_user(null, pines_url('com_mailer', 'template/list'));
	$template = com_mailer_template::factory((int) $_REQUEST['id']);
	if (!isset($template->guid)) {
		pines_error('Requested template id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_mailer/newtemplate') )
		punt_user(null, pines_url('com_mailer', 'template/list'));
	$template = com_mailer_template::factory();
}

// General
$template->name = $_REQUEST['name'];
$template->enabled = ($_REQUEST['enabled'] == 'ON');
$template->content = $_REQUEST['content'];

// Replace
$template->replacements = (array) json_decode($_REQUEST['replacements']);
foreach ($template->replacements as &$cur_string) {
	$array = array(
		'search' => $cur_string->values[1],
		'replace' => $cur_string->values[2],
		'macros' => ($cur_string->values[3] == 'Yes' ? true : false)
	);
	$cur_string = $array;
}
unset($cur_string);

// Document
$template->document = $_REQUEST['document'];

// Conditions
$conditions = (array) json_decode($_REQUEST['conditions']);
$template->conditions = array();
foreach ($conditions as $cur_condition) {
	if (!isset($cur_condition->values[0], $cur_condition->values[1]))
		continue;
	$template->conditions[$cur_condition->values[0]] = $cur_condition->values[1];
}

if (empty($template->name)) {
	$template->print_form();
	pines_notice('Please specify a name.');
	return;
}
$test = $pines->entity_manager->get_entity(array('class' => com_mailer_template, 'skip_ac' => true), array('&', 'tag' => array('com_mailer', 'template'), 'data' => array('name', $template->name)));
if (isset($test) && $test->guid != $_REQUEST['id']) {
	$template->print_form();
	pines_notice('There is already a template with that name. Please choose a different name.');
	return;
}

if ($template->save()) {
	pines_notice('Saved template ['.$template->name.']');
} else {
	pines_error('Error saving template. Do you have permission?');
}

pines_redirect(pines_url('com_mailer', 'template/list'));

?>