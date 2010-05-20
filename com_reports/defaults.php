<?php
/**
 * com_reports' configuration defaults.
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
	array(
		'name' => 'global_sales_rankings',
		'cname' => 'Globalize Sales Rankings',
		'description' => 'Ensure that every user can access all sales rankings by setting the "other" access control to read.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'timespans',
		'cname' => 'Report Timespans',
		'description' => 'Hours separated by dashes, using 24-hour time format.',
		'value' => array(
			'10-13',
			'13-16',
			'16-19',
			'19-24'
		),
		'peruser' => true,
	),
	array(
		'name' => 'default_goal',
		'cname' => 'Default Sales Goal',
		'description' => 'The dollar value of the default sales goal for monthly rankings.',
		'value' => '500',
		'peruser' => true,
	),
	array(
		'name' => 'ranking_statuses',
		'cname' => 'Ranking Status',
		'description' => 'The Trend % of a salesperson will place them in the Green, Yellow and Red.',
		'value' => array(
			'100',
			'80',
			'0'
		),
		'peruser' => true,
	),
);

?>