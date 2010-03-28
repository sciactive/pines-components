<?php
/**
 * Load the customer status checker.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (gatekeeper('com_customertimer/viewstatus') && !gatekeeper('com_customertimer/ignorestatus'))
	$com_customertimer_module = new module('com_customertimer', 'status_check', 'head');

?>