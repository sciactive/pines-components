<?php
/**
 * com_authorizenet class.
 *
 * @package Pines
 * @subpackage com_authorizenet
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_authorizenet main class.
 *
 * Process sales through Authorize.Net.
 *
 * @package Pines
 * @subpackage com_authorizenet
 */
class com_authorizenet extends component {
	/**
	 * Process a payment.
	 *
	 * @param array $args The argument array.
	 */
	function payment_credit($args) {
		global $config, $page;
		switch ($args['action']) {
			case 'request':
				$module = new module('com_authorizenet', 'form_payment');
				if ($args['sale']->customer->guid) {
					$module->name_first = $args['sale']->customer->name_first;
					$module->name_last = $args['sale']->customer->name_last;
					$module->address = $args['sale']->customer->address_1;
					$module->state = $args['sale']->customer->state;
					$module->zip = $args['sale']->customer->zip;
				}
				$page->override_doc($module->render());
				break;
			case 'approve':
				$args['payment']['status'] = 'pending';
				//$args['payment']['data']['name_first'];
				//$args['payment']['status'] = 'declined';
				//$args['payment']['status'] = 'approved';
				break;
			case 'tender':
				$args['payment']['status'] = 'pending';
				//$args['payment']['status'] = 'declined';
				//$args['payment']['status'] = 'tendered';
				break;
		}
	}
}

?>