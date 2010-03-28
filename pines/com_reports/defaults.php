<?php
/**
 * com_reports' configuration defaults.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
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
	),
);

?>