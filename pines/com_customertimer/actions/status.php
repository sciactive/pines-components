<?php
/**
 * See logged in customers and their status.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_customertimer/viewstatus') )
	punt_user('You don\'t have necessary permission.', pines_url('com_customertimer', 'status', null, false));

$module = new module('com_customertimer', 'status', 'content');
?>