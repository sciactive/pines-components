<?php
/**
 * Add abilities.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$pines->ability_manager->add('com_reports', 'reportsales', 'Report Sales', 'User can see sales reports.');
$pines->ability_manager->add('com_reports', 'reportattendance', 'Report Attendance', 'User can see attendance reports.');
?>