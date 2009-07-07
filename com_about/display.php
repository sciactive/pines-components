<?php
defined('D_RUN') or die('Direct access prohibited');

if ( gatekeeper() ) $page->main_menu->add('About', $config->template->url('com_about'));

?>