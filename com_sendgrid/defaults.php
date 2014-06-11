<?php
/**
 * com_sendgrid's defaults.
 *
 * @package com_sendgrid
 * @license none
 * @author Mohammed Ahmed <mohammedsadikahmed@gmail.com>
 * @copyright Smart Industries, LLC
 * @link http://smart108.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');
return array(
	array(
		'name' => 'api_user',
		'cname' => 'Sendgrid API Username',
		'description' => 'The username to use for accessing Sendgrid.',
		'value' => '',
	),
	array(
		'name' => 'api_password',
		'cname' => 'Sendgrid API Password',
		'description' => 'The password to use for accessing Sendgrid',
		'value' => '',
	),
);

?>