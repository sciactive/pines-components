<?php
/**
 * List all customers with available MiFi financing.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_reports/reportmifi') && !gatekeeper('com_reports/reportmifiavailable') )
	punt_user(null, pines_url('com_reports', 'reportmifiavailable'));

$pines->com_reports->report_mifi_available();

?>