<?php
/**
 * Load Chat JS
 *
 * @package Components\com_gae_chat
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Mohammed Ahmed <mohammed.ahmed@108way.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');
// Load the html first so that when we register our listeners, they can be found and binded properly

if ($pines->config->com_gae_chat->chat_disabled) {
    return;
}

$employee = isset($_SESSION['user']) && $_SESSION['user']->employee;

// This is basically a check to make sure we don't give non-logged in users a channel if they are on the employee backend site
if ($pines->config->com_gae_chat->domain_specific && preg_match('#'.$pines->config->com_gae_chat->disabled_chat_domain.'#', $_SERVER['SERVER_NAME']) && !$employee) {
    // We have a disabled domain and we have a non-logged in user, so return out of here
    return;
}

if ($employee && gatekeeper('com_gae_chat/employeechat')) {
        $module = new module('com_gae_chat', 'chat_html', 'bottom');
        unset($module);
        $pines->com_timeago->load();
        $pines->com_gae_chat->load_employee_js();
} elseif (!$employee) {
        $module = new module('com_gae_chat', 'chat_html', 'bottom');
        unset($module);
        $pines->com_timeago->load();
        $pines->com_gae_chat->load_customer_js();
}
?>