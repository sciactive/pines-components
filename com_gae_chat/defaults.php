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

$base_link = pines_url('com_customer', 'customer/edit');
$symbol = (preg_match('#\?#', $base_link)) ? '&' : '?';
$base_link = $base_link.$symbol.'id=';

return array(
    array(
        'name' => 'application_id',
        'cname' => 'Application ID',
        'description' => 'The application id of your Google App Engine project.',
        'value' => '',
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
    array(
        'name'  => 'disabled_ips',
        'cname' => 'List of IPs to disable chat on',
        'description'   => 'A CSV of IPs to block from accessing gae_chat',
        'value' => ''
    ),
    array(
        'name'  => 'distinguish_employees',
        'cname' => 'Distinguish when employees connect as guests',
        'description'   => 'Whether to distinguish guests who originate from the corporate ip',
        'value' => false
    ),
    array(
        'name'  => 'corporate_ip',
        'cname' => 'The Corporate IP address of employees',
        'description'   => 'The corporate IP address of the employees. Used to distinguish guests from this ip',
        'value' => ''
    ),
    array(
        'name'  => 'get_messages_url',
        'cname' => 'The URL for customers to get their messages',
        'description'   => 'The chat url for users to retrieve their message history from App Engine',
        'value' => ''
    ),
    array(
        'name'  => 'user_link_type',
        'cname' => 'User Link Type',
        'description'   => 'The username link on chat windows.',
        'options' => array(
            'Use a constructed url' => 'constructed_url',
            'Use Mail To' => 'mail_to',
            'No Link'   => 'no_link'
        ),
        'value' => 'constructed_url'
    ),
    array(
        'name'  => 'user_link_class',
        'cname' => 'User Link Class',
        'description'   => 'The class from which to factory the user to obtain a variable',
        'value' => 'com_customer_customer'
    ),
    array(
        'name'  => 'user_link_variable',
        'cname' => 'User Link Variable',
        'description'   => 'The variable on the class to use in the link',
        'value' => 'guid'
    ),
    array(
        'name'  => 'user_link_base',
        'cname' => 'User Link Base',
        'description'   => 'The base of the link which we will append the variable to',
        'value' => $base_link
    ),
    array(
        'name'  => 'refresh_online_users_url',
        'cname' => 'URL to get an updated online list',
        'description'   => 'The URL to refresh the customer list',
        'value' => ''
    ),
    array(
        'name'  => 'ping_all_customers_url',
        'cname' => 'URL to ping all customers',
        'description'   => 'The URL to check every customer if they are still online.',
        'value' => ''
    ),
    array(
        'name'  => 'get_customer_messages_url',
        'cname' => 'URL to get a customer\'s message as an employee',
        'description'   => 'The URL for employees to GET a customer\'s message history',
        'value' => ''
    ),
    array(
        'name'  => 'get_users_and_messages_url',
        'cname' => 'URL to get all online users and their chat histories',
        'description'   => 'The URL for employees to all online users and their chat histories. For when employees connect to channels',
        'value' => ''
    ),
);

?>