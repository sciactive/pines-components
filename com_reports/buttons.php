<?php
/**
 * com_reports' buttons.
 *
 * @package Components\reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'rankings' => array(
		'description' => 'Latest sales rankings.',
		'text' => 'Ranking',
		'class' => 'picon-office-chart-area-percentage',
		'href' => pines_url('com_reports', 'viewsalesranking', array('id' => 'latest')),
		'default' => true,
		'depends' => array(
			'ability' => 'com_reports/viewsalesranking',
		),
	),
	'invoice_summary' => array(
		'description' => 'Shows a list of all sales, returns and voids.',
		'text' => 'Invoices',
		'class' => 'picon-view-list-text',
		'href' => pines_url('com_reports', 'invoicesummary'),
		'default' => false,
		'depends' => array(
			'ability' => 'com_reports/summarizeinvoices',
		),
	),
	'sales_totals' => array(
		'description' => 'Lists all sales for a given timeframe.',
		'text' => 'Sales Totals',
		'class' => 'picon-view-calendar-timeline',
		'href' => pines_url('com_reports', 'reportsales'),
		'default' => false,
		'depends' => array(
			'ability' => 'com_reports/reportsales',
		),
	),
	'warboard' => array(
		'description' => 'Show the company warboard.',
		'text' => 'Warboard',
		'class' => 'picon-view-list-details',
		'href' => pines_url('com_reports', 'warboard', array('template' => 'tpl_print')),
		'default' => false,
		'depends' => array(
			'ability' => 'com_reports/warboard',
		),
	),
);

?>