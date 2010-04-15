<?php
/**
 * Show the currently logged in user in the right of the header.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_SESSION['user']) && $pines->config->com_user->show_cur_user )
	$com_user__show_user = new module('com_user', 'show_user', 'header_right');

?>