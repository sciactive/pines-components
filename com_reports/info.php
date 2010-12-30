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
	'depend' => array(
		'pines' => '<2',
		'service' => 'icons',
		'component' => 'com_jquery&com_pgrid&com_jstree&(com_hrm|com_sales)'
	),
	'abilities' => array(
		array('reportattendance', 'Report Attendance', 'User can see attendance reports.'),
		array('reportissues', 'Report Issues', 'User can see employee issue reports.'),
		array('reportmifi', 'Report MiFi', 'User can see MiFi reports.'),
		array('reportproducts', 'Report Products', 'User can see product detail reports.'),
		array('reportsales', 'Report Sales', 'User can see sales reports.'),
		array('summarizeemployees', 'Employee Summaries', 'User can see employee summary reports.'),
		array('summarizeinvoices', 'Invoice Summaries', 'User can see invoice summary reports.'),
		array('summarizelocations', 'Location Summaries', 'User can see location summary reports.'),
		array('listsalesrankings', 'List Sales Rankings', 'User can see a list of sales rankings.'),
		array('viewsalesranking', 'Report Sales Rankings', 'User can see sales rankings.'),
		array('newsalesranking', 'Create Sales Rankings', 'User can create sales rankings.'),
		array('editsalesranking', 'Edit Sales Rankings', 'User can edit sales rankings.'),
		array('deletesalesranking', 'Delete Sales Rankings', 'User can delete sales rankings.'),
		array('warboard', 'View Warboard', 'User can view the company warboard.'),
		array('editwarboard', 'Edit Warboard', 'User can edit the company warboard.')
	),
);

?>