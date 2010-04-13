<?php
/**
 * Add abilities.
 *
 * @package Pines
 * @subpackage com_newsletter
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($pines->ability_manager) ) {
	$pines->ability_manager->add('com_newsletter', 'listmail', 'List Mail', 'Let users view the mailbox.');
	$pines->ability_manager->add('com_newsletter', 'send', 'Send', 'Let users send out mailings.');
}

?>