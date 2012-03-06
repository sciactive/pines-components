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

// Get the widget order.
$struct = json_decode($_REQUEST['order'], true);
if (!$struct) {
	header("HTTP/1.0 400 Bad Request");
	return;
}

// Order the tabs correctly.
$new_tab_array = array();
foreach ($struct as $cur_index => $cur_key) {
	if (!isset($_SESSION['user']->dashboard->tabs[$cur_key]))
		continue;
	$new_tab_array[$cur_key] = $_SESSION['user']->dashboard->tabs[$cur_key];
	unset($_SESSION['user']->dashboard->tabs[$cur_key]);
}

// Put any missing tabs in.
$new_tab_array = array_merge($new_tab_array, $_SESSION['user']->dashboard->tabs);

// Now put the new tab array in place.
$_SESSION['user']->dashboard->tabs = $new_tab_array;

$pines->page->override_doc(json_encode($_SESSION['user']->dashboard->save()));

?>