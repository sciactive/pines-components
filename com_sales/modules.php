<?php
/**
 * com_sales' modules.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'stockserialsearch' => array(
		'cname' => 'Stock Serial Search',
		'description' => 'Search for serialized stock.',
		'image' => 'includes/stock_serial_widget_screen.png',
		'view' => 'modules/serialsearch',
		'type' => 'module widget',
		'widget' => array(
			'default' => false,
			'depends' => array(
				'ability' => 'com_sales/seestock',
			),
		),
	),
);

?>