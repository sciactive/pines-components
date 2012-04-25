<?php
/**
 * Set the system user manager.
 *
 * @package Components
 * @subpackage user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * The user manager.
 * @global com_user $pines->user_manager
 */
$pines->user_manager = 'com_user';

?>