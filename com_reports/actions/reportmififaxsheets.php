<?php
/**
 * List all active MiFi contracts.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_reports/reportmifi') )
	punt_user(null, pines_url('com_reports', 'reportmififaxsheets'));

$pines->com_reports->report_mifi_faxsheets();

?>