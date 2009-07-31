<?php
/**
 * com_newsletter's common file.
 *
 * @package Pines
 * @subpackage com_newsletter
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($config->ability_manager) ) {
	$config->ability_manager->add('com_newsletter', 'managemails', 'Manage Mails', 'Let users create, edit, and delete mailings.');
	$config->ability_manager->add('com_newsletter', 'send', 'Send', 'Let users send out mailings.');
}

?>