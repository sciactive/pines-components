<?php
/**
 * Remove a customer.
 *
 * @package Components\customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_customer/editcustomer') )
	punt_user(null, pines_url('com_customer', 'customer/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_customer) {
	$cur_entity = com_customer_customer::factory((int) $cur_customer);
	$cur_entity->remove_tag('com_customer', 'customer');
	if ( !isset($cur_entity->guid) || !$cur_entity->save() )
		$failed_removes .= (empty($failed_removes) ? '' : ', ').$cur_customer;
}
if (empty($failed_removes)) {
	pines_notice('Selected customer(s) removed successfully.');
} else {
	pines_error('Could not remove customers with given IDs: '.$failed_removes);
}

pines_redirect(pines_url('com_customer', 'customer/list'));

?>