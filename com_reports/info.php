<?php
/**
 * com_reports' information.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Company Reports',
	'author' => 'SciActive',
	'version' => '1.0.0',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Production and workflow reports',
	'description' => 'Reports for sales totals, inventory and employee reports.',
	'abilities' => array(
		array('reportsales', 'Report Sales', 'User can see sales reports.'),
		array('reportattendance', 'Report Attendance', 'User can see attendance reports.')
	),
);

?>