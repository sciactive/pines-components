<?php
/**
 * Search customer history for sales, returning JSON.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_customer/listcustomers') )
	punt_user('You don\'t have necessary permission.', pines_url('com_customer', 'salesearch', $_REQUEST));

$pines->page->override = true;

$query = strtolower($_REQUEST['q']);

if (empty($query)) {
	$sales = array();
} else {
	// TODO: Use 'match_i' instead.
	$sales = $pines->entity_manager->get_entities(array('data' => array('status' => 'paid'), 'tags' => array('com_sales', 'sale'), 'class' => com_sales_sale));
	if (!is_array($sales))
		$sales = array();
}

foreach ($sales as $key => &$cur_sale) {
	if (
		(strpos(strtolower($cur_sale->customer->name), $query) !== false) ||
		(strpos(strtolower($cur_sale->customer->email), $query) !== false) ||
		(preg_replace('/\D/', '', $query) != '' && strpos($cur_sale->customer->phone_home, preg_replace('/\D/', '', $query)) !== false) ||
		(preg_replace('/\D/', '', $query) != '' && strpos($cur_sale->customer->phone_work, preg_replace('/\D/', '', $query)) !== false) ||
		(preg_replace('/\D/', '', $query) != '' && strpos($cur_sale->customer->phone_cell, preg_replace('/\D/', '', $query)) !== false) ||
		(preg_replace('/\D/', '', $query) != '' && strpos($cur_sale->customer->fax, preg_replace('/\D/', '', $query)) !== false)
		) {
		$item_dump = '';
		foreach ($cur_sale->products as $cur_item) {
			if ($item_dump != '')
				$item_dump .= '|';
			$item_dump .= $cur_item['quantity'].','.$cur_item['entity']->name.','.$cur_item['serial'].','.$cur_item['price'];
		}

		$json_struct = (object) array(
			'key' => $cur_sale->guid,
			'values' => array(
				$cur_sale->customer->name,
				$cur_sale->customer->city,
				'$'.$cur_sale->total,
				format_date($cur_sale->p_cdate, 'date_short'),
				$cur_sale->guid,
				$cur_sale->user->guid,
				$cur_sale->payments[0]['entity']->name,
				$item_dump
			)
		);
		$cur_sale = $json_struct;
	} else {
		unset($sales[$key]);
	}
}
if (empty($sales))
	$sales = null;

$pines->page->override_doc(json_encode($sales));

?>