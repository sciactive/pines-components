<?php
/**
 * Display dashboard tab.
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

if (!empty($_REQUEST['id']) && gatekeeper('com_dash/manage'))
	$dashboard = com_dash_dashboard::factory((int) $_REQUEST['id']);
else
	$dashboard =& $_SESSION['user']->dashboard;
if (!isset($dashboard->guid)) {
	header('HTTP/1.0 400 Bad Request');
	return;
}

$pines->page->override = true;
$module = $dashboard->print_tab($_REQUEST['key'], (gatekeeper('com_dash/editdash') && $_REQUEST['editable'] != 'false'));
$module->detach();
$pines->page->override_doc($module->render());

?>