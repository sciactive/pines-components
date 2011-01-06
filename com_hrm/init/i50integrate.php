<?php
/**
 * Determine whether to integrate with com_sales.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ($pines->config->com_hrm->com_sales && !$pines->depend->check('component', 'com_sales'))
	$pines->config->com_hrm->com_sales = false;

if ($pines->config->com_hrm->com_calendar && !$pines->depend->check('component', 'com_calendar'))
	$pines->config->com_hrm->com_calendar = false;

?>