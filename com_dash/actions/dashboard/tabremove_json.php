<?php
/**
 * Remove a tab.
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

if ( !gatekeeper('com_dash/dash') || !gatekeeper('com_dash/editdash') )
	punt_user(null, pines_url('com_dash'));

$pines->page->override = true;
header('Content-Type: application/json');

if (!empty($_REQUEST['id']) && gatekeeper('com_dash/manage'))
	$dashboard = com_dash_dashboard::factory((int) $_REQUEST['id']);
else
	$dashboard =& $_SESSION['user']->dashboard;
if (!isset($dashboard->guid)) {
	header('HTTP/1.0 400 Bad Request');
	return;
}

// Check the requested tab.
if (!isset($dashboard->tabs[$_REQUEST['key']])) {
	header("HTTP/1.0 400 Bad Request");
	return;
}
// Check that it's not the last tab.
if (count($dashboard->tabs) <= 1) {
	$pines->page->override_doc(json_encode('last'));
	return;
}

// Remove it.
unset($dashboard->tabs[$_REQUEST['key']]);

$pines->page->override_doc(json_encode($dashboard->save()));

?>