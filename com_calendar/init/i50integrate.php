<?php
/**
 * Determine whether to integrate with com_customer.
 *
 * @package Pines
 * @subpackage com_calendar
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ($pines->config->com_calendar->com_customer && !$pines->depend->check('component', 'com_customer'))
	$pines->config->com_calendar->com_customer = false;

?>