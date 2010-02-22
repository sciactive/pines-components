<?php
/**
 * com_about's common file.
 *
 * @package Pines
 * @subpackage com_about
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($pines->ability_manager) ) {
	$pines->ability_manager->add('com_about', 'show', 'About Page', 'User can see the about page.');
}

?>