<?php
defined('D_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_user/edit') && (!gatekeeper('com_user/self') || ($_REQUEST['user_id'] != $_SESSION['user_id'])) ) {
    $config->user_manager->punt_user("You don't have necessary permission.", $config->template->url('com_user', 'manageusers', null, false));
    return;
}

$config->user_manager->print_user_form('Editing ['.$config->user_manager->get_username($_REQUEST['user_id']).']', 'com_user', 'saveuser', $_REQUEST['user_id']);
?>