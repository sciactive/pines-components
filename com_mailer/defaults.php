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
		'name' => 'unsubscribe_key',
		'cname' => 'Unsubscribe Key',
		'description' => 'This key is used to secure unsubscribe links. You should change it to something else.',
		'value' => 'oUVY&(VF&5F&%64d78g08b97U^FC864d$#5s7R^TYfuiyg*()&^g64%C79tvityF456dc',
		'peruser' => true,
	),
	array(
		'name' => 'unsubscribe_db',
		'cname' => 'Unsubscribe Database',
		'description' => 'The SQLite database file for unsubscribed users. This file shouldn\'t be accessible to the web.',
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'master_address',
		'cname' => 'Master Address',
		'description' => 'The master address receives all mails that don\'t have a recipient. This includes system information emails.',
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'from_address',
		'cname' => 'From Address',
		'description' => 'The address used when sending emails.',
		'value' => 'noreply@'.$_SERVER['SERVER_NAME'],
		'peruser' => true,
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
	array(
		'name' => 'additional_parameters',
		'cname' => 'Additional Parameters',
		'description' => 'If your emails are not being sent correctly, try removing this option.',
		'value' => '-femail@example.com',
		'peruser' => true,
	),
        array(
                'name' => 'sendgrid',
                'cname' => 'Send Emails via SendGrid',
                'description' => 'Enabling this will try to send emails via SendGrid',
                'value' => false,
        ),
        array(
                'name' => 'sendgrid_default_template',
                'cname' => 'Use SendGrid Default Template',
                'description' => 'When using SendGrid, default to the SendGrid template instead of default Pines.',
                'value' => false,
        ),
        array(
                'name' => 'sendgrid_url',
                'cname' => 'SendGrid API URL',
                'description' => 'The URL to POST to for SendGrid',
                'value' => 'https://api.sendgrid.com/api/mail.send.json'
        ),
        array(
                'name' => 'sendgrid_api_user',
                'cname' => 'SendGrid Username',
                'description' => 'Your SendGrid Account Username',
                'value' => ''
        ),
        array(
                'name' => 'sendgrid_api_key',
                'cname' => 'SendGrid Password',
                'description' => 'Your SendGrid Account Password',
                'value' => ''
        ),
        array(
                'name' => 'bypass_sendgrid_list',
                'cname' => 'Bypass SendGrid\'s List Management',
                'description' => 'This option is a SendGrid filter which will bypass unsubscribed and bounced emails',
                'value' => false
        ),
        array(
                'name' => 'domain_bypass',
                'cname' => 'Domain to bypass emails',
                'description' => 'The domain name to check to bypass SendGrid\'s List Management',
                'value' => ''
        ),
);

?>