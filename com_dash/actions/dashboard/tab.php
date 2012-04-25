<?php
/**
 * Display dashboard tab.
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

if (!empty($_REQUEST['id']) && gatekeeper('com_dash/manage')) {
	$dashboard = com_dash_dashboard::factory((int) $_REQUEST['id']);
	$editable = true;
} else {
	$dashboard =& $_SESSION['user']->dashboard;
	$editable = !$dashboard->locked && gatekeeper('com_dash/editdash') && $_REQUEST['editable'] != 'false';
}
if (!isset($dashboard->guid))
	throw new HttpClientException(null, 400);

$pines->page->override = true;
$module = $dashboard->print_tab($_REQUEST['key'], $editable);
$module->detach();
$pines->page->override_doc($module->render());

?>