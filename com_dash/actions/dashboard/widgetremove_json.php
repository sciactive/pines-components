<?php
/**
 * Remove a widget.
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

// Get the widget location.
$widget_location =& $_SESSION['user']->dashboard->widget_location($_REQUEST['key']);
if (!$widget_location) {
	header("HTTP/1.0 400 Bad Request");
	return;
}

// Remove it. Scary isn't it. D:
unset($_SESSION['user']->dashboard->tabs[$widget_location['tab']]['columns'][$widget_location['column']]['widgets'][$_REQUEST['key']]);

$pines->page->override_doc(json_encode($_SESSION['user']->dashboard->save()));

?>