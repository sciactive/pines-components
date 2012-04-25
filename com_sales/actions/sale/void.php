<?php
/**
 * Void a sale.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/voidsale') && !gatekeeper('com_sales/voidownsale') )
	punt_user(null, pines_url('com_sales', 'sale/void', array('id' => $_REQUEST['id'])));

$entity = com_sales_sale::factory((int) $_REQUEST['id']);

if (!isset($entity->guid)) {
	pines_notice('The given ID could not be found.');
	pines_redirect(pines_url('com_sales', 'sale/list'));
	return;
}

// If they don't have com_sales/voidsale, then they only have com_sales/voidownsale.
if ( !gatekeeper('com_sales/voidsale') && !$_SESSION['user']->is($entity->user) ) {
	pines_notice('You can only void your own sales.');
} else {
	if ($entity->void() && $entity->save()) {
		pines_notice('The sale has been voided.');
	} elseif ($entity->save()) {
		pines_notice('The sale could not be voided.');
	} else {
		pines_notice('The sale could not be edited. Do you have permission?');
	}
}

pines_redirect(pines_url('com_sales', 'sale/receipt', array('id' => $entity->guid)));

?>