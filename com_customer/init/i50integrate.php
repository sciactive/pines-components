<?php
/**
 * Determine whether to integrate with com_calendar.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ($pines->config->com_customer->com_calendar && !$pines->depend->check('component', 'com_calendar'))
	$pines->config->com_customer->com_calendar = false;

?>