<?php
/**
 * Determine whether to integrate with com_sales.
 *
 * @package Components
 * @subpackage hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ($pines->config->com_hrm->com_sales && !$pines->depend->check('component', 'com_sales'))
	$pines->config->com_hrm->com_sales = false;

if ($pines->config->com_hrm->com_calendar && !$pines->depend->check('component', 'com_calendar'))
	$pines->config->com_hrm->com_calendar = false;

if ($pines->config->com_hrm->com_reports && !$pines->depend->check('component', 'com_reports'))
	$pines->config->com_hrm->com_reports = false;

?>