<?php
/**
 * Daily attendance report.
 *
 * @package Components\reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_reports/attendance') )
	punt_user(null, pines_url('com_reports', 'attendance/hoursclocked', $_GET));

if (!empty($_REQUEST['date'])) {
	$date = $_REQUEST['date'];
	if (strpos($date, '-') === false)
		$date = format_date($date, 'date_sort');
	$date = strtotime($date.' 00:00:00');
} else {
	$date = time();
}

$location = empty($_REQUEST['location']) ? null : group::factory((int) $_REQUEST['location']);
$descendants = ($_REQUEST['descendants'] == 'true');

$pines->com_reports->daily_attendance($date, $location, $descendants);

?>