<?php
/**
 * Provide a login page.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!isset($_SESSION['user']) && $pines->config->com_user->print_login != 'null')
	$pines->user_manager->print_login($pines->config->com_user->print_login);

?>