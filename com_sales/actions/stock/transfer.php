<?php
/**
 * Provide a form to create a new transfer with stock.
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

if ( !gatekeeper('com_sales/managestock') )
	punt_user(null, pines_url('com_sales', 'stock/transfer', array('id' => $_REQUEST['id'])));

$list = explode(',', $_REQUEST['id']);

if (empty($list)) {
	pines_notice('No inventory specified for transfer!');
	return;
}

$entity = com_sales_transfer::factory();
$entity->origin = null;
if (is_array($list)) {
	foreach ($list as $cur_stock_guid) {
		$cur_stock = com_sales_stock::factory((int) $cur_stock_guid);
		if (isset($cur_stock->guid)) {
			$entity->products[] = $cur_stock->product;
			if (!isset($entity->origin)) {
				// Set the transfer origin.
				$entity->origin = $cur_stock->location;
			} elseif (!$entity->origin->is($cur_stock->location)) {
				// Two origins is not allowed.
				pines_notice('All inventory on a transfer must be from the same location.');
				return;
			}
		}
	}
}
$entity->print_form();

?>