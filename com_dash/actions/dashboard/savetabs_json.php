<?php
/**
 * Save tab order.
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
if (!isset($dashboard->guid))
	throw new HttpClientException(null, 400);
if ($dashboard->locked && !gatekeeper('com_dash/manage'))
	throw new HttpClientException(null, 403);

// Get the widget order.
$struct = json_decode($_REQUEST['order'], true);
if (!$struct)
	throw new HttpClientException(null, 400);

// Order the tabs correctly.
$new_tab_array = array();
foreach ($struct as $cur_index => $cur_key) {
	if (!isset($dashboard->tabs[$cur_key]))
		continue;
	$new_tab_array[$cur_key] = $dashboard->tabs[$cur_key];
	unset($dashboard->tabs[$cur_key]);
}

// Put any missing tabs in.
$new_tab_array = array_merge($new_tab_array, $dashboard->tabs);

// Now put the new tab array in place.
$dashboard->tabs = $new_tab_array;

$pines->page->override_doc(json_encode($dashboard->save()));

?>