<?php
/**
 * Add a customer.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * @TODO Write a form to use this action. Set any customer defaults.
 */

if ( !gatekeeper('com_customer/editcustomer') || !gatekeeper('com_user/edituser') )
	punt_user(null, pines_url('com_customer', 'customer/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_user) {
	$cur_entity = user::factory((int) $cur_user);
	$cur_entity->add_tag('com_customer', 'customer');
	if ( !isset($cur_entity->guid) || !$cur_entity->save() )
		$failed_adds .= (empty($failed_adds) ? '' : ', ').$cur_user;
}
if (empty($failed_adds)) {
	pines_notice('Selected user(s) added as customer(s) successfully.');
} else {
	pines_error('Could not add users with given IDs: '.$failed_adds);
}

pines_redirect(pines_url('com_customer', 'customer/list'));

?>