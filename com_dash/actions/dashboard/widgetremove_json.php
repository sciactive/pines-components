<?php
/**
 * Remove a widget.
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

// Get the widget location.
$widget_location = $dashboard->widget_location($_REQUEST['key']);
if (!$widget_location)
	throw new HttpClientException(null, 400);

// Remove it. Scary isn't it. D:
unset($dashboard->tabs[$widget_location['tab']]['columns'][$widget_location['column']]['widgets'][$_REQUEST['key']]);

$pines->page->override_doc(json_encode($dashboard->save()));

?>