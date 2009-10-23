<?php
/**
 * Save changes to a customer.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_customer/managecustomers') ) {
    $config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_customer', 'listcustomers', null, false));
    return;
}

$config->run_customer->list_customers();
?>