<?php
defined('D_RUN') or die('Direct access prohibited');

if ( $_REQUEST['username'] ) {
	if ( gatekeeper() ) {
		display_error('Already logged in!');
		return;
	}
	if ( $id = $config->user_manager->authenticate($_REQUEST['username'], $_REQUEST['password']) ) {
		$config->user_manager->login($id);
		if ( !empty($_REQUEST['url']) ) {
			header('Location: '.urldecode($_REQUEST['url']));
			exit;
		} else {
			print_default();
		}
	} else {
		display_error("Username and password not correct!");
        $config->user_manager->print_login();
	}
} else {
	$config->user_manager->print_login();
}

?>