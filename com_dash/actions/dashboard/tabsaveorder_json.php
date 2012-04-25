<?php
/**
 * Save a tab's widget order.
 *
 * @package Components
 * @subpackage dash
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
if (!isset($dashboard->guid))
	throw new HttpClientException(null, 400);
if ($dashboard->locked && !gatekeeper('com_dash/manage'))
	throw new HttpClientException(null, 403);

// Get the widget order.
$struct = json_decode($_REQUEST['order'], true);
if (!$struct)
	throw new HttpClientException(null, 400);
// Check the requested tab.
if (!isset($dashboard->tabs[$_REQUEST['key']]))
	throw new HttpClientException(null, 400);

// Get all the widgets.
$widgets = array();
foreach ($dashboard->tabs[$_REQUEST['key']]['columns'] as &$cur_column) {
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
		$dashboard->tabs[$_REQUEST['key']]['columns'][$cur_c_key]['widgets'][$cur_w_key] = $widgets[$cur_w_key];
		unset($widgets[$cur_w_key]);
	}
}
// If there are any widgets left, throw them into whatever column. This
// *shouldn't* be necessary, but.. you know, shouldn't =/= isn't.
if ($widgets) {
	$key = key($dashboard->tabs[$_REQUEST['key']]['columns']);
	foreach ($widgets as $wkey => $widget)
		$dashboard->tabs[$_REQUEST['key']]['columns'][$key]['widgets'][$wkey] = $widget;
}

$pines->page->override_doc(json_encode($dashboard->save()));

?>