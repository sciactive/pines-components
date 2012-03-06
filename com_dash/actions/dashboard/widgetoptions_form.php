<?php
/**
 * Get a widget's options form.
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

// Get the widget entry.
$widget_entry = $_SESSION['user']->dashboard->widget($_REQUEST['key']);
if (!$widget_entry) {
	header("HTTP/1.0 400 Bad Request");
	return;
}

// Get the view and make a module.
$def = $pines->com_dash->get_widget_def($widget_entry);
$view = $def['form'];
if (!isset($view)) {
	$pines->page->override_doc('false');
	return;
}
$module = new module($widget_entry['component'], $view);

$content = $module->render();
$pines->page->override_doc($content);

?>