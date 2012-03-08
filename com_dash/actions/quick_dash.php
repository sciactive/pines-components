<?php
/**
 * Return quick dashboard.
 *
 * @package Pines
 * @subpackage com_dash
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_dash/dash') )
	punt_user(null, pines_url('com_dash'));

$pines->page->override = true;

if (!($module = $_SESSION['user']->dashboard->print_dashboard($_REQUEST['tab'], (!$dashboard->locked && gatekeeper('com_dash/editdash'))))) {
	pines_error('Couldn\'t load your dashboard.');
	header("HTTP/1.0 500 Internal Server Error");
	return;
}

$pines->page->override_doc($module->render());

?>