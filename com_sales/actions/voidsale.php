<?php
/**
 * Void a sale.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/voidsale') )
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'voidsale', array('id' => $_REQUEST['id'])));

$entity = com_sales_sale::factory((int) $_REQUEST['id']);
if ($entity->void() && $entity->save()) {
	pines_notice('The sale has been voided.');
} else {
	pines_notice('The sale could not be voided.');
}

redirect(pines_url('com_sales', 'listsales'));

?>