<?php
/**
 * com_authorizenet class.
 *
 * @package Pines
 * @subpackage com_authorizenet
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
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
	 * @param array &$array The argument array.
	 */
	function payment_credit(&$array) {
		global $pines;
		switch ($array['action']) {
			case 'request':
				$module = new module('com_authorizenet', 'form_payment');
				if ($array['sale']->customer->guid) {
					$module->name_first = $array['sale']->customer->name_first;
					$module->name_last = $array['sale']->customer->name_last;
					$module->address = $array['sale']->customer->address_1;
					$module->state = $array['sale']->customer->state;
					$module->zip = $array['sale']->customer->zip;
				}
				$pines->page->override_doc($module->render());
				break;
			case 'approve':
				$array['payment']['status'] = 'approved';
				if ($array['payment']['data']['card_swiped'] == 'ON') {
					if (empty($array['payment']['data']['name_last']) ||
						empty($array['payment']['data']['card_number']) ||
						empty($array['payment']['data']['card_exp_month']) ||
						empty($array['payment']['data']['card_exp_year']))
						$array['payment']['status'] = 'info_requested';
				} else {
					if (empty($array['payment']['data']['name_last']) ||
						empty($array['payment']['data']['card_number']) ||
						empty($array['payment']['data']['card_exp_month']) ||
						empty($array['payment']['data']['card_exp_year']) ||
						empty($array['payment']['data']['cid']))
						$array['payment']['status'] = 'info_requested';
				}
				break;
			case 'tender':
				$firstname = $array['payment']['data']['name_first'];
				$lastname = $array['payment']['data']['name_last'];
				$amt = (float) $array['payment']['amount'];
				$card_num = $array['payment']['data']['card_number'];
				$exp_date = $array['payment']['data']['card_exp_month'].$array['payment']['data']['card_exp_year'];
				//$address = $args['payment']['data']['address'];
				//$state = $args['payment']['data']['state'];
				//$zip = $args['payment']['data']['zip'];
				$card_code = $array['payment']['data']['cid'];
				// TODO: Find a better name for transactions.
				$invoice_num = $array['sale']->transaction_id;
				$transaction_name = $array['sale']->products[0]->entity->name;

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
					'x_invoice_num'		=> $invoice_num,
					'x_description'		=> $transaction_name,

					'x_first_name'		=> $firstname,
					'x_last_name'		=> $lastname,
					'x_address'			=> '', //$address,
					'x_state'			=> '', //$state,
					'x_zip'				=> '' //$zip
				);
				if ($array['payment']['data']['card_swiped'] != 'ON')
					$post_values['x_card_code'] = $card_code;
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
					pines_error('Credit processing gateway cannot be reached. Please try again, and if the problem persists, please contact an administrator.');
					break;
				}

				$response_array = explode($post_values["x_delim_char"],$post_response);
				$array['payment']['com_authorizenet_credit_info'] = array(
					'name_first'		=> $array['payment']['data']['name_first'],
					'name_last'			=> $array['payment']['data']['name_last'],
					//'address'			=> $args['payment']['data']['address'],
					//'state'			=> $args['payment']['data']['state'],
					//'zip'				=> $args['payment']['data']['zip'],
					'card_number'		=> substr($array['payment']['data']['card_number'], -4),
					'card_exp_month'	=> $array['payment']['data']['card_exp_month'],
					'card_exp_year'		=> $array['payment']['data']['card_exp_year'],
					'card_swiped'		=> $array['payment']['data']['card_swiped']
				);
				switch ($response_array[0]) {
					case 1:
						$array['payment']['status'] = 'tendered';
						$array['payment']['label'] = $this->card_type($array['payment']['data']['card_number']) . ' ' . substr($array['payment']['data']['card_number'], -4);
						unset($array['payment']['data']['name_first']);
						unset($array['payment']['data']['name_last']);
						//unset($args['payment']['data']['address']);
						//unset($args['payment']['data']['state']);
						//unset($args['payment']['data']['zip']);
						unset($array['payment']['data']['card_number']);
						unset($array['payment']['data']['card_exp_month']);
						unset($array['payment']['data']['card_exp_year']);
						unset($array['payment']['data']['cid']);
						break;
					case 2:
						$array['payment']['status'] = 'declined';
						$array['payment']['label'] = $this->card_type($array['payment']['data']['card_number']) . ' ' . substr($array['payment']['data']['card_number'], -4);
						unset($array['payment']['data']['name_first']);
						unset($array['payment']['data']['name_last']);
						//unset($args['payment']['data']['address']);
						//unset($args['payment']['data']['state']);
						//unset($args['payment']['data']['zip']);
						unset($array['payment']['data']['card_number']);
						unset($array['payment']['data']['card_exp_month']);
						unset($array['payment']['data']['card_exp_year']);
						unset($array['payment']['data']['cid']);
						break;
					case 3:
						$array['payment']['status'] = 'info_requested';
						break;
					case 4:
						pines_notice('Payment is held for review.');
						break;
					default:
						$array['payment']['status'] = 'pending';
						pines_error('Credit processing failed. Please try again, and if the problem persists, please contact an administrator.');
						break;
				}
				$array['payment']['com_authorizenet_credit_info']['transaction_id'] = $reponse_array[8];
				pines_notice($response_array[3]);
				break;
			case 'void':
				$post_values = array(
					// the API Login ID and Transaction Key must be replaced with valid values
					'x_login'			=> $pines->config->com_authorizenet->apilogin,
					'x_tran_key'		=> $pines->config->com_authorizenet->tran_key,
					'x_test_request'	=> ($pines->config->com_authorizenet->test_mode) ? 'TRUE' : 'FALSE',

					'x_version'			=> '3.1',
					'x_delim_data'		=> 'TRUE',
					'x_delim_char'		=> '|',
					'x_relay_response'	=> 'FALSE',

					'x_type'			=> 'VOID',
					'x_trans_id'		=> $array['payment']['com_authorizenet_credit_info']['transaction_id'],
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
					pines_error('Credit processing gateway cannot be reached. Please try again, and if the problem persists, please contact an administrator.');
					break;
				}

				$response_array = explode($post_values["x_delim_char"],$post_response);
				switch ($response_array[0]) {
					case 1:
						$array['payment']['status'] = 'voided';
						break;
					case 2:
						pines_notice('Payment void was declined.');
						break;
					case 3:
						pines_notice('Payment void required more information.');
						break;
					case 4:
						pines_notice('Payment void is being held for review.');
						break;
					default:
						pines_notice('Void processing failed. Please try again, and if the problem persists, please contact an administrator.');
						break;
				}
				pines_notice($response_array[3]);
				break;
		}
	}
}

?>