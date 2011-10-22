<?php
/**
 * com_user's modules.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'login' => array(
		'cname' => 'User Login Form',
		'view' => 'modules/login',
		'form' => 'modules/login_form',
	),
	'current_user' => array(
		'cname' => 'Current User Display',
		'view' => 'modules/current_user',
		'form' => 'modules/current_user_form',
	),
);

?>