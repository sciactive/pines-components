<?php
/**
 * Add widgets to a tab.
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

// Check the requested tab.
if (!isset($_SESSION['user']->dashboard->tabs[$_REQUEST['key']])) {
	header("HTTP/1.0 400 Bad Request");
	return;
}

$widgets = $pines->com_dash->widget_types();
foreach ($widgets as $cur_component => $cur_widget_set) {
	foreach ($cur_widget_set as $cur_widget_name => $cur_widget) {
		// Check its conditions.
		foreach ((array) $cur_widget['widget']['depends'] as $cur_type => $cur_value) {
			if (!$pines->depend->check($cur_type, $cur_value)) {
				unset($widgets[$cur_component][$cur_widget_name]);
				if (!$widgets[$cur_component])
					unset($widgets[$cur_component]);
			}
		}
	}
}

// Reset the column array.
reset($_SESSION['user']->dashboard->tabs[$_REQUEST['key']]['columns']);

// Add all the new widgets.
$add_widgets = json_decode($_REQUEST['widgets'], true);
foreach ($add_widgets as $cur_widget) {
	if (!isset($widgets[$cur_widget['component']][$cur_widget['widget']])) {
		$pines->page->override_doc(json_encode(false));
		return;
	}
	$key = key($_SESSION['user']->dashboard->tabs[$_REQUEST['key']]['columns']);
	$_SESSION['user']->dashboard->tabs[$_REQUEST['key']]['columns'][$key]['widgets'][uniqid()] = array(
		'component' => $cur_widget['component'],
		'widget' => $cur_widget['widget'],
		'options' => array()
	);
	if (!next($_SESSION['user']->dashboard->tabs[$_REQUEST['key']]['columns']))
		reset($_SESSION['user']->dashboard->tabs[$_REQUEST['key']]['columns']);
}

$pines->page->override_doc(json_encode($_SESSION['user']->dashboard->save()));

?>