<?php
/**
 * com_reports's configuration.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'report_duration',
		'cname' => 'Report Duration',
		'description' => 'Monthly, Weekly or Daily.',
		'value' => 'Daily',
	),
	array(
		'name' => 'timespans',
		'cname' => 'Timespans',
		'description' => 'Hours separated by dashes, using 24-hour times.',
		'value' => '10-13, 13-16, 16-19, 19-24',
	),
);

?>