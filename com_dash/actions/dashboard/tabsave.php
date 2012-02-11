<?php
/**
 * Save a tab.
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

// Check the requested tab.
if (!empty($_REQUEST['key']) && !isset($_SESSION['user']->dashboard->tabs[$_REQUEST['key']])) {
	pines_notice('Requested tab is invalid.');
	return;
}

// For both new and edited tabs:
// Build a column array.
$columns = array();
foreach (json_decode($_REQUEST['columns'], true) as $cur_column) {
	if (isset($cur_column['key']))
		$key = (string) $cur_column['key'];
	else
		$key = uniqid();
	$columns[$key] = array(
		'size' => (int) $cur_column['size'],
		'widgets' => array()
	);
}

// Now we have our column array, and things get different if this is a new tab.
if (empty($_REQUEST['key'])) {
	// New tab.
	$tab_key = uniqid();
	$_SESSION['user']->dashboard->tabs[$tab_key] = array(
		'name' => $_REQUEST['name'],
		'buttons' => array(),
		'columns' => $columns
	);
} else {
	// Current tab.
	$tab_key = $_REQUEST['key'];
	$_SESSION['user']->dashboard->tabs[$tab_key]['name'] = $_REQUEST['name'];
	// Save the old columns.
	$old_columns = $_SESSION['user']->dashboard->tabs[$_REQUEST['key']]['columns'];
	// Now copy widgets to the new columns.
	foreach ($old_columns as $col_key => $cur_column) {
		if (isset($columns[$col_key]))
			$columns[$col_key]['widgets'] = $cur_column['widgets'];
		else {
			// Since this column was deleted, copy its widgets into a current column.
			$key = key($columns);
			foreach ($cur_column['widgets'] as $wkey => $widget)
				$columns[$key]['widgets'][$wkey] = $widget;
		}
	}
	// Now put in the new columns.
	$_SESSION['user']->dashboard->tabs[$_REQUEST['key']]['columns'] = $columns;
}

if (!$_SESSION['user']->dashboard->save())
	pines_error('An error occured while trying to save the tab.');

pines_redirect(pines_url('com_dash', null, array('tab' => $tab_key)));

?>