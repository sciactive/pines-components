<?php
/**
 * com_authorizenet's configuration.
 *
 * @package Pines
 * @subpackage com_authorizenet
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array (
  0 =>
  array (
	'name' => 'apilogin',
	'cname' => 'API Login',
	'description' => 'The API login Authorize.Net provided you.',
	'value' => '',
  ),
  1 =>
  array (
	'name' => 'tran_key',
	'cname' => 'Transaction Key',
	'description' => 'The transaction key Authorize.Net provided you.',
	'value' => '',
  ),
  2 =>
  array (
	'name' => 'post_url',
	'cname' => 'Post URL',
	'description' => 'The URL Pines will use to communicate with Authorize.Net.',
	'value' => 'https://secure.authorize.net/gateway/transact.dll',
  ),
  3 =>
  array (
	'name' => 'test_mode',
	'cname' => 'Test Mode',
	'description' => 'Enabled this will prevent payments from actually being processed.',
	'value' => true,
  ),
);

?>