<?php
/**
 * com_mailer's configuration defaults.
 *
 * @package Components\mailer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'additional_parameters',
		'cname' => 'Additional Parameters',
		'description' => 'If your emails are not being sent correctly, try removing this option.',
		'value' => '-femail@example.com',
	),
	array(
		'name' => 'testing_mode',
		'cname' => 'Testing Mode',
		'description' => 'In testing mode, emails are not actually sent.',
		'value' => false,
	),
	array(
		'name' => 'testing_email',
		'cname' => 'Testing Email',
		'description' => 'In testing mode, if this is not empty, all emails are sent here instead. "*Test* " is prepended to their subject line.',
		'value' => '',
	),
);

?>