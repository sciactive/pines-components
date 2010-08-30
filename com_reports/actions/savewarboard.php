<?php
/**
 * Save changes to a warboard.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_reports/editwarboard') )
	punt_user('You don\'t have necessary permission.', pines_url('com_reports', 'warboard'));

$warboard = com_reports_warboard::factory((int) $_REQUEST['id']);
if (!isset($warboard->guid)) {
	pines_error('Requested Warboard id is not accessible.');
	return;
}

$warboard->company_name = $_REQUEST['company_name'];
$warboard->positions = !empty($_REQUEST['titles']) ? $_REQUEST['titles'] : array();
$warboard->columns = (int) $_REQUEST['columns'];
$warboard->locations = array();
$warboard->important = array();

$locations = array_map('intval', (array) $_REQUEST['locations']);
foreach ($locations as $cur_location) {
	$location = group::factory((int) $cur_location);
	if (isset($location->guid))
		$warboard->locations[] = $location;
}

$importants = array_map('intval', (array) $_REQUEST['important']);
foreach ($importants as $cur_important) {
	$important = group::factory((int) $cur_important);
	if (isset($important->guid))
		$warboard->important[] = $important;
}
$warboard->important = array_slice($warboard->important, 0, $warboard->columns-1);

$warboard->hq = group::factory((int) $_REQUEST['hq']);
if (!isset($warboard->hq->guid))
	$warboard->hq = $_SESSION['user']->group;

$warboard->ac = (object) array(
	'user' => 3,
	'group' => 2,
	'other' => 2
);

if ($warboard->save()) {
	pines_notice('Saved Warboard');
} else {
	$warboard->print_form();
	pines_error('Error saving Warboard. Do you have permission?');
	return;
}

redirect(pines_url('com_reports', 'warboard'));

?>