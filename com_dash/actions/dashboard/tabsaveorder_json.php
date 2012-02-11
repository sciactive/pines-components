<?php
/**
 * Save a tab's widget order.
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
header('Content-Type: application/json');

// Get the widget order.
$struct = json_decode($_REQUEST['order'], true);
if (!$struct) {
	header("HTTP/1.0 400 Bad Request");
	return;
}
// Check the requested tab.
if (!isset($_SESSION['user']->dashboard->tabs[$_REQUEST['key']])) {
	header("HTTP/1.0 400 Bad Request");
	return;
}

// Get all the widgets.
$widgets = array();
foreach ($_SESSION['user']->dashboard->tabs[$_REQUEST['key']]['columns'] as &$cur_column) {
	$widgets = array_merge($widgets, $cur_column['widgets']);
	// Now clear the column.
	$cur_column['widgets'] = array();
}
unset($cur_column);
// Sort the widgets into their requested order.
foreach ($struct as $cur_c_key => $cur_w_key_list) {
	foreach ($cur_w_key_list as $cur_w_key) {
		if (!isset($widgets[$cur_w_key]))
			continue;
		$_SESSION['user']->dashboard->tabs[$_REQUEST['key']]['columns'][$cur_c_key]['widgets'][$cur_w_key] = $widgets[$cur_w_key];
		unset($widgets[$cur_w_key]);
	}
}
// If there are any widgets left, throw them into whatever column. This
// *shouldn't* be necessary, but.. you know, shouldn't =/= isn't.
if ($widgets) {
	$key = key($_SESSION['user']->dashboard->tabs[$_REQUEST['key']]['columns']);
	foreach ($widgets as $wkey => $widget)
		$_SESSION['user']->dashboard->tabs[$_REQUEST['key']]['columns'][$key]['widgets'][$wkey] = $widget;
}

$pines->page->override_doc(json_encode($_SESSION['user']->dashboard->save()));

?>