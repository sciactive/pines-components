<?php
/**
 * List all the newsletters.
 *
 * @package Dandelion
 * @subpackage com_newsletter
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('D_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_newsletter/managemails') && !gatekeeper('com_newsletter/send') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", $config->template->url('com_newsletter', 'list', null, false));
	return;
}

com_newsletter::list_mails('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', '');
?>
