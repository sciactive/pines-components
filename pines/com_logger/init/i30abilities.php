<?php
/**
 * Add abilities.
 *
 * @package Pines
 * @subpackage com_logger
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($pines->ability_manager) ) {
	$pines->ability_manager->add('com_logger', 'view', 'View Log', 'Let the user view the Pines log.');
	$pines->ability_manager->add('com_logger', 'clear', 'Clear Log', 'Let the user clear (delete) the pines log.');
}

?>