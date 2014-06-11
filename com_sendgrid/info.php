<?php
/**
 * com_sendgrid's information.
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
	'name' => 'Sendgrid',
	'author' => 'Mohammed Ahmed',
	'version' => '1.0.0',
	'license' => 'none',
	'website' => 'http://www.smart108.com',
	'short_description' => 'Allow users to send emails via Sendgrid\'s API.',
	'description' => 'Allow users to send emails via Sendgrid\'s API. Requires a Sendgrid account.',
	'depend' => array(
		'pines' => '<3',
		'component' => ''
	),
);

?>