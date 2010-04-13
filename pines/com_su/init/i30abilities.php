<?php
/**
 * Add abilities.
 *
 * @package Pines
 * @subpackage com_su
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$pines->ability_manager->add('com_su', 'nopassword', 'Switch Without Password', 'User can switch to any other user without providing a password.');

?>