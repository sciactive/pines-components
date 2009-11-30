<?php
/**
 * Retrieve the required options to send a newsletter.
 *
 * @package Pines
 * @subpackage com_newsletter
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_newsletter/send') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_newsletter', 'list', null, false));
	return;
}

$sendprep = new module('com_newsletter', 'sendprep', 'content');

if ( empty($_REQUEST['mail_id']) ) {
	display_error('Mail ID not valid!');
	return;
}

$mail = $config->entity_manager->get_entity($_REQUEST['mail_id'], array('com_newsletter', 'mail'));
if ( is_null($mail) ) {
	display_error('Invalid mail specified!');
	return;
}

$sendprep->mail = $mail;

?>