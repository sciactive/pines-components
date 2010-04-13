<?php
/**
 * Set the system user manager and ability manager.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * The user manager.
 * @global com_user $pines->user_manager
 */
$pines->user_manager = 'com_user';

/**
 * The ability manager.
 * @global com_user_abilities $pines->ability_manager
 */
$pines->ability_manager = 'com_user_abilities';

?>