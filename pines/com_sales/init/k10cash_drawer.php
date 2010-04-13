<?php
/**
 * Include the cash drawer JavaScript.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ($pines->config->com_sales->cash_drawer &&
		(
			$pines->config->com_sales->cash_drawer_group == 0 ||
			(isset($_SESSION['user']) && $_SESSION['user']->in_group($pines->config->com_sales->cash_drawer_group))
		)
	)
	$com_sales_cash_drawer = new module('com_sales', 'cash_drawer', 'head');

?>