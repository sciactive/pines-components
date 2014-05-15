<?php
/**
 * com_gae_chat's defaults.
 *
 * @package Components\gae_chat
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Mohammed Ahmed <mohammedsadikahmed@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
    array(
        'name' => 'application_id',
        'cname' => 'Application ID',
        'description' => 'The application id of your Google App Engine project.',
        'value' => '',
    ),
    array(
        'name' => 'channels_js_url',
        'cname' => 'The Channels JS from App Engine',
        'description' => 'The url of Google\'s Channels JS file hosted on your App Engine account',
        'value' => '',
    ),
    array(
        'name' => 'employee_auth',
        'cname' => 'Employee Secret Auth Token',
        'description' => 'The token for which employees get employee status on App Engine',
        'value' => 'mohammed',
    ),
    array(
        'name' => 'chat_disabled',
        'cname' => 'Disabled Chat',
        'description' => 'Enable or disable GAE Chat',
        'value' => true,
    ),
    array(
        'name' => 'password',
        'cname' => 'App Engine Password for Chat',
        'description' => 'The password to use to retrieve tokens and communicate with App Engine',
        'value' => ''
    ),
    array(
        'name' => 'token_url',
        'cname' => 'App Engine URL to get tokens',
        'description' => 'The URL to POST to get channel tokens for chat',
        'value' => ''
    ),
    array(
        'name'  => 'domain_specific',
        'cname' => 'Specific domains to disable chat',
        'description'   => 'Disable Chat for (a) specific domain(s)',
        'value' => false
    ),
    array(
        'name'  => 'disabled_chat_domain',
        'cname' => 'A domain to disable chat',
        'description'   => 'Specify a domain to disable chat on to prevent non-logged guests from getting channels',
        'value' => ''
    ),
    array(
        'name'  => 'renew_token_url',
        'cname' => 'Renew Chat Token URL',
        'description'   => 'The url to renew chat tokens (For non-logged in users to perserve chat)',
        'value' => ''
    ),
    array(
        'name'  => 'message_url',
        'cname' => 'Send Message URL',
        'description'   => 'The url to POST chat messages to',
        'value' => ''
    ),
    array(
        'name'  => 'pines_token_url',
        'cname' => 'URL to get a channels token',
        'description'   => 'The url to POST to get a token',
        'value' => pines_url('com_gae_chat', 'chat')
    ),
    array(
        'name'  => 'ping_test_url',
        'cname' => 'Send Ping Check URL',
        'description'   => 'The url to POST to send an online check to a customer',
        'value' => ''
    ),
    array(
        'name'  => 'online_check_url',
        'cname' => 'Chat Online Check URL',
        'description'   => 'The url to POST to confirm if a user is still on their channel',
        'value' => ''
    ),
    array(
        'name'  => 'customer_pic',
        'cname' => 'The Customer Pic URL',
        'description'   => 'The url of the customer picture for chat',
        'value' => 'components/com_gae_chat/includes/customer.jpg'
    ),
    array(
        'name'  => 'employee_pic',
        'cname' => 'The Employee Pic URL',
        'description'   => 'The url of the employee picture for chat',
        'value' => 'components/com_gae_chat/includes/employee.jpg'
    ),
    array(
        'name'  => 'html_position',
        'cname' => 'The positioning of the chat html',
        'description'   => 'The module position for the chat html',
        'value' => 'bottom'
    ),
);

?>