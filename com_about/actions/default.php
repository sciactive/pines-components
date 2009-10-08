<?php
/**
 * Display "about" information for the current installation.
 *
 * @package Pines
 * @subpackage com_about
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_about/show') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", $config->template->url('com_about', null, null, false));
	return;
}
$mod1 = new module('com_about', 'about1', 'content');
$mod1->title = "About ".$config->option_title." (Powered by ".$config->program_title.")";
if ( $config->com_about->describe_self ) {
	$mod2 = new module('com_about', 'about2', 'content');
	$mod2->title = "About ".$config->program_title;
}
?>