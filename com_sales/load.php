<?php
/**
 * com_sales's loader.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$config->run_sales = new com_sales;

$config->run_sales->product_actions[] = array(
	'type' => 'sold',
	'name' => 'com_sales/test',
	'cname' => 'Testing',
	'description' => 'This is only used for testing.',
	'callback' => array($config->run_sales, 'test_action')
);

?>