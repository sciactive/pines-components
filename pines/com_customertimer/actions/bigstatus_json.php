<?php
/**
 * Return a JSON string of customer status.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$pines->page->override = true;

$logins = com_customertimer_login_tracker::factory();
$return = array();

$warning = false;
$critical = false;
foreach ($logins->customers as $cur_entry) {
	$session_info = $pines->com_customertimer->get_session_info($cur_entry['customer']);
	if (($cur_entry['customer']->points - $session_info['points']) < $pines->config->com_customertimer->level_warning)
		$warning = true;
	if (($cur_entry['customer']->points - $session_info['points']) < $pines->config->com_customertimer->level_critical)
		$critical = true;
}

if ($critical) {
	$pines->page->override_doc('"critical"');
	return;
}
if ($warning) {
	$pines->page->override_doc('"warning"');
	return;
}
$pines->page->override_doc('"ok"');

?>