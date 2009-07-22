<?php
defined('X_RUN') or die('Direct access prohibited');

if ( !gatekeeper() ) {
    $config->user_manager->print_login();
    return;
} else {
    if (empty($_SESSION['user']->default_component) || $_SESSION['user']->default_component == 'com_user') {
        $config->user_manager->list_users();
    } else {
        if (file_exists('components/'.$_SESSION['user']->default_component.'/actions/default.php')) {
            require('components/'.$_SESSION['user']->default_component.'/actions/default.php');
        } else {
            display_error("Action not defined! D:");
        }
    }
}

?>