<?php
/**
 * Mark warehouse items as ordered/not ordered.
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

	// Mark the item.
	$sale->products[(int) $key]['ordered'] = ($_REQUEST['ordered'] == 'true');
	// Save it.
	if (!$sale->save()) {
		pines_error('Error saving sale. Do you have permission?');
		continue;
	}
	$successful = true;
}

if ($successful)
	pines_notice('Orders marked as '.($_REQUEST['ordered'] == 'true' ? 'ordered.' : 'not ordered.'));

// Go back to the same page.
redirect(pines_url('com_sales', 'warehouse/pending', array('ordered' => ($_REQUEST['ordered'] == 'true' ? 'false' : 'true'))));

?>