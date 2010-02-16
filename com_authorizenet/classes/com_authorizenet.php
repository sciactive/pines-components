<?php
/**
 * com_authorizenet class.
 *
 * @package Pines
 * @subpackage com_authorizenet
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
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
	 * Determine the type of credit card that is being charged.
	 *
	 * @param string $card_num The credit card number.
	 * @return string The credit card type.
	 */
	function card_type($card_num) {
		$prefix = substr($card_num, 0, 1);
		if ($prefix == '4') {
			return 'VISA';
		} else {
			$prefix = substr($card_num, 0, 2);
			if ($prefix == '30' || $prefix == '36' || $prefix == '38') {
				return 'DC';
			} else if ($prefix == '34' || $prefix == '37') {
				return 'AMEX';
			} else if ($prefix == '35') {
				return 'JCB';
			} else if ($prefix == '51' || $prefix == '52' || $prefix == '53' || $prefix == '54' || $prefix == '55') {
				return 'MC';
			} else if ($prefix == '64' || $prefix == '65') {
				return 'DISC';
			} else {
				$prefix = substr($card_num, 0, 4);
				if ($prefix == '6011') {
					return 'DISC';
				} else if ($prefix == '5610' || $prefix == '5602') {
					return 'BC';
				}
			}
		}
		return 'OTHER';
	}
	
	/**
	 * Process a payment.
	 *
	 * @param array $args The argument array.
	 */
	function payment_credit($args) {
		global $pines;
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
				$pines->page->override_doc($module->render());
				break;
			case 'approve':
				$args['payment']['status'] = 'approved';
				if ($args['payment']['data']['card_swiped'] == 'ON') {
					if (empty($args['payment']['data']['name_first']) ||
						empty($args['payment']['data']['name_last']) ||
						empty($args['payment']['data']['card_number']) ||
						empty($args['payment']['data']['card_exp_month']) ||
						empty($args['payment']['data']['card_exp_year']))
						$args['payment']['status'] = 'info_requested';
				} else {
					if (empty($args['payment']['data']['name_first']) ||
						empty($args['payment']['data']['name_last']) ||
						empty($args['payment']['data']['card_number']) ||
						empty($args['payment']['data']['card_exp_month']) ||
						empty($args['payment']['data']['card_exp_year']) ||
						empty($args['payment']['data']['cid']))
						$args['payment']['status'] = 'info_requested';
				}
				break;
			case 'tender':
				$firstname = $args['payment']['data']['name_first'];
				$lastname = $args['payment']['data']['name_last'];
				$amt = (float) $args['payment']['amount'];
				$card_num = $args['payment']['data']['card_number'];
				$exp_date = $args['payment']['data']['card_exp_month'].$args['payment']['data']['card_exp_year'];
				$address = $args['payment']['data']['address'];
				$state = $args['payment']['data']['state'];
				$zip = $args['payment']['data']['zip'];

				$post_values = array(
					// the API Login ID and Transaction Key must be replaced with valid values
					'x_login'			=> $pines->config->com_authorizenet->apilogin,
					'x_tran_key'		=> $pines->config->com_authorizenet->tran_key,
					'x_test_request'	=> ($pines->config->com_authorizenet->test_mode) ? 'TRUE' : 'FALSE',

					'x_version'			=> '3.1',
					'x_delim_data'		=> 'TRUE',
					'x_delim_char'		=> '|',
					'x_relay_response'	=> 'FALSE',

					'x_type'			=> 'AUTH_CAPTURE',
					'x_method'			=> 'CC',
					'x_card_num'		=> $card_num,
					'x_exp_date'		=> $exp_date,

					'x_amount'			=> $amt,
					'x_description'		=> $transaction_name,

					'x_first_name'		=> $firstname,
					'x_last_name'		=> $lastname,
					'x_address'			=> $address,
					'x_state'			=> $state,
					'x_zip'				=> $zip
				);
				$post_string = "";
				foreach ($post_values as $key => $value) {
					$post_string .= "$key=" . urlencode($value) . "&";
				}
				$post_string = rtrim($post_string, "& ");

				$request = curl_init($pines->config->com_authorizenet->post_url);
				curl_setopt($request, CURLOPT_HEADER, 0);
				curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($request, CURLOPT_POSTFIELDS, $post_string);
				curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE);
				$post_response = curl_exec($request);
				curl_close($request);

				if ($post_response === false) {
					display_error('Credit processing gateway cannot be reached. Please try again, and if the problem persists, please contact an administrator.');
					break;
				}

				$response_array = explode($post_values["x_delim_char"],$post_response);
				$args['payment']['com_authorizenet_credit_info'] = array();
				$args['payment']['com_authorizenet_credit_info']['name_first'] = $args['payment']['data']['name_first'];
				$args['payment']['com_authorizenet_credit_info']['name_last'] = $args['payment']['data']['name_last'];
				$args['payment']['com_authorizenet_credit_info']['address'] = $args['payment']['data']['address'];
				$args['payment']['com_authorizenet_credit_info']['state'] = $args['payment']['data']['state'];
				$args['payment']['com_authorizenet_credit_info']['zip'] = $args['payment']['data']['zip'];
				$args['payment']['com_authorizenet_credit_info']['card_number'] = $args['payment']['data']['card_number'];
				$args['payment']['com_authorizenet_credit_info']['card_exp_month'] = $args['payment']['data']['card_exp_month'];
				$args['payment']['com_authorizenet_credit_info']['card_exp_year'] = $args['payment']['data']['card_exp_year'];
				switch ($response_array[0]) {
					case 1:
						$args['payment']['status'] = 'tendered';
						$args['payment']['label'] = $this->card_type($args['payment']['data']['card_number']) . ' ' . substr($args['payment']['data']['card_number'], -4, 4);
						unset($args['payment']['data']['name_first']);
						unset($args['payment']['data']['name_last']);
						unset($args['payment']['data']['address']);
						unset($args['payment']['data']['state']);
						unset($args['payment']['data']['zip']);
						unset($args['payment']['data']['card_number']);
						unset($args['payment']['data']['card_exp_month']);
						unset($args['payment']['data']['card_exp_year']);
						unset($args['payment']['data']['cid']);
						break;
					case 2:
						$args['payment']['status'] = 'declined';
						$args['payment']['label'] = $this->card_type($args['payment']['data']['card_number']) . ' ' . substr($args['payment']['data']['card_number'], -4, 4);
						unset($args['payment']['data']['name_first']);
						unset($args['payment']['data']['name_last']);
						unset($args['payment']['data']['address']);
						unset($args['payment']['data']['state']);
						unset($args['payment']['data']['zip']);
						unset($args['payment']['data']['card_number']);
						unset($args['payment']['data']['card_exp_month']);
						unset($args['payment']['data']['card_exp_year']);
						unset($args['payment']['data']['cid']);
						break;
					case 3:
						$args['payment']['status'] = 'info_requested';
						break;
					case 4:
						display_notice('Payment is held for review.');
						break;
					default:
						$args['payment']['status'] = 'pending';
						display_error('Credit processing failed. Please try again, and if the problem persists, please contact an administrator.');
						break;
				}
				display_notice($response_array[3]);
				break;
		}
	}
}

?>