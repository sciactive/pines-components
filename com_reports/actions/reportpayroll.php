<?php
/**
 * List employee payroll information.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_reports/reportpayroll') )
	punt_user(null, pines_url('com_reports', 'reportpayroll'));

$entire_company = ($_REQUEST['entire_company'] == 'true');

if (!empty($_REQUEST['location']))
	$location = group::factory((int) $_REQUEST['location']);
$descendents = ($_REQUEST['descendents'] == 'true');

$paystub = com_reports_paystub::factory((int) $_REQUEST['id']);
if (isset($paystub->guid)) {
	$paystub->show($entire_company, $location, $descendents);
} else {
	$pines->com_reports->report_payroll($entire_company, $location, $descendents);
}

?>