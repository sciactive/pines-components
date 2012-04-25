<?php
/**
 * Detach PO from warehouse items.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/warehouse') )
	punt_user(null, pines_url('com_sales', 'warehouse/pending'));

$products = $product_entities = array();
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
	
	// Remember where to go.
	$ordered = $sale->products[(int) $key]['ordered'];

	unset($sale->products[(int) $key]['po']);
	if ($sale->save())
		$success = true;
	else
		pines_notice("Couldn't save sale #{$sale->id}.");
}

if ($success)
	pines_notice('Detached PO from selected items.');

pines_redirect(pines_url('com_sales', 'warehouse/pending', array('ordered' => ($ordered ? 'true' : 'false'))));

?>