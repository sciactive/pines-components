<?php
/**
 * Add widgets to a tab.
 *
 * @package Components\dash
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
reset($dashboard->tabs[$_REQUEST['key']]['columns']);

// Add all the new widgets.
$add_widgets = json_decode($_REQUEST['widgets'], true);
foreach ($add_widgets as $cur_widget) {
	if (!isset($widgets[$cur_widget['component']][$cur_widget['widget']])) {
		$pines->page->override_doc(json_encode(false));
		return;
	}
	$key = key($dashboard->tabs[$_REQUEST['key']]['columns']);
	$dashboard->tabs[$_REQUEST['key']]['columns'][$key]['widgets'][uniqid()] = array(
		'component' => $cur_widget['component'],
		'widget' => $cur_widget['widget'],
		'options' => array()
	);
	if (!next($dashboard->tabs[$_REQUEST['key']]['columns']))
		reset($dashboard->tabs[$_REQUEST['key']]['columns']);
}

$pines->page->override_doc(json_encode($dashboard->save()));

?>