<?php
/**
 * com_authorizenet's configuration defaults.
 *
 * @package Components\authorizenet
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'trans_name',
		'cname' => 'Transaction Name',
		'description' => 'The name that will appear on customers\' records.',
		'value' => $pines->config->system_name,
		'peruser' => true,
	),
	array(
		'name' => 'apilogin',
		'cname' => 'API Login',
		'description' => 'The API login Authorize.Net provided you.',
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'tran_key',
		'cname' => 'Transaction Key',
		'description' => 'The transaction key Authorize.Net provided you.',
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'post_url',
		'cname' => 'Post URL',
		'description' => 'The URL Pines will use to communicate with Authorize.Net.',
		'value' => 'https://secure.authorize.net/gateway/transact.dll',
		'peruser' => true,
	),
	array(
		'name' => 'merchant_type',
		'cname' => 'Merchant Type',
		'description' => 'The merchant type of the Authorize.Net account.',
		'value' => 'online',
		'options' => array(
			'Online' => 'online',
			'Retail' => 'retail'
		),
		'peruser' => true,
	),
	array(
		'name' => 'test_mode',
		'cname' => 'Test Mode',
		'description' => 'Enabling this will prevent payments from actually being processed.',
		'value' => true,
		'peruser' => true,
	),
);

?>