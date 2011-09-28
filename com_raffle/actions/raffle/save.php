<?php
/**
 * Save changes to a raffle.
 *
 * @package Pines
 * @subpackage com_raffle
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_raffle/editraffle') )
		punt_user(null, pines_url('com_raffle', 'raffle/list'));
	$raffle = com_raffle_raffle::factory((int) $_REQUEST['id']);
	if (!isset($raffle->guid)) {
		pines_error('Requested raffle id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_raffle/newraffle') )
		punt_user(null, pines_url('com_raffle', 'raffle/list'));
	$raffle = com_raffle_raffle::factory();
}

$raffle->name = $_REQUEST['name'];
$raffle->public = ($_REQUEST['public'] == 'ON');
// Only name can be changed on completed raffles.
if (!$raffle->complete) {
	$raffle->back_to_form = ($_REQUEST['back_to_form'] == 'ON');
	$raffle->places = (int) $_REQUEST['places'];
	if ($raffle->places < 1)
		$raffle->places = 1;

	// Attributes
	$raffle->contestants = (array) json_decode($_REQUEST['contestants']);
	foreach ($raffle->contestants as &$cur_contestant) {
		$array = array(
			'first_name' => $cur_contestant->values[0],
			'last_name' => $cur_contestant->values[1],
			'email' => $cur_contestant->values[2],
			'phone' => preg_replace('/\D/', '', $cur_contestant->values[3])
		);
		$cur_contestant = $array;
	}
	unset($cur_contestant);
}

if (empty($raffle->name)) {
	$raffle->print_form();
	pines_notice('Please specify a name.');
	return;
}
$test = $pines->entity_manager->get_entity(array('class' => com_raffle_raffle, 'skip_ac' => true), array('&', 'tag' => array('com_raffle', 'raffle'), 'data' => array('name', $raffle->name)));
if (isset($test) && $test->guid != $_REQUEST['id']) {
	$raffle->print_form();
	pines_notice('There is already a raffle with that name. Please choose a different name.');
	return;
}

if ($pines->config->com_raffle->global_raffles)
	$raffle->ac->other = 1;

if ($raffle->save()) {
	pines_notice('Saved raffle ['.$raffle->name.']');
} else {
	pines_error('Error saving raffle. Do you have permission?');
}

pines_redirect(pines_url('com_raffle', 'raffle/list'));

?>