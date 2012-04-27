<?php
/**
 * com_sales' buttons.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'sales' => array(
		'description' => 'Sale list.',
		'text' => 'Sales',
		'class' => 'picon-document-multiple',
		'href' => pines_url('com_sales', 'sale/list'),
		'default' => true,
		'depends' => array(
			'ability' => 'com_sales/listsales',
		),
	),
	'sale_new' => array(
		'description' => 'New sale.',
		'text' => 'Sale',
		'class' => 'picon-document-new',
		'href' => pines_url('com_sales', 'sale/edit'),
		'default' => true,
		'depends' => array(
			'ability' => 'com_sales/newsale',
		),
	),
	'returns' => array(
		'description' => 'Return list.',
		'text' => 'Returns',
		'class' => 'picon-view-documents-finances',
		'href' => pines_url('com_sales', 'return/list'),
		'default' => false,
		'depends' => array(
			'ability' => 'com_sales/listreturns',
		),
	),
	'return_new' => array(
		'description' => 'New return.',
		'text' => 'Return',
		'class' => 'picon-view-financial-list',
		'href' => pines_url('com_sales', 'return/edit'),
		'default' => false,
		'depends' => array(
			'ability' => 'com_sales/newreturn',
		),
	),
	'countsheets' => array(
		'description' => 'Countsheet list.',
		'text' => 'Countsheets',
		'class' => 'picon-view-task',
		'href' => pines_url('com_sales', 'countsheet/list'),
		'default' => true,
		'depends' => array(
			'ability' => 'com_sales/listcountsheets',
		),
	),
	'countsheet_new' => array(
		'description' => 'New countsheet.',
		'text' => 'Countsheet',
		'class' => 'picon-view-task-add',
		'href' => pines_url('com_sales', 'countsheet/edit'),
		'default' => true,
		'depends' => array(
			'ability' => 'com_sales/newcountsheet',
		),
	),
	'producttracking' => array(
		'description' => 'Product tracking.',
		'text' => 'Track Prod',
		'class' => 'picon-edit-find-project',
		'href' => pines_url('com_sales', 'product/track'),
		'default' => false,
		'depends' => array(
			'ability' => 'com_sales/trackproducts',
		),
	),
	'receive' => array(
		'description' => 'Receive inventory.',
		'text' => 'Receive',
		'class' => 'picon-mail-receive',
		'href' => pines_url('com_sales', 'stock/receive'),
		'default' => true,
		'depends' => array(
			'ability' => 'com_sales/receive',
		),
	),
	'transfers' => array(
		'description' => 'Transfer list.',
		'text' => 'Transfers',
		'class' => 'picon-document-export',
		'href' => pines_url('com_sales', 'transfer/list'),
		'default' => false,
		'depends' => array(
			'ability' => 'com_sales/managestock|com_sales/shipstock',
		),
	),
	'pos' => array(
		'description' => 'PO list.',
		'text' => 'POs',
		'class' => 'picon-resource-calendar-child',
		'href' => pines_url('com_sales', 'po/list'),
		'default' => false,
		'depends' => array(
			'ability' => 'com_sales/listpos',
		),
	),
	'po_new' => array(
		'description' => 'New PO.',
		'text' => 'PO',
		'class' => 'picon-resource-calendar-child-insert',
		'href' => pines_url('com_sales', 'po/edit'),
		'default' => false,
		'depends' => array(
			'ability' => 'com_sales/newpo',
		),
	),
	'pending' => array(
		'description' => 'Pending order list.',
		'text' => 'Pending',
		'class' => 'picon-task-ongoing',
		'href' => pines_url('com_sales', 'warehouse/pending'),
		'default' => true,
		'depends' => array(
			'ability' => 'com_sales/warehouse|com_sales/viewwarehouse',
		),
	),
	'assigned' => array(
		'description' => 'Assigned order list.',
		'text' => 'Assigned',
		'class' => 'picon-task-accepted',
		'href' => pines_url('com_sales', 'warehouse/assigned'),
		'default' => false,
		'depends' => array(
			'ability' => 'com_sales/warehouse',
		),
	),
	'products' => array(
		'description' => 'Product list.',
		'text' => 'Products',
		'class' => 'picon-view-barcode',
		'href' => pines_url('com_sales', 'product/list'),
		'default' => false,
		'depends' => array(
			'ability' => 'com_sales/listproducts',
		),
	),
	'product_new' => array(
		'description' => 'New product.',
		'text' => 'Product',
		'class' => 'picon-view-barcode-add',
		'href' => pines_url('com_sales', 'product/edit'),
		'default' => false,
		'depends' => array(
			'ability' => 'com_sales/newproduct',
		),
	),
);

?>