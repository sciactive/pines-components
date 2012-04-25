<?php
/**
 * com_reports' configuration defaults.
 *
 * @package Components\reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
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
			'0-10',
			'10-13',
			'13-16',
			'16-19',
			'19-24'
		),
		'peruser' => true,
	),
	array(
		'name' => 'use_points',
		'cname' => 'Use Points in Rankings',
		'description' => 'Use "points" in rankings instead of dollar amounts.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'point_multiplier',
		'cname' => 'Point Multiplier',
		'description' => 'The value by which to multiply the dollar amount to convert to points.',
		'value' => .001,
		'peruser' => true,
	),
	array(
		'name' => 'rank_level_green',
		'cname' => 'Good Ranking Status',
		'description' => 'Trend percent at or above this value means in the Green.',
		'value' => 100,
		'peruser' => true,
	),
	array(
		'name' => 'rank_level_yellow',
		'cname' => 'Warning Ranking Status',
		'description' => 'Trend percent at or above this value means in the Yellow. Below it means in the Red.',
		'value' => 80,
		'peruser' => true,
	),
	array(
		'name' => 'warboard_states',
		'cname' => 'Warboard States',
		'description' => 'Show the state for each location in the company warboard.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'warboard_phone2_show',
		'cname' => 'Show Phone2 on Warboard',
		'description' => 'Show the second phone number of groups on the company warboard.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'warboard_phone2_label',
		'cname' => 'Phone2 Label on Warboard',
		'description' => 'The label for the second phone number on the company warboard.',
		'value' => 'Landline',
		'peruser' => true,
	),
);

?>