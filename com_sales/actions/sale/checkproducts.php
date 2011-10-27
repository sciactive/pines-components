<?php
/**
 * Determine if a group of products is available in the user's inventory.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/editsale') && !gatekeeper('com_sales/newsale') )
	punt_user(null, pines_url('com_sales', 'sale/checkproducts', $_GET));

$pines->page->override = true;

// This is all the common parts of the selector.
$selector = array('&',
		'tag' => array('com_sales', 'stock'),
		'data' => array(
			array('available', true)
		),
		'ref' => array(
			array('location', $_SESSION['user']->group)
		)
	);

$products = json_decode($_REQUEST['products']);

// Remember all the stock guids for each item.
$guids = array();
// And what's wrong, if anything.
$success = true;
$messages = array();
foreach ($products as $key => $cur_product) {
	$guid = (int) $cur_product->key;
	$product = com_sales_product::factory($guid);
	if (!isset($product)) {
		$success = false;
		$messages[] = htmlspecialchars("The product on line {$key} can't be found.");
		continue;
	}
	$delivery = $cur_product->values[3];
	if ($delivery == 'warehouse') {
		// This is a warehouse sale, so skip it.
		continue;
	}
	// Now we know it's not a warehouse sale, so check that it has a serial.
	$serial = $cur_product->values[2];
	if ($product->serialized && empty($serial)) {
		$success = false;
		$messages[] = htmlspecialchars("The product {$product->name} requires a serial number.");
		continue;
	}
	$qty = (int) $cur_product->values[4];
	if ($qty <= 0) {
		$success = false;
		$messages[] = htmlspecialchars("The product {$product->name} has no quantity.");
		continue;
	}
	if ($product->serialized && $qty > 1) {
		$success = false;
		$messages[] = htmlspecialchars("The product {$product->name} is serialized and can't take a quantity more than 1.");
		continue;
	}
	$cur_selector = $selector;
	$cur_selector['ref'][] = array('product', $product);
	if ($product->serialized)
		$cur_selector['strict'] = array('serial', $serial);
	$stock = $pines->entity_manager->get_entities(
			array('class' => com_sales_stock, 'limit' => $qty),
			$cur_selector,
			array('!&',
				'guid' => $guids // Don't get previously found stock.
			)
		);
	$count = count($stock);
	if ($count <> $qty) { // Yeah, I used > too. Whatever, it could happen.
		$success = false;
		$messages[] = htmlspecialchars('Couldn\'t find '.($count ? 'all of ' : '')."product {$product->name} in current inventory.".($count ? " Found {$count}." : ''));
	}
	foreach ($stock as $cur_stock)
		$guids[] = $cur_stock->guid;
}

$pines->page->override_doc(json_encode(array('result' => $success, 'messages' => $messages)));

?>