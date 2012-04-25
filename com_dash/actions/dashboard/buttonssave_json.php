<?php
/**
 * Save buttons to a tab.
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

// Check the requested tab.
if (!isset($dashboard->tabs[$_REQUEST['key']]))
	throw new HttpClientException(null, 400);

$buttons = $pines->com_dash->button_types();
foreach ($buttons as $cur_component => $cur_button_set) {
	foreach ($cur_button_set as $cur_button_name => $cur_button) {
		// Check its conditions.
		foreach ((array) $cur_button['depends'] as $cur_type => $cur_value) {
			if (!$pines->depend->check($cur_type, $cur_value)) {
				unset($buttons[$cur_component][$cur_button_name]);
				if (!$buttons[$cur_component])
					unset($buttons[$cur_component]);
			}
		}
	}
}

// Reset the buttons array.
$dashboard->tabs[$_REQUEST['key']]['buttons_size'] = $_REQUEST['buttons_size'];
// Reset the buttons array.
$dashboard->tabs[$_REQUEST['key']]['buttons'] = array();

// Add all the buttons.
$add_buttons = json_decode($_REQUEST['buttons'], true);
foreach ($add_buttons as $cur_button) {
	// Check that the button exists and there aren't any weird things in the array.
	if ($cur_button !== 'separator' && $cur_button !== 'line_break' && (!isset($buttons[$cur_button['component']][$cur_button['button']]) || count($cur_button) > 2)) {
		$pines->page->override_doc(json_encode(false));
		return;
	}
	$dashboard->tabs[$_REQUEST['key']]['buttons'][] = $cur_button;
}

$pines->page->override_doc(json_encode($dashboard->save()));

?>