<?php
/**
 * Load the clockin module.
 *
 * @package Pines
 * @subpackage com_fortune
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ($pines->config->com_fortune->show)
	$pines->com_fortune->print_fortune();

?>