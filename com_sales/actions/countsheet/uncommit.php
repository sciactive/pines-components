<?php
/**
 * Uncommit a countsheet.
 *
 * @package Components
 * @subpackage sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/uncommitcountsheet') )
	punt_user(null, pines_url('com_sales', 'countsheet/list'));

$pines->page->override = true;
header('Content-Type: application/json');

$countsheet = com_sales_countsheet::factory((int) $_REQUEST['id']);
if (!isset($countsheet->guid)) {
	$pines->page->override_doc(json_encode(array(false, 'Requested countsheet id is not accessible.')));
	return;
}
if (!$countsheet->final) {
	$pines->page->override_doc(json_encode(array(false, 'Requested countsheet has not been committed.')));
	return;
}

$countsheet->final = false;

if ($countsheet->save())
	$pines->page->override_doc(json_encode(array(true, 'Countsheet has been uncommitted.')));
else
	$pines->page->override_doc(json_encode(array(false, 'Error saving countsheet. Do you have permission?')));

?>