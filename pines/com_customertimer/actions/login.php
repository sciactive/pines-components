<?php
/**
 * List customers.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_customertimer/login') && !$pines->config->com_customertimer->openlogin )
	punt_user('You don\'t have necessary permission.', pines_url('com_customertimer', 'login', null, false));

if (isset($_REQUEST['id'])) {
	$id = (int) $_REQUEST['id'];
	$pines->com_customertimer->login_logout($id, $_REQUEST['password']);
}

$module = new module('com_customertimer', 'login', 'content');
?>