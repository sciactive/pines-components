<?php
/**
 * Load the customer status checker.
 *
 * @package Pines
 * @subpackage com_customer_timer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (gatekeeper('com_customer_timer/viewstatus'))
	$com_customer_timer_module = new module('com_customer_timer', 'status_check', 'head');

?>