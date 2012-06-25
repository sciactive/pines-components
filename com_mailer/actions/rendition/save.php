<?php
/**
 * Save changes to a rendition.
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
	if ( !gatekeeper('com_mailer/editrendition') )
		punt_user(null, pines_url('com_mailer', 'rendition/list'));
	$rendition = com_mailer_rendition::factory((int) $_REQUEST['id']);
	if (!isset($rendition->guid)) {
		pines_error('Requested rendition id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_mailer/newrendition') )
		punt_user(null, pines_url('com_mailer', 'rendition/list'));
	$rendition = com_mailer_rendition::factory();
}

// General
$rendition->name = $_REQUEST['name'];
$rendition->enabled = ($_REQUEST['enabled'] == 'ON');
$rendition->type = $_REQUEST['type'];
$rendition->to = $_REQUEST['to'];
$rendition->cc = $_REQUEST['cc'];
$rendition->bcc = $_REQUEST['bcc'];
$rendition->subject = $_REQUEST['subject'];
$rendition->content = $_REQUEST['content'];

// Conditions
$conditions = (array) json_decode($_REQUEST['conditions']);
$rendition->conditions = array();
foreach ($conditions as $cur_condition) {
	if (!isset($cur_condition->values[0], $cur_condition->values[1]))
		continue;
	$rendition->conditions[$cur_condition->values[0]] = $cur_condition->values[1];
}

if (empty($rendition->name)) {
	$rendition->print_form();
	pines_notice('Please specify a name.');
	return;
}
$test = $pines->entity_manager->get_entity(array('class' => com_mailer_rendition, 'skip_ac' => true), array('&', 'tag' => array('com_mailer', 'rendition'), 'data' => array('name', $rendition->name)));
if (isset($test) && $test->guid != $_REQUEST['id']) {
	$rendition->print_form();
	pines_notice('There is already a rendition with that name. Please choose a different name.');
	return;
}
list($component, $defname) = explode('/', $rendition->type, 2);
if (!$pines->com_mailer->get_mail_def(array('component' => $component, 'mail' => $defname))) {
	$rendition->print_form();
	pines_notice('The specified mail definition doesn\'t exist.');
	return;
}
if (!empty($rendition->to) && !preg_match('/^(?:(?:(?:"[^"]*" )?<)?\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b>?(?:, ?)?)+$/i', $rendition->to)) {
	$rendition->print_form();
	pines_notice('The To field is not formatted correctly.');
	return;
}
if (!empty($rendition->cc) && !preg_match('/^(?:(?:(?:"[^"]*" )?<)?\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b>?(?:, ?)?)+$/i', $rendition->cc)) {
	$rendition->print_form();
	pines_notice('The CC field is not formatted correctly.');
	return;
}
if (!empty($rendition->bcc) && !preg_match('/^(?:(?:(?:"[^"]*" )?<)?\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b>?(?:, ?)?)+$/i', $rendition->bcc)) {
	$rendition->print_form();
	pines_notice('The BCC field is not formatted correctly.');
	return;
}

if ($rendition->save()) {
	pines_notice('Saved rendition ['.$rendition->name.']');
} else {
	pines_error('Error saving rendition. Do you have permission?');
}

pines_redirect(pines_url('com_mailer', 'rendition/list'));

?>