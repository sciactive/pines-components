<?php
/**
 * Flags warehouse items.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/warehouse') )
	punt_user(null, pines_url('com_sales', 'warehouse/pending'));

if (preg_match('/^\#[a-fA-F0-9]{3,6}$/', $_REQUEST['bgcolor']))
	$bgcolor = $_REQUEST['bgcolor'];
else
	$bgcolor = null;
if (preg_match('/^\#[a-fA-F0-9]{3,6}$/', $_REQUEST['textcolor']))
	$textcolor = $_REQUEST['textcolor'];
else
	$textcolor = null;

$success = true;
foreach (explode(',', $_REQUEST['id']) as $cur_id) {
	list ($sale_id, $key) = explode('_', $cur_id);
	$sale = com_sales_sale::factory((int) $sale_id);
	if (!isset($sale->guid)) {
		pines_notice('Couldn\'t find specified sale.');
		continue;
	}

	if (!isset($sale->products[(int) $key])) {
		pines_notice('Couldn\'t find specified item.');
		continue;
	}

	if ($sale->products[(int) $key]['delivery'] != 'warehouse') {
		pines_notice('Specified item is not a warehouse order.');
		continue;
	}

	// Save the flags.
	$sale->products[(int) $key]['flag_bgcolor'] = $bgcolor;
	$sale->products[(int) $key]['flag_textcolor'] = $textcolor;
	$sale->products[(int) $key]['flag_comments'] = $_REQUEST['comments'];
	$success = $success && $sale->save();
}

if ($success)
	pines_notice('Successfully saved flags.');
else
	pines_error('Errors occurred while saving flags. Not all flags could be saved.');

pines_redirect(pines_url('com_sales', 'warehouse/pending'));

?>