<?php
/**
 * Get a widget's content.
 *
 * @package Components\dash
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

if (!empty($_REQUEST['id']) && gatekeeper('com_dash/manage'))
	$dashboard = com_dash_dashboard::factory((int) $_REQUEST['id']);
else
	$dashboard =& $_SESSION['user']->dashboard;
if (!isset($dashboard->guid))
	throw new HttpClientException(null, 400);

// Get the widget entry.
$widget_entry = $dashboard->widget($_REQUEST['key']);
if (!$widget_entry)
	throw new HttpClientException(null, 400);

// Get the view and make a module.
$def = $pines->com_dash->get_widget_def($widget_entry);
$view = $def['view'];
$view_callback = $def['view_callback'];
if (!isset($view) && !isset($view_callback))
	throw new HttpServerException(null, 500);

if (isset($view))
	$module = new module($widget_entry['component'], $view);
else {
	$module = call_user_func($view_callback, null, null, (array) $widget_entry['options']);
	if (!$module)
		throw new HttpServerException(null, 500);
}

// Include the options.
foreach ((array) $widget_entry['options'] as $cur_option) {
	switch ($cur_option['name']) {
		case 'muid':
		case 'title':
		case 'note':
		case 'classes':
		case 'content':
		case 'component':
		case 'view':
		case 'position':
		case 'order':
		case 'show_title':
		case 'is_rendered':
		case 'data_container':
			break;
		default:
			$name = $cur_option['name'];
			$module->$name = $cur_option['value'];
			break;
	}
}

$pines->page->modules['head'] = array();
$content = $module->render();
// Render any modules placed into the head. (In case they add more.)
foreach ($pines->page->modules['head'] as $cur_module)
	$cur_module->render();
// Now get their content.
$head = '';
foreach ($pines->page->modules['head'] as $cur_module)
	$head .= $cur_module->render();

$pines->page->override_doc(json_encode(array('title' => $module->title, 'content' => $content, 'head' => $head)));

?>