<?php
defined('D_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_configure/list') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", $config->template->url('com_configure', 'list', null, false));
	return;
}

$com_configure_list = new module('com_configure', 'list', 'content');
$com_configure_list->title = 'Configuration';
$com_configure_list->content("<table width=\"100%\">\n<caption>Config Files</caption>\n");
$com_configure_list->content("<thead><tr><th>Component</th><th>Config File Location</th></tr></thead>\n<tbody>\n");
foreach ($config->configurator->config_files as $cur_component => $cur_location) {
	$com_configure_list->content("<tr><td>$cur_component</td><td>$cur_location</td></tr>\n");
}
$com_configure_list->content("</tbody>\n</table>\n");
//TODO: reimplement this configuration listing to show variables (using object iteration)
//TODO: design a way to define data types and metadata for configuration.
?>
