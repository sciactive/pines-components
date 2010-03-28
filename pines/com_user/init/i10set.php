<?php
/**
 * Set the system user and ability managers.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
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
 * @global abilities $pines->ability_manager
 */
$pines->ability_manager = 'abilities';

?>